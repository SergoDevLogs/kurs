<?php 
$activePage = 'sales';
ob_start();
?>

<h2>Отчет по продажам</h2>

<div class="d-flex gap-2 mb-3">
    <a href="/cashier/export-sales-excel?start_date=<?= urlencode($_GET['start_date'] ?? date('Y-m-01')) ?>&end_date=<?= urlencode($_GET['end_date'] ?? date('Y-m-d')) ?>" class="btn btn-success">Экспорт Excel</a>
    <a href="/cashier/export-sales-csv?start_date=<?= urlencode($_GET['start_date'] ?? date('Y-m-01')) ?>&end_date=<?= urlencode($_GET['end_date'] ?? date('Y-m-d')) ?>" class="btn btn-secondary">Экспорт CSV</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label>Дата с</label>
                <input type="date" id="startDate" class="form-control" value="<?= $_GET['start_date'] ?? date('Y-m-01') ?>">
            </div>
            <div class="col-md-3">
                <label>Дата по</label>
                <input type="date" id="endDate" class="form-control" value="<?= $_GET['end_date'] ?? date('Y-m-d') ?>">
            </div>
            <div class="col-md-2">
                <label>Записей</label>
                <select id="perPageSelect" class="form-select">
                    <option value="10" <?= ($_GET['per_page'] ?? 10) == 10 ? 'selected' : '' ?>>10</option>
                    <option value="25" <?= ($_GET['per_page'] ?? 10) == 25 ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= ($_GET['per_page'] ?? 10) == 50 ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= ($_GET['per_page'] ?? 10) == 100 ? 'selected' : '' ?>>100</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary" id="applyFilters">Применить</button>
            </div>
        </div>
        <input type="hidden" id="sortField" value="<?= $_GET['sort'] ?? 'bill_timedate' ?>">
        <input type="hidden" id="sortOrder" value="<?= $_GET['order'] ?? 'DESC' ?>">
        <input type="hidden" id="currentPage" value="1">
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

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th><a href="#" class="sort-link text-white" data-sort="bill_timedate">Дата</a></th>
                <th><a href="#" class="sort-link text-white" data-sort="total_amount">Сумма (₽)</a></th>
                <th><a href="#" class="sort-link text-white" data-sort="items_count">Кол-во товаров</a></th>
                <th>Тип оплаты</th>
                <th>Карта лояльности</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php include __DIR__ . '/../partials/cashier_sales_table.php'; ?>
        </tbody>
    </table>
</div>

<div id="paginationContainer">
    <?php include __DIR__ . '/../partials/pagination.php'; ?>
</div>

<script>
let salesChart, paymentChart;

function updateCharts(stats, paymentStats) {
    if (salesChart) salesChart.destroy();
    if (paymentChart) paymentChart.destroy();
    
    const dates = stats.map(s => s.sale_date);
    const amounts = stats.map(s => s.total_amount);
    
    salesChart = new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {labels: dates, datasets: [{label: 'Выручка (₽)', data: amounts, borderColor: '#0d6efd', tension: 0.1}]}
    });
    
    paymentChart = new Chart(document.getElementById('paymentChart'), {
        type: 'pie',
        data: {labels: paymentStats.map(p => p.type), datasets: [{data: paymentStats.map(p => p.total_amount), backgroundColor: ['#0d6efd', '#198754', '#ffc107']}]}
    });
}

function fetchTableData() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const perPage = document.getElementById('perPageSelect').value;
    const sort = document.getElementById('sortField').value;
    const order = document.getElementById('sortOrder').value;
    const page = document.getElementById('currentPage').value;
    
    fetch(`${window.location.pathname}?start_date=${startDate}&end_date=${endDate}&per_page=${perPage}&sort=${sort}&order=${order}&page=${page}&ajax=1`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('tableBody').innerHTML = data.tableHtml;
            document.getElementById('paginationContainer').innerHTML = data.paginationHtml;
            updateCharts(data.stats, data.paymentStats);
            attachSortHandlers();
            attachPageHandlers();
        });
}

function attachSortHandlers() {
    document.querySelectorAll('.sort-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const sort = this.dataset.sort;
            const currentSort = document.getElementById('sortField').value;
            const currentOrder = document.getElementById('sortOrder').value;
            let newOrder = 'DESC';
            if (currentSort === sort && currentOrder === 'DESC') newOrder = 'ASC';
            document.getElementById('sortField').value = sort;
            document.getElementById('sortOrder').value = newOrder;
            document.getElementById('currentPage').value = '1';
            fetchTableData();
        });
    });
}

function attachPageHandlers() {
    document.querySelectorAll('.page-link-ajax').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = this.dataset.page;
            if (page && !this.parentElement.classList.contains('disabled')) {
                document.getElementById('currentPage').value = page;
                fetchTableData();
            }
        });
    });
}

document.getElementById('applyFilters').addEventListener('click', () => {
    document.getElementById('currentPage').value = '1';
    fetchTableData();
});

document.getElementById('perPageSelect').addEventListener('change', () => {
    document.getElementById('currentPage').value = '1';
    fetchTableData();
});

updateCharts(<?= json_encode($stats) ?>, <?= json_encode($paymentStats) ?>);
attachSortHandlers();
attachPageHandlers();
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
