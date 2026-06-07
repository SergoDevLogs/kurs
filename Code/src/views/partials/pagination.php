<?php if ($totalPages > 1): ?>
<nav>
    <ul class="pagination justify-content-center mt-3">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link page-link-ajax" href="#" data-page="<?= $page - 1 ?>">«</a>
        </li>
        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
            <a class="page-link page-link-ajax" href="#" data-page="<?= $i ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
            <a class="page-link page-link-ajax" href="#" data-page="<?= $page + 1 ?>">»</a>
        </li>
    </ul>
</nav>
<?php endif; ?>
