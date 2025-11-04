<?php
session_start();
include 'conexao.php';
// Verifica se está logado
if (!isset($_SESSION["ID"])) {
    header("Location: login.php");
    exit;
}

// Verifica se tem código na URL
if (!isset($_GET['codigo']) || empty($_GET['codigo'])) {
    header("Location: index.php");
    exit;
}

$codigo_carona = trim($_GET['codigo']);
$usuario_id = $_SESSION["ID"];

// Busca informações da carona e verifica se o usuário tem acesso
$sql = "SELECT c.*, s.passageiro_id, s.data_solicitacao 
        FROM carona c
        LEFT JOIN solicitacao_carona s ON c.codigo_carona = s.carona_codigo AND s.passageiro_id = ?
        WHERE c.codigo_carona = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $usuario_id, $codigo_carona);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>
    alert('Carona não encontrada!');
    window.location='index.php';
  </script>";
    exit;
}

$carona = $result->fetch_assoc();

// Verifica se o usuário é o motorista ou um passageiro desta carona
$tem_acesso = ($carona['motorista_id'] == $usuario_id) || ($carona['passageiro_id'] == $usuario_id);

if (!$tem_acesso) {
    echo "<script>
    alert('Você não tem acesso a esta carona!');
    window.location='index.php';
  </script>";
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código da Carona - Carona Escolar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #1e293b;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .card {
            background: white;
            border-radius: 24px;
            padding: 40px 30px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.5s ease;
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

        .icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin: 0 auto 24px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        h1 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 12px;
            text-align: center;
        }

        p {
            font-size: 15px;
            color: #64748b;
            margin-bottom: 28px;
            text-align: center;
            line-height: 1.5;
        }

        .codigo {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border: 3px solid #667eea;
            border-radius: 16px;
            font-size: 36px;
            font-weight: 800;
            letter-spacing: 8px;
            color: #667eea;
            padding: 28px;
            margin-bottom: 24px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.2);
            position: relative;
            overflow: hidden;
        }

        .codigo::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% {
                left: -50%;
            }

            50% {
                left: 150%;
            }

            100% {
                left: 150%;
            }
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            color: #64748b;
            font-weight: 500;
        }

        .info-value {
            color: #1e293b;
            font-weight: 600;
        }

        .route-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .route-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .route-item:last-child {
            margin-bottom: 0;
        }

        .route-icon {
            color: #0284c7;
            font-size: 18px;
        }

        .route-text {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }

        .btn {
            display: block;
            width: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            padding: 16px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            text-align: center;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
            box-shadow: none;
            margin-top: 12px;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
            box-shadow: none;
        }

        .footer {
            margin-top: 28px;
            font-size: 13px;
            color: #94a3b8;
            text-align: center;
        }

        .success-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #dcfce7;
            color: #166534;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="card">
        <div class="icon"><i class="fa-solid fa-ticket"></i></div>

        <div style="text-align: center;">
            <span class="success-badge">
                <i class="fa-solid fa-circle-check"></i> Carona Confirmada
            </span>
        </div>

        <h1>Seu Código de Carona</h1>
        <p>Mostre este código ao motorista para validar sua entrada na carona:</p>

        <div class="codigo" id="codigo"><?= htmlspecialchars($codigo_carona) ?></div>

        <div class="route-box">
            <div class="route-item">
                <i class="fa-solid fa-location-dot route-icon"></i>
                <div>
                    <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">ORIGEM</div>
                    <div class="route-text"><?= htmlspecialchars($carona['origem']) ?></div>
                </div>
            </div>
            <div class="route-item">
                <i class="fa-solid fa-flag-checkered route-icon"></i>
                <div>
                    <div style="font-size: 11px; color: #64748b; margin-bottom: 2px;">DESTINO</div>
                    <div class="route-text"><?= htmlspecialchars($carona['destino']) ?></div>
                </div>
            </div>
        </div>

        <div class="info-box">
            <div class="info-row">
                <span class="info-label"><i class="fa-solid fa-calendar"></i> Data</span>
                <span class="info-value"><?= date('d/m/Y', strtotime($carona['data'])) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fa-solid fa-clock"></i> Horário</span>
                <span class="info-value"><?= htmlspecialchars($carona['horario']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label"><i class="fa-solid fa-car"></i> Veículo</span>
                <span class="info-value"><?= htmlspecialchars($carona['carro']) ?></span>
            </div>
        </div>

        <a href="index.php" class="btn">
            <i class="fa-solid fa-house"></i> Voltar ao Início
        </a>

        <button onclick="compartilharCodigo()" class="btn btn-secondary">
            <i class="fa-solid fa-share-nodes"></i> Compartilhar Código
        </button>

        <div class="footer">
            Carona Escolar © 2025
        </div>
    </div>

    <script>
        function compartilharCodigo() {
            const codigo = '<?= $codigo_carona ?>';
            const texto = `Meu código de carona: ${codigo}\n\nOrigem: <?= htmlspecialchars($carona['origem']) ?>\nDestino: <?= htmlspecialchars($carona['destino']) ?>\nData: <?= date('d/m/Y', strtotime($carona['data'])) ?>\nHorário: <?= htmlspecialchars($carona['horario']) ?>`;

            if (navigator.share) {
                navigator.share({
                    title: 'Código da Carona',
                    text: texto
                }).catch(err => console.log('Erro ao compartilhar:', err));
            } else {
                // Fallback: copiar para clipboard
                navigator.clipboard.writeText(texto).then(() => {
                    alert('Código copiado para a área de transferência!');
                }).catch(err => {
                    alert('Código: ' + codigo);
                });
            }
        }
    </script>
</body>

</html>