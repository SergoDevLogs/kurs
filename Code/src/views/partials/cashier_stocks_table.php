<?php if (isset($stocks) && !empty($stocks)): ?>
    <?php foreach ($stocks as $stock): ?>
    <tr>
        <td><?= htmlspecialchars($stock['product_name']) ?></td>
        <td><?= $stock['quantity'] ?></td>
        <td><?= number_format($stock['product_selfcost'], 2) ?></td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td colspan="3" class="text-center">Нет данных</td><tr>
<?php endif; ?>
