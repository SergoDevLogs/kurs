<?php 
$activePage = 'stocks';
ob_start();
?>

<h2>Остатки товаров по всем магазинам</h2>

<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-2">
                <label>Записей</label>
                <select class="form-select" id="perPageSelect">
                    <option value="10" <?= ($_GET['per_page'] ?? 10) == 10 ? 'selected' : '' ?>>10</option>
                    <option value="25" <?= ($_GET['per_page'] ?? 10) == 25 ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= ($_GET['per_page'] ?? 10) == 50 ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= ($_GET['per_page'] ?? 10) == 100 ? 'selected' : '' ?>>100</option>
                </select>
            </div>
        </div>
        <input type="hidden" id="sortField" value="<?= $_GET['sort'] ?? 'establishment_adress' ?>">
        <input type="hidden" id="sortOrder" value="<?= $_GET['order'] ?? 'ASC' ?>">
        <input type="hidden" id="currentPage" value="1">
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th><a href="#" class="sort-link text-white" data-sort="establishment_adress">Магазин</a></th>
                <th><a href="#" class="sort-link text-white" data-sort="product_name">Товар</a></th>
                <th><a href="#" class="sort-link text-white" data-sort="quantity">Количество</a></th>
                <th><a href="#" class="sort-link text-white" data-sort="product_selfcost">Цена (₽)</a></th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php include __DIR__ . '/../partials/stocks_table.php'; ?>
        </tbody>
    </table>
</div>

<div id="paginationContainer">
    <?php include __DIR__ . '/../partials/pagination.php'; ?>
</div>

<script>
const baseUrl = '/merchandiser';

function fetchTableData() {
    const perPage = document.getElementById('perPageSelect').value;
    const sort = document.getElementById('sortField').value;
    const order = document.getElementById('sortOrder').value;
    const page = document.getElementById('currentPage').value;
    
    fetch(`${baseUrl}?per_page=${perPage}&sort=${sort}&order=${order}&page=${page}&ajax=1`)
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
            let newOrder = 'ASC';
            if (currentSort === sort && currentOrder === 'ASC') newOrder = 'DESC';
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

document.getElementById('perPageSelect').addEventListener('change', () => {
    document.getElementById('currentPage').value = '1';
    fetchTableData();
});

attachSortHandlers();
attachPageHandlers();
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
