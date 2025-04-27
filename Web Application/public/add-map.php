<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../functions/auth_check.php');
include('../includes/header.php');
require_once('../actions/fetch_worksites.php');
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow border-0">
                <div class="card-header bg-dark text-white text-center">
                    <h4 class="mb-0">
                        Add Escape Route Map
                        <a href="map-list.php" class="btn btn-light btn-sm float-end">Back</a>
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['status'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($_SESSION['status']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['status']); ?>
                    <?php endif; ?>

                    <form action="../actions/add_map_process.php" method="POST" enctype="multipart/form-data">
                        <!-- Upload image -->
                        <div class="form-group mb-4">
                            <label for="map_image" class="form-label">Upload Map Image</label>
                            <input type="file" name="map_image" id="map_image" class="form-control" accept="image/*" required>
                            <small class="form-text text-muted">
                                Supported formats: JPG, JPEG, PNG.
                            </small>
                        </div>

                        <!-- Select your work location -->
                        <div class="form-group mb-4">
                            <label for="worksite_id" class="form-label">Select Worksite</label>
                            <select name="worksite_id" id="worksite_id" class="form-select" required>
                                <option value="" disabled selected>Select a worksite</option>
                                <?php
                                // Dynamically generate work location options
                                $worksites = getWorksiteNameANDID();
                                if (!empty($worksites)) {
                                    foreach ($worksites as $worksite) {
                                        echo "<option value='" . htmlspecialchars($worksite['worksite_id']) . "'>" . htmlspecialchars($worksite['worksite_name']) . "</option>";
                                    }
                                } else {
                                    echo "<option value='' disabled>No worksites available</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group mb-4">
                            <label for="question" class="form-label">Enter Question</label>
                            <textarea name="question" id="question" class="form-control" rows="3" placeholder="Enter the question here..." required></textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label for="">Option A</label>
                            <input type="text" name="option_a" class="form-control" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="">Option B</label>
                            <input type="text" name="option_b" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="">Option C</label>
                            <input type="text" name="option_c" class="form-control"required> 
                        </div>
                        <div class="form-group mb-3">
                            <label for="correct_answer">Correct Answer</label>
                            <select name="correct_answer" class="form-control" required>
                                <option value="" disabled selected>Please select the answer</option>
                                <option value="option_a">Option A</option>
                                <option value="option_b">Option B</option>
                                <option value="option_c">Option C</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="add_map" class="btn btn-success w-100">
                                Upload Map
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>