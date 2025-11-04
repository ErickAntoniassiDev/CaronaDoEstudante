<?php
session_start();
include 'conexao.php';

// Verifica se está logado
if (!isset($_SESSION["ID"])) {
  header("Location: login.php");
  exit;
}

$idusuario = $_SESSION["ID"];
$nomeusuario = $_SESSION["nome"];
$telefone = $_SESSION["telefone"];
$carro = $_SESSION["carro"] ?? null;
$tipousuario = $_SESSION["tipo"];
$email = $_SESSION["email"];

// Verifica se é motorista
if ($tipousuario !== 'motorista') {
  header("Location: index.php");
  exit;
}

// Processa o formulário
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $origem = trim($_POST["origem"]);
  $destino = trim($_POST["destino"]);
  $data = trim($_POST["data"]);
  $horario = trim($_POST["horario"]);
  $vagas_totais = intval($_POST["vagas_totais"]);
  $vagas_disponiveis = intval($_POST["vagas_disponiveis"]);
  $observacoes = trim($_POST["observacoes"]);
  $status = trim($_POST["status"]);

  // Gerar código único de carona
function gerarCodigoCarona($tamanho = 6) {
    $letras = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $codigo = 'CRN-';
    for ($i = 0; $i < $tamanho; $i++) {
        $codigo .= $letras[rand(0, strlen($letras) - 1)];
    }
    return $codigo;
}

$codigo_carona = gerarCodigoCarona();


  // Validação básica
  if ($vagas_disponiveis > $vagas_totais) {
    $erro = "Vagas disponíveis não pode ser maior que vagas totais!";
  } else {
    $sql = "INSERT INTO carona (motorista_id, origem, destino, data, horario, vagas_total, vagas_disponiveis, observacoes, status, carro, codigo_carona) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssiiisss", $idusuario, $origem, $destino, $data, $horario, $vagas_totais, $vagas_disponiveis, $observacoes, $status, $carro, $codigo_carona);

    if ($stmt->execute()) {
      $sucesso = "Carona cadastrada com sucesso!";
      // Limpa o formulário redirecionando
      echo "<script>
        alert('Carona cadastrada com sucesso!');
        window.location='index.php';
      </script>";
      exit;
    } else {
      $erro = "Erro ao cadastrar carona: " . $stmt->error;
    }
    $stmt->close();
  }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title>Oferecer Carona - Carona Do Estudante</title>
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
      padding-bottom: 80px;
    }

    /* Header */
    .header {
      background: white;
      border-bottom: 1px solid #e2e8f0;
      padding: 16px;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .header-content {
      display: flex;
      align-items: center;
      justify-content: space-between;
      max-width: 1200px;
      margin: 0 auto;
    }

    .header-logo {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .logo-icon {
      width: 36px;
      height: 36px;
      background: #2563eb;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
    }

    .logo-text {
      font-size: 18px;
      font-weight: 700;
    }

    .user-greeting {
      font-size: 14px;
      color: #64748b;
    }

    /* Container */
    .container {
      max-width: 600px;
      margin: 0 auto;
      padding: 20px;
    }

    /* Alertas */
    .alert {
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 16px;
      font-size: 14px;
    }

    .alert-success {
      background: #dcfce7;
      color: #166534;
      border: 1px solid #bbf7d0;
    }

    .alert-error {
      background: #fee2e2;
      color: #991b1b;
      border: 1px solid #fecaca;
    }

    /* Form */
    .form-card {
      background: white;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .form-title {
      font-size: 20px;
      font-weight: 700;
      margin-bottom: 20px;
      text-align: center;
      color: #1e293b;
    }

    .info-card {
      background: #dbeafe;
      border: 1px solid #93c5fd;
      border-radius: 8px;
      padding: 12px;
      margin-bottom: 20px;
      font-size: 13px;
      color: #1e40af;
    }

    .info-card i {
      margin-right: 6px;
    }

    .form-group {
      margin-bottom: 18px;
    }

    .form-label {
      display: block;
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 8px;
      color: #1e293b;
    }

    .form-input,
    .form-select,
    textarea.form-input {
      width: 100%;
      padding: 12px;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      font-size: 15px;
      font-family: inherit;
      background: #f8fafc;
    }

    .form-input:focus,
    .form-select:focus,
    textarea.form-input:focus {
      outline: none;
      border-color: #2563eb;
      background: white;
    }

    textarea.form-input {
      min-height: 100px;
      resize: vertical;
    }

    .btn {
      width: 100%;
      padding: 16px;
      border: none;
      border-radius: 12px;
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

    .btn-primary:disabled {
      background: #94a3b8;
      cursor: not-allowed;
    }

    /* Bottom Nav */
    .bottom-nav {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: white;
      border-top: 1px solid #e2e8f0;
      padding: 12px 0;
      box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
      z-index: 100;
    }

    .nav-content {
      display: flex;
      justify-content: space-around;
      max-width: 1200px;
      margin: 0 auto;
    }

    .nav-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 4px;
      padding: 8px 24px;
      cursor: pointer;
      border-radius: 12px;
      text-decoration: none;
      color: #64748b;
    }

    .nav-item.active {
      color: #2563eb;
      background: #dbeafe;
    }

    .nav-label {
      font-size: 12px;
      font-weight: 500;
    }
  </style>
</head>

<body>

  <!-- Header -->
  <header class="header">
    <div class="header-content">
      <div class="header-logo">
        <div class="logo-icon"><i class="fa-solid fa-bus"></i></div>
        <div class="logo-text">Carona Escolar</div>
      </div>
      <div class="user-greeting">Olá, <strong><?= htmlspecialchars($nomeusuario); ?></strong></div>
    </div>
  </header>

  <!-- Container -->
  <div class="container">
    <div class="form-card">
      <h2 class="form-title">Oferecer Carona</h2>

      <div class="info-card">
        <i class="fa-solid fa-car"></i>
        <strong>Veículo:</strong> <?= htmlspecialchars($carro ?? 'Não informado') ?>
      </div>

      <?php if (isset($erro)): ?>
        <div class="alert alert-error">
          <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($erro) ?>
        </div>
      <?php endif; ?>

      <?php if (isset($sucesso)): ?>
        <div class="alert alert-success">
          <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($sucesso) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label class="form-label">Origem</label>
          <input type="text" name="origem" class="form-input" placeholder="Local de partida" required>
        </div>

        <div class="form-group">
          <label class="form-label">Destino</label>
          <input type="text" name="destino" class="form-input" placeholder="Destino da carona" required>
        </div>

        <div class="form-group">
          <label class="form-label">Data</label>
          <input type="date" name="data" class="form-input" required>
        </div>

        <div class="form-group">
          <label class="form-label">Horário</label>
          <input type="time" name="horario" class="form-input" required>
        </div>

        <div class="form-group">
          <label class="form-label">Total de Vagas</label>
          <input type="number" name="vagas_totais" id="vagas_totais" min="1" max="10" class="form-input" placeholder="Ex: 4" required>
        </div>

        <div class="form-group">
          <label class="form-label">Vagas Disponíveis</label>
          <input type="number" name="vagas_disponiveis" id="vagas_disponiveis" min="0" max="10" class="form-input" placeholder="Ex: 4" required>
        </div>

        <div class="form-group">
          <label class="form-label">Observações</label>
          <textarea name="observacoes" class="form-input" placeholder="Alguma informação adicional? (opcional)"></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-select" required>
            <option value="ativo">Ativo</option>
            <option value="inativo">Inativo</option>
          </select>
        </div>

        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-check"></i> Salvar Carona
        </button>
      </form>
    </div>
  </div>

  <!-- Bottom Navigation -->
  <nav class="bottom-nav">
    <div class="nav-content">
      <a href="index.php" class="nav-item">
        <i class="fa-solid fa-house"></i>
        <span class="nav-label">Início</span>
      </a>
      <a href="oferecer.php" class="nav-item active">
        <i class="fa-solid fa-plus"></i>
        <span class="nav-label">Oferecer</span>
      </a>
      <a href="perfil.php" class="nav-item">
        <i class="fa-solid fa-user"></i>
        <span class="nav-label">Perfil</span>
      </a>
    </div>
  </nav>

  <script>

    $codigo_carona = gerarCodigoCarona();


    $codigo_carona = gerarCodigoCarona();

    // Validação: vagas disponíveis não pode ser maior que vagas totais
    const vagasTotais = document.getElementById('vagas_totais');
    const vagasDisponiveis = document.getElementById('vagas_disponiveis');

    function validarVagas() {
      const total = parseInt(vagasTotais.value) || 0;
      const disponiveis = parseInt(vagasDisponiveis.value) || 0;

      if (disponiveis > total) {
        vagasDisponiveis.setCustomValidity('Vagas disponíveis não pode ser maior que o total');
      } else {
        vagasDisponiveis.setCustomValidity('');
      }
    }

    vagasTotais.addEventListener('input', validarVagas);
    vagasDisponiveis.addEventListener('input', validarVagas);

    // Define data mínima como hoje
    const dataInput = document.querySelector('input[type="date"]');
    const hoje = new Date().toISOString().split('T')[0];
    dataInput.min = hoje;
  </script>

</body>

</html>