<?php
include('../functions/auth_check.php');
include('../includes/header.php');
?>

<div class="container">
<?php
if (isset($_SESSION['status'])) {
    echo "<h5 class='alert alert-success'>" . $_SESSION['status'] . "</h5>";
    unset($_SESSION['status']);
}
?>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>
                        Add Safety Form
                        <a href="form-list.php" class="btn btn-primary float-end"> Back </a>
                    </h4>
                </div>
                <div class="card-body">
                    <form action="../actions/add_form_process.php" method="POST">
                        <div class="form-group mb-3">
                            <label for="">Title</label>
                            <input type="text" name="title" class="form-control" required></input>
                        </div>
                        <div class="form-group mb-3">
                            <label for="">News date</label>
                            <input type="date" name="news_date" class="form-control" required></input>
                        </div>
                        <div class="form-group mb-3">
                            <label for="">Content</label>
                            <textarea name="content" class="form-control" rows="6" placeholder="Enter content here..." required></textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label for="">Question</label>
                            <textarea name="question" class="form-control" rows="3" placeholder="Enter question here..." required></textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label for="">Option A</label>
                            <input type="text" name="option_a" class="form-control">
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="">Option B</label>
                            <input type="text" name="option_b" class="form-control">
                        </div>
                        <div class="form-group mb-3">
                            <label for="">Option C</label>
                            <input type="text" name="option_c" class="form-control">
                        </div>
                        <div class="form-group mb-3">
                            <label for="correct_answer">Correct Answer</label>
                            <select name="correct_answer" class="form-control" required>
                                <option value="option_a">Option A</option>
                                <option value="option_b">Option B</option>
                                <option value="option_c">Option C</option>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <button type="submit" name="save_form" class="btn btn-primary">Save Form</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/footer.php');
?>
    