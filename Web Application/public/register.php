<?php
session_start();
if (isset($_SESSION['verified_user_id'])) {
    $_SESSION['status'] = "You are already Logged in";
    header('Location: home.php');
    exit();
}
include('../includes/header.php');
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <?php
            if (isset($_SESSION['status'])) {
                $alertClass = isset($_SESSION['status_type']) && $_SESSION['status_type'] === "error" ? "alert-danger" : "alert-success";
                echo "<h5 class='alert $alertClass text-center'>" . $_SESSION['status'] . "</h5>";
                unset($_SESSION['status']);
                unset($_SESSION['status_type']);
            }
            ?>

            <div class="card">
                <div class="card-header">
                    <h4>
                        Register
                        <a href="../index.php" class="btn btn-primary float-end">Back</a>
                    </h4>
                </div>
                <div class="card-body">
                    <form action="../actions/register_process.php" method="POST">

                        <div class="form-group mb-3">
                            <label for="">Full Name</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="">Phone Number</label>
                            <input type="number" name="phone_number" class="form-control" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="">Email Address</label>
                            <input type="email" name="email" class="form-control <?php echo isset($_SESSION['highlight_email']) ? 'is-invalid' : ''; ?>" required>
                            <?php if (isset($_SESSION['highlight_email'])) : ?>
                                <div class="invalid-feedback">This email address has been registered!</div>
                                <?php unset($_SESSION['highlight_email']); ?>
                            <?php endif; ?>
                        </div>

                        <div class="form-group mb-3">
                            <label for="">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="form-group mb-3">
                            <button type="submit" name="register_btn" class="btn btn-primary w-100">Register</button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    <?php if (isset($_SESSION['success_redirect'])) : ?>
        setTimeout(function () {
            window.location.href = "index.php";
        }, 3000);
        <?php unset($_SESSION['success_redirect']); ?>
    <?php endif; ?>
</script>

<?php include('../includes/footer.php'); ?>
