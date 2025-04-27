package com.fyp.prototype;

import android.content.Intent;
import android.content.SharedPreferences;
import android.content.pm.PackageManager;
import android.os.Build;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;


import com.android.volley.Request;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;
import com.fyp.prototype.utils.ApiConstants;

import org.json.JSONException;
import org.json.JSONObject;

public class LoginActivity extends AppCompatActivity {
    private static final int REQUEST_CODE_PERMISSIONS = 101;
    private TextView tv_email, tv_password;
    private Button btnLogin, btnSignUp, btnFillInfo;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_login);
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        // Check permissions
        if (checkPermissions()) {
            initializeViews();
            setupClickListeners();
            checkExistingSession();
        } else {
            requestPermissions();
        }
    }

    private void initializeViews(){
        tv_email = findViewById(R.id.tv_email);
        tv_password = findViewById(R.id.tv_password);
        btnLogin = findViewById(R.id.btnLogin);
        btnSignUp = findViewById(R.id.btnSignUp);
        btnFillInfo = findViewById(R.id.btnFillInfo);
    }
    private void setupClickListeners() {
        btnLogin.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                // startActivity(new Intent(LoginActivity.this, MainActivity.class));
                attemptLogin();
            }
        });

        btnSignUp.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                startActivity(new Intent(LoginActivity.this, SignupActivity.class));
            }
        });

        btnFillInfo.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                tv_email.setText("workera@email.com");
                tv_password.setText("123456");
            }
        });
    }
    private void checkExistingSession() {
        SharedPreferences prefs = getSharedPreferences("user_session", MODE_PRIVATE);
        if(prefs.contains("user_id")) {
            // If there is a valid session, jump directly to the main interface
            startActivity(new Intent(LoginActivity.this,MainActivity.class));
            finish();
        }
    }
    private void attemptLogin() {
        String email = tv_email.getText().toString().trim();
        String password = tv_password.getText().toString().trim();

        if(!validateInput(email, password)) return;

        sendLoginRequest(email, password); // Send login request
    }

    //Input validation logic
    private boolean validateInput(String email, String password) {
        if(email.isEmpty() || password.isEmpty()) {
            showToast("Email and password cannot be empty");
            return false;
        }
        if(!android.util.Patterns.EMAIL_ADDRESS.matcher(email).matches()) {
            showToast("The email format is incorrect");
            return false;
        }
        return true;
    }
    private void sendLoginRequest(String email, String password) {
        JSONObject payload = new JSONObject();
        try {
            payload.put("email", email);
            payload.put("password", password);
        } catch (JSONException e) {
            e.printStackTrace();
        }

        JsonObjectRequest request = new JsonObjectRequest(
                Request.Method.POST,
                ApiConstants.LOGIN_ENDPOINT,
                payload,
                this::handleLoginResponse,
                this::handleLoginError
        );

        Volley.newRequestQueue(this).add(request);
    }
    private void handleLoginResponse(JSONObject response) {
        try {
            if(response.getBoolean("success")) {
                JSONObject user = response.getJSONObject("user");
                saveUserSession(user);  // 保存用户会话信息
                showToast("Login successful");
                startActivity(new Intent(LoginActivity.this,MainActivity.class));
                finish(); // 关闭当前Activity
            } else {
                // 显示服务器返回的错误信息
                showToast(response.getString("message"));
            }
        } catch (JSONException e) {
            showToast("The server response is malformed");
            e.printStackTrace();
        }
    }
    private void saveUserSession(JSONObject user) throws JSONException {
        SharedPreferences.Editor editor = getSharedPreferences("user_session", MODE_PRIVATE).edit();
        editor.putInt("user_id", user.getInt("user_ID"))
                .putString("username", user.getString("username"))
                .putString("email", user.getString("email"))
                .putString("role", user.getString("role"))
                .putBoolean("is_checked_in", false)
                .apply();
    }
    private void handleLoginError(VolleyError error) {
        String errorMessage = "Network connection error";
        if(error.networkResponse != null) {
            // Display specific HTTP status code
            errorMessage = "Request failed (" + error.networkResponse.statusCode + ")";
        }
        showToast(errorMessage);
    }
    private void showToast(String message) {
        Toast.makeText(this, message, Toast.LENGTH_SHORT).show();
    }

    private boolean checkPermissions() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
            // For API 31+ (Android 12+), request Bluetooth permissions (BLUETOOTH_SCAN, BLUETOOTH_CONNECT)
            return  //ContextCompat.checkSelfPermission(this, android.Manifest.permission.BLUETOOTH) == PackageManager.PERMISSION_GRANTED &&
                    //ContextCompat.checkSelfPermission(this, android.Manifest.permission.BLUETOOTH_ADMIN) == PackageManager.PERMISSION_GRANTED &&
                    ContextCompat.checkSelfPermission(this, android.Manifest.permission.BLUETOOTH_SCAN) == PackageManager.PERMISSION_GRANTED &&
                    ContextCompat.checkSelfPermission(this, android.Manifest.permission.BLUETOOTH_CONNECT) == PackageManager.PERMISSION_GRANTED &&
                    ContextCompat.checkSelfPermission(this, android.Manifest.permission.ACCESS_FINE_LOCATION) == PackageManager.PERMISSION_GRANTED &&
                    ContextCompat.checkSelfPermission(this, android.Manifest.permission.FOREGROUND_SERVICE) == PackageManager.PERMISSION_GRANTED;
        } else  {
            // For API 26+ (Android 8.0 and above), request basic Bluetooth permissions (BLUETOOTH, BLUETOOTH_ADMIN)
            return  //ContextCompat.checkSelfPermission(this, android.Manifest.permission.BLUETOOTH) == PackageManager.PERMISSION_GRANTED &&
                    //ContextCompat.checkSelfPermission(this, android.Manifest.permission.BLUETOOTH_ADMIN) == PackageManager.PERMISSION_GRANTED &&
                    ContextCompat.checkSelfPermission(this, android.Manifest.permission.ACCESS_FINE_LOCATION) == PackageManager.PERMISSION_GRANTED &&
                    ContextCompat.checkSelfPermission(this, android.Manifest.permission.FOREGROUND_SERVICE) == PackageManager.PERMISSION_GRANTED;
        }
    }

    private void requestPermissions() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
            // Request permissions for API 31+ (Android 12+)
            String[] permissions = {
                    //android.Manifest.permission.BLUETOOTH,
                    //android.Manifest.permission.BLUETOOTH_ADMIN,
                    android.Manifest.permission.BLUETOOTH_SCAN,
                    android.Manifest.permission.BLUETOOTH_CONNECT,
                    android.Manifest.permission.ACCESS_FINE_LOCATION,
                    android.Manifest.permission.FOREGROUND_SERVICE
            };
            ActivityCompat.requestPermissions(this, permissions, REQUEST_CODE_PERMISSIONS);
        } else {
            // Request permissions for API 26+ (Android 8.0 and above)
            String[] permissions = {
                    //android.Manifest.permission.BLUETOOTH,
                    //android.Manifest.permission.BLUETOOTH_ADMIN,
                    android.Manifest.permission.ACCESS_FINE_LOCATION,
                    android.Manifest.permission.FOREGROUND_SERVICE
            };
            ActivityCompat.requestPermissions(this, permissions, REQUEST_CODE_PERMISSIONS);
        }
    }

    // Handle the result of the permission request
    @Override
    public void onRequestPermissionsResult(int requestCode, String[] permissions, int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);

        if (requestCode == REQUEST_CODE_PERMISSIONS) {
            boolean allPermissionsGranted = true;
            for (int i = 0; i < grantResults.length; i++) {
                if (grantResults[i] != PackageManager.PERMISSION_GRANTED) {
                    allPermissionsGranted = false;

                    Log.e("PermissionDenied", "Permission Granted: " + permissions[i]);

                } else {
                    Log.d("PermissionGranted", "Permission Not Granted: " + permissions[i]);
                }
            }

            if (allPermissionsGranted) {
                initializeViews();
                setupClickListeners();
                checkExistingSession();
            } else {
                showToast("All permissions are required to proceed.");
                finish();
            }
        }


    }
}