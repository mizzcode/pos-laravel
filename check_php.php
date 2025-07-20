<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Diagnosis Server Hosting</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      background: #f5f5f5;
    }

    .container {
      max-width: 1000px;
      margin: 0 auto;
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .status-ok {
      color: #27ae60;
      font-weight: bold;
    }

    .status-error {
      color: #e74c3c;
      font-weight: bold;
    }

    .status-warning {
      color: #f39c12;
      font-weight: bold;
    }

    .section {
      margin: 20px 0;
      padding: 15px;
      border-left: 4px solid #3498db;
      background: #f8f9fa;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin: 10px 0;
    }

    th,
    td {
      padding: 8px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    th {
      background: #f8f9fa;
    }

    .code {
      background: #2c3e50;
      color: #ecf0f1;
      padding: 10px;
      border-radius: 4px;
      font-family: monospace;
    }
  </style>
</head>

<body>
  <div class="container">
    <h1>🔍 Diagnosis Server Hosting</h1>
    <p><strong>Waktu Check:</strong> <?php echo date('Y-m-d H:i:s'); ?> WIB</p>

    <?php
    // 1. Server Information
    echo "<div class='section'>";
    echo "<h3>📋 Informasi Server</h3>";
    echo "<table>";
    echo "<tr><td>PHP Version</td><td>" . phpversion() . "</td></tr>";
    echo "<tr><td>Server Software</td><td>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</td></tr>";
    echo "<tr><td>Document Root</td><td>" . $_SERVER['DOCUMENT_ROOT'] . "</td></tr>";
    echo "<tr><td>HTTP Host</td><td>" . $_SERVER['HTTP_HOST'] . "</td></tr>";
    echo "<tr><td>Request URI</td><td>" . $_SERVER['REQUEST_URI'] . "</td></tr>";
    echo "<tr><td>Current Directory</td><td>" . getcwd() . "</td></tr>";
    echo "</table>";
    echo "</div>";

    // 2. Database Extensions Check
    echo "<div class='section'>";
    echo "<h3>🔌 Ekstensi Database</h3>";
    echo "<table>";

    $db_extensions = [
      'PDO' => class_exists('PDO'),
      'pdo_mysql' => extension_loaded('pdo_mysql'),
      'mysqli' => extension_loaded('mysqli'),
      'mysql' => extension_loaded('mysql')
    ];

    foreach ($db_extensions as $ext => $loaded) {
      $status = $loaded ? "<span class='status-ok'>✅ Tersedia</span>" : "<span class='status-error'>❌ Tidak Tersedia</span>";
      echo "<tr><td>$ext</td><td>$status</td></tr>";
    }
    echo "</table>";
    echo "</div>";

    // 3. PDO Drivers
    if (class_exists('PDO')) {
      echo "<div class='section'>";
      echo "<h3>🚗 PDO Drivers</h3>";
      $drivers = PDO::getAvailableDrivers();
      if (!empty($drivers)) {
        echo "<p><strong>Available drivers:</strong> " . implode(', ', $drivers) . "</p>";
      } else {
        echo "<p class='status-error'>❌ Tidak ada PDO drivers tersedia</p>";
      }
      echo "</div>";
    }

    // 5. Laravel Structure Check (Separated public folder)
    echo "<div class='section'>";
    echo "<h3>📁 Laravel Structure Check</h3>";

    // Check current directory structure
    echo "<h4>📂 Current Directory Structure:</h4>";
    echo "<table>";
    echo "<tr><td>Current Path</td><td>" . __DIR__ . "</td></tr>";
    echo "<tr><td>Document Root</td><td>" . $_SERVER['DOCUMENT_ROOT'] . "</td></tr>";
    echo "<tr><td>Script Name</td><td>" . $_SERVER['SCRIPT_NAME'] . "</td></tr>";
    echo "</table>";

    // Possible Laravel paths
    $possible_laravel_paths = [
      '../',           // One level up
      '../../',        // Two levels up  
      '../../../',     // Three levels up
      './',            // Same directory
      '../laravel/',   // Common folder name
      '../app/',       // Another common name
      '../' . basename($_SERVER['HTTP_HOST']) . '/', // Based on domain name
    ];

    echo "<h4>🔍 Searching for Laravel Files:</h4>";
    echo "<table>";
    echo "<tr><th>Path</th><th>artisan</th><th>composer.json</th><th>config/</th><th>app/</th><th>Status</th></tr>";

    $found_laravel_path = null;
    foreach ($possible_laravel_paths as $path) {
      $artisan = file_exists($path . 'artisan');
      $composer = file_exists($path . 'composer.json');
      $config = is_dir($path . 'config');
      $app = is_dir($path . 'app');

      $artisan_icon = $artisan ? '✅' : '❌';
      $composer_icon = $composer ? '✅' : '❌';
      $config_icon = $config ? '✅' : '❌';
      $app_icon = $app ? '✅' : '❌';

      $is_laravel = $artisan && $composer && $config && $app;
      $status = $is_laravel ? "<span class='status-ok'>✅ Laravel Found!</span>" : "<span class='status-error'>❌ Not Laravel</span>";

      echo "<tr>";
      echo "<td>" . realpath($path) . "</td>";
      echo "<td>$artisan_icon</td>";
      echo "<td>$composer_icon</td>";
      echo "<td>$config_icon</td>";
      echo "<td>$app_icon</td>";
      echo "<td>$status</td>";
      echo "</tr>";

      if ($is_laravel && !$found_laravel_path) {
        $found_laravel_path = $path;
      }
    }
    echo "</table>";

    // If Laravel found, show detailed info
    if ($found_laravel_path) {
      echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
      echo "<h4 style='color: #155724; margin-top: 0;'>🎉 Laravel Installation Found!</h4>";
      echo "<p><strong>Laravel Path:</strong> " . realpath($found_laravel_path) . "</p>";

      // Check Laravel version
      $composer_json = $found_laravel_path . 'composer.json';
      if (file_exists($composer_json)) {
        $composer_data = json_decode(file_get_contents($composer_json), true);
        if (isset($composer_data['require']['laravel/framework'])) {
          echo "<p><strong>Laravel Version:</strong> " . $composer_data['require']['laravel/framework'] . "</p>";
        }
      }

      // Check .env in Laravel directory
      $env_path = $found_laravel_path . '.env';
      if (file_exists($env_path)) {
        echo "<p class='status-ok'>✅ .env file found in Laravel directory</p>";
      } else {
        echo "<p class='status-error'>❌ .env file not found in Laravel directory</p>";
      }

      // Check if vendor exists
      if (is_dir($found_laravel_path . 'vendor')) {
        echo "<p class='status-ok'>✅ Vendor directory exists (Composer dependencies installed)</p>";
      } else {
        echo "<p class='status-error'>❌ Vendor directory missing (Run composer install)</p>";
      }

      // Check storage permissions
      $storage_path = $found_laravel_path . 'storage';
      if (is_dir($storage_path)) {
        $writable = is_writable($storage_path);
        $status = $writable ? "<span class='status-ok'>✅ Writable</span>" : "<span class='status-error'>❌ Not Writable</span>";
        echo "<p><strong>Storage permissions:</strong> $status</p>";
      }

      echo "</div>";
    } else {
      echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
      echo "<h4 style='color: #721c24; margin-top: 0;'>⚠️ Laravel Installation Not Found</h4>";
      echo "<p>Laravel files tidak ditemukan di path yang umum. Pastikan struktur folder benar.</p>";
      echo "</div>";
    }

    echo "</div>";

    // 6. Public Folder Connection Test
    echo "<div class='section'>";
    echo "<h3>🌐 Public Folder Connection Test</h3>";

    // Check if we're in public folder
    $current_files = scandir('.');
    $has_index_php = in_array('index.php', $current_files);
    $has_htaccess = in_array('.htaccess', $current_files);

    echo "<h4>📋 Current Folder Analysis:</h4>";
    echo "<table>";
    echo "<tr><td>Has index.php</td><td>" . ($has_index_php ? '✅ Yes' : '❌ No') . "</td></tr>";
    echo "<tr><td>Has .htaccess</td><td>" . ($has_htaccess ? '✅ Yes' : '❌ No') . "</td></tr>";

    if ($has_index_php) {
      // Check if index.php contains Laravel bootstrap
      $index_content = file_get_contents('index.php');
      $has_laravel_bootstrap = strpos($index_content, 'laravel') !== false || strpos($index_content, 'bootstrap') !== false;
      echo "<tr><td>Laravel Bootstrap in index.php</td><td>" . ($has_laravel_bootstrap ? '✅ Yes' : '❌ No') . "</td></tr>";

      if ($has_laravel_bootstrap) {
        echo "<tr><td colspan='2'><span class='status-ok'>✅ This appears to be Laravel's public folder</span></td></tr>";
      }
    }
    echo "</table>";

    // Show current folder contents
    echo "<h4>📂 Current Folder Contents:</h4>";
    $files = array_diff(scandir('.'), array('..', '.'));
    echo "<p>" . implode(', ', array_slice($files, 0, 20)) . (count($files) > 20 ? '... (' . count($files) . ' total files)' : '') . "</p>";

    echo "</div>";

    // Database Connection Test using found Laravel path
    echo "<div class='section'>";
    echo "<h3>🗄️ Database Connection Test</h3>";

    $env_to_check = [
      '.env',  // Current directory
      '../.env', // One level up
      $found_laravel_path ? $found_laravel_path . '.env' : null
    ];

    $env_found = false;
    foreach ($env_to_check as $env_path) {
      if ($env_path && file_exists($env_path)) {
        echo "<p class='status-ok'>✅ File .env ditemukan di: " . realpath($env_path) . "</p>";
        $env_found = true;

        // Parse .env file
        $envVars = [];
        $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
          if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $envVars[trim($key)] = trim($value, '"\'');
          }
        }

        if (isset($envVars['DB_HOST'], $envVars['DB_DATABASE'], $envVars['DB_USERNAME'])) {
          echo "<table>";
          echo "<tr><td>DB_HOST</td><td>" . $envVars['DB_HOST'] . "</td></tr>";
          echo "<tr><td>DB_DATABASE</td><td>" . $envVars['DB_DATABASE'] . "</td></tr>";
          echo "<tr><td>DB_USERNAME</td><td>" . $envVars['DB_USERNAME'] . "</td></tr>";
          echo "<tr><td>DB_PASSWORD</td><td>" . (empty($envVars['DB_PASSWORD']) ? 'Empty' : 'Set (hidden)') . "</td></tr>";
          echo "</table>";

          // Test database connection
          if (class_exists('PDO') && extension_loaded('pdo_mysql')) {
            try {
              $dsn = "mysql:host={$envVars['DB_HOST']};dbname={$envVars['DB_DATABASE']}";
              $pdo = new PDO($dsn, $envVars['DB_USERNAME'], $envVars['DB_PASSWORD'] ?? '');
              $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

              echo "<p class='status-ok'>✅ Koneksi database berhasil!</p>";

              // Test query
              $stmt = $pdo->query("SELECT DATABASE() as db_name, NOW() as current_time");
              $result = $stmt->fetch(PDO::FETCH_ASSOC);
              echo "<p><strong>Database aktif:</strong> " . $result['db_name'] . "</p>";
              echo "<p><strong>Server time:</strong> " . $result['current_time'] . "</p>";

              // Check tables
              $stmt = $pdo->query("SHOW TABLES");
              $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
              if (!empty($tables)) {
                echo "<p><strong>Tables found:</strong> " . count($tables) . " (" . implode(', ', array_slice($tables, 0, 5)) . (count($tables) > 5 ? '...' : '') . ")</p>";
              } else {
                echo "<p class='status-warning'>⚠️ Tidak ada tabel ditemukan</p>";
              }
            } catch (PDOException $e) {
              echo "<p class='status-error'>❌ Error koneksi database: " . $e->getMessage() . "</p>";
            }
          }
        } else {
          echo "<p class='status-error'>❌ Konfigurasi database tidak lengkap di .env</p>";
        }
        break;
      }
    }

    if (!$env_found) {
      echo "<p class='status-error'>❌ File .env tidak ditemukan di semua lokasi yang dicek</p>";
    }
    echo "</div>";

    // 6. Permissions Check
    echo "<div class='section'>";
    echo "<h3>🔐 Permissions Check</h3>";
    $paths_to_check = [
      'storage' => is_writable('storage'),
      'bootstrap/cache' => is_writable('bootstrap/cache'),
      'storage/logs' => is_writable('storage/logs'),
      'storage/framework' => is_writable('storage/framework'),
    ];

    echo "<table>";
    foreach ($paths_to_check as $path => $writable) {
      if (is_dir($path)) {
        $status = $writable ? "<span class='status-ok'>✅ Writable</span>" : "<span class='status-error'>❌ Not Writable</span>";
        echo "<tr><td>$path/</td><td>$status</td></tr>";
      } else {
        echo "<tr><td>$path/</td><td><span class='status-error'>❌ Directory not found</span></td></tr>";
      }
    }
    echo "</table>";
    echo "</div>";

    // 7. Sample .env configuration
    echo "<div class='section'>";
    echo "<h3>⚙️ Sample .env Configuration untuk Shared Hosting</h3>";
    echo "<div class='code'>";
    echo "# Database Configuration untuk cPanel/Shared Hosting<br>";
    echo "DB_CONNECTION=mysql<br>";
    echo "DB_HOST=localhost<br>";
    echo "DB_PORT=3306<br>";
    echo "DB_DATABASE=username_databasename<br>";
    echo "DB_USERNAME=username_dbuser<br>";
    echo "DB_PASSWORD=your_password<br><br>";
    echo "# Session untuk subdomain<br>";
    echo "SESSION_DOMAIN=.yourdomain.com<br>";
    echo "</div>";
    echo "</div>";

    // 8. Loaded Extensions
    echo "<div class='section'>";
    echo "<h3>🔧 All PHP Extensions (" . count(get_loaded_extensions()) . " loaded)</h3>";
    $extensions = get_loaded_extensions();
    sort($extensions);
    echo "<p>" . implode(', ', $extensions) . "</p>";
    echo "</div>";
    ?>

    <div class="section">
      <h3>🚀 Panduan Setup Laravel di Shared Hosting</h3>

      <h4>📂 Struktur Folder yang Benar:</h4>
      <div class="code">
        public_html/ &lt;-- Document Root<br>
        ├── subdomain_folder/ &lt;-- Folder subdomain Anda (pos/)<br>
        │ ├── index.php &lt;-- Laravel public/index.php<br>
        │ ├── .htaccess &lt;-- Laravel public/.htaccess<br>
        │ ├── css/, js/, images/ &lt;-- Assets<br>
        │ └── check_php.php &lt;-- File diagnosis ini<br>
        └── laravel_app/ &lt;-- Folder aplikasi Laravel<br>
        ├── app/<br>
        ├── config/<br>
        ├── routes/<br>
        ├── storage/<br>
        ├── vendor/<br>
        ├── .env<br>
        ├── artisan<br>
        └── composer.json
      </div>

      <h4>⚙️ Langkah Setup:</h4>
      <ol>
        <li><strong>Upload Laravel:</strong> Upload semua file Laravel (kecuali folder public) ke folder
          <code>laravel_app/</code>
        </li>
        <li><strong>Upload Public:</strong> Upload isi folder <code>public/</code> Laravel ke folder subdomain Anda</li>
        <li><strong>Edit index.php:</strong> Ubah path di <code>index.php</code> untuk menunjuk ke folder Laravel yang
          benar</li>
        <li><strong>Set Permissions:</strong>
          <ul>
            <li><code>chmod 755 laravel_app/storage/</code></li>
            <li><code>chmod 755 laravel_app/bootstrap/cache/</code></li>
          </ul>
        </li>
        <li><strong>Database:</strong> Buat database di cPanel dan update <code>.env</code></li>
        <li><strong>Test:</strong> Akses subdomain Anda</li>
      </ol>

      <h4>🔧 Contoh index.php yang Benar:</h4>
      <div class="code">
        &lt;?php<br><br>
        // Sesuaikan path ini dengan lokasi Laravel Anda<br>
        require __DIR__.'/../laravel_app/vendor/autoload.php';<br><br>
        $app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';<br><br>
        $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);<br><br>
        $response = $kernel->handle(<br>
        &nbsp;&nbsp;&nbsp;&nbsp;$request = Illuminate\Http\Request::capture()<br>
        );<br><br>
        $response->send();<br><br>
        $kernel->terminate($request, $response);
      </div>

      <h4>🔍 Troubleshooting:</h4>
      <ul>
        <li><strong>Error 500:</strong> Periksa path di index.php dan permissions folder storage</li>
        <li><strong>Class PDO not found:</strong> Hubungi support hosting untuk aktifkan ekstensi PDO</li>
        <li><strong>Database error:</strong> Periksa kredensial database di .env</li>
        <li><strong>Assets tidak load:</strong> Pastikan file CSS/JS di folder public subdomain</li>
      </ul>
    </div>
  </div>
</body>

</html>