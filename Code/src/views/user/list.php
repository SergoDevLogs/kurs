<?php 
$activePage = 'users';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Управление пользователями</h2>
    <a href="/register" class="btn btn-primary">+ Добавить пользователя</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>ФИО</th>
                <th>Должность</th>
                <th>Магазин</th>
                <th>Логин</th>
                <th>Роль</th>
                <th>Действие</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['employee_fullname']) ?></td>
                <td><?= htmlspecialchars($user['employee_position']) ?></td>
                <td><?= htmlspecialchars($user['establishment_adress']) ?></td>
                <td><?= htmlspecialchars($user['user_login']) ?></td>
                <td>
                    <?php
                    $roles = ['cashier' => 'Кассир', 'warehouse' => 'Склад', 'merchandiser' => 'Товаровед', 'admin' => 'Директор'];
                    echo $roles[$user['user_role']] ?? $user['user_role'];
                    ?>
                </td>
                <td>
                    <?php if ($user['employee_contract'] != $_SESSION['user_id']): ?>
                    <button class="btn btn-sm btn-danger delete-user" data-contract="<?= $user['employee_contract'] ?>">Удалить</button>
                    <?php else: ?>
                    <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
document.querySelectorAll('.delete-user').forEach(btn => {
    btn.addEventListener('click', function() {
        if (confirm('Удалить пользователя?')) {
            const contract = this.dataset.contract;
            fetch('/admin/delete-user', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `contract=${contract}`
            }).then(() => location.reload());
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
