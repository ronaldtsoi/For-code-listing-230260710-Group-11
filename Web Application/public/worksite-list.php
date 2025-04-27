<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../functions/auth_check.php');
include('../includes/header.php');
require_once('../actions/fetch_worksites.php');
require_once('../functions/pagination.php');

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination setup
$limit = 20; 
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $limit;

// Get the construction site records and total number
$totalWorksites = getTotalWorksites($search);
$totalPages = ceil($totalWorksites / $limit);
$worksites = getPaginatedWorksites($search, $limit, $offset);

// Page title and placeholder
$pageTitle = "Worksite Records";
$placeholder = "Search by Worksite Name";
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
                    <h4>
                        <?php include('../functions/search_bar.php'); ?>
                    </h4>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sl.no</th>
                                <th>Worksite Name</th>
                                <th>Latitude</th>
                                <th>Longitude</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            
                            if ($worksites) {
                                foreach ($worksites as $worksite) {
                            ?>
                                    <tr>
                                        <td><?= htmlspecialchars($worksite['worksite_id']); ?></td>
                                        <td><?= htmlspecialchars($worksite['worksite_name']); ?></td>
                                        <td><?= htmlspecialchars($worksite['latitude']); ?></td>
                                        <td><?= htmlspecialchars($worksite['longitude']); ?></td>
                                        <td>
                                            <a href="edit-worksite.php?id=<?= htmlspecialchars($worksite['worksite_id']); ?>" class="btn btn-primary btn-sm">Edit</a>
                                        </td>
                                        <td>
                                            <form action="../actions/delete-worksite.php" method="POST" onsubmit="return confirmDelete(<?= $worksite['worksite_id'];?>, '<?=$worksite['worksite_name'];?>')">
                                                <input type="hidden" name="worksite_id" value="<?= htmlspecialchars($worksite['worksite_id']) ?>">
                                                <button type="submit" name="delete_worksite_btn" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="6">No Records found</td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                    <!-- Dynamic Pagination -->
                    <?= generatePagination($currentPage, $totalPages, "?search=$search&"); ?>
                    <a href="create-worksite.php" class="btn btn-primary float-end"> Add Worksite </a>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function confirmDelete(worksite_id, worksite_name){
        const enteredWorksitename = prompt(`Please enter worksite name "${worksite_name}" to confirm delete the worksite: `)
        if(enteredWorksitename === null){
            return false;
        }
        if (enteredWorksitename.trim() === worksite_name) {
            // User entered the correct username
            return true;
        } else {
            alert("The entered worksite name does not match, the operation has been cancelled!"); 
            return false; 
        }
    }
</script>
<?php
include('../includes/footer.php');
?>
