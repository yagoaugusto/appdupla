<?php
require_once '#_global.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php
require_once '_head.php';

// --- LÓGICA DA PÁGINA ---

// 1. Verifica se o usuário está logado
if (!isset($_SESSION['DuplaUserId'])) {
    header("Location: index.php");
    exit;
}
$usuario_id = $_SESSION['DuplaUserId'];

// 2. Pega o ID do torneio da URL e valida
$torneio_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$torneio_id) {
    $_SESSION['mensagem'] = ["danger", "ID de torneio inválido."];
    header("Location: principal.php");
    exit;
}

// 3. Busca os dados do torneio e verifica a propriedade
$torneio = Torneio::getTorneioById($torneio_id);
if (!$torneio || $torneio['responsavel_id'] != $usuario_id) {
    $_SESSION['mensagem'] = ["danger", "Torneio não encontrado ou você não tem permissão para gerenciá-lo."];
    header("Location: principal.php");
    exit;
}

// Função para formatar datas para campos datetime-local
function formatarDataParaInput($data)
{
    return $data ? date('Y-m-d\TH:i', strtotime($data)) : '';
}

// Função para formatar valores monetários para exibição no input (formato brasileiro)
function formatarValorParaInput($valor) {
    return number_format($valor, 2, ',', '.');
}

// Busca as categorias do torneio
$categorias = Categoria::getCategoriesByTorneioId($torneio_id);
?>

<body class="bg-gray-100 min-h-screen text-gray-800">

    <!-- Navbar superior -->
    <?php require_once '_nav_superior.php'; ?>

    <div class="flex pt-16">
        <!-- Menu lateral -->
        <?php require_once '_nav_lateral.php'; ?>

        <!-- Conteúdo principal -->
        <main class="flex-1 p-4">
            <section class="mx-auto w-full bg-white rounded-2xl shadow-xl p-6 md:p-8"> <!-- Removido max-w-4xl -->

                <!-- Cabeçalho -->
                <div class="flex items-start gap-4 mb-6">
                    <span class="text-4xl">🏆</span>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($torneio['titulo']) ?></h1>
                        <p class="text-sm text-gray-500">Gerenciando na arena: <span class="font-semibold"><?= htmlspecialchars($torneio['arena_titulo']) ?></span></p>
                    </div>
                </div>

                <!-- Seções Colapsáveis -->
                <div class="space-y-6">

                    <!-- Visão Geral -->
                    <div class="collapse collapse-arrow bg-base-100 rounded-box shadow-xl border border-gray-200">
                        <input type="checkbox" checked /> <!-- Aberto por padrão -->
                        <div class="collapse-title text-lg font-bold flex items-center gap-2">
                            <span class="text-xl">📊</span> Visão Geral
                        </div>
                        <div class="collapse-content">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Coluna da Esquerda: Sobre e Valores -->
                                <div class="space-y-6">
                                    <!-- Card Sobre -->
                                    <div>
                                        <h3 class="font-bold text-lg mb-2 flex items-center gap-2"><span class="text-xl">ℹ️</span> Sobre</h3>
                                        <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg border"><?= nl2br(htmlspecialchars($torneio['sobre'] ?: 'Nenhuma descrição fornecida.')) ?></p>
                                    </div>
                                    <!-- Card Valores -->
                                    <div>
                                        <h3 class="font-bold text-lg mb-2 flex items-center gap-2"><span class="text-xl">💰</span> Valores</h3>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg border">
                                                <span class="font-semibold text-gray-700">1ª Inscrição:</span>
                                                <span class="font-bold text-green-600">R$ <?= number_format($torneio['valor_primeira_insc'], 2, ',', '.') ?></span>
                                            </div>
                                            <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg border">
                                                <span class="font-semibold text-gray-700">2ª Inscrição:</span>
                                                <span class="font-bold text-green-600">R$ <?= number_format($torneio['valor_segunda_insc'], 2, ',', '.') ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Coluna da Direita: Cronograma -->
                                <div>
                                    <h3 class="font-bold text-lg mb-2 flex items-center gap-2"><span class="text-xl">🗓️</span> Cronograma</h3>
                                    <div class="space-y-2 text-sm">
                                        <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                                            <p class="font-semibold text-blue-800">Início das Inscrições:</p>
                                            <p class="font-mono text-blue-700"><?= date('d/m/Y \à\s H:i', strtotime($torneio['inicio_inscricao'])) ?></p>
                                        </div>
                                        <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                                            <p class="font-semibold text-blue-800">Fim das Inscrições:</p>
                                            <p class="font-mono text-blue-700"><?= date('d/m/Y \à\s H:i', strtotime($torneio['fim_inscricao'])) ?></p>
                                        </div>
                                        <div class="bg-green-50 p-3 rounded-lg border border-green-200 mt-4">
                                            <p class="font-semibold text-green-800">Início do Torneio:</p>
                                            <p class="font-mono text-green-700"><?= date('d/m/Y \à\s H:i', strtotime($torneio['inicio_torneio'])) ?></p>
                                        </div>
                                        <div class="bg-green-50 p-3 rounded-lg border border-green-200">
                                            <p class="font-semibold text-green-800">Fim do Torneio:</p>
                                            <p class="font-mono text-green-700"><?= date('d/m/Y \à\s H:i', strtotime($torneio['fim_torneio'])) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gerenciar Inscritos -->
                    <div class="collapse collapse-arrow bg-base-100 rounded-box shadow-xl border border-gray-200">
                        <input type="checkbox" />
                        <div class="collapse-title text-lg font-bold flex items-center gap-2">
                            <span class="text-xl">👥</span> Gerenciar Inscritos
                        </div>
                        <div class="collapse-content">
                            <h3 class="font-bold text-lg mb-4">Gerenciar Inscritos</h3>
                            <p class="text-gray-600">Funcionalidade em desenvolvimento. Em breve você poderá ver e gerenciar os participantes aqui, enviar comunicados e organizar as chaves do torneio.</p>
                        </div>
                    </div>

                    <!-- Categorias do Torneio -->
                    <div class="collapse collapse-arrow bg-base-100 rounded-box shadow-xl border border-gray-200">
                        <input type="checkbox" />
                        <div class="collapse-title text-lg font-bold flex items-center gap-2">
                            <span class="text-xl">🏷️</span> Categorias do Torneio
                        </div>
                        <div class="collapse-content">
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

                            <!-- Formulário para adicionar nova categoria -->
                            <form action="controller-torneio/salvar-categoria.php" method="POST" class="space-y-4 mb-8 p-4 border rounded-lg bg-gray-50">
                                <input type="hidden" name="torneio_id" value="<?= $torneio_id ?>">
                                <h4 class="font-semibold text-md mb-2">Adicionar Nova Categoria</h4>
                                <div class="form-control w-full">
                                    <label class="label" for="categoria_titulo"><span class="label-text font-semibold">Título da Categoria</span></label>
                                    <input type="text" name="titulo" id="categoria_titulo" placeholder="Ex: Categoria A, Mista Iniciante" class="input input-bordered w-full" required>
                                </div>
                                <div class="form-control w-full">
                                    <label class="label" for="categoria_genero"><span class="label-text font-semibold">Gênero</span></label>
                                    <select name="genero" id="categoria_genero" class="select select-bordered w-full" required>
                                        <option value="" disabled selected>Selecione o gênero</option>
                                        <option value="masculino">Masculino</option>
                                        <option value="feminino">Feminino</option>
                                        <option value="mista">Mista</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-success w-full">Adicionar Categoria</button>
                            </form>

                            <!-- Lista de Categorias Cadastradas -->
                            <h4 class="font-semibold text-md mb-3">Categorias Cadastradas</h4>
                            <div id="listaCategorias" class="space-y-2">
                                <?php if (empty($categorias)): ?>
                                    <p class="text-gray-600 italic">Nenhuma categoria cadastrada ainda.</p>
                                <?php else: ?>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <div class="flex items-center justify-between bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-gray-700"><?= htmlspecialchars($categoria['titulo']) ?></span>
                                                <span class="badge badge-outline badge-sm capitalize"><?= htmlspecialchars($categoria['genero']) ?></span>
                                            </div>
                                            <button class="btn btn-xs btn-error btn-delete-categoria" data-categoria-id="<?= htmlspecialchars($categoria['id']) ?>" data-torneio-id="<?= htmlspecialchars($torneio_id) ?>">Excluir</button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Configurações do Torneio -->
                    <div class="collapse collapse-arrow bg-base-100 rounded-box shadow-xl border border-gray-200">
                        <input type="checkbox" />
                        <div class="collapse-title text-lg font-bold flex items-center gap-2">
                            <span class="text-xl">⚙️</span> Configurações do Torneio
                        </div>
                        <div class="collapse-content">
                            <h3 class="font-bold text-lg mb-4">Editar Informações</h3>
                            <form action="controller-torneio/atualizar-torneio.php" method="POST" class="w-full">
                                <input type="hidden" name="torneio_id" value="<?= $torneio_id ?>">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                    <!-- Título (Ocupa 2 colunas) -->
                                    <div class="form-control w-full md:col-span-2">
                                        <label class="label" for="titulo"><span class="label-text font-semibold">Título do Torneio</span></label>
                                        <input type="text" name="titulo" id="titulo" value="<?= htmlspecialchars($torneio['titulo']) ?>" class="input input-bordered w-full" required>
                                    </div>

                                    <!-- Sobre (Ocupa 2 colunas) -->
                                    <div class="form-control w-full md:col-span-2">
                                        <label class="label" for="sobre"><span class="label-text font-semibold">Sobre o Torneio</span></label>
                                        <textarea name="sobre" id="sobre" rows="3" class="textarea textarea-bordered w-full"><?= htmlspecialchars($torneio['sobre']) ?></textarea>
                                    </div>

                                    <!-- Datas -->
                                    <div class="form-control w-full">
                                        <label class="label" for="inicio_inscricao"><span class="label-text font-semibold">Início das Inscrições</span></label>
                                        <input type="datetime-local" name="inicio_inscricao" id="inicio_inscricao" value="<?= formatarDataParaInput($torneio['inicio_inscricao']) ?>" class="input input-bordered w-full" required>
                                    </div>
                                    <div class="form-control w-full">
                                        <label class="label" for="fim_inscricao"><span class="label-text font-semibold">Fim das Inscrições</span></label>
                                        <input type="datetime-local" name="fim_inscricao" id="fim_inscricao" value="<?= formatarDataParaInput($torneio['fim_inscricao']) ?>" class="input input-bordered w-full" required>
                                    </div>
                                    <div class="form-control w-full">
                                        <label class="label" for="inicio_torneio"><span class="label-text font-semibold">Início do Torneio</span></label>
                                        <input type="datetime-local" name="inicio_torneio" id="inicio_torneio" value="<?= formatarDataParaInput($torneio['inicio_torneio']) ?>" class="input input-bordered w-full" required>
                                    </div>
                                    <div class="form-control w-full">
                                        <label class="label" for="fim_torneio"><span class="label-text font-semibold">Fim do Torneio</span></label>
                                        <input type="datetime-local" name="fim_torneio" id="fim_torneio" value="<?= formatarDataParaInput($torneio['fim_torneio']) ?>" class="input input-bordered w-full" required>
                                    </div>

                                    <!-- Valores -->
                                    <div class="form-control w-full">
                                        <label class="label" for="valor_primeira_insc"><span class="label-text font-semibold">Valor 1ª Inscrição (R$)</span></label>
                                        <input type="text" name="valor_primeira_insc" id="valor_primeira_insc" value="<?= formatarValorParaInput($torneio['valor_primeira_insc']) ?>" class="input input-bordered w-full" placeholder="0,00">
                                    </div>
                                    <div class="form-control w-full">
                                        <label class="label" for="valor_segunda_insc"><span class="label-text font-semibold">Valor 2ª Inscrição (R$)</span></label>
                                        <input type="text" name="valor_segunda_insc" id="valor_segunda_insc" value="<?= formatarValorParaInput($torneio['valor_segunda_insc']) ?>" class="input input-bordered w-full" placeholder="0,00">
                                    </div>

                                    <!-- Botão de Submissão (Ocupa 2 colunas) -->
                                    <div class="md:col-span-2 mt-4">
                                        <button type="submit" class="btn btn-primary w-full">
                                            Salvar Alterações
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Zona de Perigo -->
                    <div class="collapse collapse-arrow bg-base-100 rounded-box shadow-xl border border-gray-200">
                        <input type="checkbox" />
                        <div class="collapse-title text-lg font-bold flex items-center gap-2">
                            <span class="text-xl">⚠️</span> Zona de Perigo
                        </div>
                        <div class="collapse-content">
                            <div class="border-l-4 border-red-500 bg-red-50 p-4 rounded-lg">
                                <h3 class="font-bold text-lg mb-2 text-red-700 flex items-center gap-2"><span class="text-xl">⚠️</span> Zona de Perigo</h3>
                                <p class="text-sm text-red-600 mb-4">As ações abaixo são permanentes e devem ser usadas com cuidado.</p>
                                <div class="space-y-4">
                                    <div>
                                        <h4 class="font-semibold text-gray-800">Cancelar Torneio</h4>
                                        <p class="text-sm text-gray-600 mb-2">Esta ação irá marcar o torneio como 'cancelado', bloqueando novas inscrições e futuras edições. O registro será mantido para histórico.</p>
                                        <button class="btn btn-warning btn-sm" disabled>Cancelar Torneio (em breve)</button>
                                    </div>
                                    <div class="border-t border-red-200 pt-4">
                                        <h4 class="font-semibold text-gray-800">Excluir Torneio</h4>
                                        <p class="text-sm text-gray-600 mb-2">Esta ação removerá permanentemente o torneio e todas as suas informações do banco de dados. <strong>Não pode ser desfeito.</strong></p>
                                        <button class="btn btn-error btn-sm" disabled>Excluir Torneio Permanentemente (em breve)</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </section>
            <br><br><br>
        </main>
    </div>

    <footer class="w-full bg-white border-t border-gray-200 py-4 text-center fixed bottom-0 left-0 z-50">
        DUPLA - Deu Game? Dá Ranking!
    </footer>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize masks for monetary inputs
            $('#valor_primeira_insc').mask('#.##0,00', {reverse: true});
            $('#valor_segunda_insc').mask('#.##0,00', {reverse: true});

            // Handle category deletion
            $(document).on('click', '.btn-delete-categoria', async function() {
                const categoriaId = $(this).data('categoria-id');
                const torneioId = $(this).data('torneio-id');

                if (confirm('Tem certeza que deseja excluir esta categoria?')) {
                    try {
                        const response = await fetch('controller-torneio/excluir-categoria.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({ categoria_id: categoriaId, torneio_id: torneioId })
                        });
                        const data = await response.json();

                        if (data.success) {
                            alert(data.message);
                            location.reload(); // Reload to reflect changes
                        } else {
                            alert('Erro: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Erro ao excluir categoria:', error);
                        alert('Ocorreu um erro de comunicação ao tentar excluir a categoria.');
                    }
                }
            });
        });
    </script>

</body>

</html>