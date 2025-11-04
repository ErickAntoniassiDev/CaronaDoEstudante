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

$sql = "SELECT c.*, u.nome as motorista_nome, u.telefone as motorista_telefone 
        FROM carona c 
        INNER JOIN usuario u ON c.motorista_id = u.ID 
        WHERE c.codigo_carona = ? AND c.status = 'ativo' AND c.vagas_disponiveis > 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $carona_codigo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index.php");
    exit;
}

$carona = $result->fetch_assoc();

$check_sql = "SELECT * FROM solicitacao_carona WHERE codigo_carona = ? AND passageiro_id = ? AND status = 'ativa'";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("si", $carona_codigo, $idusuario);
$check_stmt->execute();
$ja_solicitou = $check_stmt->get_result()->num_rows > 0;

$sucesso = false;

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$ja_solicitou) {
    $conn->begin_transaction();
    
    try {
        $insert_sql = "INSERT INTO solicitacao_carona (codigo_carona, passageiro_id, status, data_solicitacao) VALUES (?, ?, 'ativa', NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("si", $carona_codigo, $idusuario);
        
        if (!$insert_stmt->execute()) {
            throw new Exception("Erro ao registrar solicitação");
        }
        
        $update_sql = "UPDATE carona SET vagas_disponiveis = vagas_disponiveis - 1 WHERE codigo_carona = ? AND vagas_disponiveis > 0";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("s", $carona_codigo);
        
        if (!$update_stmt->execute() || $update_stmt->affected_rows == 0) {
            throw new Exception("Erro ao atualizar vagas");
        }
        
        $conn->commit();
        $sucesso = true;
        $ja_solicitou = true;
        $carona['vagas_disponiveis']--;
        
    } catch (Exception $e) {
        $conn->rollback();
        $erro = "Erro ao solicitar carona. Tente novamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Solicitar Carona</title>
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
            max-width: 600px;
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

        .info-section {
            margin-bottom: 24px;
        }

        .info-title {
            font-size: 14px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #f8fafc;
            border-radius: 8px;
            margin-bottom: 8px;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            background: #dbeafe;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2563eb;
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 12px;
            color: #64748b;
        }

        .info-value {
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
        }

        .alert {
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 16px;
        }

        .alert-warning {
            background: #fef3c7;
            border: 1px solid #fde68a;
            color: #78350f;
        }

        .alert-info {
            background: #dbeafe;
            border: 1px solid #93c5fd;
            color: #1e40af;
        }

        .alert-error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
            margin-top: 8px;
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

        /* Modal de Sucesso */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: white;
            border-radius: 24px;
            padding: 48px 32px;
            max-width: 450px;
            width: 90%;
            text-align: center;
            animation: slideUp 0.3s ease;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            animation: scaleIn 0.5s ease;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }

        .success-icon i {
            font-size: 56px;
            color: white;
        }

        .modal-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #1e293b;
        }

        .modal-subtitle {
            font-size: 15px;
            color: #64748b;
            margin-bottom: 32px;
            line-height: 1.6;
        }

        .codigo-display {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 2px solid #10b981;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 32px;
        }

        .codigo-label {
            font-size: 12px;
            color: #065f46;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .codigo-value {
            font-size: 32px;
            font-weight: 700;
            color: #059669;
            font-family: 'Courier New', monospace;
            letter-spacing: 4px;
        }

        .modal-info {
            background: #dbeafe;
            border: 1px solid #93c5fd;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            font-size: 14px;
            color: #1e40af;
        }

        .modal-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #10b981, #059669);
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

        .modal-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
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
        <?php if (isset($erro)): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i> <?= $erro ?>
            </div>
        <?php endif; ?>

        <?php if (!$sucesso): ?>
            <div class="card">
                <h2 style="font-size: 22px; font-weight: 700; margin-bottom: 24px; text-align: center;">
                    Confirmar Solicitação
                </h2>

                <div class="alert alert-info">
                    <i class="fa-solid fa-info-circle"></i> Após confirmar, você receberá um código que deverá mostrar ao motorista.
                </div>

                <div class="info-section">
                    <div class="info-title">Detalhes da Carona</div>

                    <div class="info-item">
                        <div class="info-icon"><i class="fa-solid fa-user"></i></div>
                        <div class="info-content">
                            <div class="info-label">Motorista</div>
                            <div class="info-value"><?= $carona['motorista_nome'] ?></div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="fa-solid fa-car"></i></div>
                        <div class="info-content">
                            <div class="info-label">Veículo</div>
                            <div class="info-value"><?= $carona['carro'] ?></div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="info-content">
                            <div class="info-label">Origem</div>
                            <div class="info-value"><?= $carona['origem'] ?></div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="fa-solid fa-flag-checkered"></i></div>
                        <div class="info-content">
                            <div class="info-label">Destino</div>
                            <div class="info-value"><?= $carona['destino'] ?></div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="fa-solid fa-calendar"></i></div>
                        <div class="info-content">
                            <div class="info-label">Data e Horário</div>
                            <div class="info-value"><?= date('d/m/Y', strtotime($carona['data'])) ?> às <?= $carona['horario'] ?></div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="fa-solid fa-users"></i></div>
                        <div class="info-content">
                            <div class="info-label">Vagas Disponíveis</div>
                            <div class="info-value"><?= $carona['vagas_disponiveis'] ?> vaga(s)</div>
                        </div>
                    </div>
                </div>

                <?php if ($carona['observacoes'] && $carona['observacoes'] != '0'): ?>
                    <div class="alert alert-warning">
                        <strong><i class="fa-solid fa-circle-info"></i> Observação:</strong><br>
                        <?= $carona['observacoes'] ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check"></i> Confirmar Solicitação
                    </button>
                </form>

                <button class="btn btn-secondary" onclick="window.location.href='index.php'">
                    <i class="fa-solid fa-arrow-left"></i> Cancelar
                </button>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($sucesso): ?>
    <div class="modal-overlay">
        <div class="modal-content">
            <div class="success-icon">
                <i class="fa-solid fa-check"></i>
            </div>
            
            <h2 class="modal-title">Carona Confirmada!</h2>
            <p class="modal-subtitle">Sua solicitação foi aceita. Mostre este código ao motorista para validar sua entrada.</p>
            
            <div class="codigo-display">
                <div class="codigo-label">SEU CÓDIGO DA CARONA</div>
                <div class="codigo-value"><?= $carona['codigo_carona'] ?></div>
            </div>

            <div class="modal-info">
                <i class="fa-solid fa-clock"></i> Este código é válido por <strong>30 minutos</strong> após o horário da carona
            </div>

            <button onclick="window.location.href='index.php'" class="modal-btn">
                <i class="fa-solid fa-house"></i>
                Voltar ao Início
            </button>
        </div>
    </div>
    <?php endif; ?>

    <nav class="bottom-nav">
        <div class="nav-content">
            <a href="index.php" class="nav-item">
                <i class="fa-solid fa-house"></i>
                <span class="nav-label">Início</span>
            </a>
            <a href="caronas.php" class="nav-item">
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