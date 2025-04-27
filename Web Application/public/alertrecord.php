<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../functions/auth_check.php');
include('../includes/header.php');
require_once('../actions/fetch_alert_record.php');
require_once('../functions/pagination.php');

// Get search keyword
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Set dynamic variables for the search bar
$pageTitle = 'Alert Records';
$placeholder = 'Search by alert type, username, or message';

// Pagination setup
$recordsPerPage = 20;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max($currentPage, 1);
$offset = ($currentPage - 1) * $recordsPerPage;

$totalRecords = getAlertRecordCount($search);
$totalPages = ceil($totalRecords / $recordsPerPage);
$alertRecords = getAllAlertRecord($recordsPerPage, $offset, $search);

$url = '?';
if (!empty($search)) {
    $url .= 'search=' . urlencode($search) . '&';
}
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <?php include('../functions/search_bar.php'); ?>
        </div>

        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <th>ID</th>
                    <th>Alert Type</th>
                    <th>User ID</th>
                    <th>Alert Message</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                </thead>
                <tbody>
                    <?php if ($alertRecords): ?>
                        <?php foreach ($alertRecords as $alertRecord): ?>
                        <tr>
                            <td><?= htmlspecialchars($alertRecord['id']); ?></td>
                            <td><?= htmlspecialchars($alertRecord['alert_type']); ?></td>
                            <td>
                                <?= htmlspecialchars($alertRecord['username']); ?>
                                (<?= htmlspecialchars($alertRecord['user_id']); ?>)
                            </td>
                            <td><?= htmlspecialchars($alertRecord['alert_message']); ?></td>
                            <td><?= htmlspecialchars($alertRecord['alert_time']); ?></td>
                            <td><?= htmlspecialchars($alertRecord['alert_end_time']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Dynamic Pagination -->
            <?= generatePagination($currentPage, $totalPages, $url); ?>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>