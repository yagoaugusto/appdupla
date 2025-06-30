<?php require_once '#_global.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>

<body class="bg-gray-100 min-h-screen text-gray-800" style="color-scheme: light;">

    <!-- Navbar superior -->
    <?php require_once '_nav_superior.php'; ?>

    <div class="flex pt-16">
        <!-- Menu lateral -->
        <?php require_once '_nav_lateral.php'; ?>

        <!-- Conteúdo principal -->
        <main class="flex-1 min-h-[calc(100vh-4rem)] p-4 sm:p-6">
            <section class="max-w-4xl mx-auto w-full">
                <div class="flex items-center gap-4 mb-6">
                    <span class="text-4xl">🎟️</span>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">Minhas Reservas</h1>
                        <p class="text-sm text-gray-500">Acompanhe o status das suas reservas de quadra.</p>
                    </div>
                </div>

                <?php
                $usuario_id = $_SESSION['DuplaUserId'] ?? null;
                $reservas = [];
                if ($usuario_id) {
                    $reservas = Agendamento::getReservasByUsuarioId($usuario_id);
                }

                ?>

                <?php if (empty($reservas)): ?>
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                        </svg>
                        <h3 class="mt-2 text-lg font-medium text-gray-900">Nenhuma reserva encontrada</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Você ainda não fez nenhuma reserva. Que tal encontrar uma quadra agora?
                        </p>
                        <div class="mt-6">
                            <a href="encontre-quadra.php" class="btn btn-primary">
                                Encontrar Quadras
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($reservas as $reserva):
                            $dataReserva = new DateTime($reserva['data']);
                            $isPast = $dataReserva < new DateTime('today');
                            $cardOpacity = $isPast ? 'opacity-60' : '';
                        ?>
                            <div class="collapse collapse-arrow bg-white rounded-lg shadow-sm border border-gray-200 <?= $cardOpacity ?>">
                                <input type="checkbox" />
                                <div class="collapse-title text-lg font-medium flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl"><?= htmlspecialchars($reserva['arena_bandeira']) ?></span>
                                        <div>
                                            <span class="font-bold text-gray-800"><?= htmlspecialchars($reserva['arena_titulo']) ?></span>
                                            <span class="block text-sm text-gray-500"><?= date('d/m/Y', strtotime($reserva['data'])) ?> às <?= date('H:i', strtotime($reserva['hora_inicio'])) ?></span>
                                        </div>
                                    </div>
                                    <div class="badge badge-success badge-outline font-semibold">✅ Aprovada</div>
                                </div>
                                <div class="collapse-content bg-gray-50/50">
                                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <p class="font-semibold text-gray-500">Quadra</p>
                                            <p class="text-gray-800"><?= htmlspecialchars($reserva['quadra_nome']) ?></p>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-500">Horário</p>
                                            <p class="text-gray-800"><?= date('H:i', strtotime($reserva['hora_inicio'])) ?> - <?= date('H:i', strtotime($reserva['hora_fim'])) ?></p>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-500">Valor Pago</p>
                                            <p class="text-gray-800 font-bold">R$ <?= number_format($reserva['preco'], 2, ',', '.') ?></p>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-500">ID da Reserva</p>
                                            <p class="text-gray-800 font-mono">#<?= htmlspecialchars($reserva['id']) ?></p>
                                        </div>
                                        <?php if (!empty($reserva['observacoes'])): ?>
                                            <div class="sm:col-span-2">
                                                <p class="font-semibold text-gray-500">Observações</p>
                                                <p class="text-gray-800"><?= htmlspecialchars($reserva['observacoes']) ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <?php require_once '_footer.php'; ?>
</body>

</html>