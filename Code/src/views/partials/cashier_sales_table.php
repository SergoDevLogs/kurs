<?php if (isset($sales) && !empty($sales)): ?>
    <?php foreach ($sales as $sale): ?>
    <tr>
        <td><?= date('d.m.Y H:i', strtotime($sale['bill_timedate'])) ?></td>
        <td><?= number_format($sale['total_amount'], 2) ?></td>
        <td><?= $sale['items_count'] ?></td>
        <td><?= ['Наличные', 'Карта', 'Приложение'][$sale['bill_paytype']] ?? 'Неизвестно' ?></td>
        <td><?= $sale['loyalty_card_number'] ?? '—' ?></td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td colspan="5" class="text-center">Нет данных</td><tr>
<?php endif; ?>
