<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../functions/auth_check.php');
include('../includes/header.php');
?>

<div class="container mt-4">
    <h1 class="text-center mb-4">Check-In Records</h1>

    <?php
    if (isset($_SESSION['status'])) {
        echo "<h5 class='alert alert-success'>" . $_SESSION['status'] . "</h5>";
        unset($_SESSION['status']);
    }
    ?>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Filter Records</h5>
            <form id="filterForm" class="row g-3">
                <div class="col-md-6">
                    <label for="date" class="form-label">Select Date:</label>
                    <input type="date" id="date" name="date" class="form-control" value="<?= date('Y-m-d'); ?>">
                </div>
                <div class="col-md-6">
                    <label for="worksite" class="form-label">Select Worksite:</label>
                    <select id="worksite" name="worksite" class="form-select">
                        <option value="">All Worksites</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="button" id="filterBtn" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Records</h5>
                    <div id="recordsTable" class="table-responsive">
                        <p class="text-center text-muted">Loading records...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        fetchWorksites();
        fetchRecords();
    });

    document.getElementById('filterBtn').addEventListener('click', function () {
        fetchRecords();
    });

    function fetchWorksites() {
        fetch('../actions/fetch_checkin_record_process.php?action=getWorksites')
            .then(response => response.json())
            .then(data => {
                const worksiteSelect = document.getElementById('worksite');
                if (data.success) {
                    data.worksites.forEach(site => {
                        const option = document.createElement('option');
                        option.value = site.location;
                        option.textContent = site.location;
                        worksiteSelect.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error fetching worksites:', error));
    }

    function fetchRecords() {
        const date = document.getElementById('date').value;
        const worksite = document.getElementById('worksite').value;

        fetch(`../actions/fetch_checkin_record_process.php?action=getRecords&date=${date}&worksite=${worksite}`)
            .then(response => response.json())
            .then(data => {
                const recordsTable = document.getElementById('recordsTable');

                if (data.success) {
                    if (data.records.length > 0) {
                        let tableHtml = `
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>User Name</th>
                                        <th>Check-In Time</th>
                                        <th>Location</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;

                        data.records.forEach(record => {
                            tableHtml += `
                                <tr>
                                    <td>${record.id}</td>
                                    <td>${record.user_name}</td>
                                    <td>${record.checkin_time}</td>
                                    <td>${record.location}</td>
                                </tr>
                            `;
                        });

                        tableHtml += '</tbody></table>';
                        recordsTable.innerHTML = tableHtml;
                    } else {
                        recordsTable.innerHTML = '<p class="text-center text-muted">No records found.</p>';
                    }
                } else {
                    recordsTable.innerHTML = '<p class="text-center text-muted">No records found.</p>';
                }
            })
            .catch(error => console.error('Error fetching records:', error));
    }
</script>

<?php
include('../includes/footer.php');
?>