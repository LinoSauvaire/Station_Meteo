<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "station_meteo";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    $sql = "INSERT INTO parameters (
        update_frequency, 
        history_period,
        temp_min,
        temp_max,
        humidity_min,
        humidity_max,
        pressure_min,
        pressure_max
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiddiidd", 
        $_POST['update_frequency'],
        $_POST['history_period'],
        $_POST['temp_min'],
        $_POST['temp_max'],
        $_POST['humidity_min'],
        $_POST['humidity_max'],
        $_POST['pressure_min'],
        $_POST['pressure_max']
    );
    
    $stmt->execute();
    $conn->close();
    
    header('Location: index.php');
    exit;
}


$conn = new mysqli($servername, $username, $password, $dbname);
$sql = "SELECT * FROM parameters ORDER BY created_at DESC LIMIT 1";
$result = $conn->query($sql);
$params = $result->fetch_assoc();
$conn->close();
?>


<!DOCTYPE html>
<html>
<head>
    <title>Station météo - Paramètres</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans text-gray-800">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 fixed inset-y-0 bg-gray-800 text-white">
            <div class="p-6">
                <div class="text-2xl font-bold mb-8 flex items-center gap-2">
                    <i class="fas fa-chart-line"></i> Station Météo
                </div>
                <ul class="space-y-2">
                    <li class="p-3 rounded-lg hover:bg-gray-700 transition-all duration-300">
                        <a href="index.php" class="flex items-center gap-2">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="p-3 rounded-lg bg-gray-700 transition-all duration-300">
                        <a href="params.php" class="flex items-center gap-2">
                            <i class="fas fa-cog"></i> Paramètres
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-64 p-8">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-3xl font-bold text-gray-800 mb-8 flex items-center gap-3">
                    <i class="fas fa-cog text-blue-500"></i> Paramètres système
                </h1>

                <form method="POST" action="" class="space-y-6">
                    <!-- Intervalles de mesure -->
                    <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                        <h2 class="text-xl font-semibold mb-6 flex items-center gap-2 text-gray-700">
                            <i class="fas fa-clock text-blue-500"></i> Intervalles de mesure
                        </h2>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    Fréquence de mise à jour
                                </label>
                                <div class="relative">
                                    <input type="number" name="update_frequency" 
                                           value="<?php echo $params['update_frequency'] ?? 30; ?>" 
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300"
                                           min="1">
                                    <span class="absolute right-3 top-2 text-gray-500">secondes</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    Période d'historique
                                </label>
                                <div class="relative">
                                    <input type="number" name="history_period" 
                                           value="<?php echo $params['history_period'] ?? 24; ?>" 
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300"
                                           min="1">
                                    <span class="absolute right-3 top-2 text-gray-500">heures</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seuils d'alerte -->
                    <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                        <h2 class="text-xl font-semibold mb-6 flex items-center gap-2 text-gray-700">
                            <i class="fas fa-exclamation-triangle text-yellow-500"></i> Seuils d'alerte
                        </h2>
                        
                        <!-- Température -->
                        <div class="space-y-6">
                            <div class="border-b pb-6">
                                <h3 class="text-lg font-medium text-gray-700 mb-4 flex items-center gap-2">
                                    <i class="fas fa-thermometer-half text-red-500"></i> Température
                                </h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-600">Minimum</label>
                                        <input type="number" step="0.1" name="temp_min" 
                                               value="<?php echo $params['temp_min'] ?? 15; ?>" 
                                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-300">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-600">Maximum</label>
                                        <input type="number" step="0.1" name="temp_max" 
                                               value="<?php echo $params['temp_max'] ?? 30; ?>" 
                                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-300">
                                    </div>
                                </div>
                            </div>

                            <!-- Humidité -->
                            <div class="border-b pb-6">
                                <h3 class="text-lg font-medium text-gray-700 mb-4 flex items-center gap-2">
                                    <i class="fas fa-tint text-blue-500"></i> Humidité
                                </h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-600">Minimum</label>
                                        <input type="number" step="1" name="humidity_min" 
                                               value="<?php echo $params['humidity_min'] ?? 30; ?>" 
                                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-600">Maximum</label>
                                        <input type="number" step="1" name="humidity_max" 
                                               value="<?php echo $params['humidity_max'] ?? 70; ?>" 
                                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300">
                                    </div>
                                </div>
                            </div>

                            <!-- Pression -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-700 mb-4 flex items-center gap-2">
                                    <i class="fas fa-compress-alt text-green-500"></i> Pression
                                </h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-600">Minimum</label>
                                        <input type="number" step="1" name="pressure_min" 
                                               value="<?php echo $params['pressure_min'] ?? 980; ?>" 
                                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-600">Maximum</label>
                                        <input type="number" step="1" name="pressure_max" 
                                               value="<?php echo $params['pressure_max'] ?? 1020; ?>" 
                                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4">
                        <button type="reset" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-300">
                            Réinitialiser
                        </button>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-300 flex items-center gap-2">
                            <i class="fas fa-save"></i> Sauvegarder
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <footer class="fixed bottom-0 left-0 w-full bg-gray-800 text-white py-4 px-8">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <div class="text-sm text-gray-400">
            © <?php echo date('Y'); ?> Station Météo. Tous droits réservés.
        </div>
        <div class="flex items-center gap-2 text-sm">
            <span>Développé avec le</span>
            <span class="text-red-500 animate-pulse text-lg">❤</span>
            <span>par</span>
            <span class="text-blue-400 hover:text-blue-300 transition-colors duration-300">
            <a href='https://github.com/LinoSauvaire' target="_blank">Lino</a>, <a href='https://github.com/Najeko' target="_blank">Emma</a>, <a href='https://github.com/Chessco13' target="_blank">Jean-Baptiste</a>
            </span>
        </div>
    </div>
</footer>
</body>
</html>