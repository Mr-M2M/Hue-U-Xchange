#!/usr/bin/env python3
"""
Hue U Xchange - HTTP regression tests.

Drives a running copy of the application over HTTP exactly like a browser
would (sessions, cookies, POST forms, redirects) and checks the result of
each major flow. Database state is verified through the MySQL/MariaDB
command-line client.

Usage:
    python3 tests/http_regression.py --base http://127.0.0.1:8000 \
        [--fail-base http://127.0.0.1:8001] [--mysql "mariadb -uUSER -pPASS"] \
        [--json results.json]

--fail-base must point at a second copy of the app configured with an
unreachable database (used only for the database-failure test).

Requires: Python 3, requests. The script creates one clearly named test
product ("Regression Test Offering ...") and deactivates it at the end;
the five core offerings are restored to active before exiting.
"""
import argparse
import html
import json
import re
import subprocess
import sys
import time

import requests

RESULTS = []


def record(test_id, area, description, passed, detail=""):
    RESULTS.append({
        "id": test_id,
        "area": area,
        "description": description,
        "status": "Pass" if passed else "Fail",
        "detail": detail,
    })
    print(("PASS " if passed else "FAIL ") + test_id + "  " + description + ("  -- " + detail if detail else ""))


def sql(mysql_cmd, query):
    out = subprocess.run(mysql_cmd.split() + ["-N", "-B", "hue_u_xchange", "-e", query],
                         capture_output=True, text=True)
    return out.stdout.strip() if out.returncode == 0 else ""


def token(page_html):
    m = re.search(r'name="csrf_token" value="([^"]+)"', page_html)
    return m.group(1) if m else ""


def php_errors(text):
    return re.search(r"(Warning|Notice|Fatal error|Deprecated|Parse error)</b>:|Stack trace|PDOException", text)


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--base", default="http://127.0.0.1:8000")
    ap.add_argument("--fail-base", default="")
    ap.add_argument("--mysql", default="mariadb")
    ap.add_argument("--json", default="")
    a = ap.parse_args()
    B = a.base.rstrip("/") + "/index.php"
    s = requests.Session()
    all_pages = []

    def get(route, sess=s, **params):
        params = dict(params)
        params["route"] = route
        r = sess.get(B, params=params, allow_redirects=True, timeout=15)
        all_pages.append(r.text)
        return r

    def post(route, data, sess=s, **params):
        params = dict(params)
        params["route"] = route
        r = sess.post(B, params=params, data=data, allow_redirects=True, timeout=15)
        all_pages.append(r.text)
        return r

    # ---- Environment / database ----
    r = get("")
    record("ENV-01", "Environment", "Web server responds to the front controller", r.status_code == 200)
    try:
        n = int(sql(a.mysql, "SELECT COUNT(*) FROM products"))
        record("DB-01", "Database", "MySQL/MariaDB is reachable and products table exists", n >= 5, "%d rows" % n)
    except Exception as e:  # noqa
        record("DB-01", "Database", "MySQL/MariaDB is reachable and products table exists", False, str(e)[:80])

    r = get("offerings")
    record("DB-02", "Database", "Application reads products through PDO (Offerings lists Divine Hoodie)",
           "Divine Hoodie" in r.text)

    if a.fail_base:
        fr = requests.get(a.fail_base.rstrip("/") + "/index.php", params={"route": "offerings"}, timeout=15)
        safe = "could not be loaded" in fr.text and not re.search(r"SQLSTATE|PDOException|password|root@|Access denied", fr.text)
        record("DB-03", "Database failure", "Unreachable database shows a safe visitor message with no technical detail", safe)

    # ---- Pages ----
    record("UI-01", "Pages", "Home page loads with Shining Light Army branding",
           get("").status_code == 200 and "Shining Light Army" in r.text or "Shining Light Army" in get("").text)
    ab = get("about")
    record("UI-02", "Pages", "About page loads", ab.status_code == 200 and "About Hue U Xchange" in ab.text)
    lore = get("lore")
    record("UI-03", "Pages", "The Curing Process lore / music page loads",
           lore.status_code == 200 and "The Curing Process" in lore.text and "Coming Soon" in lore.text)

    # ---- Offerings ----
    off = get("offerings").text
    core = ["Divine Hoodie", "Aura Oils", "Ritual Kit", "Music EP", "Access Code"]
    record("CRUD-R-01", "Product Read", "Offerings shows all five active core products", all(c in off for c in core))
    order_ok = [off.find(c) for c in core] == sorted(off.find(c) for c in core)
    record("CRUD-R-02", "Product Read", "Offerings are displayed in display order", order_ok)
    record("CRUD-R-03", "Product Read", "Prices are formatted as currency ($44.00)", "$44.00" in off and "$11.00" in off)

    man = get("products")
    record("CRUD-R-04", "Product Read", "Product-management page loads and lists all products",
           man.status_code == 200 and all(c in man.text for c in core))

    # ---- Create validation ----
    cform = get("products/create").text
    t = token(cform)
    bad = post("products/create", {"csrf_token": t, "product_name": "", "symbolic_description": "",
                                   "price": "abc", "display_order": "x", "is_active": "1"}).text
    record("CRUD-V-01", "Product validation", "Empty name/description, non-numeric price and bad display order are rejected",
           "Product name is required" in bad and "Symbolic description is required" in bad
           and "Price must be" in bad and "Display order must be" in bad)
    t = token(bad)
    neg = post("products/create", {"csrf_token": t, "product_name": "N" * 121, "symbolic_description": "ok desc",
                                   "price": "-5", "display_order": "-1", "is_active": "1"}).text
    record("CRUD-V-02", "Product validation", "Over-long name, negative price and negative display order are rejected",
           "between 2 and 120" in neg and "cannot be negative" in neg and "Display order must be" in neg)
    t = token(neg)
    sci = post("products/create", {"csrf_token": t, "product_name": "Sci Price", "symbolic_description": "ok desc",
                                   "price": "1e3", "display_order": "1", "is_active": "1",
                                   "image_reference": "../../etc/passwd"}).text
    record("CRUD-V-03", "Product validation", "Scientific-notation price and unsafe image path are rejected",
           "Price must be" in sci and "Image reference" in sci and "may only contain" in sci)

    # ---- Create ----
    stamp = str(int(time.time()))
    name = "Regression Test Offering " + stamp
    xss = "<script>alert('xss')</script> Light"
    t = token(sci)
    cr = post("products/create", {"csrf_token": t, "product_name": name, "symbolic_description": xss,
                                  "price": "12.50", "display_order": "9", "is_active": "1",
                                  "image_reference": "images/placeholder.png"})
    created = "Product created" in cr.text and name in cr.text
    pid = sql(a.mysql, "SELECT product_id FROM products WHERE product_name='%s'" % name)
    record("CRUD-C-01", "Product Create", "Valid product is saved to MySQL and confirmation message shown",
           created and pid.isdigit(), "product_id=%s" % pid)
    off2 = get("offerings").text
    record("SEC-01", "Security", "Script input is stored but rendered escaped (no raw <script>)",
           "<script>alert('xss')</script>" not in off2 and "&lt;script&gt;" in off2)

    # ---- Duplicate name ----
    t = token(get("products/create").text)
    dup = post("products/create", {"csrf_token": t, "product_name": name, "symbolic_description": "dup",
                                   "price": "1", "display_order": "1", "is_active": "1"}).text
    record("CRUD-V-04", "Product validation", "Duplicate product name is rejected with a clear message", "already exists" in dup)

    # ---- Update ----
    ed = get("products/edit", id=pid).text
    record("CRUD-U-01", "Product Update", "Edit form loads with existing values", name in html.unescape(ed) and "12.50" in ed)
    t = token(ed)
    up = post("products/edit", {"csrf_token": t, "id": pid, "product_name": name + " Updated",
                                "symbolic_description": "Updated light description", "price": "15.75",
                                "display_order": "10", "is_active": "1", "image_reference": "images/placeholder.png"},
              id=pid)
    dbrow = sql(a.mysql, "SELECT product_name, price FROM products WHERE product_id=%s" % pid)
    record("CRUD-U-02", "Product Update", "Valid update changes the MySQL record and management list",
           "Product updated" in up.text and "15.75" in dbrow and (name + " Updated") in up.text, dbrow.replace("\t", " | "))
    record("CRUD-U-03", "Product Update", "Updated product appears on the public Offerings page",
           "Updated light description" in get("offerings").text)
    t = token(get("products/edit", id=pid).text)
    upbad = post("products/edit", {"csrf_token": t, "id": pid, "product_name": "", "symbolic_description": "x y",
                                   "price": "-1", "display_order": "1", "is_active": "1"}, id=pid).text
    record("CRUD-U-04", "Product Update", "Invalid update values are rejected and record is unchanged",
           "Product name is required" in upbad and "15.75" in sql(a.mysql, "SELECT price FROM products WHERE product_id=%s" % pid))
    record("CRUD-U-05", "Product Update", "Invalid product ID on edit is rejected",
           "valid product ID" in get("products/edit", id="abc").text and "could not be found" in get("products/edit", id="999999").text)

    # ---- Cart ----
    c = requests.Session()
    ot = get("offerings", sess=c).text
    t = token(ot)
    r1 = post("cart/add", {"csrf_token": t, "product_id": "1", "quantity": "2"}, sess=c).text
    record("CART-01", "Cart", "Valid product is added to the cart", "Added" in r1 and "Divine Hoodie" in r1)
    t = token(r1)
    r2 = post("cart/add", {"csrf_token": t, "product_id": "4", "quantity": "3"}, sess=c).text
    t = token(r2)
    r3 = post("cart/add", {"csrf_token": t, "product_id": "999999", "quantity": "1"}, sess=c).text
    record("CART-02", "Cart", "Missing product ID is rejected by the cart", "no longer available" in r3)
    t = token(r3)
    r4 = post("cart/add", {"csrf_token": t, "product_id": "1", "quantity": "0"}, sess=c).text
    t = token(r4)
    r5 = post("cart/add", {"csrf_token": t, "product_id": "1", "quantity": "-3"}, sess=c).text
    t = token(r5)
    r6 = post("cart/add", {"csrf_token": t, "product_id": "1", "quantity": "500"}, sess=c).text
    record("CART-03", "Cart", "Quantities of 0, negative, and 500 are rejected",
           "at least 1" in r4 and "whole number" in r5 and "cannot exceed" in r6)
    t = token(r6)
    r6b = post("cart/add", {"csrf_token": t, "product_id": "1", "quantity": "24"}, sess=c).text
    record("CART-04", "Cart", "Repeated adds cannot push one line past the 25-item limit", "cannot exceed" in r6b)
    t = token(r6b)
    r7 = post("cart/add", {"csrf_token": t, "product_id": "1", "quantity": "1", "price": "0.01"}, sess=c).text
    record("CART-05", "Cart", "Client-supplied price is ignored (trusted MySQL price used)",
           "$44.00" in r7 and "0.01" not in r7)
    cart = get("cart", sess=c).text
    # hoodie 3 x 44 = 132, EP 3 x 11 = 33 -> 165
    record("CART-06", "Cart calculations", "Line subtotals are correct (3 x $44.00 = $132.00; 3 x $11.00 = $33.00)",
           "$132.00" in cart and "$33.00" in cart)
    record("CART-07", "Cart calculations", "Order total is correct ($165.00)", "$165.00" in cart)
    get("about", sess=c)
    get("offerings", sess=c)
    record("CART-08", "Cart", "Cart persists across navigation and refresh", "$165.00" in get("cart", sess=c).text)
    t = token(cart)
    u = post("cart/update", {"csrf_token": t, "product_id": "4", "quantity": "1"}, sess=c).text
    record("CART-09", "Cart", "Quantity update works and totals recalculate ($143.00)", "Cart updated" in u and "$143.00" in u)
    t = token(u)
    u2 = post("cart/update", {"csrf_token": t, "product_id": "4", "quantity": "abc"}, sess=c).text
    record("CART-10", "Cart", "Invalid quantity on update is rejected", "whole number" in u2 and "$143.00" in u2)
    t = token(u2)
    rm = post("cart/remove", {"csrf_token": t, "product_id": "4"}, sess=c).text
    record("CART-11", "Cart", "Item removal works", "removed" in rm and "Music EP" not in rm.split("<main")[-1].split("Your Cart")[-1].split("Total")[0])
    record("CART-12", "Cart", "Direct GET to cart/add does not change the cart", "$132.00" in get("cart/add", sess=c, product_id=5, quantity=1).text
           and "Access Code" not in get("cart", sess=c).text.split("Your Cart")[-1])
    t = token(get("cart", sess=c).text)
    csrf = post("cart/add", {"csrf_token": "forged", "product_id": "5", "quantity": "1"}, sess=c).text
    record("SEC-02", "Security", "POST with a missing/forged form token is refused", "expired" in csrf and "Access Code" not in csrf.split("Your Cart")[-1])

    # ---- Checkout ----
    ck = get("checkout", sess=c).text
    record("CHECKOUT-01", "Checkout", "Checkout form shows an accurate order summary from the cart",
           "Order Summary" in ck and "$132.00" in ck and "Divine Hoodie" in ck)
    t = token(ck)
    e1 = post("checkout/submit", {"csrf_token": t, "name": "", "email": "light@example.com", "signature": ""}, sess=c).text
    record("CHECKOUT-02", "Checkout validation", "Empty name is rejected with a field-level message", "Name is required" in e1)
    t = token(e1)
    e2 = post("checkout/submit", {"csrf_token": t, "name": "Donny <b>D</b>", "email": "not-an-email", "signature": "Flame"}, sess=c).text
    record("CHECKOUT-03", "Checkout validation", "Invalid email is rejected and safe values are preserved (escaped)",
           "valid email" in e2 and "Donny &lt;b&gt;D&lt;/b&gt;" in e2)
    t = token(e2)
    e3 = post("checkout/submit", {"csrf_token": t, "name": "L" * 121, "email": "light@example.com", "signature": "Nope"}, sess=c).text
    record("CHECKOUT-04", "Checkout validation", "Over-long name and an unknown Energy Signature are rejected",
           "120 characters" in e3 and "Energy Signature" in e3 and "not a recognized" in e3)
    record("CHECKOUT-05", "Checkout validation", "Cart is NOT cleared by a failed checkout", "$132.00" in get("cart", sess=c).text)
    t = token(get("checkout", sess=c).text)
    ok = post("checkout/submit", {"csrf_token": t, "name": "Nivlema O'Sage", "email": "lightbearer@example.com", "signature": ""}, sess=c)
    record("CHECKOUT-06", "Checkout", "Valid information (Energy Signature left blank) is accepted",
           ok.status_code == 200 and "route=confirm" in ok.url and "You are now a" in ok.text)
    record("CHECKOUT-07", "Confirmation", "Confirmation shows the name safely and an accurate order summary",
           "Nivlema O&#039;Sage" in ok.text and "$132.00" in ok.text and "Divine Hoodie" in ok.text)
    record("CHECKOUT-08", "Confirmation", "Cart is cleared after successful checkout", "Your cart is empty" in get("cart", sess=c).text)
    again = post("checkout/submit", {"csrf_token": t, "name": "Nivlema", "email": "lightbearer@example.com"}, sess=c).text
    record("CHECKOUT-09", "Checkout", "Re-submitting the same checkout form is controlled (no second order)",
           "You are now a" not in again)
    record("CHECKOUT-10", "Checkout", "Checkout with an empty cart is blocked", "cart is empty" in get("checkout", sess=c).text)
    record("CHECKOUT-11", "Confirmation", "Refreshing/reopening confirmation does not replay the order",
           "You are now a" not in get("confirm", sess=c).text)

    # ---- Delete / deactivate ----
    g = requests.get(B, params={"route": "products/delete", "id": pid}, timeout=15).text
    still = sql(a.mysql, "SELECT is_active FROM products WHERE product_id=%s" % pid)
    record("CRUD-D-01", "Product Deactivate", "GET request shows a confirmation step and does not change the record",
           "Please confirm" in g and still == "1")
    t = token(get("products/delete", id=pid).text)
    d = post("products/delete", {"csrf_token": t, "id": pid, "confirm": "yes"})
    record("CRUD-D-02", "Product Deactivate", "Confirmed POST deactivates the product (prepared statement) with a clear message",
           "deactivated" in d.text and sql(a.mysql, "SELECT is_active FROM products WHERE product_id=%s" % pid) == "0")
    record("CRUD-D-03", "Product Deactivate", "Deactivated product no longer appears publicly but stays in management list",
           (name + " Updated") not in get("offerings").text and (name + " Updated") in get("products").text)
    c2 = requests.Session()
    t = token(get("offerings", sess=c2).text)
    rin = post("cart/add", {"csrf_token": t, "product_id": pid, "quantity": "1"}, sess=c2).text
    record("CART-13", "Cart", "Inactive product is rejected by the cart", "no longer available" in rin)
    t = token(get("products/create", sess=s).text)
    inv = post("products/delete", {"csrf_token": t, "id": "abc", "confirm": "yes"}).text
    record("CRUD-D-04", "Product Deactivate", "Invalid product ID is rejected", "valid product ID" in inv)

    # ---- SQL injection ----
    sqli = get("products/edit", id="1 OR 1=1").text
    t = token(get("offerings", sess=c2).text)
    sq2 = post("cart/add", {"csrf_token": t, "product_id": "1; DROP TABLE products", "quantity": "1"}, sess=c2).text
    cnt = sql(a.mysql, "SELECT COUNT(*) FROM products")
    record("SEC-03", "Security", "SQL-injection strings in IDs are rejected and the table is intact",
           "valid product ID" in sqli and cnt.isdigit() and int(cnt) >= 5)

    # ---- Routes / navigation ----
    nf = requests.get(B, params={"route": "does-not-exist"}, timeout=15)
    record("NAV-01", "Invalid routes", "Unknown route returns 404 Not Found page", nf.status_code == 404 and "Page Not Found" in nf.text)
    home = get("").text
    links = ["route=offerings", "route=cart", "route=about", "route=lore", "route=products"]
    record("NAV-02", "Navigation", "Main navigation links to every section", all(l in home for l in links))
    status_ok = all(get(rt).status_code == 200 for rt in ["", "home", "about", "lore", "offerings", "cart", "products", "products/create"])
    record("NAV-03", "Navigation", "Every navigation target returns HTTP 200", status_ok)
    record("NAV-04", "Navigation", "Current page is marked with aria-current", 'aria-current="page"' in get("about").text)

    # ---- Accessibility basics (markup checks) ----
    ck_html = home + get("offerings").text
    record("A11Y-01", "Accessibility", "Skip link, lang attribute, and single h1 present on pages",
           "skip-link" in home and '<html lang="en">' in home and home.count("<h1") == 1)
    imgs = re.findall(r"<img[^>]*>", ck_html)
    record("A11Y-02", "Accessibility", "Every image has an alt attribute", all("alt=" in i for i in imgs), "%d images" % len(imgs))

    # ---- Clean-up / global ----
    errs = [p for p in all_pages if php_errors(p)]
    record("SEC-04", "Error handling", "No PHP warnings, notices, fatal errors, or stack traces in any response", not errs,
           "%d responses checked" % len(all_pages))

    sql(a.mysql, "UPDATE products SET is_active=1 WHERE product_name IN ('Divine Hoodie','Aura Oils','Ritual Kit','Music EP','Access Code')")
    passed = sum(1 for r in RESULTS if r["status"] == "Pass")
    print("\n%d/%d passed" % (passed, len(RESULTS)))
    if a.json:
        with open(a.json, "w") as f:
            json.dump(RESULTS, f, indent=2)
    return 0 if passed == len(RESULTS) else 1


if __name__ == "__main__":
    sys.exit(main())
