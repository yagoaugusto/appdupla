<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>
<body class="bg-gradient-to-br from-gray-50 via-gray-100 to-gray-200 min-h-screen text-gray-800">

  <?php require_once '_nav_superior.php'; ?>

  <div class="flex pt-16">
    <?php require_once '_nav_lateral.php'; ?>

    <main class="flex-1 flex flex-col min-h-screen p-6">
      <section class="bg-white/90 backdrop-blur-md rounded-2xl shadow-2xl p-8 max-w-3xl mx-auto border border-gray-200">
        <div class="flex flex-col items-center mb-6">
          <div class="bg-gradient-to-tr from-yellow-400 to-yellow-600 rounded-full p-4 shadow-lg mb-2">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <h2 class="text-3xl font-extrabold mb-2 text-gray-800 text-center">Partida registrada com sucesso!</h2>
          <p class="text-center text-lg text-gray-600">
            Parabéns! Sua partida foi registrada.<br>
            Agora é só aguardar a confirmação dos outros jogadores.<br>
            Assim que tudo estiver validado, você será avisado no WhatsApp.
          </p>
        </div>

        <!-- Resultado da partida -->
        <div class="bg-gradient-to-r from-gray-100 to-gray-50 border border-gray-200 rounded-xl p-6 mb-8 shadow-inner">
          <div class="grid grid-cols-2 gap-4 mb-2">
            <div class="flex items-center gap-2">
              <span class="font-semibold text-gray-800">Davi Ballerini</span>
            </div>
            <div class="text-right font-bold text-green-600 text-xl">6 <span class="align-middle">✔</span></div>
            <div class="flex items-center gap-2">
              <span class="font-semibold text-gray-800">Mateus Busnardo Buemo</span>
            </div>
            <div></div>
          </div>
          <hr class="my-3 border-gray-300">
          <div class="grid grid-cols-2 gap-4">
            <div class="flex items-center gap-2">
              <span class="font-semibold text-gray-800">Vinicius Yohan Belusso</span>
            </div>
            <div class="text-right font-bold text-gray-400 text-xl">0</div>
            <div class="flex items-center gap-2">
              <span class="font-semibold text-gray-800">Denzel West Sousa West</span>
            </div>
            <div></div>
          </div>
        </div>

        <!-- Status de validação -->
        <div class="mb-8">
          <h3 class="font-semibold mb-3 text-gray-700 text-lg">Status de Confirmação</h3>
          <ul class="grid grid-cols-2 gap-3 text-base">
            <li class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm">
              <span class="text-lg">✔</span> Davi Ballerini confirmou
            </li>
            <li class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm">
              <span class="text-lg">✔</span> Mateus Buemo confirmou
            </li>
            <li class="flex items-center gap-2 bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg shadow-sm">
              <span class="text-lg">⌛</span> Vinicius Belusso pendente
            </li>
            <li class="flex items-center gap-2 bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg shadow-sm">
              <span class="text-lg">⌛</span> Denzel West pendente
            </li>
          </ul>
        </div>

        <!-- Botão de compartilhamento -->
        <div class="text-center mt-10">
          <a href="https://wa.me/?text=Acabei%20de%20registrar%20uma%20partida%20no%20DUPLA!%20Confira%20o%20resultado:%20https://dupla.app"
             target="_blank"
             class="inline-flex items-center gap-2 bg-gradient-to-r from-green-500 to-green-700 hover:from-green-600 hover:to-green-800 text-white font-bold px-8 py-4 rounded-full shadow-lg transition-all duration-200">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
              <path d="M20.52 3.48A12.07 12.07 0 0 0 12 0C5.37 0 0 5.37 0 12c0 2.12.55 4.18 1.6 6.02L0 24l6.18-1.62A12.07 12.07 0 0 0 12 24c6.63 0 12-5.37 12-12 0-3.21-1.25-6.23-3.48-8.52zM12 22c-1.85 0-3.66-.5-5.22-1.44l-.37-.22-3.67.96.98-3.57-.24-.37A9.98 9.98 0 0 1 2 12c0-5.52 4.48-10 10-10s10 4.48 10 10-4.48 10-10 10zm5.2-7.8c-.28-.14-1.65-.81-1.9-.9-.25-.09-.43-.14-.61.14-.18.28-.7.9-.86 1.08-.16.18-.32.2-.6.07-.28-.14-1.18-.44-2.25-1.4-.83-.74-1.39-1.65-1.55-1.93-.16-.28-.02-.43.12-.57.13-.13.28-.34.42-.51.14-.17.18-.29.28-.48.09-.19.05-.36-.02-.5-.07-.14-.61-1.47-.84-2.01-.22-.53-.45-.46-.61-.47-.16-.01-.35-.01-.54-.01-.19 0-.5.07-.76.34-.26.27-1 1-.97 2.43.03 1.43 1.04 2.81 1.19 3 .15.19 2.05 3.13 5.01 4.27.7.3 1.25.48 1.68.61.71.23 1.36.2 1.87.12.57-.09 1.65-.67 1.89-1.32.23-.65.23-1.21.16-1.32-.07-.11-.25-.18-.53-.32z"/>
            </svg>
            Compartilhar no WhatsApp
          </a>
        </div>
      </section>
    </main>
  </div>

</body>
</html>