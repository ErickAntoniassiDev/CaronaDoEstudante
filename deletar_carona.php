<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION["ID"])) {
    header("Location: login.php");
    exit;
}

$idusuario = $_SESSION["ID"];
$carona_codigo = $_GET['codigo_carona'] ?? null;

if (!$carona_codigo) {
    header("Location: index.php");
    exit;
}

$sql_verifica = "SELECT * FROM carona WHERE codigo_carona = ? AND motorista_id = ?";
$stmt_verifica = $conn->prepare($sql_verifica);
$stmt_verifica->bind_param("si", $carona_codigo, $idusuario);
$stmt_verifica->execute();
$result_verifica = $stmt_verifica->get_result();

if ($result_verifica->num_rows == 0) {
    header("Location: index.php?erro=permissao");
    exit;
}

$carona = $result_verifica->fetch_assoc();
$deletado = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $conn->begin_transaction();
    
    try {
        $delete_solicitacoes = "DELETE FROM solicitacao_carona WHERE codigo_carona = ?";
        $stmt_sol = $conn->prepare($delete_solicitacoes);
        $stmt_sol->bind_param("s", $carona_codigo);
        $stmt_sol->execute();
        
        $delete_carona = "DELETE FROM carona WHERE codigo_carona = ?";
        $stmt_car = $conn->prepare($delete_carona);
        $stmt_car->bind_param("s", $carona_codigo);
        $stmt_car->execute();
        
        $conn->commit();
        $deletado = true;
        
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: index.php?erro=delete");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Deletar Carona</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .modal {
            background: white;
            border-radius: 16px;
            padding: 32px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            animation: slideUp 0.3s ease;
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

        .icon-warning {
            width: 80px;
            height: 80px;
            background: #fef3c7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            color: #f59e0b;
            font-size: 40px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
            text-align: center;
            color: #1e293b;
        }

        .subtitle {
            font-size: 15px;
            color: #64748b;
            text-align: center;
            margin-bottom: 24px;
            line-height: 1.5;
        }

        .carona-info {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-row i {
            color: #2563eb;
            width: 20px;
        }

        .alert-danger {
            background: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            color: #991b1b;
            font-size: 14px;
        }

        .alert-danger strong {
            display: block;
            margin-bottom: 8px;
        }

        .btn-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .btn {
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
            text-decoration: none;
        }

        .btn-cancel {
            background: #f1f5f9;
            color: #475569;
        }

        .btn-cancel:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: #dc2626;
            color: white;
        }

        .btn-delete:hover {
            background: #b91c1c;
            transform: translateY(-2px);
        }

        /* Modal de Sucesso */
        .success-overlay {
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

        .success-modal {
            background: white;
            border-radius: 24px;
            padding: 48px 32px;
            max-width: 450px;
            width: 90%;
            text-align: center;
            animation: slideUp 0.3s ease;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            animation: scaleIn 0.5s ease;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0) rotate(0deg);
            }
            50% {
                transform: scale(1.1) rotate(5deg);
            }
            100% {
                transform: scale(1) rotate(0deg);
            }
        }

        .success-icon i {
            font-size: 56px;
            color: white;
        }

        .success-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #1e293b;
        }

        .success-subtitle {
            font-size: 15px;
            color: #64748b;
            margin-bottom: 32px;
            line-height: 1.6;
        }

        .success-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
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

        .success-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.3);
        }
    </style>
</head>
<body>
    <?php if (!$deletado): ?>
    <div class="modal">
        <div class="icon-warning">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>

        <h1>Deletar Carona?</h1>
        <p class="subtitle">Esta ação não pode ser desfeita. A carona e todas as solicitações relacionadas serão removidas permanentemente.</p>

        <div class="carona-info">
            <div class="info-row">
                <i class="fa-solid fa-location-dot"></i>
                <strong>De:</strong> <?= htmlspecialchars($carona['origem']) ?>
            </div>
            <div class="info-row">
                <i class="fa-solid fa-flag-checkered"></i>
                <strong>Para:</strong> <?= htmlspecialchars($carona['destino']) ?>
            </div>
            <div class="info-row">
                <i class="fa-solid fa-calendar"></i>
                <strong>Data:</strong> <?= date('d/m/Y', strtotime($carona['data'])) ?> às <?= $carona['horario'] ?>
            </div>
            <div class="info-row">
                <i class="fa-solid fa-users"></i>
                <strong>Vagas:</strong> <?= $carona['vagas_total'] - $carona['vagas_disponiveis'] ?> passageiro(s) confirmado(s)
            </div>
        </div>

        <?php if ($carona['vagas_total'] - $carona['vagas_disponiveis'] > 0): ?>
        <div class="alert-danger">
            <strong><i class="fa-solid fa-circle-exclamation"></i> Atenção!</strong>
            Esta carona possui passageiros confirmados. Ao deletá-la, eles perderão o acesso ao código da carona.
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="btn-group">
                <a href="index.php" class="btn btn-cancel">
                    <i class="fa-solid fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-delete">
                    <i class="fa-solid fa-trash"></i> Deletar
                </button>
            </div>
        </form>
    </div>
    <?php else: ?>
    <div class="success-overlay">
        <div class="success-modal">
            <div class="success-icon">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            
            <h2 class="success-title">Carona Deletada!</h2>
            <p class="success-subtitle">A carona foi removida com sucesso. Todos os passageiros foram notificados sobre o cancelamento.</p>

            <button onclick="window.location.href='index.php'" class="success-btn">
                <i class="fa-solid fa-house"></i>
                Voltar ao Início
            </button>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>