<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION["ID"])) {
    header("Location: login.php");
    exit;
}

$idusuario = $_SESSION["ID"];
$nomeusuario = $_SESSION["nome"];
$carona_codigo = $_GET['codigo_carona'] ?? null;

if (!$carona_codigo) {
    header("Location: index.php");
    exit;
}

// Busca informações da carona
$sql = "SELECT c.*, u.nome as motorista_nome, u.telefone as motorista_telefone, u.email as motorista_email
        FROM carona c 
        INNER JOIN usuario u ON c.motorista_id = u.ID 
        WHERE c.codigo_carona = ? AND c.motorista_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $carona_codigo, $idusuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index.php");
    exit;
}

$carona = $result->fetch_assoc();

// Busca passageiros que solicitaram esta carona
$sql_passageiros = "SELECT s.*, u.nome, u.telefone, u.email 
                    FROM solicitacao_carona s
                    INNER JOIN usuario u ON s.passageiro_id = u.ID
                    WHERE s.codigo_carona = ? AND s.status = 'ativa'
                    ORDER BY s.data_solicitacao";
$stmt_pass = $conn->prepare($sql_passageiros);
$stmt_pass->bind_param("s", $carona_codigo);
$stmt_pass->execute();
$result_passageiros = $stmt_pass->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Passageiros da Carona</title>
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
            gap: 16px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .back-btn {
            width: 36px;
            height: 36px;
            background: #f1f5f9;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #475569;
            text-decoration: none;
        }

        .header-title {
            font-size: 18px;
            font-weight: 700;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 16px;
        }

        .carona-info {
            background: #dbeafe;
            border: 1px solid #93c5fd;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            color: #1e40af;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-row i {
            width: 20px;
        }

        .codigo-destaque {
            background: #fef3c7;
            border: 2px solid #fde68a;
            border-radius: 12px;
            padding: 16px;
            margin-top: 16px;
            text-align: center;
        }

        .codigo-label {
            font-size: 12px;
            color: #78350f;
            margin-bottom: 8px;
        }

        .codigo-valor {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 4px;
            color: #78350f;
            font-family: 'Courier New', monospace;
        }

        .motorista-contato {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .motorista-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .motorista-avatar {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: 700;
        }

        .motorista-nome {
            font-size: 18px;
            font-weight: 700;
            color: #065f46;
        }

        .motorista-role {
            font-size: 13px;
            color: #059669;
        }

        .contato-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: white;
            border-radius: 8px;
            margin-bottom: 8px;
            color: #065f46;
        }

        .contato-info i {
            width: 20px;
        }

        .contato-btns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 12px;
        }

        .btn-contato {
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-whatsapp {
            background: #10b981;
            color: white;
        }

        .btn-phone {
            background: #059669;
            color: white;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .passageiro-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
        }

        .passageiro-header {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .passageiro-avatar {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            font-weight: 700;
        }

        .passageiro-nome {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
        }

        .passageiro-info {
            font-size: 13px;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 4px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-icon {
            font-size: 48px;
            color: #cbd5e1;
            margin-bottom: 16px;
        }

        .vagas-info {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            font-weight: 600;
            color: #065f46;
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
            <a href="index.php" class="back-btn">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="header-title">Passageiros da Carona</div>
        </div>
    </header>

    <div class="container">
        <!-- Informações da Carona -->
        <div class="card">
            <div class="carona-info">
                <div class="info-row">
                    <i class="fa-solid fa-location-dot"></i>
                    <strong>Origem:</strong> <?= $carona['origem'] ?>
                </div>
                <div class="info-row">
                    <i class="fa-solid fa-flag-checkered"></i>
                    <strong>Destino:</strong> <?= $carona['destino'] ?>
                </div>
                <div class="info-row">
                    <i class="fa-solid fa-calendar"></i>
                    <strong>Data:</strong> <?= date('d/m/Y', strtotime($carona['data'])) ?>
                </div>
                <div class="info-row">
                    <i class="fa-solid fa-clock"></i>
                    <strong>Horário:</strong> <?= $carona['horario'] ?>
                </div>

                <div class="codigo-destaque">
                    <div class="codigo-label">SEU CÓDIGO DA CARONA</div>
                    <div class="codigo-valor"><?= $carona['codigo_carona'] ?></div>
                </div>
            </div>

            <div class="vagas-info">
                <i class="fa-solid fa-users"></i>
                <?= $carona['vagas_total'] - $carona['vagas_disponiveis'] ?> de <?= $carona['vagas_total'] ?> vagas ocupadas
            </div>
        </div>

        <!-- Informações de Contato do Motorista -->
        <div class="card">
            <div class="motorista-contato">
                <div class="motorista-header">
                    <div class="motorista-avatar">
                        <?= strtoupper(substr($carona['motorista_nome'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="motorista-nome"><?= $carona['motorista_nome'] ?></div>
                        <div class="motorista-role">Motorista da Carona</div>
                    </div>
                </div>

                <div class="contato-info">
                    <i class="fa-solid fa-phone"></i>
                    <strong><?= $carona['motorista_telefone'] ?></strong>
                </div>

                <div class="contato-info">
                    <i class="fa-solid fa-envelope"></i>
                    <?= $carona['motorista_email'] ?>
                </div>

                <div class="contato-btns">
                    <a href="https://wa.me/55<?= preg_replace('/[^0-9]/', '', $carona['motorista_telefone']) ?>"
                        class="btn-contato btn-whatsapp" target="_blank">
                        <i class="fa-brands fa-whatsapp"></i> WhatsApp
                    </a>
                    <a href="tel:<?= $carona['motorista_telefone'] ?>" class="btn-contato btn-phone">
                        <i class="fa-solid fa-phone"></i> Ligar
                    </a>
                </div>
            </div>
        </div>

        <!-- Lista de Passageiros -->
        <div class="card">
            <div class="section-title">
                <i class="fa-solid fa-users"></i>
                Passageiros Confirmados (<?= $result_passageiros->num_rows ?>)
            </div>

            <?php if ($result_passageiros->num_rows > 0): ?>
                <?php while ($passageiro = $result_passageiros->fetch_assoc()): ?>
                    <div class="passageiro-card">
                        <div class="passageiro-header">
                            <div class="passageiro-avatar">
                                <?= strtoupper(substr($passageiro['nome'], 0, 1)) ?>
                            </div>
                            <div style="flex: 1;">
                                <div class="passageiro-nome"><?= $passageiro['nome'] ?></div>
                                <div class="passageiro-info">
                                    <i class="fa-solid fa-clock"></i>
                                    Solicitou em <?= date('d/m/Y H:i', strtotime($passageiro['data_solicitacao'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon"><i class="fa-solid fa-user-slash"></i></div>
                    <h3>Nenhum passageiro ainda</h3>
                    <p style="color: #64748b; margin-top: 8px;">
                        Aguarde solicitações para sua carona
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Botão de Deletar Carona -->
        <div class="card">
            <a href="deletar_carona.php?codigo_carona=<?= $carona_codigo ?>"
                style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 16px; background: #dc2626; color: white; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 16px; transition: all 0.2s;"
                onmouseover="this.style.background='#b91c1c'"
                onmouseout="this.style.background='#dc2626'">
                <i class="fa-solid fa-trash"></i> Deletar Esta Carona
            </a>
        </div>
    </div>

    <nav class="bottom-nav">
        <div class="nav-content">
            <a href="index.php" class="nav-item">
                <i class="fa-solid fa-house"></i>
                <span class="nav-label">Início</span>
            </a>
            <a href="oferecer.php" class="nav-item">
                <i class="fa-solid fa-plus"></i>
                <span class="nav-label">Oferecer</span>
            </a>
            <a href="perfil.php" class="nav-item">
                <i class="fa-solid fa-user"></i>
                <span class="nav-label">Perfil</span>
            </a>
        </div>
    </nav>
</body>

</html>