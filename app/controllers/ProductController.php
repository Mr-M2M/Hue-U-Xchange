<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\Product;
use App\Models\DuplicateProductException;

/**
 * Handles the public Offerings catalog and the product-management
 * (Create/Read/Update/Deactivate) area. Reads and normalizes request
 * data, validates it, calls the Product model, and selects a view -
 * it never builds SQL and never prints HTML itself.
 */
class ProductController extends Controller
{
    /** Public catalog: GET /offerings */
    public function offerings()
    {
        $products = array();
        $catalogError = false;

        try {
            $pdo = get_db_connection();
            $productModel = new Product($pdo);
            $products = $productModel->getActive();
        } catch (\Throwable $e) {
            error_log('Offerings page could not load products: ' . $e->getMessage());
            $catalogError = true;
        }

        $this->render('products/offerings', array(
            'pageTitle'    => 'Offerings - Hue U Xchange',
            'products'     => $products,
            'catalogError' => $catalogError,
        ));
    }

    /** Product-management list: GET /products */
    public function manage()
    {
        $products = array();
        $loadError = false;

        try {
            $pdo = get_db_connection();
            $productModel = new Product($pdo);
            $products = $productModel->getAll();
        } catch (\Throwable $e) {
            error_log('Product management list could not load: ' . $e->getMessage());
            $loadError = true;
        }

        $this->render('products/manage', array(
            'pageTitle' => 'Manage Products - Hue U Xchange',
            'products'  => $products,
            'loadError' => $loadError,
        ));
    }

    /** Create a product: GET shows the form, POST validates and saves. */
    public function create()
    {
        $errors = array();
        $values = array('is_active' => 1, 'display_order' => 0);
        $dbError = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            list($values, $errors) = Product::validateInput($_POST);

            if (empty($errors)) {
                try {
                    $pdo = get_db_connection();
                    $productModel = new Product($pdo);
                    $productModel->create($values);
                    Flash::set('success', 'Product created successfully.');
                    $this->redirect('products');
                } catch (DuplicateProductException $e) {
                    $errors['product_name'] = $e->getMessage();
                } catch (\Throwable $e) {
                    error_log('Product create failed: ' . $e->getMessage());
                    $dbError = true;
                }
            }
        }

        $this->render('products/create', array(
            'pageTitle'   => 'Add Product - Hue U Xchange',
            'formAction'  => \App\Core\Url::to('products/create'),
            'submitLabel' => 'Create Product',
            'values'      => $values,
            'errors'      => $errors,
            'dbError'     => $dbError,
        ));
    }

    /** Edit a product: GET loads and prepopulates, POST validates and saves. */
    public function edit()
    {
        $errors = array();
        $dbError = false;
        $notFound = false;
        $invalidId = false;
        $values = array();

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        }

        if (!$id || $id < 1) {
            $invalidId = true;
        } else {
            try {
                $pdo = get_db_connection();
                $productModel = new Product($pdo);

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    list($values, $errors) = Product::validateInput($_POST);

                    if (empty($errors)) {
                        if (!$productModel->exists($id)) {
                            $notFound = true;
                        } else {
                            try {
                                $productModel->update($id, $values);
                                Flash::set('success', 'Product updated successfully.');
                                $this->redirect('products');
                            } catch (DuplicateProductException $e) {
                                $errors['product_name'] = $e->getMessage();
                            }
                        }
                    }
                } else {
                    $existing = $productModel->find($id);
                    if ($existing === null) {
                        $notFound = true;
                    } else {
                        $values = $existing;
                    }
                }
            } catch (\Throwable $e) {
                error_log('Product edit failed: ' . $e->getMessage());
                $dbError = true;
            }
        }

        $this->render('products/edit', array(
            'pageTitle'   => 'Edit Product - Hue U Xchange',
            'id'          => $id,
            'formAction'  => \App\Core\Url::to('products/edit', array('id' => (int) $id)),
            'submitLabel' => 'Save Changes',
            'values'      => $values,
            'errors'      => $errors,
            'dbError'     => $dbError,
            'notFound'    => $notFound,
            'invalidId'   => $invalidId,
        ));
    }

    /**
     * Deactivate/reactivate a product: GET shows a confirmation screen
     * only (no mutation); only a confirmed POST changes the record.
     */
    public function delete()
    {
        $invalidId = false;
        $notFound = false;
        $dbError = false;
        $product = null;

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        }

        if (!$id || $id < 1) {
            $invalidId = true;
        } else {
            try {
                $pdo = get_db_connection();
                $productModel = new Product($pdo);
                $product = $productModel->find($id);

                if ($product === null) {
                    $notFound = true;
                } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    // Only a POST request (the confirmation form) may
                    // change the product's active status - a plain GET
                    // only renders the confirmation screen below.
                    $confirmed = (isset($_POST['confirm']) ? $_POST['confirm'] : '') === 'yes';
                    if ($confirmed) {
                        $goingActive = !((int) $product['is_active'] === 1);
                        $productModel->setActive($id, $goingActive);
                        Flash::set('success', $goingActive
                            ? 'Product reactivated. It now appears on the public Offerings page.'
                            : 'Product deactivated. It no longer appears on the public Offerings page.');
                        $this->redirect('products');
                    }
                }
            } catch (\Throwable $e) {
                error_log('Product deactivate/activate failed: ' . $e->getMessage());
                $dbError = true;
            }
        }

        $this->render('products/delete_confirm', array(
            'pageTitle' => 'Deactivate Product - Hue U Xchange',
            'product'   => $product,
            'invalidId' => $invalidId,
            'notFound'  => $notFound,
            'dbError'   => $dbError,
        ));
    }
}
