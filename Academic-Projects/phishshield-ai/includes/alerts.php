<?php
if (isset($_SESSION['flash_msg']) && !empty($_SESSION['flash_msg'])):
    $type = $_SESSION['flash_type'] ?? 'info';
    $icons = ['success' => 'check-circle', 'danger' => 'exclamation-circle', 'warning' => 'exclamation-triangle', 'info' => 'info-circle'];
    $icon = $icons[$type] ?? 'info-circle';
?>
<div class="alert alert-<?= h($type) ?> alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
    <i class="bi bi-<?= $icon ?>-fill fs-5"></i>
    <span><?= h($_SESSION['flash_msg']) ?></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php
    unset($_SESSION['flash_msg'], $_SESSION['flash_type']);
endif;
?>
