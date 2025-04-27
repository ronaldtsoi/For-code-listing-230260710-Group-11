<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../functions/auth_check.php');
include('../includes/header.php');
require_once('../actions/fetch_users.php');
require_once('../functions/pagination.php');

// Get the search keyword from the URL
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination parameters
$limit = 10; 
$currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($currentPage - 1) * $limit;

// Fetch total users and users for the current page
$totalUsers = getTotalUsers($search);
$totalPages = ceil($totalUsers / $limit);
$users = getAllUsers($search, $limit, $offset);

// Page title and placeholder
$pageTitle = "Registered User List";
$placeholder = "Search by username, email, or phone";
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <?php
            if (isset($_SESSION['status'])) {
                echo "<h5 class='alert alert-success'>" . $_SESSION['status'] . "</h5>";
                unset($_SESSION['status']);
            }
            ?>
            <div class="card">
                <div class="card-header">
                    <?php include('../functions/search_bar.php'); ?>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Enable/Disable</th>
                                <th>User Role</th>
                                <th>Created At</th>
                                <th>Last online time</th>
                                <th>Edit</th>
                                <th>Disable account</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($users) {
                                foreach ($users as $user) {
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($user['user_ID']); ?></td>
                                <td><?= htmlspecialchars($user['username']); ?></td>
                                <td><?= htmlspecialchars($user['email']); ?></td>
                                <td><?= htmlspecialchars($user['phone_number']); ?></td>
                                <td><?= htmlspecialchars($user['account_status']); ?></td>
                                <td><?= htmlspecialchars($user['user_role']); ?></td>
                                <td><?= htmlspecialchars($user['created_at']); ?></td>
                                <td><?= htmlspecialchars($user['updated_at']); ?></td>
                                <td>
                                    <a href="user-edit.php?id=<?= $user['user_ID']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                </td>
                                <td>
                                    <form action="../actions/disable_account_process.php" method="POST" onsubmit="return confirmDisable(<?= $user['user_ID']; ?>, '<?= $user['username']; ?>')">
                                        <button type="submit" name="disable_account_btn" value="<?= $user['user_ID']; ?>" class="btn btn-danger btn-sm">Disable</button>
                                    </form>
                                </td>
                            </tr>
                            <?php
                                }
                            } else {
                            ?>
                            <tr>
                                <td colspan="10">No Records found</td>
                            </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>

                    <!-- Pagination links -->
                    <div class="mt-3">
                        <?= generatePagination($currentPage, $totalPages, "?search=$search&"); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // JavaScript function to confirm the action
    function confirmDisable(userId, username) {
        const enteredUsername = prompt(`Please enter username "${username}" to confirm disabling the account:`); 
        if (enteredUsername === null) {
            return false;
        }
        if (enteredUsername.trim() === username) {
            return true;
        } else {
            alert("The entered username does not match, the operation has been cancelled!"); 
            return false; 
        }
    }
</script>
<?php
include('../includes/footer.php');
?>