<?php
include('../functions/auth_check.php');
include('../includes/header.php');
require_once('../actions/fetch_forms.php');

// Check if 'id' is passed in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $formID = intval($_GET['id']); 
    $form = getFormByID($formID); 

    if (!$form) {
        // If no form found, redirect back to the form list with an error message
        $_SESSION['status'] = "Form not found.";
        header("Location: form-list.php");
        exit();
    }
} else {
    // If 'id' is not present in the URL, redirect to the form list
    $_SESSION['status'] = "Invalid form ID.";
    header("Location: form-list.php");
    exit();
}
?>

<div class="container">
    <?php
    if (isset($_SESSION['status'])) {
        echo "<h5 class='alert alert-success'>" . $_SESSION['status'] . "</h5>";
        unset($_SESSION['status']);
    }
    ?>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>
                        Edit & Update Safety Form
                        <a href="form-list.php" class="btn btn-primary float-end"> Back </a>
                    </h4>
                </div>
                <div class="card-body">

                    <!-- Display Form ID  -->
                    <div class="form-group mb-3">
                        <strong>Form ID: <?= htmlspecialchars($form['news_id']); ?></strong>
                    </div>

                    <form action="../actions/update_forms_process.php" method="POST">
                        <input type="hidden" name="form_id" value="<?= htmlspecialchars($form['news_id']); ?>"> 
                        <div class="form-group mb-3">
                            <label for="">Title</label>
                            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($form['title']); ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="">News date</label>
                            <input type="date" name="news_date" class="form-control" value="<?= htmlspecialchars($form['news_date']); ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="">Content</label>
                            <textarea name="content" class="form-control" rows="6" required><?= htmlspecialchars($form['content']); ?></textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label for="">Question</label>
                            <textarea name="news_question" class="form-control" rows="3" required><?= htmlspecialchars($form['question']); ?></textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label for="ans_a">Option A</label>
                            <input type="text" name="ans_a" class="form-control" value="<?= htmlspecialchars($form['option_a']); ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="ans_b">Option B</label>
                            <input type="text" name="ans_b" class="form-control" value="<?= htmlspecialchars($form['option_b']); ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="ans_c">Option C</label>
                            <input type="text" name="ans_c" class="form-control" value="<?= htmlspecialchars($form['option_c']); ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="correct_answer">Correct Answer</label>
                            <select name="correct_answer" class="form-control" required>
                                <option value="option_a" <?= $form['correct_answer'] === 'option_a' ? 'selected' : ''; ?>>Option A</option>
                                <option value="option_b" <?= $form['correct_answer'] === 'option_b' ? 'selected' : ''; ?>>Option B</option>
                                <option value="option_c" <?= $form['correct_answer'] === 'option_c' ? 'selected' : ''; ?>>Option C</option>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <button type="submit" name="update_form" class="btn btn-primary">Update Form</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include('../includes/footer.php');
?>