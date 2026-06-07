<?php 
$activePage = 'transportations';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Перемещения товаров</h2>
    <?php if ($_SESSION['user_role'] === 'admin'): ?>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
        + Новое перемещение
    </button>
    <?php endif; ?>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3" id="filterForm">
            <div class="col-md-3">
                <label>Дата с</label>
                <input type="date" name="start_date" class="form-control" value="<?= $_GET['start_date'] ?? date('Y-m-01') ?>">
            </div>
            <div class="col-md-3">
                <label>Дата по</label>
                <input type="date" name="end_date" class="form-control" value="<?= $_GET['end_date'] ?? date('Y-m-d') ?>">
            </div>
            <div class="col-md-2">
                <label>Записей</label>
                <select name="per_page" class="form-select" id="perPageSelect">
                    <option value="10" <?= ($_GET['per_page'] ?? 10) == 10 ? 'selected' : '' ?>>10</option>
                    <option value="25" <?= ($_GET['per_page'] ?? 10) == 25 ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= ($_GET['per_page'] ?? 10) == 50 ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= ($_GET['per_page'] ?? 10) == 100 ? 'selected' : '' ?>>100</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="button" class="btn btn-primary" id="applyFilters">Применить</button>
            </div>
            <input type="hidden" name="sort" id="sortField" value="<?= $_GET['sort'] ?? 'transportation_date' ?>">
            <input type="hidden" name="order" id="sortOrder" value="<?= $_GET['order'] ?? 'DESC' ?>">
            <input type="hidden" name="page" id="currentPage" value="1">
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th><a href="#" class="sort-link text-white" data-sort="transportation_date">Дата</a></th>
                <th><a href="#" class="sort-link text-white" data-sort="product_name">Товар</a></th>
                <th>Откуда</th>
                <th>Куда</th>
                <th>Тип</th>
                <th><a href="#" class="sort-link text-white" data-sort="transportation_distance">Расстояние (км)</a></th>
                <th>Статус</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php include __DIR__ . '/../partials/transportations_table.php'; ?>
        </tbody>
    </table>
</div>

<div id="paginationContainer">
    <?php include __DIR__ . '/../partials/pagination.php'; ?>
</div>

<?php if ($_SESSION['user_role'] === 'admin'): ?>
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Новое перемещение</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/merchandiser/create-transportation">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Товар</label>
                        <select name="product_article" class="form-select" required>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product['product_article'] ?>"><?= htmlspecialchars($product['product_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Откуда</label>
                        <select name="from_address" class="form-select" required>
                            <?php foreach ($establishments as $est): ?>
                                <option value="<?= htmlspecialchars($est['establishment_adress']) ?>"><?= htmlspecialchars($est['establishment_adress']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Куда</label>
                        <select name="to_address" class="form-select" required>
                            <?php foreach ($establishments as $est): ?>
                                <option value="<?= htmlspecialchars($est['establishment_adress']) ?>"><?= htmlspecialchars($est['establishment_adress']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Тип перемещения</label>
                        <select name="type" class="form-select">
                            <option value="0">Внешняя</option>
                            <option value="1">Внутренняя</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Расстояние (км)</label>
                        <input type="number" name="distance" class="form-control" step="0.001" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Создать</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
const baseUrl = '/merchandiser/transportations';

function fetchTableData() {
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate = document.querySelector('input[name="end_date"]').value;
    const perPage = document.getElementById('perPageSelect').value;
    const sort = document.getElementById('sortField').value;
    const order = document.getElementById('sortOrder').value;
    const page = document.getElementById('currentPage').value;
    
    fetch(`${baseUrl}?start_date=${startDate}&end_date=${endDate}&per_page=${perPage}&sort=${sort}&order=${order}&page=${page}&ajax=1`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('tableBody').innerHTML = data.tableHtml;
            document.getElementById('paginationContainer').innerHTML = data.paginationHtml;
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
            if (currentSort === sort && currentOrder === 'DESC') {
                newOrder = 'ASC';
            }
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

attachSortHandlers();
attachPageHandlers();
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
