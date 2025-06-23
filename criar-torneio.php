<?php require_once '#_global.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<?php
require_once '_head.php';
// A lógica de verificação de sessão e busca de dados vem após o _head.php, que inicia a sessão.
?>

<body class="bg-gray-100 min-h-screen text-gray-800">

    <!-- Navbar superior -->
    <?php require_once '_nav_superior.php'; ?>

    <div class="flex pt-16">
        <!-- Menu lateral -->
        <?php require_once '_nav_lateral.php'; ?>

        <!-- Conteúdo principal -->
        <main class="flex-1 p-4">
            <?php
            // Verifica se o usuário está logado
            if (!isset($_SESSION['DuplaUserId'])) {
                header("Location: index.php"); // Redireciona para a página de login se não estiver logado
                exit;
            }

            $usuario_id = $_SESSION['DuplaUserId'];

            // Busca as arenas onde o usuário é fundador
            $arenas_fundadas = Arena::getUserArenasFundadas($usuario_id);
            ?>
            <section class="max-w-2xl mx-auto w-full bg-white rounded-2xl shadow-xl p-6 md:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-3xl">🏆</span>
                    <h1 class="text-2xl font-bold text-gray-800">Criar Novo Torneio</h1>
                </div>

                <!-- Exibição de mensagens de feedback -->
                <?php if (isset($_SESSION['mensagem'])) : ?>
                    <?php
                    $tipo_alerta = $_SESSION['mensagem'][0]; // 'success' ou 'danger'
                    $texto_alerta = $_SESSION['mensagem'][1];
                    $cor_alerta = ($tipo_alerta === 'success')
                        ? 'bg-green-100 border-green-400 text-green-700'
                        : 'bg-red-100 border-red-400 text-red-700';
                    ?>
                    <div class="border px-4 py-3 rounded-lg relative mb-4 <?= $cor_alerta ?>" role="alert">
                        <span class="block sm:inline"><?= htmlspecialchars($texto_alerta) ?></span>
                    </div>
                    <?php unset($_SESSION['mensagem']); ?>
                <?php endif; ?>

                <form action="controller-torneio/salvar-torneio.php" method="POST" class="space-y-4">
                    <!-- Arena -->
                    <div class="form-control w-full">
                        <label class="label" for="arena">
                            <span class="label-text font-semibold">Arena Sede</span>
                        </label>
                        <select name="arena" id="arena" class="select select-bordered w-full" required>
                            <option value="" disabled selected>Selecione uma de suas arenas</option>
                            <?php if (empty($arenas_fundadas)) : ?>
                                <option disabled>Você não é fundador de nenhuma arena.</option>
                            <?php else : ?>
                                <?php foreach ($arenas_fundadas as $arena) : ?>
                                    <option value="<?= htmlspecialchars($arena['id']) ?>"><?= htmlspecialchars($arena['titulo']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <?php if (empty($arenas_fundadas)) : ?>
                            <label class="label">
                                <span class="label-text-alt text-red-500">Para criar um torneio, você precisa primeiro <a href="criar-arena.php" class="link link-primary">criar uma arena</a>.</span>
                            </label>
                        <?php endif; ?>
                    </div>

                    <!-- Título -->
                    <div class="form-control w-full">
                        <label class="label" for="titulo">
                            <span class="label-text font-semibold">Título do Torneio</span>
                        </label>
                        <input type="text" name="titulo" id="titulo" placeholder="Ex: Torneio de Verão" class="input input-bordered w-full" required>
                    </div>

                    <!-- Sobre -->
                    <div class="form-control w-full">
                        <label class="label" for="sobre">
                            <span class="label-text font-semibold">Sobre o Torneio</span>
                        </label>
                        <textarea name="sobre" id="sobre" rows="3" placeholder="Descreva as regras, categorias, etc." class="textarea textarea-bordered w-full"></textarea>
                    </div>

                    <!-- Datas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control w-full">
                            <label class="label" for="inicio_inscricao"><span class="label-text font-semibold">Início das Inscrições</span></label>
                            <input type="datetime-local" name="inicio_inscricao" id="inicio_inscricao" class="input input-bordered w-full" required>
                        </div>
                        <div class="form-control w-full">
                            <label class="label" for="fim_inscricao"><span class="label-text font-semibold">Fim das Inscrições</span></label>
                            <input type="datetime-local" name="fim_inscricao" id="fim_inscricao" class="input input-bordered w-full" required>
                        </div>
                        <div class="form-control w-full">
                            <label class="label" for="inicio_torneio"><span class="label-text font-semibold">Início do Torneio</span></label>
                            <input type="datetime-local" name="inicio_torneio" id="inicio_torneio" class="input input-bordered w-full" required>
                        </div>
                        <div class="form-control w-full">
                            <label class="label" for="fim_torneio"><span class="label-text font-semibold">Fim do Torneio</span></label>
                            <input type="datetime-local" name="fim_torneio" id="fim_torneio" class="input input-bordered w-full" required>
                        </div>
                    </div>

                    <!-- Valores -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control w-full">
                            <label class="label" for="valor_primeira_insc"><span class="label-text font-semibold">Valor 1ª Inscrição (R$)</span></label>
                            <input type="text" name="valor_primeira_insc" id="valor_primeira_insc" class="input input-bordered w-full" value="" placeholder="0,00">
                        </div>
                        <div class="form-control w-full">
                            <label class="label" for="valor_segunda_insc"><span class="label-text font-semibold">Valor 2ª Inscrição (R$)</span></label>
                            <input type="text" name="valor_segunda_insc" id="valor_segunda_insc" class="input input-bordered w-full" value="" placeholder="0,00">
                        </div>
                    </div>

                    <!-- Botão de Submissão -->
                    <div class="mt-8">
                        <button type="submit" class="btn btn-primary w-full text-lg" <?= empty($arenas_fundadas) ? 'disabled' : '' ?>>
                            Criar Torneio
                        </button>
                    </div>
                </form>
            </section>
            <br><br><br>
        </main>
    </div>

    <footer class="w-full bg-white border-t border-gray-200 py-4 text-center fixed bottom-0 left-0 z-50">
        DUPLA - Deu Game? Dá Ranking!
    </footer>

</body>

</html>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $(document).ready(function() {
        $('#valor_primeira_insc').mask('#.##0,00', {reverse: true});
        $('#valor_segunda_insc').mask('#.##0,00', {reverse: true});
    });
</script>