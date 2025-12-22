<?php require_once __DIR__ . '/../includes/layout_start.php'; ?>
<?php if(!canAccessAdmin()){ http_response_code(403); echo '<div class="card" style="padding:20px">Acesso negado</div>'; require_once __DIR__ . '/../includes/layout_end.php'; exit; } ?>
<div class='card' style='padding:20px'>Admin: Configurações</div>
<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>