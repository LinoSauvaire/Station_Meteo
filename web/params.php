<!DOCTYPE html>
<html>
<head>
    <title>Station météo - Paramètres</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans text-gray-800">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 fixed inset-y-0 bg-gray-800 text-white">
            <div class="p-6">
                <div class="text-2xl font-bold mb-8 flex items-center gap-2">
                    📊 Station Météo
                </div>
                <ul class="space-y-2">
                    <li class="p-3 rounded-lg hover:bg-gray-700 transition-all duration-300 cursor-pointer">
                        <a href="index.php">Dashboard</a>
                    </li>
                    <li class="p-3 rounded-lg bg-gray-700 transition-all duration-300 cursor-pointer">
                        <a href="params.php">Paramètres</a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-64 p-8">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-3xl font-bold text-gray-800 mb-8">⚙️ Paramètres</h1>

                <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                    <h2 class="text-xl font-semibold mb-6">Intervalles de mesure</h2>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Fréquence de mise à jour
                            </label>
                            <select class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option>30 secondes</option>
                                <option>1 minute</option>
                                <option>5 minutes</option>
                                <option>15 minutes</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Période d'historique
                            </label>
                            <select class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option>1 heure</option>
                                <option>6 heures</option>
                                <option>24 heures</option>
                                <option>7 jours</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                    <h2 class="text-xl font-semibold mb-6">Seuils d'alerte</h2>
                    <div class="space-y-6">
                        <!-- Température -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-4">Température</label>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Minimum</label>
                                    <input type="number" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" placeholder="0°C">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Maximum</label>
                                    <input type="number" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" placeholder="30°C">
                                </div>
                            </div>
                        </div>

                        <!-- Humidité -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-4">Humidité</label>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Minimum</label>
                                    <input type="number" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="30%">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Maximum</label>
                                    <input type="number" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="70%">
                                </div>
                            </div>
                        </div>

                        <!-- Pression -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-4">Pression</label>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Minimum</label>
                                    <input type="number" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" placeholder="980 hPa">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Maximum</label>
                                    <input type="number" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" placeholder="1020 hPa">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <button class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Annuler
                    </button>
                    <button class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Sauvegarder
                    </button>
                </div>
            </div>
        </main>
    </div>
</body>
</html>