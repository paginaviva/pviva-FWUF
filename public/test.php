<?php
/**
 * UserFrosting Installation Diagnostic Tool
 * Analyzes system requirements, permissions, and configuration
 * Generates downloadable JSON and TXT reports
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', 'localhost:3306');
define('DB_NAME', 'pvuf5fw');
define('DB_USER', 'usrpvuf5fw');
define('DB_PASS', 'gegkK9tkkyZDaADG');

// Project paths
$projectRoot = dirname(__DIR__);
$publicDir = __DIR__;

// Initialize diagnostic results array
$diagnosticResults = [];

// Handle download requests
if (isset($_GET['download'])) {
    $format = $_GET['download'];
    $timestamp = date('Y-m-d_His');
    
    if ($format === 'json') {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="uf_diagnostic_' . $timestamp . '.json"');
        echo json_encode($diagnosticResults, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    } elseif ($format === 'txt') {
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="uf_diagnostic_' . $timestamp . '.txt"');
        
        echo "UserFrosting Installation Diagnostic Report\n";
        echo "Generated: " . date('Y-m-d H:i:s') . "\n";
        echo str_repeat("=", 80) . "\n\n";
        
        foreach ($diagnosticResults as $section => $data) {
            echo strtoupper($section) . "\n";
            echo str_repeat("-", 80) . "\n";
            echo formatArrayAsText($data, 0);
            echo "\n";
        }
        exit;
    }
}

/**
 * Format array as indented text
 */
function formatArrayAsText($array, $indent = 0) {
    $output = '';
    $prefix = str_repeat('  ', $indent);
    
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $output .= $prefix . $key . ":\n";
            $output .= formatArrayAsText($value, $indent + 1);
        } else {
            $output .= $prefix . $key . ": " . $value . "\n";
        }
    }
    
    return $output;
}

/**
 * Check if a path exists and get its status
 */
function checkPath($path) {
    $exists = file_exists($path);
    $readable = is_readable($path);
    $writable = is_writable($path);
    
    return [
        'exists' => $exists,
        'readable' => $readable,
        'writable' => $writable,
        'path' => $path,
        'absolute' => realpath($path) ?: $path
    ];
}

/**
 * Get directory permissions info
 */
function getPermissionsInfo($path) {
    if (!file_exists($path)) {
        return ['error' => 'Path does not exist'];
    }
    
    $perms = fileperms($path);
    $owner = function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($path)) : null;
    $group = function_exists('posix_getgrgid') ? posix_getgrgid(filegroup($path)) : null;
    
    return [
        'octal' => substr(sprintf('%o', $perms), -4),
        'owner' => $owner ? $owner['name'] : fileowner($path),
        'group' => $group ? $group['name'] : filegroup($path),
        'is_dir' => is_dir($path),
        'is_file' => is_file($path),
        'is_link' => is_link($path)
    ];
}

/**
 * Check PHP extension
 */
function checkExtension($name, $required = true) {
    $loaded = extension_loaded($name);
    
    return [
        'name' => $name,
        'loaded' => $loaded,
        'required' => $required,
        'status' => $loaded ? '✓ OK' : ($required ? '✗ MISSING' : '○ Optional')
    ];
}

/**
 * Check PHP ini setting
 */
function checkIniSetting($setting, $recommended = null) {
    $value = ini_get($setting);
    
    return [
        'setting' => $setting,
        'value' => $value === false ? 'not set' : ($value === '' ? 'empty' : $value),
        'recommended' => $recommended
    ];
}

// ============================================================================
// 1. SYSTEM INFORMATION
// ============================================================================

$diagnosticResults['system'] = [
    'php_version' => PHP_VERSION,
    'php_sapi' => php_sapi_name(),
    'os' => PHP_OS,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
    'script_filename' => __FILE__,
    'current_user' => get_current_user(),
    'temp_dir' => sys_get_temp_dir(),
    'php_ini_path' => php_ini_loaded_file(),
    'timezone' => date_default_timezone_get()
];

if (function_exists('posix_getuid')) {
    $diagnosticResults['system']['uid'] = posix_getuid();
    $diagnosticResults['system']['gid'] = posix_getgid();
}

// ============================================================================
// 2. PHP REQUIREMENTS CHECK
// ============================================================================

$diagnosticResults['php_requirements'] = [
    'version' => [
        'current' => PHP_VERSION,
        'required' => '8.1.0',
        'recommended' => '8.2.0',
        'status' => version_compare(PHP_VERSION, '8.1.0', '>=') ? '✓ OK' : '✗ INSUFFICIENT'
    ]
];

// Required extensions
$requiredExtensions = [
    'pdo', 'pdo_mysql', 'mbstring', 'gd', 'curl', 'zip', 
    'openssl', 'tokenizer', 'json', 'fileinfo'
];

$diagnosticResults['php_requirements']['extensions'] = [];
foreach ($requiredExtensions as $ext) {
    $diagnosticResults['php_requirements']['extensions'][$ext] = checkExtension($ext, true);
}

// Optional but recommended extensions
$optionalExtensions = ['intl', 'imagick', 'apcu'];
foreach ($optionalExtensions as $ext) {
    $diagnosticResults['php_requirements']['extensions'][$ext] = checkExtension($ext, false);
}

// PHP configuration
$diagnosticResults['php_requirements']['ini_settings'] = [
    'memory_limit' => checkIniSetting('memory_limit', '256M'),
    'max_execution_time' => checkIniSetting('max_execution_time', '120'),
    'upload_max_filesize' => checkIniSetting('upload_max_filesize', '20M'),
    'post_max_size' => checkIniSetting('post_max_size', '20M'),
    'display_errors' => checkIniSetting('display_errors', 'Off'),
    'error_reporting' => checkIniSetting('error_reporting', 'E_ALL & ~E_DEPRECATED'),
    'file_uploads' => checkIniSetting('file_uploads', 'On')
];

// ============================================================================
// 3. PROJECT STRUCTURE CHECK
// ============================================================================

$criticalPaths = [
    'project_root' => $projectRoot,
    'public_dir' => $publicDir,
    'app_dir' => $projectRoot . '/app',
    'config_dir' => $projectRoot . '/app/config',
    'src_dir' => $projectRoot . '/app/src',
    'storage_dir' => $projectRoot . '/app/storage',
    'cache_dir' => $projectRoot . '/app/storage/cache',
    'logs_dir' => $projectRoot . '/app/storage/logs',
    'sessions_dir' => $projectRoot . '/app/storage/sessions',
    'vendor_dir' => $projectRoot . '/vendor',
    'composer_json' => $projectRoot . '/composer.json',
    'composer_lock' => $projectRoot . '/composer.lock',
    'index_php' => $publicDir . '/index.php',
    'htaccess' => $publicDir . '/.htaccess'
];

$diagnosticResults['project_structure'] = [];
foreach ($criticalPaths as $name => $path) {
    $diagnosticResults['project_structure'][$name] = checkPath($path);
}

// ============================================================================
// 4. PERMISSIONS CHECK
// ============================================================================

$writablePaths = [
    'storage' => $projectRoot . '/app/storage',
    'cache' => $projectRoot . '/app/storage/cache',
    'logs' => $projectRoot . '/app/storage/logs',
    'sessions' => $projectRoot . '/app/storage/sessions'
];

$diagnosticResults['permissions'] = [];
foreach ($writablePaths as $name => $path) {
    if (file_exists($path)) {
        $diagnosticResults['permissions'][$name] = getPermissionsInfo($path);
        $diagnosticResults['permissions'][$name]['writable'] = is_writable($path);
    } else {
        $diagnosticResults['permissions'][$name] = ['error' => 'Path does not exist'];
    }
}

// ============================================================================
// 5. DATABASE CONNECTION TEST
// ============================================================================

$diagnosticResults['database'] = [
    'host' => DB_HOST,
    'database' => DB_NAME,
    'user' => DB_USER
];

try {
    $dsn = 'mysql:host=' . str_replace(':3306', '', DB_HOST) . ';port=3306;dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    $diagnosticResults['database']['connection'] = '✓ SUCCESS';
    
    // Get database version
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    $diagnosticResults['database']['version'] = $version;
    
    // Check tables
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $diagnosticResults['database']['tables_count'] = count($tables);
    $diagnosticResults['database']['tables'] = $tables;
    
    // Check if UserFrosting tables exist
    $ufTables = ['users', 'roles', 'permissions', 'groups', 'role_users', 'permission_roles'];
    $existingUfTables = array_intersect($tables, $ufTables);
    $diagnosticResults['database']['uf_tables_found'] = count($existingUfTables);
    $diagnosticResults['database']['uf_tables_list'] = $existingUfTables;
    
} catch (PDOException $e) {
    $diagnosticResults['database']['connection'] = '✗ FAILED';
    $diagnosticResults['database']['error'] = $e->getMessage();
    $diagnosticResults['database']['error_code'] = $e->getCode();
}

// ============================================================================
// 6. COMPOSER CHECK
// ============================================================================

$diagnosticResults['composer'] = [
    'composer_json' => file_exists($projectRoot . '/composer.json'),
    'composer_lock' => file_exists($projectRoot . '/composer.lock'),
    'vendor_dir' => file_exists($projectRoot . '/vendor')
];

// Check if composer is available
$composerCheck = shell_exec('command -v composer 2>&1');
$diagnosticResults['composer']['composer_binary'] = !empty(trim($composerCheck)) ? trim($composerCheck) : 'not found';

// Check if autoload exists
if (file_exists($projectRoot . '/vendor/autoload.php')) {
    $diagnosticResults['composer']['autoload'] = '✓ EXISTS';
} else {
    $diagnosticResults['composer']['autoload'] = '✗ MISSING - Run: composer install';
}

// Count installed packages
if (file_exists($projectRoot . '/composer.lock')) {
    $composerLock = json_decode(file_get_contents($projectRoot . '/composer.lock'), true);
    $diagnosticResults['composer']['packages_installed'] = count($composerLock['packages'] ?? []);
}

// ============================================================================
// 7. ENVIRONMENT CHECK
// ============================================================================

$diagnosticResults['environment'] = [
    'env_file' => file_exists($projectRoot . '/.env'),
    'env_example' => file_exists($projectRoot . '/.env.example')
];

if (file_exists($projectRoot . '/.env')) {
    $envContent = file_get_contents($projectRoot . '/.env');
    $envLines = explode("\n", $envContent);
    $envVars = [];
    
    foreach ($envLines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, ) = explode('=', $line, 2);
            $envVars[] = trim($key);
        }
    }
    
    $diagnosticResults['environment']['env_variables'] = $envVars;
}

// ============================================================================
// 8. WEB SERVER CHECK
// ============================================================================

$diagnosticResults['webserver'] = [
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
    'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
    'server_addr' => $_SERVER['SERVER_ADDR'] ?? 'unknown',
    'server_port' => $_SERVER['SERVER_PORT'] ?? 'unknown',
    'request_scheme' => $_SERVER['REQUEST_SCHEME'] ?? 'unknown',
    'https' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'htaccess_exists' => file_exists($publicDir . '/.htaccess'),
    'mod_rewrite' => function_exists('apache_get_modules') ? in_array('mod_rewrite', apache_get_modules()) : 'unknown'
];

// ============================================================================
// GENERATE HTML OUTPUT
// ============================================================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UserFrosting Diagnostic Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f7fa;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
        }
        
        .download-buttons {
            background: #f8f9fa;
            padding: 20px 30px;
            border-bottom: 2px solid #e9ecef;
            text-align: center;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 10px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background: #48bb78;
        }
        
        .btn-secondary:hover {
            background: #38a169;
            box-shadow: 0 4px 8px rgba(72, 187, 120, 0.3);
        }
        
        .content {
            padding: 30px;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section h2 {
            color: #2d3748;
            font-size: 22px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }
        
        .info-item {
            background: #f7fafc;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        
        .info-item strong {
            display: block;
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        .info-item span {
            color: #4a5568;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        
        .status-ok {
            color: #48bb78;
            font-weight: bold;
        }
        
        .status-error {
            color: #f56565;
            font-weight: bold;
        }
        
        .status-warning {
            color: #ed8936;
            font-weight: bold;
        }
        
        .table-container {
            overflow-x: auto;
            margin: 15px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        tr:hover {
            background: #f7fafc;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .badge-error {
            background: #fed7d7;
            color: #742a2a;
        }
        
        .badge-warning {
            background: #feebc8;
            color: #7c2d12;
        }
        
        .badge-info {
            background: #bee3f8;
            color: #2c5282;
        }
        
        code {
            background: #2d3748;
            color: #e2e8f0;
            padding: 2px 8px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 5px;
            margin: 15px 0;
        }
        
        .alert-error {
            background: #fed7d7;
            border-left: 4px solid #f56565;
            color: #742a2a;
        }
        
        .alert-warning {
            background: #feebc8;
            border-left: 4px solid #ed8936;
            color: #7c2d12;
        }
        
        .alert-success {
            background: #c6f6d5;
            border-left: 4px solid #48bb78;
            color: #22543d;
        }
        
        .footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            color: #718096;
            border-top: 1px solid #e2e8f0;
        }
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .download-buttons .btn {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 UserFrosting Diagnostic Report</h1>
            <p>Complete system analysis for installation requirements</p>
            <p style="font-size: 14px; margin-top: 10px;">Generated: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
        
        <div class="download-buttons">
            <a href="?download=json" class="btn">📥 Download JSON</a>
            <a href="?download=txt" class="btn btn-secondary">📥 Download TXT</a>
        </div>
        
        <div class="content">
            <!-- SYSTEM INFORMATION -->
            <div class="section">
                <h2>💻 System Information</h2>
                <div class="info-grid">
                    <?php foreach ($diagnosticResults['system'] as $key => $value): ?>
                        <div class="info-item">
                            <strong><?php echo ucwords(str_replace('_', ' ', $key)); ?></strong>
                            <span><?php echo htmlspecialchars($value); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- PHP REQUIREMENTS -->
            <div class="section">
                <h2>🐘 PHP Requirements</h2>
                
                <div class="info-item" style="margin-bottom: 20px;">
                    <strong>PHP Version</strong>
                    <span>
                        Current: <?php echo $diagnosticResults['php_requirements']['version']['current']; ?>
                        | Required: <?php echo $diagnosticResults['php_requirements']['version']['required']; ?>
                        | 
                        <span class="<?php echo strpos($diagnosticResults['php_requirements']['version']['status'], '✓') !== false ? 'status-ok' : 'status-error'; ?>">
                            <?php echo $diagnosticResults['php_requirements']['version']['status']; ?>
                        </span>
                    </span>
                </div>
                
                <h3 style="margin: 20px 0 10px;">Extensions</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Extension</th>
                                <th>Status</th>
                                <th>Required</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($diagnosticResults['php_requirements']['extensions'] as $ext): ?>
                                <tr>
                                    <td><code><?php echo $ext['name']; ?></code></td>
                                    <td>
                                        <?php
                                        if (strpos($ext['status'], '✓') !== false) {
                                            echo '<span class="badge badge-success">' . $ext['status'] . '</span>';
                                        } elseif (strpos($ext['status'], '✗') !== false) {
                                            echo '<span class="badge badge-error">' . $ext['status'] . '</span>';
                                        } else {
                                            echo '<span class="badge badge-info">' . $ext['status'] . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo $ext['required'] ? 'Yes' : 'Optional'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <h3 style="margin: 20px 0 10px;">Configuration</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Setting</th>
                                <th>Current Value</th>
                                <th>Recommended</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($diagnosticResults['php_requirements']['ini_settings'] as $setting): ?>
                                <tr>
                                    <td><code><?php echo $setting['setting']; ?></code></td>
                                    <td><?php echo htmlspecialchars($setting['value']); ?></td>
                                    <td><?php echo $setting['recommended'] ?? 'N/A'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- PROJECT STRUCTURE -->
            <div class="section">
                <h2>📁 Project Structure</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Path</th>
                                <th>Exists</th>
                                <th>Readable</th>
                                <th>Writable</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($diagnosticResults['project_structure'] as $name => $info): ?>
                                <tr>
                                    <td><strong><?php echo $name; ?></strong><br><small><?php echo htmlspecialchars($info['path']); ?></small></td>
                                    <td>
                                        <?php echo $info['exists'] 
                                            ? '<span class="badge badge-success">Yes</span>' 
                                            : '<span class="badge badge-error">No</span>'; ?>
                                    </td>
                                    <td>
                                        <?php echo $info['readable'] 
                                            ? '<span class="badge badge-success">Yes</span>' 
                                            : '<span class="badge badge-error">No</span>'; ?>
                                    </td>
                                    <td>
                                        <?php echo $info['writable'] 
                                            ? '<span class="badge badge-success">Yes</span>' 
                                            : '<span class="badge badge-warning">No</span>'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- PERMISSIONS -->
            <div class="section">
                <h2>🔐 Permissions</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Directory</th>
                                <th>Permissions</th>
                                <th>Owner</th>
                                <th>Group</th>
                                <th>Writable</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($diagnosticResults['permissions'] as $name => $info): ?>
                                <tr>
                                    <td><strong><?php echo $name; ?></strong></td>
                                    <td>
                                        <?php if (isset($info['error'])): ?>
                                            <span class="badge badge-error"><?php echo $info['error']; ?></span>
                                        <?php else: ?>
                                            <code><?php echo $info['octal']; ?></code>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo isset($info['owner']) ? htmlspecialchars($info['owner']) : 'N/A'; ?></td>
                                    <td><?php echo isset($info['group']) ? htmlspecialchars($info['group']) : 'N/A'; ?></td>
                                    <td>
                                        <?php if (isset($info['writable'])): ?>
                                            <?php echo $info['writable'] 
                                                ? '<span class="badge badge-success">Yes</span>' 
                                                : '<span class="badge badge-error">No</span>'; ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- DATABASE -->
            <div class="section">
                <h2>🗄️ Database Connection</h2>
                <?php if (strpos($diagnosticResults['database']['connection'], 'SUCCESS') !== false): ?>
                    <div class="alert alert-success">
                        <strong>✓ Database connection successful!</strong>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Host</strong>
                            <span><?php echo htmlspecialchars($diagnosticResults['database']['host']); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Database</strong>
                            <span><?php echo htmlspecialchars($diagnosticResults['database']['database']); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Version</strong>
                            <span><?php echo htmlspecialchars($diagnosticResults['database']['version']); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Tables Found</strong>
                            <span><?php echo $diagnosticResults['database']['tables_count']; ?> tables</span>
                        </div>
                        <div class="info-item">
                            <strong>UserFrosting Tables</strong>
                            <span><?php echo $diagnosticResults['database']['uf_tables_found']; ?> found</span>
                        </div>
                    </div>
                    
                    <?php if (!empty($diagnosticResults['database']['tables'])): ?>
                        <h3 style="margin: 20px 0 10px;">Database Tables</h3>
                        <div style="background: #f7fafc; padding: 15px; border-radius: 5px;">
                            <?php foreach ($diagnosticResults['database']['tables'] as $table): ?>
                                <span class="badge badge-info" style="margin: 5px;"><?php echo htmlspecialchars($table); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-error">
                        <strong>✗ Database connection failed!</strong><br>
                        Error: <?php echo htmlspecialchars($diagnosticResults['database']['error']); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- COMPOSER -->
            <div class="section">
                <h2>📦 Composer</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>composer.json</strong>
                        <span class="<?php echo $diagnosticResults['composer']['composer_json'] ? 'status-ok' : 'status-error'; ?>">
                            <?php echo $diagnosticResults['composer']['composer_json'] ? '✓ Found' : '✗ Missing'; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <strong>composer.lock</strong>
                        <span class="<?php echo $diagnosticResults['composer']['composer_lock'] ? 'status-ok' : 'status-error'; ?>">
                            <?php echo $diagnosticResults['composer']['composer_lock'] ? '✓ Found' : '✗ Missing'; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <strong>vendor/ directory</strong>
                        <span class="<?php echo $diagnosticResults['composer']['vendor_dir'] ? 'status-ok' : 'status-error'; ?>">
                            <?php echo $diagnosticResults['composer']['vendor_dir'] ? '✓ Found' : '✗ Missing'; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <strong>Autoload</strong>
                        <span class="<?php echo strpos($diagnosticResults['composer']['autoload'], '✓') !== false ? 'status-ok' : 'status-error'; ?>">
                            <?php echo $diagnosticResults['composer']['autoload']; ?>
                        </span>
                    </div>
                    <?php if (isset($diagnosticResults['composer']['packages_installed'])): ?>
                        <div class="info-item">
                            <strong>Packages Installed</strong>
                            <span><?php echo $diagnosticResults['composer']['packages_installed']; ?> packages</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- ENVIRONMENT -->
            <div class="section">
                <h2>⚙️ Environment</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>.env file</strong>
                        <span class="<?php echo $diagnosticResults['environment']['env_file'] ? 'status-ok' : 'status-warning'; ?>">
                            <?php echo $diagnosticResults['environment']['env_file'] ? '✓ Found' : '○ Not found'; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <strong>.env.example file</strong>
                        <span class="<?php echo $diagnosticResults['environment']['env_example'] ? 'status-ok' : 'status-warning'; ?>">
                            <?php echo $diagnosticResults['environment']['env_example'] ? '✓ Found' : '○ Not found'; ?>
                        </span>
                    </div>
                </div>
                
                <?php if (isset($diagnosticResults['environment']['env_variables']) && !empty($diagnosticResults['environment']['env_variables'])): ?>
                    <h3 style="margin: 20px 0 10px;">Environment Variables Defined</h3>
                    <div style="background: #f7fafc; padding: 15px; border-radius: 5px;">
                        <?php foreach ($diagnosticResults['environment']['env_variables'] as $var): ?>
                            <span class="badge badge-info" style="margin: 5px;"><?php echo htmlspecialchars($var); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- WEB SERVER -->
            <div class="section">
                <h2>🌐 Web Server</h2>
                <div class="info-grid">
                    <?php foreach ($diagnosticResults['webserver'] as $key => $value): ?>
                        <div class="info-item">
                            <strong><?php echo ucwords(str_replace('_', ' ', $key)); ?></strong>
                            <span>
                                <?php 
                                if (is_bool($value)) {
                                    echo $value ? '✓ Yes' : '✗ No';
                                } else {
                                    echo htmlspecialchars($value);
                                }
                                ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>UserFrosting Diagnostic Tool</strong> | Generated: <?php echo date('Y-m-d H:i:s'); ?></p>
            <p style="font-size: 13px; margin-top: 10px;">Use the download buttons above to save this report</p>
        </div>
    </div>
</body>
</html>
