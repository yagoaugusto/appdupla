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
define('MP_ACCESS_TOKEN', 'APP_USR-3719204345760693-063018-7cbb5662433929d5ace680fbe5b6cff7-2523463753'); // Substitua pelo seu Access Token real
define('MP_NOTIFICATION_URL', APP_BASE_URL . '/controller-pagamento/webhook.php'); // URL do seu webhook

define('DB_DATABASE', 'u580429014_app');
define('DB_USERNAME', 'u580429014_app');
define('DB_PASSWORD', 'Caninde.123');
