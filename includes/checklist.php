<?php
// includes/checklist.php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

/** Chave do período atual conforme a frequência */
function period_key_for(string $freq, ?DateTime $when=null): string {
  $when = $when ?: new DateTime('now');
  switch ($freq) {
    case 'weekly':   return $when->format('o-\WW');                     // 2025-W41
    case 'biweekly': $w=(int)$when->format('W'); return sprintf('%s-B%02d',$when->format('o'), (int)ceil($w/2));
    case 'monthly':  return $when->format('Y-m');                       // 2025-10
    default:         return $when->format('Y-m-d');                     // daily
  }
}

/** Chave do período anterior (para regularização) */
function period_key_prev(string $freq, ?DateTime $when=null): string {
  $when = $when ?: new DateTime('now');
  switch ($freq) {
    case 'weekly':   $when->modify('-1 week'); break;
    case 'biweekly': $when->modify('-2 weeks'); break;
    case 'monthly':  $when->modify('first day of previous month'); break;
    default:         $when->modify('-1 day');
  }
  return period_key_for($freq,$when);
}

/** Se já passou a janela do período atual (marcações contam como atraso) */
function dueWindowLate(string $freq, ?DateTime $now=null): bool {
  $now = $now ?: new DateTime('now');
  switch ($freq) {
    case 'weekly':   $end = (clone $now)->modify('sunday this week 23:59:59'); break;
    case 'biweekly': $end = (clone $now)->modify('sunday this week 23:59:59'); break;
    case 'monthly':  $end = (clone $now)->modify('last day of this month 23:59:59'); break;
    default:         $end = (clone $now)->setTime(23,59,59);
  }
  return $now > $end;
}

/** Checklists por cargos do usuário na empresa */
function checklistsForUser(PDO $pdo, int $userId, int $companyId): array {
  $sql = "SELECT DISTINCT c.*
          FROM checklists c
          JOIN checklist_role cr ON cr.checklist_id=c.id
          JOIN user_role ur ON ur.role_id=cr.role_id AND ur.user_id=?
          WHERE c.company_id=? AND c.is_active=1
          ORDER BY FIELD(c.frequency,'daily','weekly','biweekly','monthly'), c.title";
  $st = $pdo->prepare($sql);
  $st->execute([$userId,$companyId]);
  return $st->fetchAll() ?: [];
}

/** Tarefas ativas do checklist */
function checklistTasks(PDO $pdo, int $checklistId): array {
  $st = $pdo->prepare("SELECT * FROM checklist_tasks WHERE checklist_id=? AND is_active=1 ORDER BY priority ASC, id ASC");
  $st->execute([$checklistId]);
  return $st->fetchAll() ?: [];
}

/** Registro de conclusão (se existir) */
function isTaskDone(PDO $pdo, int $taskId, int $companyId, string $periodKey): ?array {
  $st = $pdo->prepare("SELECT id, completed_at, was_late FROM checklist_task_done WHERE task_id=? AND company_id=? AND period_key=? LIMIT 1");
  $st->execute([$taskId,$companyId,$periodKey]);
  return $st->fetch(PDO::FETCH_ASSOC) ?: null;
}

/** Tarefa ficou sem registro no período anterior? */
function isTaskPendingPrev(PDO $pdo, int $taskId, int $companyId, string $freq): bool {
  $prev = period_key_prev($freq);
  $st = $pdo->prepare("SELECT 1 FROM checklist_task_done WHERE task_id=? AND company_id=? AND period_key=? LIMIT 1");
  $st->execute([$taskId,$companyId,$prev]);
  return !$st->fetchColumn();
}

/** Marcar tarefa (cria/atualiza registro do período) */
function markTask(PDO $pdo, int $checklistId, int $taskId, int $userId, int $companyId, string $periodKey, bool $late=false): void {
  $sql = "INSERT INTO checklist_task_done (checklist_id, task_id, user_id, company_id, period_key, was_late)
          VALUES (?,?,?,?,?,?)
          ON DUPLICATE KEY UPDATE completed_at=NOW(), user_id=VALUES(user_id), was_late=VALUES(was_late)";
  $st  = $pdo->prepare($sql);
  $st->execute([$checklistId,$taskId,$userId,$companyId,$periodKey, $late?1:0]);
}

/** Desmarcar tarefa (remove marcação daquele período) */
function unmarkTask(PDO $pdo, int $taskId, int $companyId, string $periodKey): void {
  $st = $pdo->prepare("DELETE FROM checklist_task_done WHERE task_id=? AND company_id=? AND period_key=?");
  $st->execute([$taskId,$companyId,$periodKey]);
}

/** Totais para dashboard */
function totalsForUser(PDO $pdo, int $userId, int $companyId): array {
  $lists = checklistsForUser($pdo,$userId,$companyId);
  $tot = ['overdue'=>0,'today'=>0,'week'=>0,'month'=>0];

  foreach ($lists as $cl) {
    $freq  = $cl['frequency'];
    $pkey  = period_key_for($freq);
    $tasks = checklistTasks($pdo,(int)$cl['id']);

    foreach ($tasks as $t) {
      $done = isTaskDone($pdo,(int)$t['id'],$companyId,$pkey);
      if (!$done) {
        if ($freq==='daily') $tot['today']++;
        if ($freq==='weekly' || $freq==='biweekly') $tot['week']++;
        if ($freq==='monthly') $tot['month']++;
      } else if (!empty($done['was_late'])) {
        $tot['overdue']++;
      }
      // não marcado no período anterior também conta como atraso
      if (isTaskPendingPrev($pdo,(int)$t['id'],$companyId,$freq)) $tot['overdue']++;
    }
  }
  return $tot;
}

/** Helpers UI */
function checklistPendingNow(PDO $pdo,array $cl,int $companyId): int {
  $tasks = checklistTasks($pdo,(int)$cl['id']); $pkey = period_key_for($cl['frequency']);
  $pend = 0; foreach($tasks as $t){ if(!isTaskDone($pdo,(int)$t['id'],$companyId,$pkey)) $pend++; }
  return $pend;
}
function checklistHasPrevOverdue(PDO $pdo,array $cl,int $companyId): bool {
  foreach(checklistTasks($pdo,(int)$cl['id']) as $t){
    if(isTaskPendingPrev($pdo,(int)$t['id'],$companyId,$cl['frequency'])) return true;
  }
  return false;
}
function period_label($p){ return [
  'inicio_dia'=>'Início do dia','final_dia'=>'Até o final do dia',
  'inicio_semana'=>'Início da semana','final_semana'=>'Até o final da semana'
][$p] ?? $p; }
