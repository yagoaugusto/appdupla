<?php
session_start();
require_once '#_global.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // O jogador 1 (o próprio usuário) deve ser obtido diretamente da sessão para segurança
    $jogador1_id = $_SESSION['DuplaUserId'] ?? null;

    $torneio_id = filter_input(INPUT_POST, 'torneio_id', FILTER_VALIDATE_INT);
    $categoria_id = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);
    $titulo_dupla = trim($_POST['titulo_dupla'] ?? '');
    $jogador2_id = filter_input(INPUT_POST, 'jogador2_id', FILTER_VALIDATE_INT); // O parceiro

    // Validação básica
    if (!$torneio_id || !$categoria_id || empty($titulo_dupla) || $jogador1_id === null || !$jogador2_id) {
        $_SESSION['mensagem'] = ["danger", "Todos os campos obrigatórios devem ser preenchidos."];
        header("Location: ../inscrever-torneio.php?torneio_id=" . $torneio_id);
        exit;
    }
    // Verifica se o parceiro não é o próprio usuário
    if ($jogador1_id === $jogador2_id) {
        $_SESSION['mensagem'] = ["danger", "Você não pode ser seu próprio parceiro."];
        header("Location: ../inscrever-torneio.php?torneio_id=" . $torneio_id);
        exit;
    }

    try {
        // Verifica se o torneio e a categoria existem e são válidos
        $torneio = Torneio::getTorneioById($torneio_id);
        $categoria = Categoria::getCategoriaById($categoria_id);

        if (!$torneio || !$categoria || $categoria['torneio_id'] != $torneio_id) {
            $_SESSION['mensagem'] = ["danger", "Torneio ou categoria inválidos."];
            header("Location: ../encontrar-torneio.php");
            exit;
        }

        // Validação de Gênero da Dupla vs. Categoria
        $jogador1 = Usuario::getUsuarioInfoById($jogador1_id);
        $jogador2 = Usuario::getUsuarioInfoById($jogador2_id);

        if (!$jogador1 || !$jogador2) {
            $_SESSION['mensagem'] = ["danger", "Um ou ambos os jogadores não foram encontrados."];
            header("Location: ../inscrever-torneio.php?torneio_id=" . $torneio_id);
            exit;
        }

        $genero_jogador1 = $jogador1['sexo'];
        $genero_jogador2 = $jogador2['sexo'];
        $genero_categoria = $categoria['genero'];

        $is_dupla_masculina = ($genero_jogador1 === 'M' && $genero_jogador2 === 'M');
        $is_dupla_feminina = ($genero_jogador1 === 'F' && $genero_jogador2 === 'F');
        $is_dupla_mista = ($genero_jogador1 !== $genero_jogador2); // Simplificado: verifica se os gêneros são diferentes

        if ($genero_categoria === 'masculino' && !$is_dupla_masculina) {
            $_SESSION['mensagem'] = ["danger", "Esta categoria é apenas para duplas masculinas."];
            header("Location: ../inscrever-torneio.php?torneio_id=" . $torneio_id);
            exit;
        } elseif ($genero_categoria === 'feminino' && !$is_dupla_feminina) {
            $_SESSION['mensagem'] = ["danger", "Esta categoria é apenas para duplas femininas."];
            header("Location: ../inscrever-torneio.php?torneio_id=" . $torneio_id);
            exit;
        } elseif ($genero_categoria === 'mista' && !$is_dupla_mista) {
            $_SESSION['mensagem'] = ["danger", "Esta categoria é apenas para duplas mistas."];
            header("Location: ../inscrever-torneio.php?torneio_id=" . $torneio_id);
            exit;
        }


        // Obtém os valores de inscrição base do torneio
        $valor_primeira_insc_torneio = $torneio['valor_primeira_insc'];
        $valor_segunda_insc_torneio = $torneio['valor_segunda_insc'];

        // Determina o valor a ser pago pelo jogador 1
        $jogador1_ja_inscrito = InscricaoTorneio::hasExistingRegistrationInTorneio($torneio_id, $jogador1_id);
        $valor_pagamento_jogador1 = $jogador1_ja_inscrito ? $valor_segunda_insc_torneio : $valor_primeira_insc_torneio;

        // Determina o valor a ser pago pelo jogador 2
        $jogador2_ja_inscrito = InscricaoTorneio::hasExistingRegistrationInTorneio($torneio_id, $jogador2_id);
        $valor_pagamento_jogador2 = $jogador2_ja_inscrito ? $valor_segunda_insc_torneio : $valor_primeira_insc_torneio;

        // Tenta realizar a inscrição
        $inscricao_id = InscricaoTorneio::inscreverDupla($torneio_id, $categoria_id, $titulo_dupla, $jogador1_id, $jogador2_id);
        if ($inscricao_id) {
            // Cria os registros de pagamento com os valores calculados
            if (InscricaoTorneio::criarRegistrosPagamento($inscricao_id, $jogador1_id, $jogador2_id, $valor_pagamento_jogador1, $valor_pagamento_jogador2)) {
                $_SESSION['mensagem'] = ["success", "Inscrição realizada com sucesso! Registros de pagamento criados."];
                header("Location: ../torneio-inscrito.php?inscricao_id=" . $inscricao_id); // Redireciona para a nova página
                exit;
            } else {
                // Se a criação dos registros de pagamento falhar, avisa o usuário mas mantém a inscrição da dupla.
                $_SESSION['mensagem'] = ["warning", "Inscrição da dupla realizada, mas houve um erro ao criar os registros de pagamento. Entre em contato com o suporte."];
                header("Location: ../encontrar-torneio.php"); // Redireciona para a página de torneios
                exit;
            }
        } else {
            $_SESSION['mensagem'] = ["danger", "Falha ao realizar a inscrição. Verifique se você ou seu parceiro já estão inscritos nesta categoria."];
            header("Location: ../inscrever-torneio.php?torneio_id=" . $torneio_id);
            exit;
        }
    } catch (Exception $e) {
        error_log("Erro ao inscrever dupla: " . $e->getMessage());
        $_SESSION['mensagem'] = ["danger", "Ocorreu um erro inesperado ao processar sua inscrição."];
        header("Location: ../inscrever-torneio.php?torneio_id=" . $torneio_id);
        exit;
    }
} else {
    $_SESSION['mensagem'] = ["danger", "Método de requisição inválido."];
    header("Location: ../encontrar-torneio.php");
    exit;
}
