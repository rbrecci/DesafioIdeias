<?php
/**
 * AtivoLab - configuracao.
 *
 * Copie para config.php e preencha com os dados do painel do InfinityFree.
 * config.php esta no .gitignore: senha de banco nunca vai para o repositorio.
 */
return [
    'db' => [
        'host'  => 'sqlXXX.infinityfree.com',
        'nome'  => 'if0_00000000_ativolab',
        'user'  => 'if0_00000000',
        'senha' => 'SUA_SENHA_AQUI',
    ],

    // Nome do app exibido na interface.
    'app_nome' => 'AtivoLab',

    // Caminho do app dentro do dominio. Vazio se estiver na raiz de htdocs.
    // Exemplo: se o app estiver em htdocs/ativolab, use '/ativolab'.
    'base_url' => '',

    // true mostra erros na tela. SEMPRE false em producao.
    'debug' => false,

    'upload' => [
        'max_bytes' => 2097152, // 2 MB
        'tipos'     => ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'],
    ],
];
