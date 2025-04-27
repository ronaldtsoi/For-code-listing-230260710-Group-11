<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../functions/auth_check.php');
include('../includes/header.php');
require_once('../actions/fetch_maps_process.php');
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <?php
            if(isset($_SESSION['status']))
            {
                echo "<h5 class='alert alert-success'>".$_SESSION['status']."</h5>";
                unset($_SESSION['status']);
            }
            ?>
            <div class="card">
                <div class="card-header">
                    <h4>
                        Escape Route Map
                        <a href="add-map.php" class="btn btn-primary float-end"> Add Map </a>
                    </h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image Path</th>
                                <th>Created By</th>
                                <th>Uploaded At</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $maps = getAllMapsInfo();
            
                                if (!empty($maps)) {
                                    foreach ($maps as $map) {
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($map['id']);?></td>
                                <td><?= htmlspecialchars($map['image_path']);?></td>
                                <td><?= htmlspecialchars($map['username']);?></td>
                                <td><?= htmlspecialchars($map['uploaded_at']);?></td>
                                <td>
                                    <!-- Edit Button -->
                                    <a href="edit-map.php?id=<?= $map['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                </td>
                                <td>
                                    <!-- Delete Button with Confirmation -->
                                    <form action="../actions/delete-map.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this map?');">
                                        <input type="hidden" name="map_id" value="<?= $map['id']; ?>">
                                        <button type="submit" name="delete_map" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php          
                                    }
                                } else {
                            ?>        
                            <!-- Display a message if no records are found -->
                            <tr>
                                <td colspan='4' class='text-center'>No maps found.</td>
                            </tr>
                            <?php
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>    
<?php
include('../includes/footer.php');
?>