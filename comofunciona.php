<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>

<body class="bg-gray-50 min-h-screen text-gray-900">

    <!-- Navbar superior -->
    <?php require_once '_nav_superior.php' ?>

    <div class="flex pt-14">
        <!-- Menu lateral -->
        <?php require_once '_nav_lateral.php' ?>

        <!-- Conteúdo principal -->
        <main class="flex-1 flex flex-col min-h-screen">
            <section class="p-2 sm:p-6 md:p-10 max-w-5xl w-full mx-auto">
                <div class="text-center mb-6">
                    <span class="mx-auto w-14 h-14 flex items-center justify-center text-5xl mb-2" role="img" aria-label="Ranking">🏆</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-800 mb-2">
                        Como funciona o Ranking <span class="bg-gradient-to-r from-blue-600 via-pink-500 to-yellow-400 bg-clip-text text-transparent">DUPLA</span>?
                    </h2>
                    <p class="text-base sm:text-lg text-gray-600 font-medium mb-2">Nada de achismo. Aqui o <span class="text-orange-500 font-bold">Glicko-2</span> avalia <span class="font-bold">como</span> você joga, não só quantas partidas faz.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                    <!-- Rating -->
                    <div class="bg-white rounded-xl shadow-lg flex flex-col items-center p-4 border-b-4 border-blue-500">
                        <div class="w-12 h-12 flex items-center justify-center rounded-full bg-blue-500 text-white text-2xl mb-1 shadow">⭐</div>
                        <div class="text-base font-bold text-blue-700 mb-1">Rating (R)</div>
                        <ul class="text-xs text-gray-700 space-y-1 text-center">
                            <li>Ganhou de alguém mais forte? <span class="font-bold text-green-600">Sobe mais</span> 🟢</li>
                            <li>Perdeu pra alguém mais fraco? <span class="font-bold text-red-600">Cai mais</span> 🔴</li>
                            <li>Quanto maior, melhor seu ranking.</li>
                        </ul>
                    </div>
                    <!-- RD -->
                    <div class="bg-white rounded-xl shadow-lg flex flex-col items-center p-4 border-b-4 border-pink-500">
                        <div class="w-12 h-12 flex items-center justify-center rounded-full bg-pink-500 text-white text-2xl mb-1 shadow">📉</div>
                        <div class="text-base font-bold text-pink-700 mb-1">RD (Desvio)</div>
                        <ul class="text-xs text-gray-700 space-y-1 text-center">
                            <li>Jogou pouco? <span class="font-bold text-orange-600">RD alto</span> 🤔</li>
                            <li>Jogou muito? <span class="font-bold text-green-600">RD baixo</span> 💪</li>
                            <li>Ficou inativo? RD sobe com o tempo ⏳</li>
                        </ul>
                    </div>
                    <!-- Volatilidade -->
                    <div class="bg-white rounded-xl shadow-lg flex flex-col items-center p-4 border-b-4 border-yellow-400">
                        <div class="w-12 h-12 flex items-center justify-center rounded-full bg-yellow-400 text-white text-2xl mb-1 shadow">⚡</div>
                        <div class="text-base font-bold text-yellow-700 mb-1">Volatilidade (σ)</div>
                        <ul class="text-xs text-gray-700 space-y-1 text-center">
                            <li>Instável? <span class="font-bold text-pink-600">σ alto</span> 🎢</li>
                            <li>Consistente? <span class="font-bold text-green-600">σ baixo</span> 🧠</li>
                        </ul>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <div class="bg-white rounded-xl shadow flex flex-col items-center p-4">
                        <span class="text-4xl mb-2" role="img" aria-label="Dúvida">❓</span>
                        <h3 class="text-base font-bold text-orange-500 mb-1">Como isso funciona na prática?</h3>
                        <p class="text-xs text-gray-700 mb-1">Cada partida confirmada recalcula seu nível. O sistema analisa seus oponentes, o resultado e a dificuldade da vitória. A cada jogo, seu rating, RD e volatilidade são ajustados automaticamente.</p>
                    </div>
                    <div class="bg-white rounded-xl shadow flex flex-col items-center p-4">
                        <span class="text-4xl mb-2" role="img" aria-label="Exemplo">📝</span>
                        <h4 class="text-base font-bold text-green-600 mb-1">Exemplos práticos:</h4>
                        <div class="space-y-1 text-xs text-gray-700 w-full">
                            <div class="flex items-center gap-2">
                                <span class="text-green-600 text-lg">🟢</span>
                                <span><b>Ganhou de um top:</b> Rating sobe bastante, RD cai, σ pode subir.</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-red-600 text-lg">🔴</span>
                                <span><b>Perdeu pra um fraco:</b> Rating despenca, RD cai, σ sobe.</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-600 text-lg">⚪</span>
                                <span><b>Vitória esperada:</b> Rating ajusta levemente, RD e σ quase não mudam.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <span class="mx-auto text-5xl mb-2" role="img" aria-label="Alvo">🎯</span>
                    <p class="text-base font-semibold text-gray-800">Quanto mais você joga, mais o DUPLA entende seu nível.<br> E quanto mais difícil a vitória, maior a recompensa.</p>
                    <p class="text-orange-600 mt-2 text-sm">Não adianta só jogar muito. Tem que jogar bem. 💥</p>
                </div>
            </section>
    </div>

    <!-- Footer -->
    <?php require_once '_footer.php' ?>
    </main>
    </div>
</body>

</html>