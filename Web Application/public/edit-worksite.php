<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../functions/auth_check.php');
include('../includes/header.php');
require_once('../actions/fetch_worksites.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $worksite = getWorksite($id);
    if (!$worksite) {
        $_SESSION['status'] = "Worksite not found.";
        header("Location: worksite-list.php");
        exit();
    }
} else {
    $_SESSION['status'] = "Invalid request.";
    header("Location: worksite-list.php");
    exit();
}
?>

<div class="container-fluid">
    <?php
    if (isset($_SESSION['status'])) {
        echo "<h5 class='alert alert-success'>" . $_SESSION['status'] . "</h5>";
        unset($_SESSION['status']);
    }
    ?>
    <div class="row">
        <div class="col-md-8">
            <div id="map" style="height: 800px;"></div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Worksite
                        <a href="worksite-list.php" class="btn btn-primary float-end"> Back </a>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <strong>Worksite ID: <?= htmlspecialchars($worksite['worksite_id']); ?></strong>
                    </div>

                    <form action="../actions/update-worksite.php" method="POST">
                        <input type="hidden" name="worksite_id" value="<?= htmlspecialchars($worksite['worksite_id']); ?>">
                        <div class="form-group mb-3">
                            <label for="worksite_name">Worksite Name:</label>
                            <input type="text" class="form-control" id="worksite_name" name="worksite_name" value="<?= htmlspecialchars($worksite['worksite_name']); ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="latitude">Latitude:</label>
                            <input type="text" class="form-control" id="latitude" name="latitude" value="<?= htmlspecialchars($worksite['latitude']); ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="longitude">Longitude:</label>
                            <input type="text" class="form-control" id="longitude" name="longitude" value="<?= htmlspecialchars($worksite['longitude']); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Worksite</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let map;

    function initMap() {
        const defaultLatLng = { lat: <?= $worksite['latitude']; ?>, lng: <?= $worksite['longitude']; ?> };

        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 12,
            center: defaultLatLng,
        });

        marker = new google.maps.Marker({
            position: map.getCenter(),
            map: map,
            title: '<?= $worksite['worksite_name']; ?>'
        });

        map.addListener("center_changed", () => {
            const center = map.getCenter();
            marker.setPosition(center);
            document.getElementById("latitude").value = center.lat();
            document.getElementById("longitude").value = center.lng();
        });
    }

    window.initMap = initMap;
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAfZ-i7XWbdBRRO0PDXY4Mlp5rnKx2czfM&callback=initMap" defer></script>

<?php
include('../includes/footer.php');
?>
