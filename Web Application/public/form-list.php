<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../functions/auth_check.php');
include('../includes/header.php');
require_once('../actions/fetch_forms.php');
?>

<div class="container">
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                <h5>Total No of Record(s): <?= htmlspecialchars(getTotalSafetyForms()); ?></h5>             
                </div>
            </div>
        </div>

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
                        Safety Form Records
                        <a href="add-form.php" class="btn btn-primary float-end"> Add Form </a>
                    </h4>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sl.no</th>
                                <th>Title</th>
                                <th>News Date</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th>Last Updater</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php
                            $forms = getSafetyForms();
                            if ($forms) {                      
                                foreach ($forms as $form) {
                        ?>
                                    <tr>
                                        <td><?= htmlspecialchars($form['news_id']); ?></td>
                                        <td><?= htmlspecialchars($form['title']); ?></td>
                                        <td><?= htmlspecialchars($form['news_date']); ?></td>
                                        <td><?= htmlspecialchars($form['created_at']);?></td>
                                        <td><?= htmlspecialchars($form['updated_at']);?></td>
                                        <td><?= htmlspecialchars($form['updated_by_user'] ?? 'N/A'); ?></td> 
                                        <td>
                                            <a href="form-edit.php?id=<?=$form['news_id'];?>" class="btn btn-primary btn-sm">Edit</a>
                                        </td>
                                        <td>
                                        <form action="../actions/delete_form_process.php" method="POST" onsubmit="return confirmDelete();">
                                                <input type="hidden" name="news_id" value ="<?= htmlspecialchars($form['news_id']) ?>" >
                                                <button class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                    ?>
                                <tr>
                                    <td colspan="14">No Records found</td>
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
<script>
    function confirmDelete() {
        return confirm("Are you sure you want to delete this record?");
    }
</script>
<?php
include('../includes/footer.php');
?>