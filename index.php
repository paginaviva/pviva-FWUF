<?php
/**
 * PVUF - Deployment Validation & Server Info Dashboard
 * 
 * This page validates:
 * (a) PHP version running on the server
 * (b) GitHub Actions deployment with commit tracking
 * (c) Display server phpinfo() and configuration
 * (d) Allow download of server info for development
 */

// Load deployment info if available
$deploymentInfo = [
    'commitHash' => 'unknown',
    'commitShort' => 'unknown',
    'buildTimestamp' => 'unknown',
    'buildDate' => 'unknown'
];

$buildFile = __DIR__ . '/build.json';
if (file_exists($buildFile)) {
    try {
        $buildData = json_decode(file_get_contents($buildFile), true);
        if (is_array($buildData)) {
            $deploymentInfo = array_merge($deploymentInfo, $buildData);
            // Extract short commit hash if not present
            if (!empty($buildData['commitHash']) && !isset($buildData['commitShort'])) {
                $deploymentInfo['commitShort'] = substr($buildData['commitHash'], 0, 7);
            }
        }
    } catch (Throwable $e) {
        // Silently fail if build.json is corrupted
    }
}

// Handle download request
if (isset($_GET['download']) && $_GET['download'] === 'true') {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="server-info-' . date('Y-m-d-His') . '.json"');
    
    // Get all important PHP info
    $serverInfo = [
        'timestamp' => date('Y-m-d H:i:s'),
        'php_version' => phpversion(),
        'php_sapi' => php_sapi_name(),
        'uname' => php_uname(),
        'extensions' => get_loaded_extensions(),
        'ini_settings' => [],
        'deployment_info' => $deploymentInfo,
        'environment' => [
            'os' => PHP_OS,
            'os_family' => PHP_OS_FAMILY,
            'platform' => PHP_INT_MAX == 2147483647 ? '32-bit' : '64-bit',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'max_input_vars' => ini_get('max_input_vars'),
            'display_errors' => ini_get('display_errors'),
            'error_reporting' => ini_get('error_reporting'),
        ]
    ];
    
    // Get important INI settings
    $important_inis = [
        'memory_limit', 'max_execution_time', 'max_input_vars', 
        'upload_max_filesize', 'post_max_size', 'display_errors',
        'error_reporting', 'date.timezone', 'default_charset',
        'session.save_path', 'extension_dir', 'include_path'
    ];
    
    foreach ($important_inis as $ini) {
        $serverInfo['ini_settings'][$ini] = ini_get($ini);
    }
    
    echo json_encode($serverInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

$phpVersion = phpversion();
$phpVersionParts = explode('.', $phpVersion);
$phpVersionMajor = (int)$phpVersionParts[0];
$phpVersionMinor = (int)$phpVersionParts[1];

$isPhp83OrHigher = ($phpVersionMajor > 8) || ($phpVersionMajor == 8 && $phpVersionMinor >= 3);
$statusClass = $isPhp83OrHigher ? 'success' : 'warning';
$statusText = $isPhp83OrHigher ? '✓ Compatible' : '⚠ Incompatible';

// Get some important configurations
$extensions = get_loaded_extensions();
$memory_limit = ini_get('memory_limit');
$max_execution_time = ini_get('max_execution_time');
$upload_max_filesize = ini_get('upload_max_filesize');
$post_max_size = ini_get('post_max_size');

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PVUF - Validación de Despliegue & Información del Servidor</title>
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
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .card-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .card-header p {
            opacity: 0.9;
            font-size: 16px;
        }
        .card-body {
            padding: 30px;
        }
        .status-badge {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
        }
        .status-badge.success {
            background: #d4edda;
            color: #155724;
            border: 2px solid #28a745;
        }
        .status-badge.warning {
            background: #fff3cd;
            color: #856404;
            border: 2px solid #ffc107;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .info-box h3 {
            color: #667eea;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .info-box p {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            word-break: break-all;
            font-family: 'Courier New', monospace;
        }
        .info-box.deployment {
            border-left-color: #764ba2;
        }
        .info-box.deployment h3 {
            color: #764ba2;
        }
        .section-title {
            font-size: 24px;
            color: #333;
            margin: 30px 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        .extensions-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 10px;
            margin: 20px 0;
        }
        .extension-tag {
            background: #e7f3ff;
            color: #0066cc;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 13px;
            text-align: center;
            border: 1px solid #0066cc;
            font-family: 'Courier New', monospace;
        }
        .config-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .config-table th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        .config-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .config-table tr:hover {
            background: #f8f9fa;
        }
        .button-group {
            display: flex;
            gap: 10px;
            margin: 30px 0;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .footer {
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 50px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Card -->
        <div class="card">
            <div class="card-header">
                <h1>🚀 PVUF</h1>
                <p>Validación de Despliegue & Información del Servidor</p>
            </div>
            <div class="card-body">
                <!-- PHP Version Status -->
                <div style="text-align: center; margin-bottom: 30px;">
                    <h2 style="margin-bottom: 15px; font-size: 20px;">PHP Version</h2>
                    <div style="font-size: 40px; font-weight: bold; margin-bottom: 10px;">
                        <?php echo htmlspecialchars($phpVersion); ?>
                    </div>
                    <div class="status-badge <?php echo $statusClass; ?>">
                        <?php echo $statusText; ?>
                    </div>
                </div>

                <!-- Deployment Info -->
                <div class="info-grid">
                    <div class="info-box deployment">
                        <h3>Commit Hash</h3>
                        <p><?php echo htmlspecialchars($deploymentInfo['commitHash']); ?></p>
                    </div>
                    <div class="info-box deployment">
                        <h3>Build Timestamp</h3>
                        <p><?php echo htmlspecialchars($deploymentInfo['buildTimestamp']); ?></p>
                    </div>
                    <div class="info-box deployment">
                        <h3>Build Date</h3>
                        <p><?php echo htmlspecialchars($deploymentInfo['buildDate']); ?></p>
                    </div>
                    <div class="info-box deployment">
                        <h3>SAPI</h3>
                        <p><?php echo htmlspecialchars(php_sapi_name()); ?></p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="button-group">
                    <a href="?download=true" class="btn btn-primary">⬇️ Descargar Información del Servidor</a>
                </div>
            </div>
        </div>

        <!-- Server Configuration Card -->
        <div class="card">
            <div class="card-body">
                <h2 class="section-title">📋 Configuración del Servidor</h2>
                
                <table class="config-table">
                    <thead>
                        <tr>
                            <th>Configuración</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Sistema Operativo</td>
                            <td><?php echo htmlspecialchars(PHP_OS_FAMILY); ?></td>
                        </tr>
                        <tr>
                            <td>Memory Limit</td>
                            <td><?php echo htmlspecialchars($memory_limit); ?></td>
                        </tr>
                        <tr>
                            <td>Max Execution Time</td>
                            <td><?php echo htmlspecialchars($max_execution_time); ?> segundos</td>
                        </tr>
                        <tr>
                            <td>Upload Max Filesize</td>
                            <td><?php echo htmlspecialchars($upload_max_filesize); ?></td>
                        </tr>
                        <tr>
                            <td>Post Max Size</td>
                            <td><?php echo htmlspecialchars($post_max_size); ?></td>
                        </tr>
                        <tr>
                            <td>Max Input Vars</td>
                            <td><?php echo htmlspecialchars(ini_get('max_input_vars')); ?></td>
                        </tr>
                        <tr>
                            <td>Timezone</td>
                            <td><?php echo htmlspecialchars(ini_get('date.timezone')); ?></td>
                        </tr>
                        <tr>
                            <td>Display Errors</td>
                            <td><?php echo htmlspecialchars(ini_get('display_errors') ? 'On' : 'Off'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Extensions Card -->
        <div class="card">
            <div class="card-body">
                <h2 class="section-title">🔌 Extensiones Cargadas (<?php echo count($extensions); ?>)</h2>
                <div class="extensions-list">
                    <?php foreach ($extensions as $ext): ?>
                        <div class="extension-tag"><?php echo htmlspecialchars($ext); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>✨ Proyecto PVUF - Validación de PHP 8.3+ y Despliegue Automatizado desde GitHub Actions</p>
        </div>
    </div>
</body>
</html>
