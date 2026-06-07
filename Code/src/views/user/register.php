<?php 
$activePage = 'register';
ob_start();
?>

<h2>Регистрация нового пользователя</h2>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/register">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">ФИО сотрудника</label>
                    <input type="text" name="fullname" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Должность</label>
                    <select name="position" class="form-select" required>
                        <option value="">Выберите должность</option>
                        <option value="Продавец-кассир">Продавец-кассир</option>
                        <option value="Складской работник">Складской работник</option>
                        <option value="Товаровед">Товаровед</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Магазин</label>
                    <select name="establishment" class="form-select" required>
                        <option value="">Выберите магазин</option>
                        <?php foreach ($establishments as $est): ?>
                            <option value="<?= htmlspecialchars($est['establishment_adress']) ?>">
                                <?= htmlspecialchars($est['establishment_adress']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Логин</label>
                    <input type="text" name="login" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Пароль</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Создать пользователя</button>
                <a href="/admin/users" class="btn btn-secondary">Отмена</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
