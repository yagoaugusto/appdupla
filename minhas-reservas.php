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
        <main class="flex-1 p-4 sm:p-6">
            <section class="max-w-4xl mx-auto w-full">
                <div class="flex items-center gap-4 mb-6">
                    <span class="text-4xl">🎟️</span>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">Minhas Reservas</h1>
                        <p class="text-sm text-gray-500">Acompanhe o status das suas reservas de quadra.</p>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">Em breve</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Esta área está em desenvolvimento. Em breve você poderá ver todas as suas reservas aqui.
                    </p>
                    <div class="mt-6">
                        <a href="principal.php" class="btn btn-primary">
                            Voltar para o Início
                        </a>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <?php require_once '_footer.php'; ?>

</body>
</html>