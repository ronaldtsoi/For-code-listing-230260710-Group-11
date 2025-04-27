<?php
session_start();
if (isset($_SESSION['verified_user_id'])) {
    header("Location: ./public/home.php");
    exit();
}
include('./includes/header.php');
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
            <div class="container text-center mt-5">
                h1>Welcome to <strong>Health and Safety Executive Plus</strong></h1> 
                <p class="lead">The system where you can manage your health and safety work.</p>
                
                <div class="mt-4">
                    <a href="./public/login.php" class="btn btn-primary btn-lg me-3">Sign in</a>
                    <a href="./public/register.php" class="btn btn-outline-primary btn-lg">Register</a>
                </div>
            </div>
            <div class="container mt-5">
                <h2>System functions</h2>
                <p class="text-muted">Understand the advantages of using the Health and Safety Executive Plus:</p>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">✔️ Security equipment detection system</li>
                    <li class="list-group-item">✔️ Security inspection work plan</li>
                    <li class="list-group-item">✔️ Work check-in with GPS</li>
                    <li class="list-group-item">✔️ Safety briefing sheet</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/footer.php');
?>