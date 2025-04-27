package com.fyp.prototype;


import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
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


import java.nio.charset.StandardCharsets;
import java.util.regex.Pattern;

public class SignupActivity extends AppCompatActivity {
    private TextView tv_name, tv_email, tv_phone, tv_password, tv_confirmPassword;
    private Button btnSignUp;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_signup);
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        initializeViews();
        setupClickListeners();
    }
    private void initializeViews(){
        tv_name = findViewById(R.id.tv_name);
        tv_email = findViewById(R.id.tv_email);
        tv_phone = findViewById(R.id.tv_phone);
        tv_password = findViewById(R.id.tv_password);
        tv_confirmPassword = findViewById(R.id.tv_confirmPassword);
        btnSignUp = findViewById(R.id.btnSignUp);
    }

    private void setupClickListeners(){
        btnSignUp.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                attemptRegister();
            }
        });
    }

    private void attemptRegister(){
        String name = tv_name.getText().toString();
        String email = tv_email.getText().toString();
        String phone = tv_phone.getText().toString();
        String password = tv_password.getText().toString();
        String confirmPassword = tv_confirmPassword.getText().toString();

        if(!validateInput(name, email, phone, password, confirmPassword)) return;
        sendRegisterRequest(name, email, phone, password);
    }

    private boolean validateInput(String name, String email, String phone, String password, String confirmPassword ){
        if (name.isEmpty() || email.isEmpty() || phone.isEmpty() || password.isEmpty() || confirmPassword.isEmpty()){
            showToast("Please fill in all required fields");
            return false;
        }

        if(!android.util.Patterns.EMAIL_ADDRESS.matcher(email).matches()) {
            showToast("The email format is incorrect");
            return false;
        }

        // Regex pattern for 8-digit phone number
        Pattern pattern = Pattern.compile("^\\d{8}$");
        if(!pattern.matcher(phone).matches()){
            showToast("Please enter a valid 8-digit mobile phone number");
            return false;
        }

        if (!password.equals(confirmPassword)) {
            showToast("Passwords do not match.");
            return false;
        }
        return  true;
    }

    private void sendRegisterRequest(String name, String email, String phone, String password){
        JSONObject payload = new JSONObject();
        try{
            payload.put("username", name);
            payload.put("email", email);
            payload.put("phone", phone);
            payload.put("password", password);
        } catch (JSONException e) {
            e.printStackTrace();
        }

        JsonObjectRequest request = new JsonObjectRequest(
                Request.Method.POST,
                ApiConstants.REGISTER_ENDPOINT,
                payload,
                this::handleRegisterResponse,
                this::handleRegisterError
        );

        Volley.newRequestQueue(this).add(request);
    }

    private void handleRegisterResponse(JSONObject response){
        try {
            if (response.getBoolean("success")) {
                JSONObject user = response.getJSONObject("user");
                saveUserSession(user);  // 保存用户会话信息
                showToast("Registration successful");
                startActivity(new Intent(SignupActivity.this, MainActivity.class));
                finish(); // 关闭当前 Activity
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
                .apply();
    }

    private void handleRegisterError(VolleyError error) {
        String errorMessage = "Network connection error"; // 默认错误信息

        // 检查网络响应是否存在
        if (error.networkResponse != null) {
            // 从服务器响应中提取错误信息
            try {
                String responseBody = new String(error.networkResponse.data, StandardCharsets.UTF_8);
                JSONObject jsonResponse = new JSONObject(responseBody);
                errorMessage = jsonResponse.optString("message", "Request failed with status code: " + error.networkResponse.statusCode);
            } catch (Exception e) {
                errorMessage = "Request failed with status code: " + error.networkResponse.statusCode;
            }
        } else if (error.getMessage() != null) {
            // 如果有其他错误消息，使用该消息
            errorMessage = error.getMessage();
        }

        showToast(errorMessage); // 显示错误信息
    }
    private void showToast(String message) {
        Toast.makeText(this, message, Toast.LENGTH_SHORT).show();
    }

}