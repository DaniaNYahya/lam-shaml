<?php
$totalPages = max(1, (int)ceil(($rows['total'] ?? 0) / 20));
if ($totalPages > 1):
?>
<nav class="mt-3"><ul class="pagination">
  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <li class="page-item <?= $i === ($page ?? 1) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
  <?php endfor; ?>
</ul></nav>
<?php endif; ?>
