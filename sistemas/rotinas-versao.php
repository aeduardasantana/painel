<?php
declare(strict_types=1);

/**
 * Identifica somente a versão efetivamente publicada na hospedagem.
 * O arquivo é criado pelo processo de implantação APÓS a transferência.
 * A versão não contém informações privadas ou tokens.
 */
function pm_build_version(): string {
    $file = __DIR__ . '/rotinas-version.json';
    if (!is_file($file)) return '';
    $raw = @file_get_contents($file);
    if ($raw === false || strlen($raw) > 512) return '';
    $data = json_decode($raw, true);
    $version = is_array($data) ? ($data['version'] ?? '') : '';
    return is_string($version) && preg_match('/^[a-f0-9]{40}$/D', $version) ? $version : '';
}
