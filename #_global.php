<?php
// Headers para previnir o cache, especialmente em navegadores como o Safari
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once 'system-classes/config.php';

spl_autoload_register('carregarClasse');

function carregarClasse($nomeClasse)
{
    if (file_exists('system-classes/' . $nomeClasse . '.php')) {
        require_once 'system-classes/' .$nomeClasse . '.php';
    }
}
