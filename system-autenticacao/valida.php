<?php
	session_start();
	//Incluindo a conexão com banco de dados
	include_once("conexao.php");
	//O campo usuário e senha preenchido entra no if para validar
	if((isset($_POST['telefone'])) && (isset($_POST['senha']))){
		$telefone = mysqli_real_escape_string($conn, $_POST['telefone']); //Escapar de caracteres especiais, como aspas, prevenindo SQL injection
		$senha = mysqli_real_escape_string($conn, $_POST['senha']);

		$telefone = '55'.$telefone;

		//Buscar na tabela usuario o usuário que corresponde com os dados digitado no formulário
		$result_usuario = "SELECT * from usuario where telefone='{$telefone}' and senha='{$senha}' 
		-- and status='ATIVO'
		limit 1";
		$resultado_usuario = mysqli_query($conn, $result_usuario);
		$resultado = mysqli_fetch_assoc($resultado_usuario);

		//Encontrado um usuario na tabela usuário com os mesmos dados digitado no formulário
		if(isset($resultado)){
			//VARIAVEIS DE USUARIO
			$_SESSION['DuplaUserId'] = $resultado['id'];
			$_SESSION['DuplaUserNome'] = $resultado['nome'];
			$_SESSION['DuplaUserTelefone'] = $resultado['telefone'];
			$_SESSION['DuplaUserSenha'] = $resultado['senha'];
			$_SESSION['DuplaUserCidade'] = $resultado['cidade'];
			$_SESSION['DuplaUserEmpunhadura'] = $resultado['empunhadura'];

			header("Location: ../principal.php");
		//Não foi encontrado um usuario na tabela usuário com os mesmos dados digitado no formulário
		//redireciona o usuario para a página de login
		}
		else{
			//Váriavel global recebendo a mensagem de erro
			$_SESSION['DuplaLogin'] = "Usuário ou senha Inválido";
			header("Location: ../index.php");
		}
	//O campo usuário e senha não preenchido entra no else e redireciona o usuário para a página de login
	}else{
		$_SESSION['DuplaLogin'] = "Usuário ou senha inválido";
		header("Location: ../index.php");
	}
?>
