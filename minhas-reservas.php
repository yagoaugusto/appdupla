<?php require_once '#_global.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>
<style>
    /* CSS CORRIGIDO E FINALIZADO para as estrelas */
    .star-rating-interactive {
        display: inline-flex;
        /* A mágica acontece aqui: Inverte a ordem visual dos elementos */
        flex-direction: row-reverse;
        justify-content: flex-end; /* Alinha as estrelas à esquerda */
    }

    .star-rating-interactive label {
        color: #d1d5db; /* Cor da estrela vazia */
        font-size: 2rem;
        padding: 0 0.1rem;
        cursor: pointer;
        transition: color 0.2s ease-in-out;
    }

    /* Pinta as estrelas até a que o mouse está em cima */
    .star-rating-interactive:hover label {
        color: #facc15 !important;
    }
    
    /* Mantém as estrelas seguintes (após o hover) com a cor original */
    .star-rating-interactive label:hover ~ label {
        color: #d1d5db !important;
    }
    
    /* Pinta as estrelas selecionadas (após o clique) de forma permanente */
    .star-rating-interactive input:checked ~ label {
        color: #f59e0b;
    }
</style>

<body class="bg-gray-100 min-h-screen text-gray-800" style="color-scheme: light;">

    <?php require_once '_nav_superior.php'; ?>

    <div class="flex pt-16">
        <?php require_once '_nav_lateral.php'; ?>

        <main class="flex-1 min-h-[calc(100vh-4rem)] p-4 sm:p-6">
            <section class="max-w-4xl mx-auto w-full">
                <div class="flex items-center gap-4 mb-6">
                    <span class="text-4xl">🎟️</span>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">Minhas Reservas</h1>
                        <p class="text-sm text-gray-500">Acompanhe, avalie ou cancele seus horários.</p>
                    </div>
                </div>

                <?php
                $usuario_id = $_SESSION['DuplaUserId'] ?? null;
                $reservas_futuras = [];
                $reservas_passadas = [];

                if ($usuario_id) {
                    $reservas = Agendamento::getReservasByUsuarioId($usuario_id);
                    $agora = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));

                    foreach ($reservas as $reserva) {
                        $data_reserva_str = $reserva['data'] . ' ' . $reserva['hora_inicio'];
                        $data_reserva = new DateTime($data_reserva_str, new DateTimeZone('America/Sao_Paulo'));

                        if ($data_reserva > $agora) {
                            $reservas_futuras[] = $reserva;
                        } else {
                            $reserva['ja_avaliada'] = Avaliacao::jaAvaliou($reserva['id'], $usuario_id);
                            $reservas_passadas[] = $reserva;
                        }
                    }
                }
                ?>

                <div class="mb-10">
                    <h2 class="text-2xl font-semibold mb-4 border-b pb-2">Próximos Jogos</h2>
                    <?php if (count($reservas_futuras) > 0): ?>
                        <div class="space-y-4">
                            <?php foreach ($reservas_futuras as $reserva):
                                $data_reserva_str = $reserva['data'] . ' ' . $reserva['hora_inicio'];
                                $data_reserva = new DateTime($data_reserva_str, new DateTimeZone('America/Sao_Paulo'));
                                $agora = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
                                $diferenca = $agora->diff($data_reserva);
                                $total_horas_restantes = ($diferenca->days * 24) + $diferenca->h;
                                $pode_cancelar = $total_horas_restantes >= 24;
                            ?>
                                <div id="reserva-card-<?= $reserva['id'] ?>" class="collapse collapse-arrow bg-white rounded-lg shadow-sm border border-gray-200">
                                    <input type="checkbox" />
                                    <div class="collapse-title text-lg font-medium flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <span class="text-2xl"><?= htmlspecialchars($reserva['arena_bandeira']) ?></span>
                                            <div>
                                                <span class="font-bold text-gray-800"><?= htmlspecialchars($reserva['arena_titulo']) ?></span>
                                                <span class="block text-sm text-gray-500"><?= date('d/m/Y', strtotime($reserva['data'])) ?> às <?= date('H:i', strtotime($reserva['hora_inicio'])) ?></span>
                                            </div>
                                        </div>
                                        <div class="badge badge-success badge-outline font-semibold">✅ Agendado</div>
                                    </div>
                                    <div class="collapse-content bg-gray-50/50">
                                        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                            <div><p class="font-semibold text-gray-500">Quadra</p><p class="text-gray-800"><?= htmlspecialchars($reserva['quadra_nome']) ?></p></div>
                                            <div><p class="font-semibold text-gray-500">Horário</p><p class="text-gray-800"><?= date('H:i', strtotime($reserva['hora_inicio'])) ?> - <?= date('H:i', strtotime($reserva['hora_fim'])) ?></p></div>
                                            <div><p class="font-semibold text-gray-500">Valor Pago</p><p class="text-gray-800 font-bold">R$ <?= number_format($reserva['preco'], 2, ',', '.') ?></p></div>
                                            <div><p class="font-semibold text-gray-500">ID da Reserva</p><p class="text-gray-800 font-mono">#<?= htmlspecialchars($reserva['id']) ?></p></div>
                                            <?php if (!empty($reserva['observacoes'])): ?>
                                                <div class="sm:col-span-2"><p class="font-semibold text-gray-500">Observações</p><p class="text-gray-800"><?= htmlspecialchars($reserva['observacoes']) ?></p></div>
                                            <?php endif; ?>
                                            <div class="sm:col-span-2"><p class="font-semibold text-gray-500">Tempo para o jogo</p><p class="font-bold text-primary countdown-timer" data-start-time="<?= htmlspecialchars($data_reserva_str) ?>">Carregando...</p></div>
                                        </div>
                                        <div class="p-4 border-t border-gray-200 text-right">
                                             <?php if ($pode_cancelar): ?>
                                                <button class="btn btn-sm btn-outline btn-error" onclick="cancelarReserva(<?= $reserva['id'] ?>)">Cancelar Reserva</button>
                                            <?php else: ?>
                                                <span class="text-xs text-gray-400 italic" title="O cancelamento só é permitido com mais de 24 horas de antecedência.">Prazo para cancelamento expirado.</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="bg-white rounded-lg p-6 text-center text-gray-500 italic">Você não possui nenhuma reserva futura.</div>
                    <?php endif; ?>
                </div>

                <div>
                    <h2 class="text-2xl font-semibold mb-4 border-b pb-2">Histórico de Jogos</h2>
                     <?php if (count($reservas_passadas) > 0): ?>
                        <div class="space-y-4">
                            <?php foreach ($reservas_passadas as $reserva): ?>
                                <div id="reserva-card-<?= $reserva['id'] ?>" class="collapse collapse-arrow bg-white rounded-lg shadow-sm border border-gray-200 opacity-70">
                                    <input type="checkbox" />
                                    <div class="collapse-title text-lg font-medium flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <span class="text-2xl"><?= htmlspecialchars($reserva['arena_bandeira']) ?></span>
                                            <div>
                                                <span class="font-bold text-gray-800"><?= htmlspecialchars($reserva['arena_titulo']) ?></span>
                                                <span class="block text-sm text-gray-500"><?= date('d/m/Y', strtotime($reserva['data'])) ?> às <?= date('H:i', strtotime($reserva['hora_inicio'])) ?></span>
                                            </div>
                                        </div>
                                        <div class="badge badge-ghost font-semibold">Finalizada</div>
                                    </div>
                                    <div class="collapse-content bg-gray-50/50">
                                        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                            <div><p class="font-semibold text-gray-500">Quadra</p><p class="text-gray-800"><?= htmlspecialchars($reserva['quadra_nome']) ?></p></div>
                                            <div><p class="font-semibold text-gray-500">Horário</p><p class="text-gray-800"><?= date('H:i', strtotime($reserva['hora_inicio'])) ?> - <?= date('H:i', strtotime($reserva['hora_fim'])) ?></p></div>
                                            <div><p class="font-semibold text-gray-500">Valor Pago</p><p class="text-gray-800 font-bold">R$ <?= number_format($reserva['preco'], 2, ',', '.') ?></p></div>
                                            <div><p class="font-semibold text-gray-500">ID da Reserva</p><p class="text-gray-800 font-mono">#<?= htmlspecialchars($reserva['id']) ?></p></div>
                                            <?php if (!empty($reserva['observacoes'])): ?>
                                                <div class="sm:col-span-2"><p class="font-semibold text-gray-500">Observações</p><p class="text-gray-800"><?= htmlspecialchars($reserva['observacoes']) ?></p></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="p-4 border-t border-gray-200 text-right" id="actions-reserva-<?= $reserva['id'] ?>">
                                            <?php if ($reserva['ja_avaliada']): ?>
                                                <div class="badge badge-success gap-2">⭐ Avaliado</div>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-primary btn-avaliar" 
                                                        data-reserva-id="<?= $reserva['id'] ?>" 
                                                        data-arena-titulo="<?= htmlspecialchars($reserva['arena_titulo']) ?>"
                                                        data-quadra-nome="<?= htmlspecialchars($reserva['quadra_nome']) ?>"
                                                        data-reserva-data="<?= date('d/m/Y', strtotime($reserva['data'])) ?>">
                                                    Avaliar Reserva
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="bg-white rounded-lg p-6 text-center text-gray-500 italic">Você ainda não possui histórico de reservas.</div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <dialog id="modalAvaliacao" class="modal sm:modal-middle">
      <div class="modal-box w-11/12 max-w-2xl">
        <form method="dialog">
          <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>

        <div class="text-center mb-4">
            <h3 class="font-bold text-2xl" id="modal-title-arena">Avalie sua Experiência</h3>
            <p class="text-sm text-gray-500" id="modal-subtitle-reserva">Sua opinião ajuda a comunidade a crescer!</p>
        </div>
        
        <form id="formAvaliacao" class="space-y-6 p-2">
            <input type="hidden" name="reserva_id" id="form_reserva_id">
            
            <?php
            $criterios = [
                'qualidade_quadra' => ['label' => 'Qualidade da Quadra', 'icon' => 'fa-solid fa-volleyball'],
                'pontualidade_disponibilidade' => ['label' => 'Pontualidade e Disponibilidade', 'icon' => 'fa-regular fa-clock'],
                'atendimento_suporte' => ['label' => 'Atendimento e Suporte', 'icon' => 'fa-regular fa-handshake'],
                'ambiente_arena' => ['label' => 'Ambiente da Arena', 'icon' => 'fa-solid fa-map-location-dot'],
                'facilidade_reserva' => ['label' => 'Facilidade de Reserva', 'icon' => 'fa-solid fa-mobile-screen-button']
            ];
            foreach($criterios as $key => $data):
            ?>
                <div class="flex flex-col sm:flex-row items-center gap-4">
                    <div class="w-full sm:w-1/3 text-left">
                        <i class="<?= $data['icon'] ?> text-primary mr-2"></i>
                        <span class="font-semibold"><?= $data['label'] ?></span>
                    </div>
                    <div class="w-full sm:w-2/3 flex items-center gap-4">
                        <div class="star-rating-interactive" data-rating-group="<?= $key ?>">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" name="<?= $key ?>" id="<?= $key . $i ?>" value="<?= $i ?>" class="hidden" />
                                <label for="<?= $key . $i ?>" title="<?= $i ?> estrela(s)">★</label>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-feedback text-sm font-semibold text-gray-500 w-24 text-center" data-feedback-for="<?= $key ?>"></span>
                    </div>
                </div>
            <?php endforeach; ?>

            <div>
                <label class="label" for="comentario"><span class="label-text font-semibold">Comentário (opcional)</span></label>
                <textarea id="comentario" name="comentario" class="textarea textarea-bordered w-full" placeholder="Deixe sua opinião sobre a experiência..."></textarea>
            </div>

            <div class="modal-action mt-6">
                <button type="submit" id="submit-avaliacao" class="btn btn-primary w-full sm:w-auto">
                    <span class="btn-text">Enviar Avaliação</span>
                    <span class="loading loading-spinner loading-sm hidden"></span>
                </button>
            </div>
        </form>
      </div>
    </dialog>

    <?php require_once '_footer.php'; ?>
    <script src="https://kit.fontawesome.com/SUA_CHAVE.js" crossorigin="anonymous"></script>

    <script>
    // Seus outros scripts (contador, cancelamento) permanecem aqui...
    // ...

    // ===== SCRIPT DO MODAL DE AVALIAÇÃO (Funcional e Corrigido) =====
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modalAvaliacao');
        if (!modal) return;
        const form = document.getElementById('formAvaliacao');
        const modalTitle = document.getElementById('modal-title-arena');
        const modalSubtitle = document.getElementById('modal-subtitle-reserva');
        const hiddenInputReservaId = document.getElementById('form_reserva_id');

        const feedbackText = { 1: "Ruim", 2: "Regular", 3: "Bom", 4: "Ótimo", 5: "Excelente!" };

        document.querySelectorAll('.btn-avaliar').forEach(button => {
            button.addEventListener('click', function() {
                modalTitle.textContent = `Avalie sua experiência em ${this.dataset.arenaTitulo}`;
                modalSubtitle.textContent = `Referente à sua reserva em ${this.dataset.quadraNome} no dia ${this.dataset.reservaData}`;
                hiddenInputReservaId.value = this.dataset.reservaId;
                form.reset();
                document.querySelectorAll('.rating-feedback').forEach(el => el.textContent = '');
                modal.showModal();
            });
        });

        document.querySelectorAll('.star-rating-interactive').forEach(group => {
            const groupName = group.dataset.ratingGroup;
            const feedbackEl = document.querySelector(`[data-feedback-for="${groupName}"]`);
            
            group.addEventListener('mouseover', e => {
                if (e.target.tagName === 'LABEL') {
                    const ratingValue = e.target.htmlFor.slice(-1);
                    if (feedbackEl) feedbackEl.textContent = feedbackText[ratingValue] || '';
                }
            });
            group.addEventListener('mouseout', () => {
                const checkedInput = group.querySelector('input:checked');
                if (feedbackEl) {
                    if (checkedInput) {
                        feedbackEl.textContent = feedbackText[checkedInput.value] || '';
                    } else {
                        feedbackEl.textContent = '';
                    }
                }
            });
            group.addEventListener('change', e => {
                if (feedbackEl) feedbackEl.textContent = feedbackText[e.target.value] || '';
            });
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitButton = document.getElementById('submit-avaliacao');
            const btnText = submitButton.querySelector('.btn-text');
            const loadingSpinner = submitButton.querySelector('.loading');
            btnText.classList.add('hidden');
            loadingSpinner.classList.remove('hidden');
            submitButton.disabled = true;

            fetch('controller-agendamento/salvar-avaliacao.php', { method: 'POST', body: new FormData(form) })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Avaliação enviada com sucesso! Obrigado.');
                    modal.close();
                    const actionsDiv = document.getElementById(`actions-reserva-${new FormData(form).get('reserva_id')}`);
                    if (actionsDiv) {
                        actionsDiv.innerHTML = '<div class="badge badge-success gap-2">⭐ Avaliado</div>';
                    }
                } else {
                    alert('Erro: ' + (data.message || 'Não foi possível enviar sua avaliação.'));
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                alert('Ocorreu um erro de comunicação.');
            })
            .finally(() => {
                btnText.classList.remove('hidden');
                loadingSpinner.classList.add('hidden');
                submitButton.disabled = false;
            });
        });
    });
    </script>
</body>
</html>