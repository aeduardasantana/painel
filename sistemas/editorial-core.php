<?php
declare(strict_types=1);

/**
 * Repositório de registros editoriais por eventos (append-only).
 * Fonte dos materiais continua no Google Drive; este arquivo guarda apenas
 * metadados operacionais e o histórico de mudanças, sem credenciais.
 */
const ED_STORE = __DIR__.'/.editorial-events.jsonl';
function ed_h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
}
function ed_text($raw,int $max,string $label): string {
    if (!is_string($raw)) throw new DomainException("Campo inválido: $label");
    $val=trim(str_replace("\r\n","\n",$raw));
    $val=(string)preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u','',$val);
    if (strlen($val)>$max) throw new DomainException("$label excede o tamanho permitido.");
    return $val;
}
function ed_date($raw): string {
    if ($raw===null || $raw==='') return '';
    if (!is_string($raw) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D',$raw)) throw new DomainException('Data inválida.');
    $d=DateTimeImmutable::createFromFormat('!Y-m-d',$raw,new DateTimeZone('America/Sao_Paulo'));
    if (!$d || $d->format('Y-m-d')!==$raw || (int)$d->format('Y')<2020 || (int)$d->format('Y')>2100) throw new DomainException('Data fora do intervalo permitido.');
    return $raw;
}
function ed_url($raw): string {
    $url=ed_text($raw,1500,'Endereço');
    if ($url==='') return '';
    if (!filter_var($url,FILTER_VALIDATE_URL)) throw new DomainException('Use um link HTTPS válido.');
    $parts=parse_url($url);
    if (!is_array($parts) || strtolower((string)($parts['scheme']??''))!=='https'
        || !isset($parts['host']) || isset($parts['user']) || isset($parts['pass'])) throw new DomainException('Use somente endereços HTTPS públicos sem credenciais.');
    return $url;
}
function ed_choice($raw,array $allowed,string $label): string {
    $v=(string)$raw;
    if (!array_key_exists($v,$allowed)) throw new DomainException("$label não reconhecido.");
    return $v;
}
function ed_time(): string { return (new DateTimeImmutable('now',new DateTimeZone('America/Sao_Paulo')))->format(DATE_ATOM); }
function ed_read_locked($handle): array {
    rewind($handle);
    $items=[];
    $count=0;
    while (($line=fgets($handle))!==false) {
        if (trim($line)==='') continue;
        if (++$count>50000) throw new RuntimeException('Limite do histórico alcançado.');
        if (strlen($line)>20000) throw new RuntimeException('Registro histórico excede o limite.');
        $event=json_decode($line,true);
        if (!is_array($event) || !isset($event['item']) || !is_array($event['item'])
            || !isset($event['item']['id'])) throw new RuntimeException('Histórico incompleto. Nenhum registro será modificado.');
        $item=$event['item'];
        $id=(string)$item['id'];
        if (!preg_match('/^[a-f0-9]{24}$/D',$id)) throw new RuntimeException('Identificador inválido no histórico.');
        $items[$id]=$item;
    }
    if (!feof($handle)) throw new RuntimeException('Não foi possível ler todo o histórico.');
    return $items;
}
function ed_load(): array {
    if (!is_file(ED_STORE)) return [];
    $fh=@fopen(ED_STORE,'r');
    if (!$fh) throw new RuntimeException('Histórico editorial indisponível.');
    if (!flock($fh,LOCK_SH)) { fclose($fh); throw new RuntimeException('Bloqueio de leitura indisponível.'); }
    try { return ed_read_locked($fh); }
    finally {flock($fh,LOCK_UN);fclose($fh);}
}
function ed_commit(callable $make): array {
    $fh=@fopen(ED_STORE,'c+');
    if (!$fh) throw new RuntimeException('Sem permissão para criar o histórico no servidor.');
    @chmod(ED_STORE,0600);
    if (!flock($fh,LOCK_EX)) {fclose($fh);throw new RuntimeException('Bloqueio de escrita indisponível.');}
    try {
        $items=ed_read_locked($fh);
        $changed=$make($items);
        if (!is_array($changed) || !isset($changed['id'],$changed['revision'])) throw new RuntimeException('Alteração inválida.');
        $event=['quando'=>ed_time(),'acao'=>(string)($changed['_action']??'alteracao'),'item'=>$changed];
        unset($event['item']['_action']);
        $json=json_encode($event,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
        if (strlen($json)>16000) throw new DomainException('Registro grande demais.');
        if (fseek($fh,0,SEEK_END)!==0) throw new RuntimeException('Arquivo de histórico indisponível.');
        $prior=ftell($fh);
        if (!is_int($prior) || $prior>30000000) throw new RuntimeException('Capacidade do histórico excedida. Revise a infraestrutura antes de continuar.');
        $wrote=fwrite($fh,$json);
        if ($wrote!==strlen($json) || !fflush($fh)) {
            ftruncate($fh,$prior);
            fflush($fh);
            throw new RuntimeException('A gravação não foi concluída. Nenhuma confirmação foi emitida.');
        }
        return $event['item'];
    } finally {
        flock($fh,LOCK_UN); fclose($fh);
    }
}
function ed_expected(array $item,$post): void {
    $rev=filter_var($post,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
    if ($rev===false || (int)$rev !== (int)$item['revision']) throw new DomainException('Outro registro foi salvo depois desta página. Recarregue antes de editar para não perder alterações.');
}
function ed_duplicate(array $items,string $brand,string $code,?string $skipId): void {
    if ($code==='') return;
    foreach ($items as $id=>$candidate) {
        if ($id===$skipId || !empty($candidate['archived'])) continue;
        if (($candidate['brand']??'')===$brand && mb_strtolower((string)($candidate['code']??''),'UTF-8')===mb_strtolower($code,'UTF-8'))
            throw new DomainException('Este código já está registrado nessa marca. Abra o conteúdo existente e registre os canais nele.');
    }
}
function ed_form_item(array $post,array $config): array {
    $brand=ed_choice($post['brand']??'',$config['brands'],'Marca');
    $title=ed_text($post['title']??'',180,'Título');
    if ($title==='') throw new DomainException('Informe o título do conteúdo.');
    $code=ed_text($post['code']??'',80,'Código');
    $project=ed_text($post['project']??'',120,'Quadro ou projeto');
    $format=ed_choice($post['format']??'',$config['formats'],'Formato');
    $phase=ed_choice($post['phase']??'',$config['phases'],'Etapa');
    return [
        'brand'=>$brand,'title'=>$title,'code'=>$code,'project'=>$project,
        'format'=>$format,'phase'=>$phase,'planned_date'=>ed_date($post['planned_date']??''),
        'asset_url'=>ed_url($post['asset_url']??''),
        'notes'=>ed_text($post['notes']??'',2500,'Observações'),
    ];
}
function ed_form_distribution(array $post,array $config): array {
    $network=ed_choice($post['network']??'',$config['networks'],'Rede');
    $account=ed_text($post['account']??'',100,'Perfil');
    if ($account==='') throw new DomainException('Identifique a conta ou perfil de publicação.');
    $state=ed_choice($post['state']??'',$config['distribution_states'],'Estado do canal');
    $date=ed_date($post['scheduled_date']??'');
    $url=ed_url($post['published_url']??'');
    if ($state==='agendado' && $date==='') throw new DomainException('Para marcar Agendado, informe a data programada.');
    if ($state==='publicado' && $url==='') throw new DomainException('Para marcar Publicado, registre o link público de comprovação.');
    return [
        'network'=>$network,'account'=>$account,'state'=>$state,
        'scheduled_date'=>$date,'published_url'=>$url,
        'notes'=>ed_text($post['distribution_notes']??'',900,'Nota do canal'),
    ];
}
function ed_no_dup_distribution(array $all,array $dist,?string $skip): void {
    foreach($all as $existing) {
        if (($existing['id']??'')===$skip) continue;
        if (($existing['network']??'')===$dist['network'] &&
            mb_strtolower((string)($existing['account']??''),'UTF-8')===mb_strtolower($dist['account'],'UTF-8')) {
            throw new DomainException('Já existe uma distribuição para essa rede e perfil neste conteúdo. Atualize a distribuição existente.');
        }
    }
}
