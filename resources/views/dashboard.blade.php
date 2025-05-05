<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IoT Environmental Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.3.2/dist/echarts.min.js"></script>
    <style>
        @media (max-width: 640px) {
            .chart-container {
                height: 250px !important;
            }

            .summary-card {
                min-height: 120px;
            }
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .alert-indicator {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }

        /* Light sensor specific styles */
        .light-intensity {
            height: 20px;
            border-radius: 10px;
            background: linear-gradient(to right, #000000, #555555, #aaaaaa, #ffffff);
            margin-top: 8px;
        }

        .light-indicator {
            width: 10px;
            height: 30px;
            background-color: #f59e0b;
            position: absolute;
            top: -5px;
            transform: translateX(-50%);
            transition: left 0.5s ease;
        }
    </style>
</head>

<body class="bg-gray-100 font-sans">
    <div class="container mx-auto px-2 sm:px-4 py-4 sm:py-8">
        <!-- Header -->
        <header class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-center mb-2">Environmental Dashboard</h1>
            <div class="flex justify-center">
                <div class="bg-white rounded-full px-4 py-1 shadow-sm text-sm flex items-center">
                    <span class="relative flex h-3 w-3 mr-2">
                        <span id="connection-ping" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span id="connection-dot" class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                    <span id="connection-status">Connected</span>
                </div>
            </div>
        </header>

        <!-- Time Filters - Now Stacked on Mobile -->
        <div class="flex flex-wrap justify-center mb-4 sm:mb-8 gap-2">
            <button onclick="filterData('today')" class="px-3 py-1 sm:px-4 sm:py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm sm:text-base">Today</button>
            <button onclick="filterData('week')" class="px-3 py-1 sm:px-4 sm:py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm sm:text-base">This Week</button>
            <button onclick="filterData('month')" class="px-3 py-1 sm:px-4 sm:py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm sm:text-base">This Month</button>
            <button onclick="filterData('all')" class="px-3 py-1 sm:px-4 sm:py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm sm:text-base">All Data</button>
        </div>

        <!-- Summary Cards - Stack on Mobile, Row on Desktop -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-6">
            <!-- Temperature Card -->
            <div class="summary-card bg-white p-4 rounded-lg shadow-md flex flex-col">
                <div class="flex justify-between items-start mb-2">
                    <h2 class="text-lg sm:text-xl font-semibold">Temperature</h2>
                </div>
                <div class="flex items-center justify-between mt-auto">
                    <div class="flex items-center">
                        <span id="current-temp" class="text-2xl sm:text-3xl font-bold mr-2">--</span>
                        <span id="temp-trend" class="text-xs sm:text-sm"></span>
                    </div>
                    <span class="text-gray-500 text-sm">°C</span>
                </div>
            </div>

            <!-- Humidity Card -->
            <div class="summary-card bg-white p-4 rounded-lg shadow-md flex flex-col">
                <div class="flex justify-between items-start mb-2">
                    <h2 class="text-lg sm:text-xl font-semibold">Humidity</h2>
                </div>
                <div class="flex items-center justify-between mt-auto">
                    <div class="flex items-center">
                        <span id="current-humidity" class="text-2xl sm:text-3xl font-bold mr-2">--</span>
                        <span id="humidity-trend" class="text-xs sm:text-sm"></span>
                    </div>
                    <span class="text-gray-500 text-sm">%</span>
                </div>
            </div>

            <!-- Air Quality Card -->
            <div class="summary-card bg-white p-4 rounded-lg shadow-md flex flex-col">
                <div class="flex justify-between items-start mb-2">
                    <h2 class="text-lg sm:text-xl font-semibold">Air Quality</h2>
                </div>
                <div class="flex items-center justify-between mt-auto">
                    <div class="flex items-center">
                        <span id="current-air" class="text-2xl sm:text-3xl font-bold mr-2">--</span>
                        <span id="air-trend" class="text-xs sm:text-sm"></span>
                    </div>
                    <span class="text-gray-500 text-sm">AQI</span>
                </div>
            </div>

            <!-- Light Sensor Card -->
            <div class="summary-card bg-white p-4 rounded-lg shadow-md flex flex-col">
                <div class="flex justify-between items-start mb-2">
                    <h2 class="text-lg sm:text-xl font-semibold">Light Intensity</h2>
                </div>
                <div class="flex items-center justify-between mt-auto">
                    <div class="flex items-center">
                        <span id="current-light" class="text-2xl sm:text-3xl font-bold mr-2">--</span>
                        <span id="light-trend" class="text-xs sm:text-sm"></span>
                    </div>
                    <span class="text-gray-500 text-sm">lux</span>
                </div>
                <div class="relative mt-2">
                    <div class="light-intensity">
                        <div id="light-indicator" class="light-indicator"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>0</span>
                        <span>500</span>
                        <span>1000</span>
                        <span>1500</span>
                        <span>2000+</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts - Single Column on Mobile -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-6 mb-6">
            <!-- Temperature Chart -->
            <div class="bg-white p-3 sm:p-6 rounded-lg shadow-md">
                <h2 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Temperature Over Time</h2>
                <div class="chart-container">
                    <canvas id="tempChart"></canvas>
                </div>
            </div>

            <!-- Humidity Chart -->
            <div class="bg-white p-3 sm:p-6 rounded-lg shadow-md">
                <h2 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Humidity Over Time</h2>
                <div class="chart-container">
                    <canvas id="humidityChart"></canvas>
                </div>
            </div>

            <!-- Air Quality Chart -->
            <div class="bg-white p-3 sm:p-6 rounded-lg shadow-md">
                <h2 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Air Quality Over Time</h2>
                <div class="chart-container">
                    <canvas id="airChart"></canvas>
                </div>
            </div>

            <!-- Light Sensor Chart -->
            <div class="bg-white p-3 sm:p-6 rounded-lg shadow-md">
                <h2 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Light Intensity Over Time</h2>
                <div class="chart-container">
                    <canvas id="lightChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Alerts Section -->
        <div class="bg-white p-3 sm:p-6 rounded-lg shadow-md mb-6">
            <div class="flex justify-between items-center mb-3 sm:mb-4">
                <h2 class="text-lg sm:text-xl font-semibold">Alerts</h2>
                <span id="alert-count" class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">0</span>
            </div>
            <div id="alerts-container" class="space-y-2">
                <div class="text-green-500 text-sm">No alerts - all readings are normal</div>
            </div>
        </div>

        <!-- Last Updated -->
        <div class="text-center text-xs text-gray-500">
            Last updated: <span id="last-updated">--</span>
        </div>
    </div>

    <script>
        // Global variables for charts
        let tempChart, humidityChart, airChart, lightChart, combinedChart;
        let currentData = {};
        let previousData = {};
        let connectionStatus = true;
        let lastUpdateTime = null;

        // Initialize the dashboard
        document.addEventListener('DOMContentLoaded', function() {
            initializeData();
            setInterval(updateDashboard, 5000); // Update every 5 seconds

            // Simulate connection changes for demo purposes
            setInterval(() => {
                if (Math.random() < 0.1) { // 10% chance to toggle connection status
                    setConnectionStatus(!connectionStatus);
                }
            }, 10000);
        });

        function initializeData() {
            // Generate initial data
            const timeFrame = 'today';
            const data = generateMockData(timeFrame);
            currentData = generateLatestData();

            updateSummary(data);
            updateCharts(data);
            updateCurrentReadings(currentData);
            checkForAlerts(data);

            lastUpdateTime = new Date();
            document.getElementById('last-updated').textContent = lastUpdateTime.toLocaleTimeString();
            setConnectionStatus(true);
        }

        function updateDashboard() {
            if (!connectionStatus) return;

            const timeFrame = 'today';
            const data = generateMockData(timeFrame);
            currentData = generateLatestData();

            updateSummary(data);
            updateCharts(data);
            updateCurrentReadings(currentData);
            checkForAlerts(data);

            lastUpdateTime = new Date();
            document.getElementById('last-updated').textContent = lastUpdateTime.toLocaleTimeString();
        }

        // Set connection status
        function setConnectionStatus(isConnected) {
            connectionStatus = isConnected;
            const statusElement = document.getElementById('connection-status');
            const pingElement = document.getElementById('connection-ping');
            const dotElement = document.getElementById('connection-dot');

            if (isConnected) {
                statusElement.textContent = 'Connected';
                pingElement.classList.remove('bg-red-400');
                pingElement.classList.add('bg-green-400');
                dotElement.classList.remove('bg-red-500');
                dotElement.classList.add('bg-green-500');
                pingElement.classList.add('animate-ping');
            } else {
                statusElement.textContent = 'Disconnected';
                pingElement.classList.remove('bg-green-400');
                pingElement.classList.add('bg-red-400');
                dotElement.classList.remove('bg-green-500');
                dotElement.classList.add('bg-red-500');
                pingElement.classList.remove('animate-ping');
            }
        }

        // =============== MOCK DATA GENERATION START ===============
        // These functions generate mock data for demonstration purposes
        // Replace with actual API calls when backend is available

        function generateMockData(timeFrame) {
            const data = [];
            const now = new Date();
            let hours = 24;

            if (timeFrame === 'week') hours = 24 * 7;
            if (timeFrame === 'month') hours = 24 * 30;
            if (timeFrame === 'all') hours = 24 * 90;

            for (let i = 0; i < hours; i++) {
                const time = new Date(now.getTime() - i * 3600000);
                const lightIntensity = calculateLightIntensity(time);

                data.push({
                    recorded_at: time.toISOString(),
                    temperature: 20 + 10 * Math.sin(i / 6) + (Math.random() - 0.5) * 2,
                    humidity: 50 + 30 * Math.sin(i / 8) + (Math.random() - 0.5) * 5,
                    air_quality: 30 + 70 * Math.sin(i / 10) + (Math.random() - 0.5) * 10,
                    light_intensity: lightIntensity
                });
            }
            return data;
        }

        function generateLatestData() {
            const now = new Date();
            const lightIntensity = calculateLightIntensity(now);

            return {
                recorded_at: now.toISOString(),
                temperature: 20 + 10 * Math.sin(Date.now() / 21600000) + (Math.random() - 0.5) * 2,
                humidity: 50 + 30 * Math.sin(Date.now() / 28800000) + (Math.random() - 0.5) * 5,
                air_quality: 30 + 70 * Math.sin(Date.now() / 36000000) + (Math.random() - 0.5) * 10,
                light_intensity: lightIntensity
            };
        }

        function calculateLightIntensity(time) {
            // Simulate daylight cycle - higher values during daytime
            const hours = time.getHours();
            const minutes = time.getMinutes();
            const timeOfDay = hours + minutes / 60;

            // Base light intensity based on time of day (0-23.99)
            let intensity;
            if (timeOfDay >= 5 && timeOfDay <= 19) { // Daytime
                // Peak at noon (12:00)
                const distanceFromNoon = Math.abs(12 - timeOfDay);
                intensity = 2000 * Math.pow(Math.cos(distanceFromNoon * Math.PI / 14), 2);
            } else { // Nighttime
                // Minimum light with occasional artificial light
                intensity = 10 + Math.random() * 50;

                // 20% chance of artificial light being on
                if (Math.random() < 0.2) {
                    intensity += 100 + Math.random() * 400;
                }
            }

            // Add some randomness
            intensity += (Math.random() - 0.5) * 200;

            // Ensure within reasonable bounds
            return Math.max(0, Math.min(2500, intensity));
        }
        // =============== MOCK DATA GENERATION END ===============

        // Filter data by time frame
        function filterData(timeFrame) {
            const data = generateMockData(timeFrame);
            updateSummary(data);
            updateCharts(data);
        }

        // Update summary cards
        function updateSummary(data) {
            if (data.length === 0) return;

            // Calculate averages
            const avgTemp = data.reduce((sum, item) => sum + item.temperature, 0) / data.length;
            const avgHumidity = data.reduce((sum, item) => sum + item.humidity, 0) / data.length;
            const avgAir = data.reduce((sum, item) => sum + item.air_quality, 0) / data.length;
            const avgLight = data.reduce((sum, item) => sum + item.light_intensity, 0) / data.length;

            // Calculate trends (simple comparison of first and last)
            const tempTrend = data.length > 1 ?
                (data[0].temperature - data[data.length - 1].temperature).toFixed(2) : 0;
            const humidityTrend = data.length > 1 ?
                (data[0].humidity - data[data.length - 1].humidity).toFixed(2) : 0;
            const airTrend = data.length > 1 ?
                (data[0].air_quality - data[data.length - 1].air_quality).toFixed(2) : 0;
            const lightTrend = data.length > 1 ?
                (data[0].light_intensity - data[data.length - 1].light_intensity).toFixed(2) : 0;

            // Update trend indicators
            document.getElementById('temp-trend').textContent = `${tempTrend > 0 ? '+' : ''}${tempTrend}°C`;
            document.getElementById('temp-trend').className = `text-sm ${tempTrend > 0 ? 'text-red-500' : 'text-green-500'}`;

            document.getElementById('humidity-trend').textContent = `${humidityTrend > 0 ? '+' : ''}${humidityTrend}%`;
            document.getElementById('humidity-trend').className = `text-sm ${humidityTrend > 0 ? 'text-red-500' : 'text-green-500'}`;

            document.getElementById('air-trend').textContent = `${airTrend > 0 ? '+' : ''}${airTrend}AQI`;
            document.getElementById('air-trend').className = `text-sm ${airTrend > 0 ? 'text-red-500' : 'text-green-500'}`;

            document.getElementById('light-trend').textContent = `${lightTrend > 0 ? '+' : ''}${lightTrend}lux`;
            document.getElementById('light-trend').className = `text-sm ${lightTrend > 0 ? 'text-red-500' : 'text-green-500'}`;
        }

        // Update current readings
        function updateCurrentReadings(data) {
            if (!data) return;

            document.getElementById('current-temp').textContent = data.temperature.toFixed(1);
            document.getElementById('current-humidity').textContent = data.humidity.toFixed(1);
            document.getElementById('current-air').textContent = data.air_quality.toFixed(1);
            document.getElementById('current-light').textContent = data.light_intensity.toFixed(0);

            // Update light indicator position
            const lightValue = Math.min(2000, data.light_intensity);
            const percentage = (lightValue / 2000) * 100;
            const indicator = document.getElementById('light-indicator');
            indicator.style.left = `${percentage}%`;

            // Change indicator color based on intensity
            if (data.light_intensity < 100) {
                indicator.style.backgroundColor = '#4b5563'; // Dark
            } else if (data.light_intensity < 500) {
                indicator.style.backgroundColor = '#f59e0b'; // Amber
            } else {
                indicator.style.backgroundColor = '#fcd34d'; // Light yellow
            }
        }

        // Update charts
        function updateCharts(data) {
            if (data.length === 0) return;

            const labels = data.map(item => new Date(item.recorded_at).toLocaleTimeString()).reverse();
            const tempData = data.map(item => item.temperature).reverse();
            const humidityData = data.map(item => item.humidity).reverse();
            const airData = data.map(item => item.air_quality).reverse();
            const lightData = data.map(item => item.light_intensity).reverse();

            // Temperature Chart
            if (!tempChart) {
                const ctx = document.getElementById('tempChart').getContext('2d');
                tempChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Temperature (°C)',
                            data: tempData,
                            borderColor: 'rgb(255, 99, 132)',
                            tension: 0.1,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: false
                            }
                        }
                    }
                });
            } else {
                tempChart.data.labels = labels;
                tempChart.data.datasets[0].data = tempData;
                tempChart.update();
            }

            // Humidity Chart
            if (!humidityChart) {
                const ctx = document.getElementById('humidityChart').getContext('2d');
                humidityChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Humidity (%)',
                            data: humidityData,
                            borderColor: 'rgb(54, 162, 235)',
                            tension: 0.1,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100
                            }
                        }
                    }
                });
            } else {
                humidityChart.data.labels = labels;
                humidityChart.data.datasets[0].data = humidityData;
                humidityChart.update();
            }

            // Air Quality Chart
            if (!airChart) {
                const ctx = document.getElementById('airChart').getContext('2d');
                airChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Air Quality (AQI)',
                            data: airData,
                            borderColor: 'rgb(75, 192, 192)',
                            tension: 0.1,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            } else {
                airChart.data.labels = labels;
                airChart.data.datasets[0].data = airData;
                airChart.update();
            }

            // Light Sensor Chart
            if (!lightChart) {
                const ctx = document.getElementById('lightChart').getContext('2d');
                lightChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Light Intensity (lux)',
                            data: lightData,
                            borderColor: 'rgb(234, 179, 8)',
                            tension: 0.1,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            } else {
                lightChart.data.labels = labels;
                lightChart.data.datasets[0].data = lightData;
                lightChart.update();
            }
        }

        // Check for abnormal readings and create alerts
        function checkForAlerts(data) {
            if (data.length === 0) return;

            const alertsContainer = document.getElementById('alerts-container');
            alertsContainer.innerHTML = '';

            let alertCount = 0;

            // Check temperature
            const latestTemp = data[0].temperature;
            if (latestTemp > 30) {
                createAlert('High Temperature', `Temperature is high: ${latestTemp}°C`, 'red');
                alertCount++;
            } else if (latestTemp < 10) {
                createAlert('Low Temperature', `Temperature is low: ${latestTemp}°C`, 'blue');
                alertCount++;
            }

            // Check humidity
            const latestHumidity = data[0].humidity;
            if (latestHumidity > 80) {
                createAlert('High Humidity', `Humidity is high: ${latestHumidity}%`, 'red');
                alertCount++;
            } else if (latestHumidity < 30) {
                createAlert('Low Humidity', `Humidity is low: ${latestHumidity}%`, 'blue');
                alertCount++;
            }

            // Check air quality
            const latestAir = data[0].air_quality;
            if (latestAir > 100) {
                createAlert('Poor Air Quality', `Air quality is poor: ${latestAir} AQI`, 'red');
                alertCount++;
            } else if (latestAir > 50) {
                createAlert('Moderate Air Quality', `Air quality is moderate: ${latestAir} AQI`, 'orange');
                alertCount++;
            }

            // Check light intensity
            const latestLight = data[0].light_intensity;
            if (latestLight > 1500) {
                createAlert('High Light Intensity', `Light intensity is very high: ${latestLight.toFixed(0)} lux`, 'red');
                alertCount++;
            } else if (latestLight < 20) {
                createAlert('Low Light Intensity', `Light intensity is very low: ${latestLight.toFixed(0)} lux`, 'blue');
                alertCount++;
            }

            // Update alert count
            document.getElementById('alert-count').textContent = alertCount;

            // If no alerts
            if (alertsContainer.children.length === 0) {
                const noAlerts = document.createElement('div');
                noAlerts.className = 'text-green-500';
                noAlerts.textContent = 'No alerts - all readings are normal';
                alertsContainer.appendChild(noAlerts);
            }
        }

        // Create an alert element
        function createAlert(title, message, color) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `p-4 mb-2 rounded border-l-4 border-${color}-500 bg-${color}-50`;

            const alertTitle = document.createElement('h3');
            alertTitle.className = `font-bold text-${color}-800`;
            alertTitle.textContent = title;

            const alertMessage = document.createElement('p');
            alertMessage.className = `text-${color}-700`;
            alertMessage.textContent = message;

            alertDiv.appendChild(alertTitle);
            alertDiv.appendChild(alertMessage);

            document.getElementById('alerts-container').appendChild(alertDiv);
        }
    </script>
</body>

</html>