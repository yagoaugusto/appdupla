<?php
session_start();
unset(
$_SESSION['DuplaLogin'],
$_SESSION['DuplaUserId'],
$_SESSION['DuplaUserNome'],
$_SESSION['DuplaUserSenha'],
$_SESSION['DuplaUserTelefone'],
$_SESSION['DuplaUserCidade'],
$_SESSION['DuplaUserEmpunhadura'],
);

session_destroy();
?>
<script>location.href='../index.php';</script> 
<?php exit('Redirecionando...'); ?>