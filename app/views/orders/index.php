    <section class="intro">
      <h1>Order History</h1>
      <p class="tagline">Completed initiations saved by checkout. Each order belongs to one customer and lists the offerings it contains.</p>
      <p><a href="<?= \App\Core\Url::to('products') ?>">Back to Manage Products</a></p>

      <?php if ($loadError): ?>
        <p class="notice error" role="alert"><strong>Error:</strong> The order history could not be loaded right now. Please try again shortly.</p>
      <?php elseif (empty($orders)): ?>
        <p class="notice">No orders have been placed yet. Completed checkouts will appear here.</p>
      <?php else: ?>
        <div class="table-wrap">
        <table class="admin-table">
          <caption class="visually-hidden">Saved orders, newest first</caption>
          <thead>
            <tr>
              <th scope="col">Reference</th>
              <th scope="col">Customer</th>
              <th scope="col" class="num">Items</th>
              <th scope="col" class="num">Total</th>
              <th scope="col" class="hide-sm">Signature</th>
              <th scope="col" class="hide-sm">Placed</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $order): ?>
              <tr>
                <th scope="row"><?= htmlspecialchars($order['order_reference'], ENT_QUOTES, 'UTF-8') ?></th>
                <td><?= htmlspecialchars($order['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="num"><?= (int) $order['item_count'] ?></td>
                <td class="num">$<?= htmlspecialchars(number_format((float) $order['order_total'], 2), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="hide-sm"><?= $order['energy_signature'] !== null ? htmlspecialchars($order['energy_signature'], ENT_QUOTES, 'UTF-8') : '&mdash;' ?></td>
                <td class="hide-sm"><?= htmlspecialchars(date('M j, Y g:i A', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      <?php endif; ?>
    </section>
