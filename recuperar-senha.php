<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DUPLA - Recuperar Senha</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', sans-serif;
    }

    body {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background: linear-gradient(135deg, #0abde3, #10ac84);
      padding: 20px;
    }

    .container {
      background: white;
      width: 100%;
      max-width: 400px;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 10px 20px rgba(0,0,0,0.2);
      text-align: center;
    }

    .logo {
      width: 100%;
      max-width: 150px; /* Ajuste conforme o tamanho da sua logo */
      margin-bottom: 20px;
    }

    h1 {
      font-size: 24px;
      color: #333;
      margin-bottom: 10px;
    }

    p {
      font-size: 14px;
      color: #555;
      margin-bottom: 20px;
    }

    input[type="email"],
    input[type="tel"] {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border-radius: 10px;
      border: 1px solid #ccc;
    }

    button {
      width: 100%;
      background: #10ac84;
      color: white;
      padding: 12px;
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
    <img src="img/dupla.png" alt="Logo Dupla" class="logo">
    <h1>Recuperar Senha</h1>
    <p>Informe seu e-mail ou telefone cadastrado para receber as instruções de recuperação.</p>
    <form action="controller-usuario/solicitar-recuperacao.php" method="post">
      <input type="text" name="email_ou_telefone" placeholder="Seu e-mail ou telefone" required>
      <button type="submit">Enviar Instruções</button>
    </form>
  </div>
</body>
</html>