<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION["ID"])) {
    header("Location: login.php");
    exit;
}

$idusuario = $_SESSION["ID"];
$nomeusuario = $_SESSION["nome"];
$email = $_SESSION["email"];
$telefone = $_SESSION["telefone"];
$tipo = $_SESSION["tipo"];
$carro = $_SESSION["carro"] ?? 'Não informado';

// Estatísticas do usuário
if ($tipo == 'motorista') {
    $total_caronas = $conn->query("SELECT COUNT(*) as total FROM carona WHERE motorista_id = '$idusuario'")->fetch_assoc()['total'];
    $caronas_ativas = $conn->query("SELECT COUNT(*) as total FROM carona WHERE motorista_id = '$idusuario' AND status = 'ativo'")->fetch_assoc()['total'];
    $total_passageiros = $conn->query("SELECT COUNT(*) as total FROM solicitacao_carona sc 
                                        INNER JOIN carona c ON sc.codigo_carona = c.codigo_carona 
                                        WHERE c.motorista_id = '$idusuario' AND sc.status = 'ativa'")->fetch_assoc()['total'];
} else {
    $total_solicitadas = $conn->query("SELECT COUNT(*) as total FROM solicitacao_carona WHERE passageiro_id = '$idusuario'")->fetch_assoc()['total'];
    $solicitadas_ativas = $conn->query("SELECT COUNT(*) as total FROM solicitacao_carona WHERE passageiro_id = '$idusuario' AND status = 'ativa'")->fetch_assoc()['total'];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Meu Perfil - Carona Escolar</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            padding-bottom: 80px;
        }

        .header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 18px;
            font-weight: 700;
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

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .profile-header {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 40px;
            border-radius: 16px;
            text-align: center;
            margin-bottom: 24px;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.2);
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: 700;
            margin: 0 auto 20px;
        }

        .profile-name {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .profile-type {
            font-size: 15px;
            opacity: 0.9;
            padding: 6px 16px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            display: inline-block;
        }

        .card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 16px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 12px;
        }

        .info-icon {
            width: 48px;
            height: 48px;
            background: #dbeafe;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2563eb;
            font-size: 20px;
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            padding: 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            text-align: center;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 13px;
            color: #64748b;
        }

        .btn-logout {
            width: 100%;
            padding: 18px;
            background: #dc2626;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-logout:hover {
            background: #b91c1c;
            transform: translateY(-2px);
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #e2e8f0;
            padding: 12px 0;
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
            text-decoration: none;
            color: #64748b;
            border-radius: 8px;
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
            <div class="logo">
                <div class="logo-icon"><i class="fa-solid fa-bus"></i></div>
                Carona Escolar
            </div>
        </div>
    </header>

    <div class="container">
        <div class="profile-header">
            <div class="profile-avatar">
                <?= strtoupper(substr($nomeusuario, 0, 1)) ?>
            </div>
            <div class="profile-name"><?= htmlspecialchars($nomeusuario) ?></div>
            <div class="profile-type">
                <i class="fa-solid fa-<?= $tipo == 'motorista' ? 'car' : 'user' ?>"></i>
                <?= ucfirst($tipo) ?>
            </div>
        </div>

        <?php if ($tipo == 'motorista'): ?>
            <div class="card">
                <div class="card-title">
                    <i class="fa-solid fa-chart-simple"></i>
                    Estatísticas
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?= $total_caronas ?></div>
                        <div class="stat-label">Total de Caronas</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= $caronas_ativas ?></div>
                        <div class="stat-label">Caronas Ativas</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= $total_passageiros ?></div>
                        <div class="stat-label">Passageiros Atendidos</div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-title">
                    <i class="fa-solid fa-chart-simple"></i>
                    Estatísticas
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?= $total_solicitadas ?></div>
                        <div class="stat-label">Total Solicitadas</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= $solicitadas_ativas ?></div>
                        <div class="stat-label">Caronas Ativas</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-title">
                <i class="fa-solid fa-user"></i>
                Informações Pessoais
            </div>

            <div class="info-item">
                <div class="info-icon">
                    <i class="fa-solid fa-envelope"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">E-mail</div>
                    <div class="info-value"><?= htmlspecialchars($email) ?></div>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon">
                    <i class="fa-solid fa-phone"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Telefone</div>
                    <div class="info-value"><?= htmlspecialchars($telefone) ?></div>
                </div>
            </div>

            <?php if ($tipo == 'motorista'): ?>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fa-solid fa-car"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Veículo</div>
                        <div class="info-value"><?= htmlspecialchars($carro) ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <button onclick="confirmarLogout()" class="btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                Sair da Conta
            </button>
        </div>
    </div>

    <nav class="bottom-nav">
        <div class="nav-content">
            <a href="index.php" class="nav-item">
                <i class="fa-solid fa-house"></i>
                <span class="nav-label">Início</span>
            </a>
            <?php if ($tipo == 'motorista'): ?>
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
            <a href="perfil.php" class="nav-item active">
                <i class="fa-solid fa-user"></i>
                <span class="nav-label">Perfil</span>
            </a>
        </div>
    </nav>

    <script>
        function confirmarLogout() {
            window.location.href = 'logout.php';
        }
    </script>
</body>

</html>