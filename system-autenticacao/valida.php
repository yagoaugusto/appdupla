<?php
session_start();
//Incluindo a conexão com banco de dados
include_once("conexao.php");
//O campo usuário e senha preenchido entra no if para validar
if ((isset($_POST['telefone'])) && (isset($_POST['senha']))) {
	$telefone = mysqli_real_escape_string($conn, $_POST['telefone']); //Escapar de caracteres especiais, como aspas, prevenindo SQL injection
	$senha = mysqli_real_escape_string($conn, $_POST['senha']);

	$telefone = '55' . $telefone;

	//Buscar na tabela usuario apenas pelo telefone
	$result_usuario = "SELECT * FROM usuario WHERE telefone = '{$telefone}' LIMIT 1";
	$resultado_usuario = mysqli_query($conn, $result_usuario);
	$resultado = mysqli_fetch_assoc($resultado_usuario);

	//Encontrado um usuario na tabela usuário com os mesmos dados digitado no formulário
	if ($resultado) {
		$hash_bd = $resultado['senha'];
		$login_ok = false;

		// 1) Senha já está hasheada (bcrypt) → usa password_verify
		if (password_verify($senha, $hash_bd)) {
			$login_ok = true;
		}
		// 2) Senha em texto puro ainda
		elseif ($hash_bd === $senha) {
			$login_ok = true;

			// gera hash seguro e atualiza banco
			$novo_hash = password_hash($senha, PASSWORD_BCRYPT);
			$uid = $resultado['id'];
			mysqli_query($conn, "UPDATE usuario SET senha = '{$novo_hash}' WHERE id = {$uid}");
		}

		if ($login_ok) {
			//VARIAVEIS DE USUARIO
			$_SESSION['DuplaUserId']        = $resultado['id'];
			$_SESSION['DuplaUserNome']      = $resultado['nome'];
			$_SESSION['DuplaUserTelefone']  = $resultado['telefone'];
			$_SESSION['DuplaUserCidade']    = $resultado['cidade'];
			$_SESSION['DuplaUserEmpunhadura'] = $resultado['empunhadura'];
			$_SESSION['DuplaUserTipo']      = $resultado['tipo'];

			// mantém login (cookie) se marcado
			if (isset($_POST['manter_logado'])) {
				$token = bin2hex(random_bytes(32));
				mysqli_query($conn, "UPDATE usuario SET token_login = '{$token}' WHERE id = {$resultado['id']}");
				setcookie('DuplaLoginToken', $token, time() + (86400 * 30), "/");
			}
			header("Location: ../principal.php");
			exit;
		}
	}

	/* Falhou login */
	$_SESSION['DuplaLogin'] = "Usuário ou senha inválidos";
	header("Location: ../index.php");
	exit;
	//O campo usuário e senha não preenchido entra no else e redireciona o usuário para a página de login
} else {
	$_SESSION['DuplaLogin'] = "Usuário ou senha inválido";
	header("Location: ../index.php");
}
