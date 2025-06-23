<?php
require_once '#_global.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>

<body class="bg-gray-100 min-h-screen text-gray-800">

    <!-- Navbar superior -->
    <?php require_once '_nav_superior.php'; ?>

    <div class="flex pt-16">
        <!-- Menu lateral -->
        <?php require_once '_nav_lateral.php'; ?>

        <!-- Conteúdo principal -->
        <main class="flex-1 p-2 sm:p-4">
            <?php
            $usuario_id = $_SESSION['DuplaUserId'] ?? null;
            if (!$usuario_id) {
                // Este caso deve ser tratado pelo _head.php, mas como um fallback:
                header("Location: index.php");
                exit;
            }
            // Assumindo que a classe Usuario e o método getUsuarioInfoById existem
            $usuario = Usuario::getUsuarioInfoById($usuario_id);

            // Prepara o telefone para exibição, removendo o prefixo '55' para a máscara funcionar corretamente.
            $telefone_exibicao = $usuario['telefone'] ?? '';
            // Verifica se o número começa com '55' e tem mais de 11 dígitos (DDD + número)
            if (strpos($telefone_exibicao, '55') === 0 && strlen($telefone_exibicao) > 11) {
                $telefone_exibicao = substr($telefone_exibicao, 2);
            }
            ?>

            <section class="max-w-xl mx-auto w-full bg-white rounded-2xl shadow-xl p-6 md:p-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-6">Meu Perfil</h1>

                <?php
                // Exibir mensagens de sucesso ou erro da sessão
                if (isset($_SESSION['mensagem'])) {
                    $tipo = $_SESSION['mensagem'][0];
                    $texto = $_SESSION['mensagem'][1];
                    $alert_class = ($tipo === 'success') ? 'alert-success' : 'alert-error';
                    echo "<div class='alert {$alert_class} shadow-lg mb-4'><div><span>" . htmlspecialchars($texto) . "</span></div></div>";
                    unset($_SESSION['mensagem']); // Limpa a mensagem após exibir
                }
                ?>

                <form action="controller-usuario/atualizar-perfil.php" method="POST">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Nome -->
                            <div>
                                <label for="nome" class="label"><span class="label-text">Nome</span></label>
                                <input type="text" id="nome" name="nome" placeholder="Seu nome" class="input input-bordered w-full" value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>" required />
                            </div>
                            <!-- Sobrenome -->
                            <div>
                                <label for="sobrenome" class="label"><span class="label-text">Sobrenome</span></label>
                                <input type="text" id="sobrenome" name="sobrenome" placeholder="Seu sobrenome" class="input input-bordered w-full" value="<?= htmlspecialchars($usuario['sobrenome'] ?? '') ?>" required />
                            </div>
                        </div>

                        <!-- Apelido -->
                        <div>
                            <label for="apelido" class="label"><span class="label-text">Apelido</span></label>
                            <input type="text" id="apelido" name="apelido" placeholder="Seu apelido é gerado em eventos" class="input input-bordered w-full bg-gray-200" value="<?= htmlspecialchars($usuario['apelido'] ?? '') ?>" readonly />
                            <div class="label"><span class="label-text-alt">Seu apelido é conquistado em eventos e não pode ser editado.</span></div>
                        </div>

                        <!-- E-mail -->
                        <div>
                            <label for="email" class="label"><span class="label-text">E-mail</span></label>
                            <input type="email" id="email" name="email" placeholder="seu@email.com" class="input input-bordered w-full" value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" required />
                        </div>

                        <!-- Telefone -->
                        <div>
                            <label for="telefone" class="label"><span class="label-text">Telefone (com DDD)</span></label>
                            <input type="tel" id="telefone" name="telefone" placeholder="(99) 99999-9999" class="input input-bordered w-full" value="<?= htmlspecialchars($telefone_exibicao) ?>" pattern="\(\d{2}\) \d{5}-\d{4}" title="O telefone deve estar no formato (99) 99999-9999." />
                        </div>
                        
                        <!-- CPF -->
                        <div>
                            <label for="cpf" class="label"><span class="label-text">CPF</span></label>
                            <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" class="input input-bordered w-full" value="<?= htmlspecialchars($usuario['cpf'] ?? '') ?>" pattern="\d{3}\.\d{3}\.\d{3}-\d{2}" title="O CPF deve estar no formato 000.000.000-00." />
                            <div class="label"><span class="label-text-alt">Seu CPF é usado para pagamentos e não é compartilhado.</span></div>
                        </div>

                        <!-- Cidade -->
                        <div>
                            <label for="cidade" class="label"><span class="label-text">Cidade</span></label>
                            <input required list="lista-cidades" id="cidade" name="cidade" placeholder="Digite para buscar sua cidade" class="input input-bordered w-full" value="<?= htmlspecialchars($usuario['cidade'] ?? '') ?>" />
                            <datalist id="lista-cidades"></datalist>
                        </div>

                        <!-- Empunhadura -->
                        <div>
                            <label for="empunhadura" class="label"><span class="label-text">Empunhadura</span></label>
                            <select id="empunhadura" name="empunhadura" class="select select-bordered w-full">
                                <option value="destro" <?= ($usuario['empunhadura'] ?? '') === 'destro' ? 'selected' : '' ?>>Destro</option>
                                <option value="canhoto" <?= ($usuario['empunhadura'] ?? '') === 'canhoto' ? 'selected' : '' ?>>Canhoto</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-8">
                        <button type="submit" class="btn btn-primary w-full">Atualizar Perfil</button>
                    </div>
                </form>
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
            // Aplica máscaras para os campos de telefone e CPF para guiar o usuário
            $('#telefone').mask('(00) 00000-0000');
            $('#cpf').mask('000.000.000-00', {reverse: true});

            // Lógica para buscar cidades do IBGE e popular o datalist
            $('#cidade').on('input', function() {
                const termo = $(this).val();
                if (termo.length >= 3) { // Inicia a busca com 3 ou mais caracteres
                    // A API do IBGE não suporta busca direta, então buscamos todos e filtramos no cliente.
                    // Para uma aplicação maior, o ideal seria ter uma API própria para isso.
                    fetch(`https://servicodados.ibge.gov.br/api/v1/localidades/municipios`)
                        .then(res => res.json())
                        .then(data => {
                            const filtradas = data.filter(m => m.nome.toLowerCase().startsWith(termo.toLowerCase()));
                            $('#lista-cidades').empty();
                            filtradas.forEach(cidade => {
                                $('#lista-cidades').append(`<option value="${cidade.nome} - ${cidade.microrregiao.mesorregiao.UF.sigla}">`);
                            });
                        });
                }
            });
        });
    </script>
</body>
</html>