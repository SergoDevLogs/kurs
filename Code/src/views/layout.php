<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Учетная система магазина</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { border-radius: 0 !important; }
        body { background: #f5f5f5; }
        .sidebar { min-height: 100vh; background: #343a40; }
        .sidebar .nav-link { color: #fff; }
        .sidebar .nav-link:hover { background: #0d6efd; color: #fff; }
        .sidebar .nav-link.active { background: #0d6efd; }
        .content { padding: 20px; }
        .btn { border-radius: 0; }
        .btn-primary { background-color: #0d6efd; border-color: #0d6efd; }
        .btn-primary:hover { background-color: #0b5ed7; border-color: #0b5ed7; }
        .btn-success { background-color: #198754; border-color: #198754; }
        .btn-success:hover { background-color: #157347; border-color: #157347; }
        .btn-danger { background-color: #dc3545; border-color: #dc3545; }
        .btn-danger:hover { background-color: #bb2d3b; border-color: #bb2d3b; }
        .btn-secondary { background-color: #6c757d; border-color: #6c757d; }
        .card { border: 1px solid #dee2e6; box-shadow: none; }
        .card-header { background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; font-weight: bold; }
        .table { margin-bottom: 0; }
        .table thead th { background-color: #343a40; color: #fff; border-bottom: none; }
        .table-hover tbody tr:hover { background-color: rgba(0, 0, 0, 0.05); }
        .pagination .page-link { color: #343a40; border-radius: 0; }
        .pagination .page-item.active .page-link { background-color: #343a40; border-color: #343a40; color: #fff; }
        .pagination .page-link:hover { background-color: rgba(0, 0, 0, 0.05); color: #343a40; }
        .sort-link { color: #fff; text-decoration: none; }
        .sort-link:hover { text-decoration: underline; }
        .modal-content { border-radius: 0; }
        .form-control, .form-select { border-radius: 0; }
        .badge { border-radius: 0; }
        .alert { border-radius: 0; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 p-0 sidebar">
                <div class="p-3 text-white">
                    <h5>Учетная система</h5>
                    <hr class="bg-white">
                    <p class="small mb-0"><?= $_SESSION['user_name'] ?? 'Гость' ?></p>
                    <p class="small"><strong><?= $_SESSION['user_role'] ?? '' ?></strong></p>
                </div>
                <nav class="nav flex-column">
                    <?php if (isset($_SESSION['user_role'])): ?>
                        <?php if ($_SESSION['user_role'] === 'cashier'): ?>
                            <a class="nav-link <?= $activePage === 'stocks' ? 'active' : '' ?>" href="/cashier">
                                <i class="bi bi-box-seam"></i> Остатки
                            </a>
                            <a class="nav-link <?= $activePage === 'sales' ? 'active' : '' ?>" href="/cashier/sales">
                                <i class="bi bi-graph-up"></i> Продажи
                            </a>
                        <?php elseif ($_SESSION['user_role'] === 'warehouse'): ?>
                            <a class="nav-link <?= $activePage === 'stocks' ? 'active' : '' ?>" href="/warehouse">
                                <i class="bi bi-boxes"></i> Остатки
                            </a>
                            <a class="nav-link <?= $activePage === 'supplies' ? 'active' : '' ?>" href="/warehouse/supplies">
                                <i class="bi bi-truck"></i> Поставки
                            </a>
                        <?php elseif ($_SESSION['user_role'] === 'merchandiser'): ?>
                            <a class="nav-link <?= $activePage === 'stocks' ? 'active' : '' ?>" href="/merchandiser">
                                <i class="bi bi-boxes"></i> Остатки
                            </a>
                            <a class="nav-link <?= $activePage === 'supplies' ? 'active' : '' ?>" href="/merchandiser/supplies">
                                <i class="bi bi-truck"></i> Поставки
                            </a>
                            <a class="nav-link <?= $activePage === 'transportations' ? 'active' : '' ?>" href="/merchandiser/transportations">
                                <i class="bi bi-arrow-left-right"></i> Перемещения
                            </a>
                            <a class="nav-link <?= $activePage === 'efficiency' ? 'active' : '' ?>" href="/merchandiser/efficiency">
                                <i class="bi bi-bar-chart"></i> Эффективность
                            </a>
                        <?php elseif ($_SESSION['user_role'] === 'admin'): ?>
                            <a class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>" href="/admin">
                                <i class="bi bi-speedometer2"></i> Дашборд
                            </a>
                            <a class="nav-link <?= $activePage === 'stocks' ? 'active' : '' ?>" href="/admin/stocks">
                                <i class="bi bi-boxes"></i> Остатки
                            </a>
                            <a class="nav-link <?= $activePage === 'supplies' ? 'active' : '' ?>" href="/admin/supplies">
                                <i class="bi bi-truck"></i> Поставки
                            </a>
                            <a class="nav-link <?= $activePage === 'transportations' ? 'active' : '' ?>" href="/admin/transportations">
                                <i class="bi bi-arrow-left-right"></i> Перемещения
                            </a>
                            <a class="nav-link <?= $activePage === 'efficiency' ? 'active' : '' ?>" href="/admin/efficiency">
                                <i class="bi bi-bar-chart"></i> Эффективность
                            </a>
                            <a class="nav-link <?= $activePage === 'users' ? 'active' : '' ?>" href="/admin/users">
                                <i class="bi bi-people"></i> Пользователи
                            </a>
                            <a class="nav-link <?= $activePage === 'register' ? 'active' : '' ?>" href="/register">
                                <i class="bi bi-person-plus"></i> Новый пользователь
                            </a>
                        <?php endif; ?>
                        <hr class="bg-white">
                        <a class="nav-link" href="/logout">
                            <i class="bi bi-box-arrow-right"></i> Выход
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
            
            <div class="col-md-10 content">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $_SESSION['success'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <?php echo $content; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>
