<?php 
$activePage = 'efficiency';
ob_start();
?>

<h2>Анализ эффективности работы магазинов</h2>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div></div>
    <div class="d-flex gap-2">
        <a href="/admin/export-efficiency-excel?start_date=<?= urlencode($_GET['start_date'] ?? date('Y-m-01')) ?>&end_date=<?= urlencode($_GET['end_date'] ?? date('Y-m-d')) ?>" class="btn btn-success">Экспорт Excel</a>
        <a href="/admin/export-efficiency-csv?start_date=<?= urlencode($_GET['start_date'] ?? date('Y-m-01')) ?>&end_date=<?= urlencode($_GET['end_date'] ?? date('Y-m-d')) ?>" class="btn btn-secondary">Экспорт CSV</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label>Дата с</label>
                <input type="date" name="start_date" class="form-control" value="<?= $_GET['start_date'] ?? date('Y-m-01') ?>">
            </div>
            <div class="col-md-3">
                <label>Дата по</label>
                <input type="date" name="end_date" class="form-control" value="<?= $_GET['end_date'] ?? date('Y-m-d') ?>">
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Показать</button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">Динамика продаж</div>
            <div class="card-body"><canvas id="salesChart" height="200"></canvas></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">Типы оплаты</div>
            <div class="card-body"><canvas id="paymentChart" height="200"></canvas></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Эффективность по магазинам</div>
    <div class="card-body">
        <canvas id="storeChart" height="300"></canvas>
    </div>
</div>

<script>
const salesData = <?php 
    $dates = array_column($salesStats, 'sale_date');
    $amounts = array_column($salesStats, 'total_amount');
    echo json_encode(['dates' => $dates, 'amounts' => $amounts]);
?>;

const paymentData = <?php 
    $types = array_column($paymentStats, 'type');
    $amounts = array_column($paymentStats, 'total_amount');
    echo json_encode(['types' => $types, 'amounts' => $amounts]);
?>;

const storeData = <?php 
    $stores = array_column($salesByStore, 'establishment_adress');
    $revenues = array_column($salesByStore, 'total_revenue');
    echo json_encode(['stores' => $stores, 'revenues' => $revenues]);
?>;

new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {labels: salesData.dates, datasets: [{label: 'Выручка (₽)', data: salesData.amounts, borderColor: '#0d6efd', tension: 0.1}]}
});

new Chart(document.getElementById('paymentChart'), {
    type: 'pie',
    data: {labels: paymentData.types, datasets: [{data: paymentData.amounts, backgroundColor: ['#0d6efd', '#198754', '#ffc107']}]}
});

new Chart(document.getElementById('storeChart'), {
    type: 'bar',
    data: {labels: storeData.stores, datasets: [{label: 'Выручка (₽)', data: storeData.revenues, backgroundColor: '#0d6efd'}]},
    options: {scales: {y: {beginAtZero: true}}}
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
