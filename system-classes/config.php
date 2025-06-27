<?php
date_default_timezone_set('America/Sao_Paulo');

define('DEBUG', true);

define('DB_DRIVE', 'mysql');
define('DB_HOSTNAME', 'localhost');
//define('DB_DATABASE', 'rating_beachtennis');
//define('DB_USERNAME', 'root');
//define('DB_PASSWORD', '');

// URL Base da Aplicação
// Use http://localhost/APP_DUPLA para desenvolvimento local
define('APP_BASE_URL', 'https://beta.appdupla.com');

// Mercado Pago Credentials
define('MP_ACCESS_TOKEN', 'APP_USR-6139135356728189-062109-38d659c2b6c61a8c060d3f56d5dd4ccc-141946278'); // Substitua pelo seu Access Token real
define('MP_NOTIFICATION_URL', APP_BASE_URL . '/controller-pagamento/webhook.php'); // URL do seu webhook

define('DB_DATABASE', 'u580429014_app');
define('DB_USERNAME', 'u580429014_app');
define('DB_PASSWORD', 'Caninde.123');
