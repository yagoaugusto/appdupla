<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>

<body class="bg-gray-100 min-h-screen text-gray-800">

    <?php require_once '_nav_superior.php'; ?>
    <div class="flex pt-16">
        <?php require_once '_nav_lateral.php'; ?>

        <main class="flex-1 p-4">
            <h1 class="text-2xl font-extrabold mb-6 tracking-tight text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 8v4l3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Histórico de Partidas
            </h1>

            <div class="space-y-4">

                <!-- Partida 1 -->
                <!-- Partida 1 -->
                <div class="bg-white rounded-2xl shadow-lg p-3 sm:p-4 flex flex-col gap-2 border border-gray-100 hover:shadow-xl transition-shadow duration-200 relative">
                    <!-- Tarja de Resultado -->
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-green-500 text-white text-xs font-bold shadow-md z-10 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M5 13l4 4L19 7" />
                        </svg>
                        Vitória
                    </div>
                    <div class="flex justify-between items-center text-xs text-gray-400 mb-1 mt-3">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M8 7V3M16 7V3M4 11h16M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            12 Jun 2025
                        </span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Yago</span>
                            <span class="text-base font-bold text-green-700 bg-green-50 rounded px-2 py-0.5">6</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Yago</span>
                        </div>
                        <hr class="my-1 border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Bruno</span>
                            <span class="text-base font-bold text-red-500 bg-red-50 rounded px-2 py-0.5">3</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Bruno</span>
                            <span></span>
                        </div>
                    </div>
                </div>

                <!-- Partida 2 -->
                <div class="bg-white rounded-2xl shadow-lg p-3 sm:p-4 flex flex-col gap-2 border border-gray-100 hover:shadow-xl transition-shadow duration-200 relative">
                    <!-- Tarja de Resultado -->
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-red-500 text-white text-xs font-bold shadow-md z-10 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Derrota
                    </div>
                    <div class="flex justify-between items-center text-xs text-gray-400 mb-1 mt-3">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M8 7V3M16 7V3M4 11h16M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            11 Jun 2025
                        </span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Yago</span>
                            <span class="text-base font-bold text-red-500 bg-red-50 rounded px-2 py-0.5">4</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Yago</span>
                            <span></span>
                        </div>
                        <hr class="my-1 border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Carlos</span>
                            <span class="text-base font-bold text-green-700 bg-green-50 rounded px-2 py-0.5">6</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Carlos</span>
                            <span></span>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
    <footer class="w-full bg-white border-t border-gray-200 py-4 text-center fixed bottom-0 left-0 z-50">
        DUPLA - Deu Game? Dá Ranking!
    </footer>
</body>

</html>