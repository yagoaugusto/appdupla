<?php require_once '#_global.php'; ?>
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
        <main class="flex-1 p-4">
            <section class="max-w-6xl mx-auto w-full">

                <!-- Título da Página -->
                <h1 class="text-2xl font-extrabold mb-6 tracking-tight text-gray-800 flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <!-- Substitua por um ícone relevante do heroicons.com -->
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Título da Nova Página
                </h1>

                <!-- Card de Conteúdo Principal -->
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Seção de Conteúdo</h2>
                    <p class="text-gray-600">
                        Aqui você pode adicionar o conteúdo específico da sua nova página.
                        A estrutura base com cabeçalho, menu superior, menu lateral e rodapé já está configurada para você.
                    </p>
                    <br>
                    <p class="text-gray-600">
                        Você pode usar as classes do <a href="https://tailwindcss.com/" target="_blank" class="text-blue-500 hover:underline">Tailwind CSS</a> e componentes do <a href="https://daisyui.com/" target="_blank" class="text-purple-500 hover:underline">DaisyUI</a> para construir sua interface.
                    </p>
                </div>

            </section>
        </main>
    </div>

    <footer class="w-full bg-white border-t border-gray-200 py-4 text-center fixed bottom-0 left-0 z-50">
        DUPLA - Deu Game? Dá Ranking!
    </footer>

</body>

</html>