<?php

// 2. CARREGA AS CONFIGURAÇÕES
require_once __DIR__ . '/system-classes/config.php';

// 3. CARREGA AS SUAS PRÓPRIAS CLASSES
spl_autoload_register(function ($nomeClasse) {
    $filePath = __DIR__ . '/system-classes/' . $nomeClasse . '.php';
    if (file_exists($filePath)) {
        require_once $filePath;
    }
});