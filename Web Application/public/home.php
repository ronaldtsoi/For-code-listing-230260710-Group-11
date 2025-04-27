<?php
include('../functions/auth_check.php');
include('../includes/header.php');
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <?php
                if (isset($_SESSION['status'])) {
                    echo "<h5 class='alert alert-success'>" . $_SESSION['status'] . "</h5>";
                    unset($_SESSION['status']);
                }
            ?>
            <h2 class="mb-4">Home Page</h2>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="row">
        <!-- Menu Section -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5>Menu</h5>
                </div>

                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item border-secondary dropdown">
                            <a href="https://james.sl94.i.ng/public/home.php" class="text-decoration-none" id="menuHome" data-bs-toggle="dropdown" aria-expanded="false">
                                Home
                            </a>
                        </li>
                        <li class="list-group-item border-secondary dropdown">
                            <a href="https://james.sl94.i.ng/public/alertrecord.php" class="text-decoration-none" id="menuAlertRecord" data-bs-toggle="dropdown" aria-expanded="false">
                                Alert Record
                            </a>
                        </li>
                        <li class="list-group-item border-secondary dropdown">
                            <a href="https://james.sl94.i.ng/public/checkinrecord.php" class="text-decoration-none" id="menuCheckInRecords" data-bs-toggle="dropdown" aria-expanded="false">
                                Check-In Records
                            </a>
                        </li>
                        <li class="list-group-item border-secondary dropdown">
                            <a href="https://james.sl94.i.ng/public/worksite-list.php" class="text-decoration-none dropdown-toggle" id="menuWorksiteList" data-bs-toggle="dropdown" aria-expanded="false">
                                Worksite List
                            </a>
                            <ul class="dropdown-menu border-secondary" aria-labelledby="menuWorksiteList">
                                <li><a href="https://james.sl94.i.ng/public/create-worksite.php" class="dropdown-item">Add Worksite</a></li>
                            </ul>
                        </li>
                        <li class="list-group-item border-secondary dropdown">
                            <a href="https://james.sl94.i.ng/public/map-list.php" class="text-decoration-none dropdown-toggle" id="menuMap" data-bs-toggle="dropdown" aria-expanded="false">
                                Escape Route Map
                            </a>
                            <ul class="dropdown-menu border-secondary" aria-labelledby="menuMap">
                                <li><a href="https://james.sl94.i.ng/public/add-map.php" class="dropdown-item">Add Map</a></li>
                            </ul>
                        </li>
                        <li class="list-group-item border-secondary dropdown">
                            <a href="https://james.sl94.i.ng/public/form-list.php" class="text-decoration-none dropdown-toggle" id="menuForm" data-bs-toggle="dropdown" aria-expanded="false">
                                Safety Form
                            </a>
                            <ul class="dropdown-menu border-secondary" aria-labelledby="menuForm">
                                <li><a href="https://james.sl94.i.ng/public/add-form.php" class="dropdown-item">Add Form</a></li>
                            </ul>
                        </li>
                        <li class="list-group-item border-secondary dropdown">
                            <a href="https://james.sl94.i.ng/public/user-list.php" class="text-decoration-none" id="menuUserList" data-bs-toggle="dropdown" aria-expanded="false">
                                User List
                            </a>
                        </li>
                        <li class="list-group-item border-secondary">
                            <a href="../actions/logout_process.php" class="text-decoration-none" id="menuLogout" data-bs-toggle="dropdown" aria-expanded="false">
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Your Statistics Section -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5>Your Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Chart Section -->
                        <div class="col-md-12">
                            <canvas id="statisticsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Fetch statistics dynamically from the backend
    fetch('../actions/fetch_statistics_process.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Data for the chart
                const chartData = {
                    labels: ['Worksites', 'Login Users', 'Alerts'],
                    datasets: [{
                        label: 'Statistics',
                        data: [
                            data.data.worksiteCount,
                            data.data.loginUsersCount,
                            data.data.alertCount
                        ],
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.2)', 
                            'rgba(54, 162, 235, 0.2)', 
                            'rgba(255, 99, 132, 0.2)'
                        ],
                        borderColor: [
                            'rgba(75, 192, 192, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 99, 132, 1)'
                        ],
                        borderWidth: 1
                    }]
                };

                // Configuration options
                const config = {
                    type: 'bar',
                    data: chartData,
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            },
                            tooltip: {
                                enabled: true
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                };

                // Render the chart
                const ctx = document.getElementById('statisticsChart').getContext('2d');
                new Chart(ctx, config);
            } else {
                console.error('Failed to fetch statistics:', data.message);
            }
        })
        .catch(error => console.error('Error fetching statistics:', error));
</script>

<?php
include('../includes/footer.php'); 
?>