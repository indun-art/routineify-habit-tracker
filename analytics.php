<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Routinefy - Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style2.css">
</head>
<body class="dashboard-body d-flex flex-column min-vh-100">

    <header class="dashboard-header bg-white shadow-sm py-3 px-4 d-flex justify-content-between align-items-center">
        <h1 class="logo m-0 fw-bold fs-3">Routinefy</h1>
        <nav class="nav-links d-flex gap-3">
            <a href="dashboard.php" class="nav-link pt-1 text-dark">Dashboard</a>
            <a href="#" class="btn btn-dark btn-sm rounded-pill px-3 active">Analytics</a>
            <a href="#" class="nav-link pt-1 text-dark" onclick="window.api.logout(); return false;">Log out</a>
        </nav>
    </header>

    <main class="dashboard-container container my-5 flex-grow-1">
        
        <div class="welcome-row mb-4">
            <h2 class="fs-4 m-0" id="welcomeMsg">Welcome User 👋</h2>
        </div>

        <div class="row g-4 mb-5 text-center">
            <div class="col-md-4">
                <div class="p-4 rounded fw-semibold text-dark fs-5" style="background-color: #cdd1d3;" id="statTotal">Total habits active: 0</div>
            </div>
            <div class="col-md-4">
                <div class="p-4 rounded fw-semibold text-dark fs-5" style="background-color: #cdd1d3;" id="statStreak">Highest streak: 0</div>
            </div>
            <div class="col-md-4">
                <div class="p-4 rounded fw-semibold text-dark fs-5" style="background-color: #cdd1d3;" id="statRate">Completion Rate: 0%</div>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-md-5">
                <div class="p-4 rounded text-center d-flex flex-column align-items-center shadow-sm" style="background-color: #cdd1d3;">
                    <h3 class="fs-5 fw-bold mb-4 text-dark">Weekly Progress Chart</h3>
                    <canvas id="weeklyChart" style="max-width: 250px; max-height: 250px;"></canvas>
                </div>
            </div>
            <div class="col-md-5">
                <div class="p-4 rounded text-center d-flex flex-column align-items-center shadow-sm" style="background-color: #cdd1d3;">
                    <h3 class="fs-5 fw-bold mb-4 text-dark">Monthly Progress Chart</h3>
                    <canvas id="monthlyChart" style="max-width: 300px; max-height: 250px;"></canvas>
                </div>
            </div>
        </div>

    </main>

    <footer class="bg-white text-center py-3 border-top mt-auto shadow-sm">
        <div class="container">
            <p class="m-0 text-muted small">&copy; 2026 Routinefy. Built for ICT 1209 Mini Project - Rajarata University of Sri Lanka.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/api.js"></script>
    <script>
        async function loadAnalytics() {
            const user = await window.api.requireUserPage();
            if (!user) return;
            document.getElementById('welcomeMsg').innerText = `Welcome ${user.full_name} 👋`;
            const res = await window.api.fetchWithAuth('/analytics');
            if (!res.ok) return;
            const data = await res.json();

            document.getElementById('statTotal').innerText = `Total habits active: ${data.total_habits}`;
            document.getElementById('statStreak').innerText = `Highest streak: ${data.highest_streak}`;
            document.getElementById('statRate').innerText = `Completion Rate: ${data.completion_rate}%`;

            const weekLabels = data.weekly.map(x => x.day);
            const weekValues = data.weekly.map(x => Number(x.completed));
            new Chart(document.getElementById('weeklyChart'), {
                type: 'bar',
                data: { labels: weekLabels.length ? weekLabels : ['No data'], datasets: [{ label: 'Completed', data: weekValues.length ? weekValues : [0] }] },
                options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
            });

            const monthLabels = data.monthly.map(x => `Week ${x.week_no}`);
            const monthValues = data.monthly.map(x => Number(x.completed));
            new Chart(document.getElementById('monthlyChart'), {
                type: 'bar',
                data: { labels: monthLabels.length ? monthLabels : ['No data'], datasets: [{ label: 'Habits Completed', data: monthValues.length ? monthValues : [0] }] },
                options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
            });
        }
        loadAnalytics();
    </script>
</body>
</html>
