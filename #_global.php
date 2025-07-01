<?php
// Inicia a sessão de forma segura, se ainda não tiver sido iniciada.
// Isso é crucial para que `$_SESSION` esteja sempre disponível.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once 'system-classes/config.php';

spl_autoload_register('carregarClasse');

function carregarClasse($nomeClasse)
{
    $filePath = 'system-classes/' . $nomeClasse . '.php';
    if (file_exists($filePath)) {
        require_once $filePath;
    }
}
