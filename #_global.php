<?php

require_once 'system-classes/config.php';

spl_autoload_register('carregarClasse');

function carregarClasse($nomeClasse)
{
    if (file_exists('system-classes/' . $nomeClasse . '.php')) {
        require_once 'system-classes/' .$nomeClasse . '.php';
    }
}
