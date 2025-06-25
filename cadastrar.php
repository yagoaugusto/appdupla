<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DUPLA - Cadastro</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Segoe UI', sans-serif;
    }

    body {
      background: linear-gradient(135deg, #0abde3, #10ac84);
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
    }

    .container {
      background: #fff;
      padding: 30px;
      border-radius: 20px;
      width: 100%;
      max-width: 450px;
      box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }

    h1 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
      font-size: 24px;
    }

    label {
      font-weight: bold;
      display: block;
      margin-top: 15px;
      margin-bottom: 5px;
    }

    input, select {
      width: 100%;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 10px;
      font-size: 16px;
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      background-color: #fff;
    }

    select {
      background-image: url("data:image/svg+xml;utf8,<svg fill='gray' height='24' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/></svg>");
      background-repeat: no-repeat;
      background-position: right 10px center;
      background-size: 16px 16px;
    }

    button {
      margin-top: 25px;
      width: 100%;
      background: #10ac84;
      color: white;
      padding: 14px;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s;
    }

    button:hover {
      background: #0e9473;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Inscreva-se, é rápido!</h1>
    <form action="controller-usuario/cadastrar-usuario.php" method="post">
      <label for="nome">Nome</label>
      <input type="text" id="nome" name="nome" required>

      <label for="sobrenome">Sobrenome</label>
      <input type="text" id="sobrenome" name="sobrenome" required>

      <label for="apelido">Apelido</label>
      <input type="text" id="apelido" name="apelido" required readonly>
      <button type="button" id="update-apelido" style="margin-top: 8px; width: 100%; background: #0abde3; color: #fff; border: none; border-radius: 7px; cursor: pointer; font-size: 15px; padding: 10px 0; display: block;">
        outro apelido
      </button>

      <label for="sexo">Sexo</label>
      <select id="sexo" name="sexo" required>
        <option value="">Selecione</option>
        <option value="M">Masculino</option>
        <option value="F">Feminino</option>
      </select>

      <label for="telefone">Telefone</label>
      <input type="tel" id="telefone" name="telefone" required>

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>

      <label for="empunhadura">Mão dominante</label>
      <select id="empunhadura" name="empunhadura" required>
        <option value="">Selecione</option>
        <option value="destro">Destro</option>
        <option value="canhoto">Canhoto</option>
      </select>

      <label for="cpf">CPF</label>
      <input type="text" id="cpf" name="cpf" required>

      <label for="cidade">Cidade</label>
      <input list="lista-cidades" id="cidade" name="cidade" required>
      <datalist id="lista-cidades"></datalist>

      <button type="submit">Cadastrar</button>
    </form>
  </div>

  <script>
    $(document).ready(function() {
      $('#telefone').mask('(00) 00000-0000');
      $('#cpf').mask('000.000.000-00');

      $('#cidade').on('input', function() {
        const termo = $(this).val();
        if (termo.length >= 5) {
          fetch(`https://servicodados.ibge.gov.br/api/v1/localidades/municipios`)
            .then(res => res.json())
            .then(data => {
              const filtradas = data.filter(m => m.nome.toLowerCase().includes(termo.toLowerCase()));
              $('#lista-cidades').empty();
              filtradas.forEach(cidade => {
                $('#lista-cidades').append(`<option value="${cidade.nome}">`);
              });
            });
        }
      });
    });

    // Função para gerar apelido aleatório (exemplo simples)
    function gerarApelido() {
      const nomes = [
'ReiDaAreia 🌀','RainhaDoSol 🌀','BrisaNordestina 🎾','VentoLitoral 🌴','SombraEPraia 💥','Salitre 🌀','Maresia 🔥',
'OndaChegando 😎','ChapéuDePalha 🏆','SolArretado 🌀','SaqueCerteiro 😎','DuplaFatal 🌊','BackhandVeloz 🌀',
'AceNaVeia 🌀','AreiaNaRaquete 🔥','PegaNaRede 💥','MatchDoSol 🎾','VoleioNordestino 😎','SmashPraiano 🎾',
'AreiaNoOlho 🏖️','CactoDoBeach 🏖️','SolDeFortal 🔥','CabraDaPeste 🎾','ArretadoNaRede 🎾','MandacaruVeloz 💥',
'RaqueteDeLampião 🐚','AreiaQuente 🔥','NordestinoNaRede 💥','CearáTopSpin 💥','JagunçoDoSaque 🏖️',
'CampeãoDasDunas 🌀','MedalhaSalina 🌀','FinalistaDoLitoral 🏆','RaqueteDeOuro 🌴','GameSetNordeste 🔥',
'RankingArretado 🏖️','TopDaPraia 🏖️','DuplaDaVez 😎','InvencívelNaAreia 🌴','TroféuDoSol 🌊',
'CaranguejoAtacante 🐚','BarraqueiroTático 😎','CocoNaRede 🌊','SolEReserva 🌴','SereiaDoVento 🌴',
'TubarãoDaRede 🌊','LagostaLob 🏖️','CoralDoTopSpin 🌊','EstrelaDoMarrom 🌴','OuriçoSaqueador 🐚',
'AreiaDoCastelo 🏆','SolDoRally 🐚','SombraDeQuadra 🔥','PipaTopSpin 🐚','LitoralNaVeia 🌊',
'NordesteNoGame 🌊','BeachRei 🏆','DamaDaDuna 💥','LobDeLambada 🌊','PasseioNaRede 🐚',
'BichoSolto 🏖️','MassaDemais 🌀','TopZera 🌴','ÉoSaque 🏆','DoidoDemais 💥',
'ArrastadoNoVento 🏖️','DaqueleJeito 🌊','AveMariaVolley 🔥','SolNaCara 🎾','ÉNóisNaAreia 🐚',
'VidaPraiana 🌴','RitmoDoMar 🏖️','AreiaNaVeia 💥','BiquíniEDupla 🏆','VentoNosCabelos 🌊',
'SorrisoDoSol 🎾','QuadraLivre 🐚','CheiroDeMar 🔥','DiaDeFinal 🏆','DomingãoNaRede 🌀',
'ZéDaAreia 🌴','TonhoDoSaque 🔥','Raqueteira 🐚','NegaDaQuadra 🌊','SeuLob 🏖️',
'TiaDoRanking 🐚','DonaSmash 🔥','BarracaVip 🏖️','ReizinhoDoTorneio 🏆','DoutorGame 🌊',
'SombraNordestino 🎾','RaqueteNordestino 🔥','SolNordestino 💥','AreiaNordestino 🐚','CactoNordestino 🌊',
'MandacaruNordestino 🏖️','RedeNordestino 🌀','LobNordestino 🌴','SaqueNordestino 🏆','GameNordestino 🏖️',
'SombraVeloz 🌊','RaqueteVeloz 🎾','SolVeloz 🏖️','AreiaVeloz 🔥','CactoVeloz 🌴',
'MandacaruVeloz 🐚','RedeVeloz 💥','LobVeloz 🌀','SaqueVeloz 🏆','GameVeloz 🐚',
'SombraCabuloso 🌊','RaqueteCabuloso 🔥','SolCabuloso 🏖️','AreiaCabuloso 🌴','CactoCabuloso 🎾',
'MandacaruCabuloso 🐚','RedeCabuloso 🌀','LobCabuloso 💥','SaqueCabuloso 🏆','GameCabuloso 🌊',
'SombraArretado 🐚','RaqueteArretado 🔥','SolArretado 🏖️','AreiaArretado 🌴','CactoArretado 💥',
'MandacaruArretado 🌊','RedeArretado 🎾','LobArretado 🏆','SaqueArretado 🐚','GameArretado 🌀',
'SombraDaVez 🌴','RaqueteDaVez 🌊','SolDaVez 🏖️','AreiaDaVez 🔥','CactoDaVez 🎾',
'MandacaruDaVez 💥','RedeDaVez 🐚','LobDaVez 🏆','SaqueDaVez 🏖️','GameDaVez 🌊',
'SombraNaVeia 💥','RaqueteNaVeia 🌊','SolNaVeia 🎾','AreiaNaVeia 🔥','CactoNaVeia 🏖️',
'MandacaruNaVeia 🐚','RedeNaVeia 🏆','LobNaVeia 🌴','SaqueNaVeia 💥','GameNaVeia 🌊',
'SombraDoSol 🏖️','RaqueteDoSol 🌴','SolDoSol 🌊','AreiaDoSol 🎾','CactoDoSol 🔥',
'MandacaruDoSol 🐚','RedeDoSol 🏆','LobDoSol 💥','SaqueDoSol 🌀','GameDoSol 🏖️',
'SombraVip 🐚','RaqueteVip 🌊','SolVip 🎾','AreiaVip 💥','CactoVip 🔥',
'MandacaruVip 🏖️','RedeVip 🏆','LobVip 🌴','SaqueVip 🌀','GameVip 🌊',
'SombraDoTorneio 🏖️','RaqueteDoTorneio 🎾','SolDoTorneio 🌴','AreiaDoTorneio 🌊','CactoDoTorneio 🏆',
'MandacaruDoTorneio 🐚','RedeDoTorneio 🔥','LobDoTorneio 🌀','SaqueDoTorneio 💥','GameDoTorneio 🌴'
];
      return nomes[Math.floor(Math.random() * nomes.length)];
    }

    $(document).ready(function() {
      // Preenche o campo com um apelido inicial
      $('#apelido').val(gerarApelido());

      $('#update-apelido').on('click', function() {
        $('#apelido').val(gerarApelido());
      });

      $('#telefone').mask('(00) 00000-0000');
      $('#cpf').mask('000.000.000-00');
    });
  </script>
</body>
</html>
