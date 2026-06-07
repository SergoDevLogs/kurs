<?php if (isset($transportations) && !empty($transportations)): ?>
    <?php foreach ($transportations as $trans): ?>
    <tr>
        <td><?= date('d.m.Y', strtotime($trans['transportation_date'])) ?></td>
        <td><?= htmlspecialchars($trans['product_name']) ?></td>
        <td><?= htmlspecialchars($trans['establishment_adress_from']) ?></td>
        <td><?= htmlspecialchars($trans['establishment_adress_to']) ?></td>
        <td><?= $trans['transportation_type'] == 0 ? 'Внешняя' : 'Внутренняя' ?></td>
        <td><?= $trans['transportation_distance'] ?></td>
        <td>
            <?php 
            $statuses = ['Неизвестно', 'В пути', 'Доставлено'];
            $status = $statuses[$trans['transportation_status']] ?? 'Неизвестно';
            $badge = $trans['transportation_status'] == 2 ? 'success' : ($trans['transportation_status'] == 1 ? 'warning' : 'secondary');
            echo "<span class='badge bg-$badge'>$status</span>";
            ?>
        </td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td colspan="7" class="text-center">Нет данных</td></tr>
<?php endif; ?>
