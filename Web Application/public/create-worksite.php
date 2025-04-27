<?php
include('../functions/auth_check.php');
include('../includes/header.php');
?>

<div class="container-fluid">  
    <div class="row">
        <div class="col-md-8">  <!-- Left side: Google Map (70%) -->
            <div id="map" style="height: 800px;"></div>
        </div>

        <div class="col-md-4">
        <?php
        if (isset($_SESSION['status'])) {
            echo "<h6 class='alert alert-success'>" . $_SESSION['status'] . "</h6>";
            unset($_SESSION['status']);
        }
        ?>
            <div class="card">
                <div class="card-header">
                    <h4>Add Worksite</h4>
                </div>
                <div class="card-body">
                    <form action="../actions/add_worksite_process.php" method="POST">
                        <div class="form-group mb-3">
                            <label for="latitude">Latitude:</label>
                            <input type="text" class="form-control" id="latitude" name="latitude" readonly>
                        </div>
                        <div class="form-group mb-3">
                            <label for="longitude">Longitude:</label>
                            <input type="text" class="form-control" id="longitude" name="longitude" readonly>
                        </div>
                        <div class="form-group mb-3">
                            <label for="worksite_name">Worksite Name:</label>
                            <input type="text" class="form-control" id="worksite_name" name="worksite_name" required>
                        </div>
                        <button type="submit" class="btn btn-primary" name="add_worksite">Add Worksite</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let map;

    function initMap() {
        // The location of default
        const defaultLatLng = { lat: 22.330967, lng: 114.174244 };

        // The map, centered at default location
        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 12,
            center: defaultLatLng,
        });

        // Add a marker at the default location
        marker = new google.maps.Marker({
            position: map.getCenter(),
            map: map,
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
