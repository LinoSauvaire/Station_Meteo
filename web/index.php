<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "station_meteo";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$request = "SELECT temperature, humidity, readings_time, pression FROM readings ORDER BY readings_time DESC LIMIT 10";
$requestTemp = "SELECT temperature FROM readings ORDER BY readings_time DESC LIMIT 1";
$requestHum = "SELECT humidity FROM readings ORDER BY readings_time DESC LIMIT 1";
$requestPress = "SELECT pression FROM readings ORDER BY readings_time DESC LIMIT 1";
$result = $conn->query($request);
$resultTemp = $conn->query($requestTemp);
$resultHum = $conn->query($requestHum);
$resultPress = $conn->query($requestPress);

$params = "SELECT * FROM parameters ORDER BY created_at DESC LIMIT 1";
$paramResult = $conn->query($params);
while($rowParams = $paramResult->fetch_assoc()) {
    $paramsResultat[] = $rowParams;
}

$requestReading = "SELECT temperature, humidity, readings_time, pression 
                   FROM readings 
                   WHERE (temperature BETWEEN " . (int) $paramsResultat[0]['temp_min'] . " AND " . (int) $paramsResultat[0]['temp_max'] . ")
                     OR (humidity BETWEEN " . (int) $paramsResultat[0]['humidity_min'] . " AND " . (int) $paramsResultat[0]['humidity_max'] . ")
                     OR (pression BETWEEN " . (int) $paramsResultat[0]['pressure_min'] . " AND " . (int) $paramsResultat[0]['pressure_max'] . ")
                   ORDER BY readings_time DESC 
                   LIMIT 10";

$resultReadings = $conn->query($requestReading);
while ($row = $resultReadings->fetch_assoc()) {
    $data[] = $row;
}

$dataTemp = $resultTemp->fetch_assoc();
$temperature = $dataTemp['temperature'];


$dataHum = $resultHum->fetch_assoc();
$humidity = $dataHum['humidity'];

$dataPress = $resultPress->fetch_assoc();
$pression = $dataPress['pression'];
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Station météo - Données Principal</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <li class="p-3 rounded-lg bg-gray-700 transition-all duration-300">
                        <a href="index.php" class="flex items-center gap-2">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="p-3 rounded-lg hover:bg-gray-700 transition-all duration-300">
                        <a href="params.php" class="flex items-center gap-2">
                            <i class="fas fa-cog"></i> Paramètres
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

    
        <main class="flex-1 ml-64 p-8">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-bold text-gray-800">📊 Données Principal</h1>
                    <div class="text-gray-600"><?php echo date('d/m/Y H:i'); ?></div>
                </div>

         
<div class="grid grid-cols-3 gap-6 p-6 mb-[1px]">
    
    <div class="bg-red-50 rounded-2xl p-8 text-center transition transform hover:scale-105 shadow-2xl h-[200px] flex flex-col justify-center">
        <div class="text-5xl mb-4">🌡️</div>
        <h3 class="font-semibold text-gray-600 mb-3 text-xl">Température</h3>
        <p id="temperatureValue" class="text-3xl font-bold text-red-500"><?php echo $temperature?>°C</p>
    </div>
    
    
    <div class="bg-blue-50 rounded-2xl p-8 text-center transition transform hover:scale-105 shadow-2xl h-[200px] flex flex-col justify-center">
        <div class="text-5xl mb-4">💧</div>
        <h3 class="font-semibold text-gray-600 mb-3 text-xl">Humidité</h3>
        <p id="humidityValue" class="text-3xl font-bold text-blue-500"><?php echo $humidity ?>%</p>
    </div>
    
    
    <div class="bg-green-50 rounded-2xl p-8 text-center transition transform hover:scale-105 shadow-2xl h-[200px] flex flex-col justify-center">
        <div class="text-5xl mb-4">🌬️</div>
        <h3 class="font-semibold text-gray-600 mb-3 text-xl">Pression</h3>
        <p id="pressureValue" class="text-3xl font-bold text-green-500"><?php echo $pression ?> hPa</p>
    </div>
</div>


<div class="grid grid-cols-3 gap-6 p-6">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="p-4 bg-gray-50 border-b">
                <h3 class="text-2xl font-bold text-gray-800 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 12a1 1 0 11-2 0v-5a1 1 0 112 0v5zm-1-8a1 1 0 100 2 1 1 0 000-2z"/>
                    </svg>
                    Température
                </h3>
            </div>
            <div class="chart-container p-4">
                <canvas id="tempChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="p-4 bg-gray-50 border-b">
                <h3 class="text-2xl font-bold text-gray-800 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                    </svg>
                    Humidité
                </h3>
            </div>
            <div class="chart-container p-4">
                <canvas id="humChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="p-4 bg-gray-50 border-b">
                <h3 class="text-2xl font-bold text-gray-800 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12 13V8c0-1.105-.895-2-2-2s-2 .895-2 2v5a2 2 0 11-4 0V8a6 6 0 1112 0v5a2 2 0 11-4 0z" clip-rule="evenodd"/>
                    </svg>
                    Pression
                </h3>
            </div>
            <div class="chart-container p-4">
                <canvas id="pressChart"></canvas>
            </div>
        </div>
    </div>
<script>
    data = window.chartData = <?php echo json_encode($data); ?>;
</script>
<script src="graph.js"></script>
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