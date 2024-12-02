<?php
// monitor.php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>温湿度データモニター</title>
    <meta charset="utf-8">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <style>
        body {
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .data-display {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>温湿度データモニター</h2>

        <div class="data-display">
            <label>温度 (°C):</label>
            <input type="number" id="temperature" readonly>
            <label>湿度 (%):</label>
            <input type="number" id="humidity" readonly>
        </div>

        <div class="chart-container">
            <canvas id="myChart"></canvas>
        </div>
    </div>

    <script>
        // グラフの初期化
        const ctx = document.getElementById('myChart').getContext('2d');
        const maxDataPoints = 30;
        const initialData = {
            labels: [],
            datasets: [
                {
                    label: '温度 (°C)',
                    borderColor: 'rgb(75, 192, 192)',
                    data: [],
                    yAxisID: 'y'
                },
                {
                    label: '湿度 (%)',
                    borderColor: 'rgb(255, 99, 132)',
                    data: [],
                    yAxisID: 'y1'
                }
            ]
        };

        const myChart = new Chart(ctx, {
            type: 'line',
            data: initialData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        min: 20,
                        max: 26,
                        title: {
                            display: true,
                            text: '温度 (°C)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        min: 35,
                        max: 55,
                        title: {
                            display: true,
                            text: '湿度 (%)'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });

        // データ更新関数
        function updateChart(temperature, humidity) {
            const labels = myChart.data.labels;
            const tempData = myChart.data.datasets[0].data;
            const humData = myChart.data.datasets[1].data;

            // ラベルの更新
            labels.push(new Date().toLocaleTimeString());
            if (labels.length > maxDataPoints) {
                labels.shift();
            }

            // データの更新
            tempData.push(temperature);
            humData.push(humidity);
            if (tempData.length > maxDataPoints) {
                tempData.shift();
                humData.shift();
            }

            myChart.update();

            // 現在値の表示更新
            document.getElementById('temperature').value = temperature;
            document.getElementById('humidity').value = humidity;
        }

        // Server-Sent Eventsのセットアップ
        const eventSource = new EventSource('data_receiver.php');
        eventSource.onmessage = function(event) {
            const data = JSON.parse(event.data);
            if (data.payload) {
                updateChart(data.payload.temperature, data.payload.humidity);
            }
        };
    </script>
</body>
</html>
