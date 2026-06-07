<?php 
ob_start();
?>

<h2>Панель управления директора</h2>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label>Дата с</label>
                <input type="date" name="start_date" class="form-control" value="<?= $_GET['start_date'] ?? date('Y-m-01') ?>">
            </div>
            <div class="col-md-4">
                <label>Дата по</label>
                <input type="date" name="end_date" class="form-control" value="<?= $_GET['end_date'] ?? date('Y-m-d') ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Показать</button>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Общая выручка</h5>
                <p class="card-text display-6"><?= number_format(array_sum(array_column($salesByStore, 'total_revenue')), 2) ?> ₽</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Всего транзакций</h5>
                <p class="card-text display-6"><?= array_sum(array_column($salesByStore, 'transactions_count')) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Продано товаров</h5>
                <p class="card-text display-6"><?= array_sum(array_column($salesByStore, 'total_items')) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5>Динамика продаж</h5></div>
    <div class="card-body"><canvas id="salesChart" height="100"></canvas></div>
</div>

<div class="card">
    <div class="card-header"><h5>Эффективность по магазинам</h5></div>
    <div class="card-body table-responsive">
        <table class="table table-striped">
            <thead><tr><th>Магазин</th><th>Транзакции</th><th>Выручка (₽)</th><th>Товаров продано</th><th>Средний чек (₽)</th></tr></thead>
            <tbody>
                <?php foreach ($salesByStore as $store): ?>
                <tr>
                    <td><?= htmlspecialchars($store['establishment_adress']) ?></td>
                    <td><?= $store['transactions_count'] ?></td>
                    <td><?= number_format($store['total_revenue'], 2) ?></td>
                    <td><?= $store['total_items'] ?></td>
                    <td><?= number_format($store['total_revenue'] / $store['transactions_count'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const chartData = <?php 
    $dates = array_column($salesStats, 'sale_date');
    $amounts = array_column($salesStats, 'total_amount');
    echo json_encode(['dates' => $dates, 'amounts' => $amounts]);
?>;

new Chart(document.getElementById('salesChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: chartData.dates,
        datasets: [{label: 'Выручка (₽)', data: chartData.amounts, borderColor: 'rgb(75, 192, 192)', tension: 0.1}]
    },
    options: {responsive: true, maintainAspectRatio: true}
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
