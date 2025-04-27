<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    
    <a class="navbar-brand d-flex align-items-center" href="https://james.sl94.i.ng/index.php">
      <img src="../image/icon1.png" alt="icon">    
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="https://james.sl94.i.ng/public/home.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="https://james.sl94.i.ng/public/alertrecord.php">Alert Records</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="https://james.sl94.i.ng/public/checkinrecord.php">Check-In Records</a>
        </li>
        <li class="navitem">
          <a class="nav-link" href="https://james.sl94.i.ng/public/worksite-list.php">Worksite List</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="https://james.sl94.i.ng/public/map-list.php">Escape Route Map</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="https://james.sl94.i.ng/public/form-list.php">Safety Form</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="https://james.sl94.i.ng/public/user-list.php">User List</a>
        </li>
        <?php if(!isset($_SESSION['verified_user_id'])) : ?>
        <li class="nav-item">
          <a class="nav-link" href="https://james.sl94.i.ng/public/register.php">Register</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="https://james.sl94.i.ng/public/login.php">Login</a>
        </li>
        <?php else : ?>
        <li class="nav-item">
          <a class="nav-link" href="#" onclick="confirmLogout()">Logout</a>
        </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<script>
function confirmLogout() {
    let confirmAction = confirm("Are you sure you want to log out?");
    if (confirmAction) {
        window.location.href = "../actions/logout_process.php";
    }
}
</script>