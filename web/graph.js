console.log('Data : ', data)
const labels = data.map(e => {
    const date = new Date(e.readings_time);
    return date.toLocaleTimeString('fr-FR', { 
        hour: '2-digit', 
        minute: '2-digit'
    });
});

// Config Graphique
const chartConfigs = [
    {
        id: 'tempChart',
        label: 'Température (°C)',
        color: 'rgb(255, 99, 132)',
        data: e => e.temperature
    },
    {
        id: 'humChart',
        label: 'Humidité (%)',
        color: 'rgb(54, 162, 235)',
        data: e => e.humidity
    },
    {
        id: 'pressChart',
        label: 'Pression (hPa)',
        color: 'rgb(75, 192, 192)',
        data: e => e.pression
    }
];

const commonOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: {
            beginAtZero: false
        }
    },
    plugins: {
        legend: {
            display: false
        }
    }
};

// Création de graphique avec erreur serveur
chartConfigs.forEach(config => {
    try {
        const ctx = document.getElementById(config.id);
        if (!ctx) {
            console.error(`Canvas element ${config.id} not found`);
            return;
        }

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: config.label,
                    data: data.map(config.data),
                    borderColor: config.color,
                    backgroundColor: config.color.replace('rgb', 'rgba').replace(')', ', 0.1)'),
                    fill: true
                }]
            },
            options: commonOptions
        });
    } catch (error) {
        console.error(`Error creating chart ${config.id}:`, error);
    }
});