<?php /* Order summary table shared by the checkout form and confirmation. Expects $lines and $total. */ ?>
<div class="table-wrap">
  <table class="admin-table">
    <caption class="visually-hidden">Order summary</caption>
    <thead>
      <tr>
        <th scope="col">Offering</th>
        <th scope="col">Qty</th>
        <th scope="col" class="num">Price</th>
        <th scope="col" class="num">Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($lines as $line): $product = $line['product']; ?>
        <tr>
          <th scope="row"><?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?></th>
          <td><?= (int) $line['quantity'] ?></td>
          <td class="num">$<?= htmlspecialchars(number_format((float) $product['price'], 2), ENT_QUOTES, 'UTF-8') ?></td>
          <td class="num">$<?= htmlspecialchars(number_format($line['subtotal'], 2), ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <th scope="row" colspan="3">Total</th>
        <td class="num total">$<?= htmlspecialchars(number_format($total, 2), ENT_QUOTES, 'UTF-8') ?></td>
      </tr>
    </tfoot>
  </table>
</div>
