<?php 
$activePage = 'supplies';
ob_start();
?>

<h2>Создание новой поставки</h2>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/merchandiser/create-supply" id="supplyForm">
            <div class="mb-3">
                <label class="form-label">Магазин назначения</label>
                <select name="establishment" class="form-select" required>
                    <option value="">Выберите магазин</option>
                    <?php foreach ($establishments as $est): ?>
                        <option value="<?= htmlspecialchars($est['establishment_adress']) ?>">
                            <?= htmlspecialchars($est['establishment_adress']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Поставщик</label>
                <select name="supplier_id" class="form-select" required>
                    <option value="">Выберите поставщика</option>
                    <?php foreach ($suppliers as $sup): ?>
                        <option value="<?= $sup['supplier_id'] ?>">
                            <?= htmlspecialchars($sup['supplier_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <h5 class="mt-4">Товары в поставке</h5>
            <div id="itemsContainer">
                <div class="row mb-3 item-row">
                    <div class="col-md-5">
                        <select name="product_article[]" class="form-select" required>
                            <option value="">Выберите товар</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product['product_article'] ?>">
                                    <?= htmlspecialchars($product['product_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="quantity[]" class="form-control" placeholder="Количество" required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="cost[]" class="form-control" placeholder="Стоимость закупки" step="0.01" required>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger remove-row">×</button>
                    </div>
                </div>
            </div>
            
            <button type="button" class="btn btn-secondary" id="addRow">+ Добавить товар</button>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Создать поставку</button>
                <a href="/merchandiser/supplies" class="btn btn-secondary">Отмена</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('addRow').addEventListener('click', function() {
    const container = document.getElementById('itemsContainer');
    const newRow = container.children[0].cloneNode(true);
    newRow.querySelectorAll('input, select').forEach(input => input.value = '');
    container.appendChild(newRow);
});

document.querySelectorAll('.remove-row').forEach(btn => {
    btn.addEventListener('click', function() {
        if (document.querySelectorAll('.item-row').length > 1) {
            this.closest('.item-row').remove();
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
