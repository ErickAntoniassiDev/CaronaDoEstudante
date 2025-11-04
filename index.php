<?php
include 'conexao.php';
session_start();

if (!isset($_SESSION["ID"])) {
  header("Location: login.php");
  exit;
}

$idusuario = $_SESSION["ID"];
$nomeusuario = $_SESSION["nome"];
$tipousuario = $_SESSION["tipo"];

// Caronas disponíveis (excluindo as do próprio motorista)
$result_disponiveis = $conn->query("SELECT c.*, u.nome as motorista_nome 
                                     FROM carona c 
                                     INNER JOIN usuario u ON c.motorista_id = u.ID
                                     WHERE c.status = 'ativo' 
                                     AND c.vagas_disponiveis > 0 
                                     AND c.motorista_id != '$idusuario'
                                     ORDER BY c.data, c.horario");

// Minhas caronas (motorista)
$result_minhas = $conn->query("SELECT * FROM carona WHERE motorista_id = '$idusuario' AND status = 'ativo' ORDER BY data, horario");

// Caronas solicitadas (passageiro)
$result_solicitadas = $conn->query("SELECT carona.*, solicitacao_carona.data_solicitacao 
                                     FROM solicitacao_carona 
                                     INNER JOIN carona ON solicitacao_carona.codigo_carona = carona.codigo_carona
                                     WHERE solicitacao_carona.passageiro_id = '$idusuario' 
                                     AND solicitacao_carona.status = 'ativa' 
                                     ORDER BY carona.data, carona.horario");

// Função para renderizar card de carona
function renderCarona($row, $tipo = 'disponivel')
{
  global $conn;
  $motorista_nome = $row['motorista_nome'] ?? '';

  if (empty($motorista_nome) && !empty($row['motorista_id'])) {
    $result_motorista = $conn->query("SELECT nome FROM usuario WHERE ID = '{$row['motorista_id']}'");
    if ($result_motorista && $result_motorista->num_rows > 0) {
      $motorista_nome = $result_motorista->fetch_assoc()['nome'];
    }
  }

  $inicial = strtoupper(substr($motorista_nome, 0, 1));
  $data_formatada = date('d/m/Y', strtotime($row['data']));

  $codigo_valido = false;
  $tempo_restante = '';
  if ($tipo == 'solicitada') {
    $data_hora = $row['data'] . ' ' . $row['horario'];
    $limite = strtotime($data_hora . ' +30 minutes');
    $agora = time();
    $codigo_valido = $agora <= $limite;
    
    if ($codigo_valido) {
      $segundos_restantes = $limite - $agora;
      $minutos_restantes = floor($segundos_restantes / 60);
      $tempo_restante = $minutos_restantes > 0 ? $minutos_restantes . ' min' : 'Expirando...';
    }
  }
?>
  <div class="carona-card">
    <div class="carona-header">
      <div class="motorista-info">
        <div class="avatar"><?= $inicial ?></div>
        <div>
          <div class="motorista-name"><?= $motorista_nome ?></div>
          <div class="motorista-role"><?= $row['carro'] ?></div>
        </div>
      </div>
      <?php if ($tipo == 'disponivel'): ?>
        <div class="badge available"><?= $row['vagas_disponiveis'] ?> vagas</div>
      <?php endif; ?>
    </div>

    <?php if ($tipo == 'minha'): ?>
      <div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 12px; padding: 16px; margin-bottom: 20px; text-align: center;">
        <div style="font-size: 12px; color: #78350f; margin-bottom: 4px;">CÓDIGO DA CARONA</div>
        <div class="badge codigo"><?= $row['codigo_carona'] ?></div>
      </div>
    <?php endif; ?>

    <?php if ($tipo == 'solicitada'): ?>
      <?php if ($codigo_valido): ?>
        <div style="background: #d1fae5; border: 1px solid #a7f3d0; border-radius: 12px; padding: 16px; margin-bottom: 20px; text-align: center;">
          <div style="font-size: 12px; color: #065f46; margin-bottom: 4px;">CÓDIGO DA CARONA</div>
          <div class="badge codigo" style="background: white; color: #065f46; border: 2px solid #10b981;"><?= $row['codigo_carona'] ?></div>
          <div style="font-size: 11px; color: #065f46; margin-top: 8px;">
            <i class="fa-solid fa-clock"></i> Válido por <?= $tempo_restante ?>
          </div>
        </div>
      <?php else: ?>
        <div style="background: #fee2e2; border: 1px solid #fecaca; border-radius: 12px; padding: 16px; margin-bottom: 20px; text-align: center; color: #991b1b;">
          <i class="fa-solid fa-circle-xmark"></i> Código expirado
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <div class="route-section">
      <div class="route-item">
        <div class="route-icon"><i class="fa-solid fa-location-dot"></i></div>
        <div class="route-details">
          <div class="route-label">Origem</div>
          <div class="route-text"><?= $row['origem'] ?></div>
        </div>
      </div>
      <div class="route-item">
        <div class="route-icon"><i class="fa-solid fa-flag-checkered"></i></div>
        <div class="route-details">
          <div class="route-label">Destino</div>
          <div class="route-text"><?= $row['destino'] ?></div>
        </div>
      </div>
    </div>

    <div class="info-grid">
      <div class="info-box">
        <i class="fa-solid fa-calendar info-icon"></i>
        <div>
          <div class="info-label">Data</div>
          <div class="info-value"><?= $data_formatada ?></div>
        </div>
      </div>
      <div class="info-box">
        <i class="fa-solid fa-clock info-icon"></i>
        <div>
          <div class="info-label">Horário</div>
          <div class="info-value"><?= $row['horario'] ?></div>
        </div>
      </div>
    </div>

    <?php if (!empty($row['observacoes']) && $row['observacoes'] != '0'): ?>
      <div class="obs-box">
        <strong><i class="fa-solid fa-circle-info"></i> Observação:</strong><br>
        <?= $row['observacoes'] ?>
      </div>
    <?php endif; ?>

    <?php if ($tipo == 'disponivel'): ?>
      <a href="solicitar_carona.php?codigo_carona=<?= $row['codigo_carona'] ?>" class="btn btn-primary">
        <i class="fa-solid fa-hand-point-up"></i> Solicitar Carona
      </a>
    <?php elseif ($tipo == 'minha'): ?>
      <div style="display: grid; grid-template-columns: 1fr auto; gap: 12px;">
        <a href="ver_passageiros.php?codigo_carona=<?= $row['codigo_carona'] ?>" class="btn btn-primary">
          <i class="fa-solid fa-users"></i> Ver Passageiros
        </a>
        <a href="deletar_carona.php?codigo_carona=<?= $row['codigo_carona'] ?>" class="btn btn-danger" style="background: #dc2626;">
          <i class="fa-solid fa-trash"></i>
        </a>
      </div>
    <?php endif; ?>
  </div>
<?php
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
      padding-bottom: 80px;
    }

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
    }

    .logo-text {
      font-size: 18px;
      font-weight: 700;
    }

    .user-greeting {
      font-size: 14px;
      color: #64748b;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }

    .welcome-banner {
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      color: white;
      padding: 32px;
      border-radius: 16px;
      margin-bottom: 24px;
      text-align: center;
    }

    .welcome-title {
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 8px;
    }

    .welcome-subtitle {
      font-size: 15px;
      opacity: 0.9;
    }

    .tabs {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
      gap: 8px;
      background: white;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 4px;
      margin-bottom: 20px;
    }

    .tab-btn {
      padding: 12px;
      border: none;
      background: transparent;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
    }

    .tab-btn.active {
      background: #2563eb;
      color: white;
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }

    .carona-card {
      background: white;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      padding: 24px;
      margin-bottom: 16px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .carona-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
    }

    .motorista-info {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .avatar {
      width: 56px;
      height: 56px;
      background: linear-gradient(135deg, #3b82f6, #2563eb);
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 24px;
      font-weight: 700;
    }

    .motorista-name {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 4px;
    }

    .motorista-role {
      font-size: 13px;
      color: #64748b;
    }

    .badge {
      padding: 6px 12px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
    }

    .badge.available {
      background: #d1fae5;
      color: #065f46;
      border: 1px solid #a7f3d0;
    }

    .badge.codigo {
      background: #fef3c7;
      color: #78350f;
      border: 1px solid #fde68a;
      font-family: 'Courier New', monospace;
      font-size: 16px;
      letter-spacing: 2px;
    }

    .route-section {
      margin-bottom: 20px;
    }

    .route-item {
      display: flex;
      align-items: flex-start;
      gap: 16px;
      margin-bottom: 16px;
    }

    .route-icon {
      width: 40px;
      height: 40px;
      background: #dbeafe;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #2563eb;
      flex-shrink: 0;
    }

    .route-details {
      flex: 1;
    }

    .route-label {
      font-size: 11px;
      font-weight: 600;
      color: #64748b;
      text-transform: uppercase;
      margin-bottom: 4px;
    }

    .route-text {
      font-size: 16px;
      font-weight: 600;
      color: #1e293b;
    }

    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-bottom: 20px;
    }

    .info-box {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 16px;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
    }

    .info-icon {
      color: #2563eb;
      font-size: 18px;
    }

    .info-label {
      font-size: 11px;
      color: #64748b;
      margin-bottom: 2px;
    }

    .info-value {
      font-size: 14px;
      font-weight: 600;
    }

    .obs-box {
      padding: 16px;
      background: #fef3c7;
      border: 1px solid #fde68a;
      border-radius: 12px;
      margin-bottom: 20px;
      font-size: 14px;
      color: #78350f;
    }

    .btn {
      width: 100%;
      padding: 18px;
      border: none;
      border-radius: 12px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      text-decoration: none;
      display: block;
      text-align: center;
    }

    .btn-primary {
      background: #2563eb;
      color: white;
    }

    .btn-primary:hover {
      background: #1d4ed8;
    }

    .btn-danger {
      background: #dc2626;
      color: white;
      padding: 18px;
      width: auto;
    }

    .btn-danger:hover {
      background: #b91c1c;
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
      background: white;
      border-radius: 16px;
    }

    .empty-icon {
      font-size: 48px;
      color: #cbd5e1;
      margin-bottom: 16px;
    }

    .empty-title {
      font-size: 20px;
      font-weight: 700;
      margin-bottom: 8px;
    }

    .empty-text {
      color: #64748b;
    }

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
  <header class="header">
    <div class="header-content">
      <div class="header-logo">
        <div class="logo-icon"><i class="fa-solid fa-bus" style="color: #fff;"></i></div>
        <div class="logo-text">Carona Escolar</div>
      </div>
      <div class="user-greeting">Olá, <?= $nomeusuario ?></div>
    </div>
  </header>

  <div class="container">
    <div class="welcome-banner">
      <div class="welcome-title"> Bem-vindo ao Carona Escolar!</div>
      <div class="welcome-subtitle">Conectando estudantes de forma fácil e segura</div>
    </div>

    <div class="tabs">
      <button class="tab-btn active" onclick="showTab('disponiveis')">
        Disponíveis (<?= $result_disponiveis->num_rows ?>)
      </button>
      <button class="tab-btn" onclick="showTab('<?= $tipousuario == 'motorista' ? 'minhas' : 'solicitadas' ?>')">
        <?= $tipousuario == 'motorista' ? 'Minhas' : 'Solicitadas' ?>
        (<?= $tipousuario == 'motorista' ? $result_minhas->num_rows : $result_solicitadas->num_rows ?>)
      </button>
    </div>

    <!-- Disponíveis -->
    <div id="disponiveis" class="tab-content active">
      <?php if ($result_disponiveis->num_rows > 0): ?>
        <?php while ($row = $result_disponiveis->fetch_assoc()): ?>
          <?php renderCarona($row, 'disponivel'); ?>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="empty-state">
          <div class="empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
          <h3 class="empty-title">Nenhuma carona disponível</h3>
          <p class="empty-text">Seja o primeiro a oferecer uma carona</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Minhas (Motorista) -->
    <?php if ($tipousuario == 'motorista'): ?>
      <div id="minhas" class="tab-content">
        <?php if ($result_minhas->num_rows > 0): ?>
          <?php $result_minhas->data_seek(0);
          while ($row = $result_minhas->fetch_assoc()): ?>
            <?php renderCarona($row, 'minha'); ?>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="empty-state">
            <div class="empty-icon"><i class="fa-solid fa-car"></i></div>
            <h3 class="empty-title">Você não ofereceu nenhuma carona ainda</h3>
            <p class="empty-text">Clique em "Oferecer" para criar sua primeira carona</p>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- Solicitadas (Passageiro) -->
    <?php if ($tipousuario != 'motorista'): ?>
      <div id="solicitadas" class="tab-content">
        <?php if ($result_solicitadas->num_rows > 0): ?>
          <?php while ($row = $result_solicitadas->fetch_assoc()): ?>
            <?php renderCarona($row, 'solicitada'); ?>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="empty-state">
            <div class="empty-icon"><i class="fa-solid fa-ticket"></i></div>
            <h3 class="empty-title">Você não solicitou nenhuma carona ainda</h3>
            <p class="empty-text">Navegue pelas caronas disponíveis e solicite uma</p>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <nav class="bottom-nav">
    <div class="nav-content">
      <a href="index.php" class="nav-item active">
        <i class="fa-solid fa-house"></i>
        <span class="nav-label">Início</span>
      </a>
      <?php if ($tipousuario == 'motorista'): ?>
        <a href="oferecer.php" class="nav-item">
          <i class="fa-solid fa-plus"></i>
          <span class="nav-label">Oferecer</span>
        </a>
      <?php else: ?>
        <a href="caronas.php" class="nav-item">
          <i class="fa-solid fa-car"></i>
          <span class="nav-label">Caronas</span>
        </a>
      <?php endif; ?>
      <a href="perfil.php" class="nav-item">
        <i class="fa-solid fa-user"></i>
        <span class="nav-label">Perfil</span>
      </a>
    </div>
  </nav>

  <script>
    function showTab(tabName) {
      document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
      event.target.classList.add('active');
      document.getElementById(tabName).classList.add('active');
    }
  </script>
</body>

</html>