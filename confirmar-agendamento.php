<?php
require_once '#_global.php';
?>
<!DOCTYPE html>
<html lang="pt-br">

<?php require_once '_head.php'; ?>
    <style>
        /* --- CSS PARA A TELA DE CARREGAMENTO --- */

        /* O container do overlay que cobre a tela inteira */
        #loading-overlay {
            position: fixed; /* Fica fixo na tela, mesmo com scroll */
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7); /* Fundo preto semi-transparente */
            display: flex; /* Usamos flexbox para centralizar o spinner */
            justify-content: center;
            align-items: center;
            z-index: 9999; /* Garante que fique por cima de tudo */
        }

        /* O spinner (círculo giratório) */
        .spinner {
            border: 8px solid #f3f3f3; /* Cinza claro */
            border-top: 8px solid #3498db; /* Azul */
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1.5s linear infinite;
        }

        /* Animação de rotação */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Classe para esconder o overlay */
        .hidden {
            display: none;
        }
    </style>
    
<?php
// 1. Receber e validar os dados dos slots
$slots_json = $_POST['slots'] ?? null;
if (!$slots_json) {
    // Se não houver slots, redireciona de volta com uma mensagem de erro
    $_SESSION['mensagem'] = ['error', 'Nenhum horário foi selecionado para agendamento.'];
    header('Location: reserva-arena.php'); // Idealmente, deveria voltar para a página da arena específica
    exit;
}

$slots = json_decode($slots_json, true);
if (empty($slots)) {
    $_SESSION['mensagem'] = ['error', 'Erro ao processar os horários selecionados.'];
    header('Location: reserva-arena.php');
    exit;
}
// Captura os dados da arena
$arena_id = $_POST['arena_id'] ?? null;
$arena_nome = $_POST['arena_nome'] ?? ($slots[0]['arena_nome'] ?? '');

// 2. Calcular o valor total
$valor_total = 0;
foreach ($slots as $slot) {
    $valor_total += (float)$slot['preco'];
}

// 3. Obter dados do usuário logado para preenchimento
$usuario_id = $_SESSION['DuplaUserId'] ?? null;
$usuario_info = [];
if ($usuario_id) {
    $usuario_info = Usuario::getUsuarioInfoById($usuario_id);
}
?>

<body class="bg-gray-100 min-h-screen text-gray-800 font-sans">

    <!-- Navbar superior -->
    <?php require_once '_nav_superior.php' ?>

    <div class="flex pt-16">
        <!-- Menu lateral -->
        <?php require_once '_nav_lateral.php' ?>

        <main class="flex-1 py-8 px-4">
            <div class="max-w-5xl mx-auto w-full">

                <!-- Título da Página -->
                <div class="w-full text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-blue-100 to-green-100 mb-4 shadow-md">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h1 class="text-3xl font-extrabold text-gray-800 mb-2 tracking-tight">Quase lá!</h1>
                    <p class="text-base text-gray-600">Falta pouco para o seu próximo jogaço! Confira os dados e garanta sua quadra.</p>
                </div>

                <form id="formPagamento" action="controller-pagamento/pagamento-reserva.php" method="POST" class="w-full grid grid-cols-1 lg:grid-cols-3 lg:gap-12">
                    <!-- Passa os slots selecionados para a próxima etapa -->
                    <input type="hidden" name="slots" value="<?= htmlspecialchars($slots_json) ?>">
                    <input type="hidden" name="arena_nome" value="<?= htmlspecialchars($arena_nome) ?>">
                    <input type="hidden" name="arena_id" value="<?= htmlspecialchars($arena_id) ?>">
                    <input type="hidden" name="taxa_servico" id="taxa_servico_input" value="">

                    <!-- Coluna Esquerda: Dados e Pagamento -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-6 space-y-8">
                            <!-- 1. Seus Dados -->
                            <fieldset class="space-y-3">
                                <legend class="text-xl font-bold text-gray-700 border-b pb-2 mb-3">Seus Dados</legend>
                                <div class="form-control">
                                    <label class="label"><span class="label-text">Nome Completo</span></label>
                                    <input type="text" name="nome" class="input input-bordered bg-gray-100" value="<?= htmlspecialchars(($usuario_info['nome'] ?? '') . ' ' . ($usuario_info['sobrenome'] ?? '')) ?>" readonly required>
                                </div>
                                <div class="form-control">
                                    <label class="label"><span class="label-text">E-mail</span></label>
                                    <input type="email" name="email" class="input input-bordered" value="<?= htmlspecialchars($usuario_info['email'] ?? '') ?>" required>
                                </div>
                                <div class="form-control">
                                    <label class="label"><span class="label-text">Telefone</span></label>
                                    <input type="tel" name="telefone" class="input input-bordered" value="<?= htmlspecialchars($usuario_info['telefone'] ?? '') ?>" required>
                                </div>
                            </fieldset>

                            <!-- 2. Pagamento -->
                            <fieldset class="space-y-3">
                                <legend class="text-xl font-bold text-gray-700 border-b pb-2 mb-3">Forma de Pagamento</legend>
                                <div class="form-control">
                                    <div class="flex flex-col gap-2">
                                        <label class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer has-[:checked]:bg-blue-50 has-[:checked]:border-blue-400 transition-all">
                                            <input type="radio" name="metodo_pagamento" value="pix" class="radio radio-primary" checked />
                                            <img src="https://logopng.com.br/logos/pix-106.png" alt="PIX" class="w-6 h-6">
                                            <span class="font-semibold">PIX</span>
                                        </label>
                                        <label class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer has-[:checked]:bg-blue-50 has-[:checked]:border-blue-400 transition-all">
                                            <input type="radio" name="metodo_pagamento" value="cartao" class="radio radio-primary" />
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                                            <span class="font-semibold">Cartão de Crédito</span>
                                        </label>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>

                    <!-- Coluna Direita: Resumo (Sticky) -->
                    <div class="lg:col-span-1">
                        <div class="lg:sticky top-24 bg-white rounded-2xl shadow-xl border border-gray-200 p-6 mt-8 lg:mt-0">
                            <h2 class="text-xl font-bold text-gray-700 border-b pb-2 mb-4">Resumo da Reserva</h2>

                            <!-- Lista de Slots -->
                            <div class="space-y-2 max-h-48 overflow-y-auto pr-2 mb-4">
                                <?php foreach ($slots as $slot): ?>
                                    <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg">
                                        <div>
                                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($slot['quadra_nome']) ?></p>
                                            <p class="text-sm text-gray-600"><?= date('d/m/Y', strtotime($slot['data'])) ?> às <?= htmlspecialchars($slot['horario']) ?></p>
                                        </div>
                                        <p class="font-bold text-gray-800">R$ <?= number_format($slot['preco'], 2, ',', '.') ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Cupom -->
                            <div class="form-control mb-4">
                                <label class="label"><span class="label-text text-sm">Cupom de Desconto</span></label>
                                <div class="join w-full">
                                    <input type="text" id="cupomInput" name="cupom" placeholder="Insira seu cupom" class="input input-bordered join-item w-full input-sm">
                                    <button type="button" id="btnAplicarCupom" class="btn btn-sm join-item">Aplicar</button>
                                </div>
                                <div id="cupomFeedback" class="text-xs mt-1"></div>
                            </div>

                            <!-- Detalhes do Preço -->
                            <div class="space-y-2 border-t pt-4 text-sm">
                                <div class="flex justify-between"><span>Subtotal</span><span id="subtotalValor">R$ <?= number_format($valor_total, 2, ',', '.') ?></span></div>
                                <div class="flex justify-between text-gray-700"><span>Taxa Operacional</span><span id="taxaAdmin">R$ 0,00</span></div>
                                <div id="descontoLinha" class="flex justify-between text-red-600 hidden"><span>Desconto</span><span id="descontoValor">- R$ 0,00</span></div>
                                <div class="flex justify-between font-bold text-lg border-t pt-2 mt-2"><span>Total</span><span id="totalValor" class="text-green-600">R$ <?= number_format($valor_total, 2, ',', '.') ?></span></div>
                            </div>

                            <!-- Botão Final -->
                            <div class="pt-6">
                                <button type="submit" id="btnSubmit" class="btn btn-success w-full text-lg">
                                    <span class="loading loading-spinner hidden"></span>
                                    Confirmar e Pagar
                                </button>
                            </div>
                            <br>
                            <hr>
                            <br>
                            <div class="pt-3">
                                <a href="reserva-arena.php?arena=<?= urlencode($arena_id) ?>" class="btn btn-outline w-full text-sm">
                                    Alterar Reserva
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnAplicarCupom = document.getElementById('btnAplicarCupom');
        const cupomInput = document.getElementById('cupomInput');
        const cupomFeedback = document.getElementById('cupomFeedback');
        const subtotalValor = document.getElementById('subtotalValor');
        const descontoLinha = document.getElementById('descontoLinha');
        const descontoValor = document.getElementById('descontoValor');
        const totalValor = document.getElementById('totalValor');
        const form = document.getElementById('formPagamento');
        const submitButton = document.getElementById('btnSubmit');
        const submitButtonSpinner = submitButton.querySelector('.loading');

        const subtotalNumerico = <?= $valor_total ?>;
        const taxaAdmin = document.getElementById('taxaAdmin');
        const metodoPagamentoRadios = document.querySelectorAll('input[name="metodo_pagamento"]');
        let taxaPercentual = 0.01; // padrão PIX
        let descontoAplicado = 0;

        function formatCurrency(value) {
            return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        btnAplicarCupom.addEventListener('click', function() {
            const cupom = cupomInput.value.trim().toUpperCase();
            cupomFeedback.textContent = '';
            descontoAplicado = 0; // Reseta o desconto

            if (cupom === '') {
                cupomFeedback.textContent = 'Por favor, insira um cupom.';
                cupomFeedback.className = 'text-xs mt-1 text-red-500';
                atualizarTotal();
                return;
            }

            // --- Simulação de validação de cupom ---
            // Em um cenário real, isso seria uma chamada AJAX para o backend
            if (cupom === 'DUPLA10') {
                descontoAplicado = subtotalNumerico * 0.10; // 10% de desconto
                cupomFeedback.textContent = 'Cupom de 10% aplicado com sucesso!';
                cupomFeedback.className = 'text-xs mt-1 text-green-600';
            } else {
                cupomFeedback.textContent = 'Cupom inválido ou expirado.';
                cupomFeedback.className = 'text-xs mt-1 text-red-500';
            }
            
            atualizarTotal();
        });

        function atualizarTotal() {
            const taxaValor = subtotalNumerico * taxaPercentual;
            taxaAdmin.textContent = formatCurrency(taxaValor);
            document.getElementById('taxa_servico_input').value = taxaValor.toFixed(2);

            if (descontoAplicado > 0) {
                descontoValor.textContent = '- ' + formatCurrency(descontoAplicado);
                descontoLinha.classList.remove('hidden');
            } else {
                descontoLinha.classList.add('hidden');
            }

            const totalFinal = subtotalNumerico - descontoAplicado + taxaValor;
            totalValor.textContent = formatCurrency(totalFinal);
        }

        metodoPagamentoRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                taxaPercentual = radio.value === 'cartao' ? 0.0495 : 0.01;
                atualizarTotal();
            });
        });

        atualizarTotal();

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitButton.classList.add('btn-disabled');
            submitButtonSpinner.classList.remove('hidden');

            const formData = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(async res => { // Usamos async para poder usar await no corpo
                // Se a resposta for bem-sucedida (status 2xx), processa o JSON.
                if (res.ok) {
                    return res.json();
                }
                // Se a resposta for um erro (status 4xx, 5xx), tenta ler o corpo como JSON para obter a mensagem de erro.
                const errorData = await res.json().catch(() => null); // Lida com casos onde o corpo não é JSON
                const errorMessage = errorData?.message || `Ocorreu um erro no servidor (Código: ${res.status}).`;
                // Rejeita a promessa para acionar o bloco .catch() com a mensagem de erro específica.
                return Promise.reject(new Error(errorMessage));
            })
            .then(data => {
                if (data.status === 'success' && data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    // Caso o servidor retorne 200 OK mas com um erro lógico.
                    alert('Erro ao iniciar pagamento: ' + (data.message || 'Resposta inválida do servidor.'));
                    submitButton.classList.remove('btn-disabled');
                    submitButtonSpinner.classList.add('hidden');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                // Exibe a mensagem de erro específica capturada no .then() ou um erro de rede.
                alert('Erro: ' + error.message);
                submitButton.classList.remove('btn-disabled');
                submitButtonSpinner.classList.add('hidden');
            });
        });
    });
    </script>
<?php require_once '_footer.php'; ?>
</body>
</html>