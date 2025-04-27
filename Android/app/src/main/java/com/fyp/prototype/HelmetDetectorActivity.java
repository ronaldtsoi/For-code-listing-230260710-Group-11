package com.fyp.prototype;

import static android.hardware.Camera.CameraInfo.CAMERA_FACING_FRONT;

import static com.fyp.prototype.utils.NetworkUtils.sendApiRequest;

import android.content.Intent;
import android.content.SharedPreferences;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.net.Uri;
import android.os.Bundle;
import android.util.Log;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;

import com.fyp.prototype.utils.ApiConstants;
import com.fyp.prototype.utils.HelmetDetectorAPI;
import com.fyp.prototype.utils.NetworkUtils;

import org.json.JSONException;
import org.json.JSONObject;

public class HelmetDetectorActivity extends AppCompatActivity {
    private static final int SELECT_IMAGE = 230;
    private static final int TAKE_PHOTO = 260;
    private static final String TAG = "HelmetDetectorActivity";
    private Button btnUpload, btnTakePhoto, btnNext;
    private TextView tvResult;
    private ImageView imageView;
    private Bitmap selectedBitmap;
    private File tempPhotoFile;
    private HelmetDetectorAPI helmetDetectorAPI;
    private boolean detectResult = false;
    private JSONObject receivedJson;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_helmet_detector);
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });


        try {
            String jsonString = getIntent().getStringExtra("checkInRecord");
            if (jsonString != null) {
                receivedJson = new JSONObject(jsonString); // Parse the string into a JSONObject
            } else {
                receivedJson = new JSONObject(); // Initialize an empty JSONObject if no data is passed
            }
        } catch (JSONException e) {
            e.printStackTrace();
            receivedJson = new JSONObject(); // Handle invalid JSON gracefully
        }

        imageView = findViewById(R.id.imageView);
        tvResult = findViewById(R.id.tvResult);

        btnTakePhoto = findViewById(R.id.btnTakePhoto);
        btnUpload = findViewById(R.id.btnUpload);
        btnNext = findViewById(R.id.btnNext);

        helmetDetectorAPI = new HelmetDetectorAPI(this); // Initialize API helper

        btnTakePhoto.setOnClickListener(v -> {
            detectResult = false;
            // front camera
            Intent intent = new Intent(android.provider.MediaStore.ACTION_IMAGE_CAPTURE);
            intent.putExtra("android.intent.extras.CAMERA_FACING", CAMERA_FACING_FRONT);
            startActivityForResult(intent, TAKE_PHOTO);
        });

        btnUpload.setOnClickListener(v -> {
            if (selectedBitmap != null) {
                helmetDetectorAPI.uploadImage(selectedBitmap, new HelmetDetectorAPI.ApiCallback() {
                    @Override
                    public void onSuccess(boolean isWorn) {
                        detectResult = isWorn;
                        if (detectResult) {
                            tvResult.setText("Safety Helmet is worn");
                        } else {
                            tvResult.setText("No Safety Helmet is worn");
                        }
                    }
                    public void onError(String error) {
                        tvResult.setText(error);
                    }
                });
                if (tempPhotoFile != null && tempPhotoFile.exists()) {
                    tempPhotoFile.delete();
                }
            } else {
                tvResult.setText("Please Select an Image First");
            }
        });

        btnNext.setOnClickListener(v -> {
            if (detectResult) {
                NextStep();
            } else {
                tvResult.setText("Please upload an image and detect the safety helmet first");
            }
        });
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == SELECT_IMAGE && resultCode == RESULT_OK && data != null) {
            Uri imageUri = data.getData();
            try {
                InputStream imageStream = getContentResolver().openInputStream(imageUri);
                selectedBitmap = BitmapFactory.decodeStream(imageStream);
                imageView.setImageBitmap(selectedBitmap);
            } catch (IOException e) {
                e.printStackTrace();
            }
        } else if (requestCode == TAKE_PHOTO && resultCode == RESULT_OK && data != null) {
            Bundle extras = data.getExtras();
            selectedBitmap = (Bitmap) extras.get("data");
            imageView.setImageBitmap(selectedBitmap);

            // Save the photo to a temporary file
            try {
                tempPhotoFile = File.createTempFile("temp_photo", ".jpg", getCacheDir());
                FileOutputStream fos = new FileOutputStream(tempPhotoFile);
                selectedBitmap.compress(Bitmap.CompressFormat.JPEG, 100, fos);
                fos.close();
            } catch (IOException e) {
                e.printStackTrace();
            }
        }
    }

    private void NextStep(){
        String apiUrl = ApiConstants.CHECKIN_RECORD_ENDPOINT;

        try{
            if (receivedJson == null) {
                Log.e("SecurityCaseActivity", "Received JSON is empty");
                showToast("No data received");
                return;
            }
            sendApiRequest(apiUrl, "POST", receivedJson, new NetworkUtils.ApiCallback() {
                @Override
                public void onSuccess(String responseBody) {
                    try {
                        JSONObject responseJson = new JSONObject(responseBody);
                        boolean success = responseJson.getBoolean("success");
                        String message = responseJson.getString("message");

                        if (success) {
                            // Save user checkin boolean
                            SharedPreferences.Editor editor = getSharedPreferences("user_session", MODE_PRIVATE).edit();
                            editor.putBoolean("is_checked_in", true); // 更新为 true
                            editor.apply();

                            // Show success message
                            showToast("Check In Successfully");
                            Intent intent = new Intent(HelmetDetectorActivity.this, MainActivity.class);
                            startActivity(intent);
                            finish();
                        } else {
                            // API responded with a failure
                            showToast("Failed to send record: " + message);
                            Log.e(TAG, "API response: " + message);
                        }
                    } catch (JSONException e) {
                        e.printStackTrace();
                        showToast("Error parsing server response: " + e.getMessage());
                    }
                }

                @Override
                public void onFailure(Exception e) {
                    // Log the error and show a toast
                    Log.e("SecurityCaseActivity", "API request failed", e);

                    if (e instanceof IOException && e.getMessage().contains("HTTP error: 409")) {
                        // Handle HTTP 409 Conflict error
                        runOnUiThread(() -> showToast("You have already checked in today!"));
                        Intent intent = new Intent(HelmetDetectorActivity.this, MainActivity.class);
                        startActivity(intent);
                        finish();
                    } else {
                        // Handle other errors
                        runOnUiThread(() -> showToast("Failed to send record. Error: " + e.getMessage()));
                        Intent intent = new Intent(HelmetDetectorActivity.this, MainActivity.class);
                        startActivity(intent);
                        finish();
                    }
                }
            });
        }catch (Exception e){
            e.printStackTrace();
            showToast("Error preparing request: " + e.getMessage());
        }
    }

    private void showToast(String message) {
        runOnUiThread(() -> Toast.makeText(this, message, Toast.LENGTH_SHORT).show());
    }
}
