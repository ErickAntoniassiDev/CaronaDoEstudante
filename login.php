<?php
session_start();

include 'conexao.php';

// 🔐 Verificação de login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = trim($_POST["email"]);
  $senha = trim($_POST["senha"]);

  $sql = "SELECT * FROM usuario WHERE email = ? AND senha = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $email, $senha);
  $stmt->execute();
  $resultado = $stmt->get_result();

  if ($resultado->num_rows > 0) {
    $usuario = $resultado->fetch_assoc();
    $_SESSION["ID"] = $usuario["ID"];
    $_SESSION["nome"] = $usuario["nome"];
    $_SESSION["telefone"] = $usuario["telefone"];
    $_SESSION["carro"] = $usuario["carro"];
    $_SESSION["tipo"] = $usuario["tipo"];
    $_SESSION["email"] = $usuario["email"];
    header("Location: index.php");
    exit;
  } else {
    $erro = "Email ou senha incorretos!";
  }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title>Carona Do Estudante - Login</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: #f8fafc;
      color: #1e293b;
    }

    .container {
      max-width: 400px;
      margin: 80px auto;
      background: white;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      padding: 32px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      text-align: center;
    }

    .logo {
      width: 56px;
      height: 56px;
      background: #2563eb;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 24px;
      margin: 0 auto 16px;
    }

    .title {
      font-size: 22px;
      font-weight: 700;
      margin-bottom: 24px;
    }

    .form-group {
      margin-bottom: 20px;
      text-align: left;
    }

    .form-label {
      display: block;
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 8px;
    }

    .form-input {
      width: 100%;
      padding: 12px;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      font-size: 15px;
      background: #f8fafc;
    }

    .form-input:focus {
      outline: none;
      border-color: #2563eb;
      background: white;
    }

    .btn {
      width: 100%;
      padding: 14px;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn-primary {
      background: #2563eb;
      color: white;
    }

    .btn-primary:hover {
      background: #1d4ed8;
    }

    .link {
      display: block;
      margin-top: 16px;
      font-size: 14px;
      color: #2563eb;
      text-decoration: none;
      font-weight: 500;
    }

    .link:hover {
      text-decoration: underline;
    }

    .erro {
      background: #fee2e2;
      color: #dc2626;
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 16px;
      font-size: 14px;
      border: 1px solid #fecaca;
    }
  </style>
</head>

<body>

  <div class="container">
    <div class="logo"><i class="fa-solid fa-bus"></i></div>
    <div class="title">Entrar no Carona Escolar</div>

    <?php if (isset($erro)): ?>
      <div class="erro"><?= $erro ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label class="form-label">E-mail</label>
        <input type="email" name="email" class="form-input" placeholder="seu@email.com" required>
      </div>

      <div class="form-group">
        <label class="form-label">Senha</label>
        <input type="password" name="senha" class="form-input" placeholder="********" required>
      </div>

      <button type="submit" class="btn btn-primary">Entrar</button>
    </form>

    <a href="cadastro.php" class="link">Não tem conta? Cadastre-se</a>
  </div>

</body>

</html>