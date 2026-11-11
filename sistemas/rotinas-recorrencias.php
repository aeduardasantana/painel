<?php
declare(strict_types=1);

/** Recorrências do Painel Mestre, com cálculo no horário de Brasília. */
function pm_active(array $task, DateTimeImmutable $date): bool {
    $from=(string)($task['ativo_desde']??'');
    return $from==='' || $date->format('Y-m-d') >= $from;
}
function pm_scheduled(array $task, DateTimeImmutable $date): bool {
    if (!pm_active($task,$date)) return false;
    $weekday=(int)$date->format('N');
    switch ((string)($task['frequencia']??'semanal')) {
        case 'diaria':
        case 'semanal':
            return in_array($weekday, $task['dias']??[],true);
        case 'quinzenal':
            if (!in_array($weekday,$task['dias']??[],true)) return false;
            $anchor=(string)($task['data_ancora']??'');
            $anchorDay=DateTimeImmutable::createFromFormat('!Y-m-d',$anchor,new DateTimeZone('America/Sao_Paulo'));
            if (!$anchorDay || $anchorDay->format('Y-m-d')!==$anchor) return false;
            // Âncora incluída; não criar ocorrência para datas anteriores à referência.
            $days=(int)$anchorDay->diff($date)->format('%r%a');
            return $days>=0 && $days%14===0;
        case 'mensal':
            return (int)$date->format('j') === (int)($task['dia_mes']??0);
        default:
            return false;
    }
}
function pm_todayDue(array $task, DateTimeImmutable $date, DateTimeImmutable $now): bool {
    if (!pm_scheduled($task,$date)) return false;
    [$h,$m]=array_map('intval',explode(':',$task['hora']));
    return $date->setTime($h,$m)->getTimestamp() <= $now->getTimestamp();
}
