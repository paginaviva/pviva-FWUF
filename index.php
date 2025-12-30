<?php
/**
 * Deployment Validation Test - PHP 8.3 on Shared Hosting
 * 
 * This page validates:
 * (a) PHP version running on the server
 * (b) Deployment from GitHub Actions with commit tracking
 */

// Load deployment info if available
$deploymentInfo = [
    'commitHash' => 'unknown',
    'buildTimestamp' => 'unknown',
    'buildDate' => 'unknown'
];

$buildFile = __DIR__ . '/build.json';
if (file_exists($buildFile)) {
    try {
        $buildData = json_decode(file_get_contents($buildFile), true);
        if (is_array($buildData)) {
            $deploymentInfo = array_merge($deploymentInfo, $buildData);
        }
    } catch (Throwable $e) {
        // Silently fail if build.json is corrupted
    }
}

$phpVersion = phpversion();
$phpVersionMajor = explode('.', $phpVersion)[0];
$phpVersionMinor = explode('.', $phpVersion)[1];

$isPhp83OrHigher = ($phpVersionMajor > 8) || ($phpVersionMajor == 8 && $phpVersionMinor >= 3);
$statusClass = $isPhp83OrHigher ? 'success' : 'warning';
$statusText = $isPhp83OrHigher ? 'Compatible' : 'Incompatible';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PVUF - Validación de Despliegue</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
            font-size: 14px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            font-size: 16px;
            color: #444;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .info-block {
            background: #f9f9f9;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 12px;
        }
        .info-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            font-family: 'Courier New', monospace;
            word-break: break-all;
        }
        .status {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 16px;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .status.warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        .status::before {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: currentColor;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
            color: #999;
            font-size: 12px;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PVUF</h1>
            <p>Prueba de Despliegue Automatizado desde GitHub Actions</p>
        </div>

        <div class="section">
            <h2>Estado de PHP</h2>
            <div class="status <?php echo $statusClass; ?>">
                PHP <?php echo htmlspecialchars($phpVersion); ?> - <?php echo $statusText; ?>
            </div>
            <div class="info-block" style="border-left-color: #667eea;">
                <div class="info-label">Versión Exacta</div>
                <div class="info-value"><?php echo htmlspecialchars($phpVersion); ?></div>
            </div>
        </div>

        <div class="section">
            <h2>Identificador de Despliegue</h2>
            <div class="info-block">
                <div class="info-label">Commit Hash (Corto)</div>
                <div class="info-value"><?php echo htmlspecialchars(substr($deploymentInfo['commitHash'], 0, 7)); ?></div>
            </div>
            <div class="info-block">
                <div class="info-label">Marca de Tiempo de Construcción</div>
                <div class="info-value"><?php echo htmlspecialchars($deploymentInfo['buildTimestamp']); ?></div>
            </div>
            <div class="info-block">
                <div class="info-label">Fecha de Construcción (Legible)</div>
                <div class="info-value"><?php echo htmlspecialchars($deploymentInfo['buildDate']); ?></div>
            </div>
        </div>

        <div class="section">
            <h2>Entorno</h2>
            <div class="info-block">
                <div class="info-label">Nombre del Entorno</div>
                <div class="info-value">Prueba de Despliegue</div>
            </div>
        </div>

        <div class="footer">
            <p>Servidor: <?php echo htmlspecialchars($_SERVER['SERVER_NAME'] ?? 'desconocido'); ?></p>
            <p>Página generada: <?php echo date('Y-m-d H:i:s T'); ?></p>
            <p><a href="#">Ver documentación de despliegue</a></p>
        </div>
    </div>
</body>
</html>
