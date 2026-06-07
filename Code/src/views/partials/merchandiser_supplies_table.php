<?php if (isset($supplies) && !empty($supplies)): ?>
    <?php foreach ($supplies as $supply): ?>
    <tr>
        <td><?= date('d.m.Y', strtotime($supply['supply_date_send'])) ?></td>
        <td><?= htmlspecialchars($supply['establishment_adress']) ?></td>
        <td><?= htmlspecialchars($supply['supplier_name']) ?></td>
        <td><?= $supply['total_quantity'] ?></td>
        <td><?= number_format($supply['total_cost'], 2) ?></td>
        <td>
            <?php 
            $statuses = ['Неизвестно', 'Отправлена', 'Доставлена'];
            $status = $statuses[$supply['supply_state']] ?? 'Неизвестно';
            $badge = $supply['supply_state'] == 2 ? 'success' : ($supply['supply_state'] == 1 ? 'warning' : 'secondary');
            echo "<span class='badge bg-$badge'>$status</span>";
            ?>
        </td>
        <td>
            <?php if ($supply['supply_state'] == 1): ?>
            <button class="btn btn-sm btn-success update-status" data-id="<?= $supply['supply_id'] ?>" data-status="2">Доставлена</button>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td colspan="7" class="text-center">Нет данных</td></tr>
<?php endif; ?>

<script>
document.querySelectorAll('.update-status').forEach(btn => {
    btn.addEventListener('click', function() {
        const supplyId = this.dataset.id;
        const status = this.dataset.status;
        fetch('/merchandiser/update-supply-status', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `supply_id=${supplyId}&status=${status}`
        }).then(() => {
            document.getElementById('applyFilters').click();
        });
    });
});
</script>
