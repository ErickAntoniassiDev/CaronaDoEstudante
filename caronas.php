<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION["ID"])) {
    header("Location: login.php");
    exit;
}

$nomeusuario = $_SESSION["nome"];
$tipousuario = $_SESSION["tipo"];

// Busca com filtros
$origem = $_GET['origem'] ?? '';
$destino = $_GET['destino'] ?? '';
$data = $_GET['data'] ?? '';

$sql = "SELECT c.*, u.nome as motorista_nome, u.telefone as motorista_telefone 
        FROM carona c 
        INNER JOIN usuario u ON c.motorista_id = u.ID 
        WHERE c.status = 'ativo' AND c.vagas_disponiveis > 0";

if ($origem) $sql .= " AND c.origem LIKE '%$origem%'";
if ($destino) $sql .= " AND c.destino LIKE '%$destino%'";
if ($data) $sql .= " AND c.data = '$data'";

$sql .= " ORDER BY c.data, c.horario";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Buscar Caronas</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .search-box {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .search-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .search-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }

        .form-input {
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 15px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
        }

        .carona-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
        }

        .motorista-info {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .avatar {
            width: 48px;
            height: 48px;
            background: #2563eb;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            font-weight: 700;
        }

        .motorista-nome {
            font-size: 16px;
            font-weight: 600;
        }

        .route-info {
            margin-bottom: 16px;
        }

        .route-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }

        .route-item i {
            color: #2563eb;
            width: 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        .info-item {
            padding: 12px;
            background: #f8fafc;
            border-radius: 8px;
            font-size: 14px;
        }

        .info-label {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 4px;
        }

        .info-value {
            font-weight: 600;
        }

        .empty {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
        }

        .empty i {
            font-size: 48px;
            color: #cbd5e1;
            margin-bottom: 16px;
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

        .badge-vagas {
            background: #d1fae5;
            color: #065f46;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid #a7f3d0;
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
            <div>Olá, <?= $nomeusuario ?></div>
        </div>
    </header>

    <div class="container">
        <div class="search-box">
            <div class="search-title"><i class="fa-solid fa-magnifying-glass"></i> Buscar Caronas</div>
            <form method="GET" class="search-form">
                <input type="text" name="origem" class="form-input" placeholder="Origem" value="<?= htmlspecialchars($origem) ?>">
                <input type="text" name="destino" class="form-input" placeholder="Destino" value="<?= htmlspecialchars($destino) ?>">
                <input type="date" name="data" class="form-input" value="<?= htmlspecialchars($data) ?>">
                <button type="submit" class="btn btn-primary">Buscar</button>
                <a href="caronas.php" class="btn btn-secondary">Limpar</a>
            </form>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="carona-card">
                    <div class="motorista-info">
                        <div class="avatar"><?= strtoupper(substr($row['motorista_nome'], 0, 1)) ?></div>
                        <div style="flex: 1;">
                            <div class="motorista-nome"><?= $row['motorista_nome'] ?></div>
                            <div style="font-size: 13px; color: #64748b;"><?= $row['carro'] ?></div>
                        </div>
                        <span class="badge-vagas"><?= $row['vagas_disponiveis'] ?> vagas</span>
                    </div>

                    <div class="route-info">
                        <div class="route-item">
                            <i class="fa-solid fa-location-dot"></i>
                            <span><?= $row['origem'] ?></span>
                        </div>
                        <div class="route-item">
                            <i class="fa-solid fa-flag-checkered"></i>
                            <span><?= $row['destino'] ?></span>
                        </div>
                    </div>

                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Data</div>
                            <div class="info-value"><?= date('d/m/Y', strtotime($row['data'])) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Horário</div>
                            <div class="info-value"><?= $row['horario'] ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Vagas</div>
                            <div class="info-value"><?= $row['vagas_disponiveis'] ?> disponíveis</div>
                        </div>
                    </div>

                    <?php if ($row['observacoes'] && $row['observacoes'] != '0'): ?>
                        <div style="padding: 12px; background: #fef3c7; border-radius: 8px; margin-bottom: 12px; font-size: 14px;">
                            <?= $row['observacoes'] ?>
                        </div>
                    <?php endif; ?>

                    <a href="solicitar_carona.php?codigo_carona=<?= $row['codigo_carona'] ?>" class="btn btn-primary" style="width: 100%; display: block; text-align: center; text-decoration: none; padding: 14px;">
                        <i class="fa-solid fa-hand-point-up"></i> Solicitar Carona
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty">
                <i class="fa-solid fa-magnifying-glass"></i>
                <h3>Nenhuma carona encontrada</h3>
                <p style="color: #64748b;">Tente ajustar os filtros de busca</p>
            </div>
        <?php endif; ?>
    </div>

    <nav class="bottom-nav">
        <div class="nav-content">
            <a href="index.php" class="nav-item">
                <i class="fa-solid fa-house"></i>
                <span class="nav-label">Início</span>
            </a>
            <a href="caronas.php" class="nav-item active">
                <i class="fa-solid fa-car"></i>
                <span class="nav-label">Caronas</span>
            </a>
            <a href="perfil.php" class="nav-item">
                <i class="fa-solid fa-user"></i>
                <span class="nav-label">Perfil</span>
            </a>
        </div>
    </nav>

</body>

</html>