package com.fyp.prototype;

import android.Manifest;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.pm.PackageManager;
import android.location.Location;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.ActivityCompat;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;
import com.fyp.prototype.utils.ApiConstants;
import com.fyp.prototype.utils.LocationFetcher;
import com.fyp.prototype.utils.CheckInRecords;
import com.google.android.gms.location.FusedLocationProviderClient;
import com.google.android.gms.location.LocationCallback;
import com.google.android.gms.location.LocationRequest;
import com.google.android.gms.location.LocationResult;
import com.google.android.gms.location.LocationServices;
import com.google.android.gms.location.Priority;
import com.google.android.gms.maps.CameraUpdateFactory;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.OnMapReadyCallback;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.MarkerOptions;
import com.google.gson.Gson;


import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.Map;

public class CheckInActivity extends AppCompatActivity implements OnMapReadyCallback {

    private Spinner sp_worksites;
    private Button btn_CheckIn, btn_history;
    private GoogleMap mMap;
    private Location currentLocation;
    private TextView tvDistance;
    private LocationCallback locationCallback;
    private LatLng selectedWorksiteLatLng;
    private FusedLocationProviderClient fusedLocationClient;
    private Map<String, LatLng> worksiteLocations;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_check_in);

        initializeViews();
        setupLocationUpdates();
        setupSpinner();
        setupMapFragment();
        fetchWorksites();
        setupClickListeners();
    }

    private void initializeViews() {
        sp_worksites = findViewById(R.id.sp_worksites);
        btn_CheckIn = findViewById(R.id.btn_CheckIn);
        btn_history = findViewById(R.id.btn_History);
        tvDistance = findViewById(R.id.tv_distance);
        fusedLocationClient = LocationServices.getFusedLocationProviderClient(this);

        btn_CheckIn.setOnClickListener(view -> checkLocation());
    }

    private void setupSpinner() {
        sp_worksites.setBackgroundResource(R.drawable.style_spinner_worksites);
        sp_worksites.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                String selectedWorksite = sp_worksites.getSelectedItem().toString();
                if (position > 0) {
                    updateMapWithWorksite(selectedWorksite);
                    selectedWorksiteLatLng = worksiteLocations.get(selectedWorksite);
                    updateDistanceToWorksite(selectedWorksiteLatLng);
                }
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {
            }
        });


    }

    private void setupMapFragment() {
        SupportMapFragment mapFragment = (SupportMapFragment) getSupportFragmentManager()
                .findFragmentById(R.id.map);
        if (mapFragment != null) {
            mapFragment.getMapAsync(this);
        }
    }

    private void updateMapWithWorksite(String worksiteName) {
        LatLng worksiteLocation = worksiteLocations.get(worksiteName);
        if (worksiteLocation != null) {
            mMap.clear();
            mMap.addMarker(new MarkerOptions().position(worksiteLocation).title(worksiteName));
            mMap.moveCamera(CameraUpdateFactory.newLatLngZoom(worksiteLocation, 16.5f));
        } else {
            Toast.makeText(CheckInActivity.this, "Worksite location not found.", Toast.LENGTH_SHORT).show();
        }
    }


    private void checkLocation() {
        if (sp_worksites.getSelectedItemPosition() == 0) {
            Toast.makeText(CheckInActivity.this, "Please select a worksite.", Toast.LENGTH_SHORT).show();
            return;
        }

        LocationFetcher location = new LocationFetcher(CheckInActivity.this);
        double latitude = location.getLatitude();
        double longitude = location.getLongitude();
        String worksite = sp_worksites.getSelectedItem().toString();

        double desLatitude = worksiteLocations.get(worksite).latitude;
        double desLongitude = worksiteLocations.get(worksite).longitude;
        float[] results = new float[2];
        Location.distanceBetween(latitude, longitude, desLatitude, desLongitude, results);
        if (results[0] < 100) {
            int user_id = getIntent().getIntExtra("uid", 0);
            String worksite_name = worksite;
            SimpleDateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
            String checkIn_at = dateFormat.format(new Date());
            CheckInRecords checkInRecords = new CheckInRecords(user_id, worksite_name, checkIn_at,desLatitude,desLongitude);

            Gson gson = new Gson();
            String checkInJson = gson.toJson(checkInRecords);

            Toast.makeText(CheckInActivity.this, "Check in successful.", Toast.LENGTH_SHORT).show();
            Intent intent = new Intent(CheckInActivity.this, WarmUpActivity.class);
            intent.putExtra("checkInRecord", checkInJson);
            startActivity(intent);
            finish();
        } else {
            Toast.makeText(CheckInActivity.this, "You are not at the worksite.", Toast.LENGTH_SHORT).show();
        }
    }

    private void updateDistanceToWorksite(LatLng worksiteLocation) {
        if (currentLocation != null) {
            Location worksiteLoc = new Location("worksite");
            worksiteLoc.setLatitude(worksiteLocation.latitude);
            worksiteLoc.setLongitude(worksiteLocation.longitude);

            float distanceInMeters = currentLocation.distanceTo(worksiteLoc);
            tvDistance.setText(String.format("Distance: %.2f M", distanceInMeters));
        } else {
            tvDistance.setText("Distance: N/A");
        }
    }

    private void setupLocationUpdates() {
        LocationRequest locationRequest = new LocationRequest.Builder(Priority.PRIORITY_HIGH_ACCURACY, 10000)
                .setMinUpdateIntervalMillis(10000)
                .build();

        locationCallback = new LocationCallback() {
            @Override
            public void onLocationResult(LocationResult locationResult) {
                if (locationResult == null) {
                    return;
                }
                currentLocation = locationResult.getLastLocation();
                if (selectedWorksiteLatLng != null) {
                    updateDistanceToWorksite(selectedWorksiteLatLng);
                }
            }
        };

        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) == PackageManager.PERMISSION_GRANTED) {
            fusedLocationClient.requestLocationUpdates(locationRequest, locationCallback, getMainLooper());
        }
    }

    @Override
    public void onMapReady(GoogleMap googleMap) {
        mMap = googleMap;
        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) != PackageManager.PERMISSION_GRANTED &&
                ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_COARSE_LOCATION) != PackageManager.PERMISSION_GRANTED) {
            ActivityCompat.requestPermissions(this, new String[]{Manifest.permission.ACCESS_FINE_LOCATION}, 1);
            return;
        }
        mMap.setMyLocationEnabled(true);
        LatLng FoTanStation = new LatLng(22.330967, 114.174244);
        mMap.moveCamera(CameraUpdateFactory.newLatLngZoom(FoTanStation, 12.0f));
    }

    private void fetchWorksites() {
        RequestQueue requestQueue = Volley.newRequestQueue(this);

        JsonObjectRequest jsonObjectRequest = new JsonObjectRequest(
                Request.Method.GET,
                ApiConstants.WORKSITES_ENDPOINT,
                null,
                response -> {
                    try {
                        if (response.getBoolean("success")) {
                            JSONArray worksitesArray = response.getJSONArray("data");
                            ArrayList<String> worksiteNames = new ArrayList<>();
                            worksiteNames.add("Select a Worksite");

                            worksiteLocations = new HashMap<>();
                            for (int i = 0; i < worksitesArray.length(); i++) {
                                JSONObject worksiteObject = worksitesArray.getJSONObject(i);
                                String worksiteName = worksiteObject.getString("name");
                                JSONObject coordinates = worksiteObject.getJSONObject("coordinates");
                                double latitude = coordinates.getDouble("lat");
                                double longitude = coordinates.getDouble("lng");

                                worksiteNames.add(worksiteName);
                                worksiteLocations.put(worksiteName, new LatLng(latitude, longitude));
                            }

                            // Update the spinner
                            ArrayAdapter<String> adapter = new ArrayAdapter<>(CheckInActivity.this, R.layout.spinner_items, worksiteNames);
                            adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
                            sp_worksites.setAdapter(adapter);
                        } else {
                            showToast("Failed to fetch worksites: " + response.getString("message"));
                        }
                    } catch (JSONException e) {
                        Log.e("CheckInActivity", "Error parsing JSON: " + e.getMessage());
                        showToast("Error parsing worksites data");
                        e.printStackTrace();
                    }
                },
                error -> {
                    Log.e("CheckInActivity", "Volley error: " + error.getMessage());
                    showToast("Error fetching worksites");
                }
        );

        requestQueue.add(jsonObjectRequest);
    }
    private void showToast(String message) {
        Toast.makeText(this, message, Toast.LENGTH_SHORT).show();
    }

    private void setupClickListeners(){

        btn_history.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Intent intent = new Intent(CheckInActivity.this, HistoryActivity.class);
                intent.putExtra("uid", getIntent().getIntExtra("uid", 0));
                startActivity(intent);
            }
        });
    }
}