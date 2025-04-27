<?php
include('../functions/auth_check.php');
include('../includes/header.php');
require_once('../actions/fetch_users.php');

// Check if 'id' is passed in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $userID = intval($_GET['id']); 
    $user = getUserByID($userID); 

    if (!$user) {
        $_SESSION['status'] = "User not found.";
        header("Location: user-list.php");
        exit();
    }
} else {
    $_SESSION['status'] = "Invalid user ID.";
    header("Location: user-list.php");
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
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>
                        Edit & Update User Data
                        <a href="user-list.php" class="btn btn-primary float-end"> Back </a>
                    </h4>
                </div>
                <div class="card-body">

                    <!-- Display User ID  -->
                    <div class="form-group mb-3">
                        <strong>User ID:  <?= htmlspecialchars($user['user_ID']); ?></strong>      
                    </div>

                    <form action="../actions/update_user_process.php" method="POST">
                        <!-- Hidden field to store user ID -->
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['user_ID']); ?>">

                        <div class="form-group mb-3">
                            <label for="username">Username</label>
                            <input type="text" name="username" placeholder="Leave blank to keep current username" value="" class="form-control">
                        </div>

                        <div class="form-group mb-3">
                            <label for="email">Email</label>
                            <input type="email" name="email" placeholder="Leave blank to keep current email" value="" class="form-control">
                        </div>

                        <div class="form-group mb-3">
                            <label for="phone_number">Phone Number</label>
                            <input type="text" name="phone_number" placeholder="Leave blank to keep current phone number" value="" class="form-control">
                        </div>

                        <div class="form-group mb-3">
                            <label for="password">Change Password</label>
                            <input type="password" name="password" placeholder="Leave blank to keep current password" class="form-control">
                        </div>

                        <div class="form-group mb-3">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" name="confirm_password" placeholder="Re-enter password if changing" class="form-control">
                        </div>

                        <div class="form-group mb-3">
                            <label for="account_status">Enable or Disable User Account</label>
                            <select name="account_status" class="form-control" required>
                                <option value="Enable" <?= $user['account_status'] === 'Enable' ? 'selected' : ''; ?>>Enable</option>
                                <option value="Disable" <?= $user['account_status'] === 'Disable' ? 'selected' : ''; ?>>Disable</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="user_role">User Role</label>
                            <select name="user_role" class="form-control" required>
                                <option value="admin" <?= $user['user_role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="user" <?= $user['user_role'] === 'user' ? 'selected' : ''; ?>>User</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
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