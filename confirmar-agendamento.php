<?php
require_once '#_global.php';
?>
<!DOCTYPE html>
<html lang="pt-br">

<?php require_once '_head.php'; ?>
<style>
    /* --- CSS PARA A TELA DE CARREGAMENTO (JÁ EXISTENTE) --- */
    #loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .spinner {
        border: 8px solid #f3f3f3;
        border-top: 8px solid #3498db;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        animation: spin 1.5s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .hidden {
        display: none;
    }

    /* --- 1. ESTILIZAÇÃO DO CHECKBOX (NOVO) --- */
    .custom-checkbox-container {
        display: flex;
        align-items: center;
        margin-top: 1rem;
        margin-bottom: 1rem;
        cursor: pointer;
    }

    .custom-checkbox-container input[type="checkbox"] {
        display: none;
        /* Esconde o checkbox padrão */
    }

    .custom-checkbox-container .checkbox-label {
        position: relative;
        padding-left: 35px;
        /* Espaço para o checkbox customizado */
        cursor: pointer;
        font-size: 0.9em;
        color: #4a5568;
        user-select: none;
    }

    /* Estilo da "caixa" do checkbox */
    .custom-checkbox-container .checkbox-label::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
        height: 20px;
        border: 2px solid #cbd5e0;
        border-radius: 4px;
        background-color: #fff;
        transition: background-color 0.2s, border-color 0.2s;
    }

    /* Estilo do "check" (aparece quando marcado) */
    .custom-checkbox-container .checkbox-label::after {
        content: '';
        position: absolute;
        left: 7px;
        top: 50%;
        transform: translateY(-50%) rotate(45deg);
        width: 6px;
        height: 12px;
        border: solid white;
        border-width: 0 3px 3px 0;
        opacity: 0;
        transition: opacity 0.2s;
    }

    /* Mudança de cor quando o checkbox está marcado */
    .custom-checkbox-container input[type="checkbox"]:checked+.checkbox-label::before {
        background-color: #3b82f6;
        /* Azul primário ou a cor do seu tema */
        border-color: #3b82f6;
    }

    /* Mostra o "check" quando marcado */
    .custom-checkbox-container input[type="checkbox"]:checked+.checkbox-label::after {
        opacity: 1;
    }

    /* Estilo para o botão quando estiver desabilitado */
    #btn-pagar:disabled {
        background-color: #ccc;
        border-color: #ccc;
        cursor: not-allowed;
        opacity: 0.7;
    }
/* --- ESTILOS MELHORADOS PARA O BOTÃO DE CARREGAMENTO --- */

/* 1. Prepara o botão para o estado de loading */
#btn-pagar {
    position: relative; /* Essencial para posicionar o spinner dentro dele */
    /* Recomendo uma altura mínima para o botão não mudar de tamanho */
    min-height: 44px; 
}

/* 2. Esconde o texto quando o botão estiver em modo .loading */
#btn-pagar.loading .btn-text {
    visibility: hidden; /* Esconde o texto, mas mantém o espaço do botão */
}

/* 3. Estilo do Spinner (refinado para ser mais sutil e fluido) */
.btn-loading-spinner {
    display: none; /* Escondido por padrão */
    position: absolute; /* Posicionado em relação ao #btn-pagar */
    
    /* Centraliza o spinner no meio do botão */
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);

    width: 20px;
    height: 20px;
    border: 2px solid rgba(255, 255, 255, 0.4); /* Borda mais fina */
    border-top-color: #ffffff; /* Cor principal da animação */
    border-radius: 50%;
    animation: spin 0.8s linear infinite; /* Animação mais fluida */
}

/* 4. Mostra o spinner quando o botão estiver em modo .loading */
#btn-pagar.loading .btn-loading-spinner {
    display: block;
}

/* 5. Animação de rotação (adicione se ainda não tiver) */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

    /* Classe para mostrar o spinner e esconder o texto */
    #btn-pagar.loading {
        justify-content: center;
    }

    #btn-pagar.loading .btn-text {
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

    <?php require_once '_nav_superior.php' ?>

    <div class="flex pt-16">
        <?php require_once '_nav_lateral.php' ?>

        <main class="flex-1 py-8 px-4">
            <div class="max-w-5xl mx-auto w-full">

                <div class="w-full text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-blue-100 to-green-100 mb-4 shadow-md">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-extrabold text-gray-800 mb-2 tracking-tight">Quase lá!</h1>
                    <p class="text-base text-gray-600">Falta pouco para o seu próximo jogaço! Confira os dados e garanta sua quadra.</p>
                </div>

                <form id="formPagamento" action="controller-pagamento/pagamento-reserva.php" method="POST" class="w-full grid grid-cols-1 lg:grid-cols-3 lg:gap-12">
                    <input type="hidden" name="slots" value="<?= htmlspecialchars($slots_json) ?>">
                    <input type="hidden" name="arena_nome" value="<?= htmlspecialchars($arena_nome) ?>">
                    <input type="hidden" name="arena_id" value="<?= htmlspecialchars($arena_id) ?>">
                    <input type="hidden" name="taxa_servico" id="taxa_servico_input" value="">

                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-6 space-y-8">
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
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                            </svg>
                                            <span class="font-semibold">Cartão de Crédito</span>
                                        </label>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="lg:sticky top-24 bg-white rounded-2xl shadow-xl border border-gray-200 p-6 mt-8 lg:mt-0">
                            <h2 class="text-xl font-bold text-gray-700 border-b pb-2 mb-4">Resumo da Reserva</h2>

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

                            <div class="form-control mb-4">
                                <label class="label"><span class="label-text text-sm">Cupom de Desconto</span></label>
                                <div class="join w-full">
                                    <input type="text" id="cupomInput" name="cupom" placeholder="Insira seu cupom" class="input input-bordered join-item w-full input-sm">
                                    <button type="button" id="btnAplicarCupom" class="btn btn-sm join-item">Aplicar</button>
                                </div>
                                <div id="cupomFeedback" class="text-xs mt-1"></div>
                            </div>

                            <div class="space-y-2 border-t pt-4 text-sm">
                                <div class="flex justify-between"><span>Subtotal</span><span id="subtotalValor">R$ <?= number_format($valor_total, 2, ',', '.') ?></span></div>
                                <div class="flex justify-between text-gray-700"><span>Taxa Operacional</span><span id="taxaAdmin">R$ 0,00</span></div>
                                <div id="descontoLinha" class="flex justify-between text-red-600 hidden"><span>Desconto</span><span id="descontoValor">- R$ 0,00</span></div>
                                <div class="flex justify-between font-bold text-lg border-t pt-2 mt-2"><span>Total</span><span id="totalValor" class="text-green-600">R$ <?= number_format($valor_total, 2, ',', '.') ?></span></div>
                            </div>

                            <div class="custom-checkbox-container">
                                <input type="checkbox" id="termos-checkbox">
                                <label for="termos-checkbox" class="checkbox-label">Eu li e aceito os termos de agendamento.</label>
                            </div>

                            <div class="pt-2">
                                <button type="submit" id="btn-pagar" class="btn btn-primary w-full flex items-center" disabled>
                                    <span class="btn-text">Confirmar e Pagar</span>
                                    <span class="btn-loading-spinner"></span>
                                </button>
                            </div>

                            <div class="w-full my-4 border-t border-gray-200"></div>

                            <div class="terms-link-container text-center">
                                <p class="text-xs text-gray-500 mb-1"> Ao confirmar, você concorda com nossa política. </p>
                                <button id="open-terms-modal" type="button" class="text-sm font-medium text-blue-600 hover:underline">
                                    Ver Termos de Agendamento
                                </button>
                            </div>

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

        <div id="modal-termos" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.6); align-items:center; justify-content:center;">
            <div style="background-color:#fff; margin: auto; padding: 30px; border-radius: 15px; width: 90%; max-width: 800px; font-family: 'Poppins', sans-serif; color:#333; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">

                <h2 style="margin-bottom: 20px; color:#10ac84;">Termos de Agendamento e Cancelamento</h2>

                <p>Ao confirmar seu agendamento, você declara que leu e aceita os termos abaixo:</p>

                <h3>1. Confirmação de Agendamento</h3>
                <ul>
                    <li>A reserva é confirmada imediatamente após o pagamento.</li>
                    <li>Você receberá confirmação via WhatsApp e no aplicativo.</li>
                    <li>O horário será bloqueado exclusivamente para você.</li>
                </ul>

                <h3>2. Cancelamento pelo Jogador</h3>
                <p>Cancelamentos podem ser feitos diretamente no app. Regras padrão:</p>
                <table style="width:100%; border-collapse:collapse; margin:10px 0;">
                    <thead>
                        <tr style="background:#f0f0f0;">
                            <th style="border:1px solid #ccc; padding:8px;">Prazo do Cancelamento</th>
                            <th style="border:1px solid #ccc; padding:8px;">Condição</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="border:1px solid #ccc; padding:8px;">Mais de 12h de antecedência</td>
                            <td style="border:1px solid #ccc; padding:8px;">Crédito ou reembolso integral descontado as taxas de serviço</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #ccc; padding:8px;">Entre 6h e 12h</td>
                            <td style="border:1px solid #ccc; padding:8px;">Crédito parcial (50%)</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #ccc; padding:8px;">Menos de 6h</td>
                            <td style="border:1px solid #ccc; padding:8px;">Sem crédito ou reembolso</td>
                        </tr>
                    </tbody>
                </table>
                <p><strong>Observação:</strong> A política pode variar por arena. Consulte os detalhes antes de concluir.</p>

                <h3>3. Cancelamento por Condições Climáticas</h3>
                <p>Se a quadra estiver inutilizável por chuva:</p>
                <ul>
                    <li>Você poderá reagendar sem custo;</li>
                    <li>Ou receber crédito integral para nova reserva.</li>
                    <li>O status será definido pela arena.</li>
                </ul>

                <h3>4. Cancelamento pela Arena</h3>
                <ul>
                    <li>Arena pode cancelar por motivo operacional.</li>
                    <li>Você poderá reagendar ou ser reembolsado.</li>
                </ul>

                <h3>5. Histórico e Controle</h3>
                <ul>
                    <li>Seu histórico de agendamentos estará disponível no app.</li>
                    <li>Cancelamentos excessivos podem limitar seu uso futuro.</li>
                </ul>

                <h3>6. Reembolso e Créditos</h3>
                <ul>
                    <li>Créditos vão para sua carteira no app DUPLA.</li>
                    <li>Reembolsos, se aplicáveis, levam até 7 dias úteis.</li>
                </ul>

                <h3>7. Aceite</h3>
                <p>Ao seguir com o pagamento, você:</p>
                <ul>
                    <li>Confirma que leu e compreendeu esta política;</li>
                    <li>Aceita as condições e regras aqui descritas.</li>
                </ul>
                <div style="text-align:right; margin-top:30px;">
                    <button id="close-terms-modal" style="background:#10ac84;color:#fff;border:none;padding:10px 20px;border-radius:8px;cursor:pointer; font-size: 1em;">
                        Fechar
                    </button>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // === Elementos do DOM ===
            const termosCheckbox = document.getElementById('termos-checkbox');
            const pagarBtn = document.getElementById('btn-pagar');
            const formPagamento = document.getElementById('formPagamento');
            const modal = document.getElementById('modal-termos');
            const openModalBtn = document.getElementById('open-terms-modal');
            const closeModalBtn = document.getElementById('close-terms-modal');

            // === LÓGICA DO CHECKBOX E BOTÃO PAGAR ===
            termosCheckbox.addEventListener('change', function() {
                pagarBtn.disabled = !this.checked;
            });

            // === LÓGICA PARA ABRIR E FECHAR O MODAL ===
            openModalBtn.addEventListener('click', () => {
                modal.style.display = 'flex'; // Usar 'flex' para centralizar o conteúdo
            });

            closeModalBtn.addEventListener('click', () => {
                modal.style.display = 'none';
            });

            // Fecha o modal se o usuário clicar na área do overlay (fora do conteúdo)
            window.addEventListener('click', (event) => {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });

            // === LÓGICA DE SUBMISSÃO DO FORMULÁRIO COM LOADING ===
            formPagamento.addEventListener('submit', function(e) {
                e.preventDefault(); // Previne o envio padrão do formulário

                // Ativa o estado de loading no botão
                pagarBtn.classList.add('loading');
                pagarBtn.disabled = true;

                const formData = new FormData(formPagamento);
                fetch(formPagamento.action, {
                        method: 'POST',
                        body: formData
                    })
                    .then(async res => {
                        if (res.ok) {
                            return res.json();
                        }
                        const errorData = await res.json().catch(() => null);
                        const errorMessage = errorData?.message || `Ocorreu um erro no servidor (Código: ${res.status}).`;
                        return Promise.reject(new Error(errorMessage));
                    })
                    .then(data => {
                        if (data.status === 'success' && data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            // Trata erros lógicos retornados pelo servidor com status 200 OK
                            throw new Error(data.message || 'Resposta inválida do servidor.');
                        }
                    })
                    .catch(error => {
                        console.error('Erro na submissão:', error);
                        alert('Erro ao processar pagamento: ' + error.message);

                        // Restaura o botão ao estado normal em caso de erro
                        pagarBtn.classList.remove('loading');
                        // Re-habilita o botão apenas se o checkbox de termos continuar marcado
                        pagarBtn.disabled = !termosCheckbox.checked;
                    });
            });

            // === LÓGICA DO CUPOM E CÁLCULO (CÓDIGO ORIGINAL) ===
            const btnAplicarCupom = document.getElementById('btnAplicarCupom');
            const cupomInput = document.getElementById('cupomInput');
            const cupomFeedback = document.getElementById('cupomFeedback');
            const subtotalValor = document.getElementById('subtotalValor');
            const descontoLinha = document.getElementById('descontoLinha');
            const descontoValor = document.getElementById('descontoValor');
            const totalValor = document.getElementById('totalValor');

            const subtotalNumerico = <?= $valor_total ?>;
            const taxaAdmin = document.getElementById('taxaAdmin');
            const metodoPagamentoRadios = document.querySelectorAll('input[name="metodo_pagamento"]');
            let taxaPercentual = 0.01; // padrão PIX
            let descontoAplicado = 0;

            function formatCurrency(value) {
                return 'R$ ' + value.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
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

            atualizarTotal(); // Executa uma vez no carregamento da página
        });
    </script>
    <?php require_once '_footer.php'; ?>
</body>

</html>