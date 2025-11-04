<?php

    include 'conexao.php';

// 🧾 Cadastro de usuário
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nome = trim($_POST["nome"]);
  $email = trim($_POST["email"]);
  $telefone = trim($_POST["telefone"]);
  $senha = password_hash(trim($_POST["senha"]), PASSWORD_DEFAULT);
  $tipo = trim($_POST["tipo"]);
  $carro = isset($_POST["carro"]) ? trim($_POST["carro"]) : null;

  // Verifica se o e-mail já existe
  $check = $conn->prepare("SELECT id FROM usuario WHERE email = ?");
  $check->bind_param("s", $email);
  $check->execute();
  $check->store_result();

  if ($check->num_rows > 0) {
    $erro = "E-mail já cadastrado!";
  } else {
    $sql = "INSERT INTO usuario (nome, email, telefone, senha, tipo, carro) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $nome, $email, $telefone, $senha, $tipo, $carro);
    if ($stmt->execute()) {
      echo "<script>alert('Cadastro realizado com sucesso!'); window.location='login.php';</script>";
      exit;
    } else {
      $erro = "Erro ao cadastrar. Tente novamente.";
    }
  }
}
?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title>Carona Do Estudante</title>
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
      margin: 60px auto;
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
      margin-bottom: 18px;
      text-align: left;
    }

    .form-group.hidden {
      display: none;
    }

    .form-label {
      display: block;
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 8px;
    }

    .form-input,
    .form-select {
      width: 100%;
      padding: 12px;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      font-size: 15px;
      background: #f8fafc;
    }

    .form-input:focus,
    .form-select:focus {
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
      color: #991b1b;
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 16px;
      font-size: 14px;
    }
  </style>
</head>

<body>

  <div class="container">
    <div class="logo"><i class="fa-solid fa-bus"></i></div>
    <div class="title">Criar Conta</div>

    <?php if (isset($erro)): ?>
      <div class="erro"><?= $erro ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label class="form-label">Nome Completo</label>
        <input type="text" name="nome" class="form-input" placeholder="Digite seu nome" required>
      </div>

      <div class="form-group">
        <label class="form-label">E-mail</label>
        <input type="email" name="email" class="form-input" placeholder="seu@email.com" required>
      </div>

      <div class="form-group">
        <label class="form-label">Telefone</label>
        <input type="text" name="telefone" class="form-input" placeholder="(00) 00000-0000" required>
      </div>

      <div class="form-group">
        <label class="form-label">Senha</label>
        <input type="password" name="senha" class="form-input" placeholder="Crie uma senha" required>
      </div>

      <div class="form-group">
        <label class="form-label">Confirmar Senha</label>
        <input type="password" name="confirmar_senha" class="form-input" placeholder="Repita a senha" required>
      </div>

      <div class="form-group">
        <label class="form-label">Tipo de Usuário</label>
        <select name="tipo" id="tipoUsuario" class="form-select" required>
          <option value="">Selecione...</option>
          <option value="aluno">Aluno</option>
          <option value="motorista">Motorista</option>
        </select>
      </div>

      <div class="form-group hidden" id="campoVeiculo">
        <label class="form-label">Nome do Veículo</label>
        <input type="text" name="carro" id="inputCarro" class="form-input" placeholder="Ex: Gol Branco, Civic Preto">
      </div>

      <button type="submit" class="btn btn-primary">Cadastrar</button>
    </form>

    <a href="login.php" class="link">Já tem conta? Faça login</a>
  </div>

  <script>
    // Mostra/oculta o campo de veículo baseado no tipo de usuário
    const tipoUsuario = document.getElementById('tipoUsuario');
    const campoVeiculo = document.getElementById('campoVeiculo');
    const inputCarro = document.getElementById('inputCarro');

    tipoUsuario.addEventListener('change', function() {
      if (this.value === 'motorista') {
        campoVeiculo.classList.remove('hidden');
        inputCarro.required = true;
      } else {
        campoVeiculo.classList.add('hidden');
        inputCarro.required = false;
        inputCarro.value = '';
      }
    });
  </script>

</body>

</html>