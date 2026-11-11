<?php
declare(strict_types=1);

/**
 * Catálogo único de rotinas do Painel Mestre.
 * dias: 1=segunda ... 7=domingo; horário em America/Sao_Paulo.
 * IDs existentes são preservados para manter o histórico.
 */
return [
    'geb-blog-conferir-publicacao' => [
        'hora' => '08:15',
        'titulo' => 'Conferir os sete artigos diários do Blog GEB',
        'area' => 'GEB · Comunicação / Editorial',
        'descricao' => 'Verificar individualmente as sete publicações do dia.',
        'observacoes' => 'Rotina de domingo a domingo. Conferir as sete editorias no site publicado: Desenvolvimento, Empresarial, Educação, Saúde, Tecnologia, Inclusão e Institucional. Confirmar títulos, corpo, links, referências e funcionamento antes de marcar como concluído.',
        'links' => [
            ['titulo' => 'Abrir Blog GEB', 'url' => 'https://blog.grupoeduardabispo.com.br/'],
        ],
        'dias' => [1, 2, 3, 4, 5, 6, 7],
    ],
    'gestao-em-pauta-preparar-gravar' => [
        'hora' => '09:30',
        'titulo' => 'Estruturar e gravar o Gestão em Pauta',
        'area' => 'GEB · Comunicação',
        'descricao' => 'Produzir a pauta, estruturar o conteúdo e gravar a edição.',
        'observacoes' => 'Consultar a apuração no Bastidor GP. Guardar o material correspondente na pasta da edição no Drive. A conclusão desta tarefa não conclui automaticamente a publicação das 11h.',
        'links' => [
            ['titulo' => 'Pasta de edições no Drive', 'url' => 'https://drive.google.com/drive/folders/1YHBXSAahlwfHWeKJ1dg8SfzC5g5Gu-pN?usp=drive_link'],
            ['titulo' => 'Abrir Bastidor GP', 'url' => 'https://bastidorgp.compassrosesystems.com.br/'],
        ],
        'dias' => [1, 2, 3, 4, 5],
    ],
    'gestao-em-pauta-publicar' => [
        'hora' => '11:00',
        'titulo' => 'Publicar a edição do Gestão em Pauta',
        'area' => 'GEB · Comunicação',
        'descricao' => 'Publicar a edição e verificar seu acesso público.',
        'observacoes' => 'Conferir título, descrição, publicação efetiva e link da edição. Manter os documentos e registros da edição na pasta do Drive. A marcação é manual e independente da rotina de gravação.',
        'links' => [
            ['titulo' => 'Pasta de edições no Drive', 'url' => 'https://drive.google.com/drive/folders/1YHBXSAahlwfHWeKJ1dg8SfzC5g5Gu-pN?usp=drive_link'],
            ['titulo' => 'Abrir Bastidor GP', 'url' => 'https://bastidorgp.compassrosesystems.com.br/'],
        ],
        'dias' => [1, 2, 3, 4, 5],
    ],
];