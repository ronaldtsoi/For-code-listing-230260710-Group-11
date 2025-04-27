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

public class MainActivity extends AppCompatActivity {
    private Button btnCheckIn, btnCheckOut, btnHelmetStatus, btnLogout;
    private TextView tv_user;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_main);
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        initializeViews();
        setupClickListeners();
        showUser();
    }

    private void initializeViews(){
        tv_user = findViewById(R.id.tv_user);
        btnCheckIn = findViewById(R.id.btnCheckIn);
        btnHelmetStatus = findViewById(R.id.btnHelmetStatus);
        btnCheckOut = findViewById(R.id.btnCheckOut);
        btnLogout = findViewById(R.id.btnLogout);
    }

    private void showUser(){
        SharedPreferences prefs = getSharedPreferences("user_session", MODE_PRIVATE);
        if (prefs.contains("user_id")) {
            int userId = prefs.getInt("user_id", 0);
            String username = prefs.getString("username", "Unnamed User");
            String email = prefs.getString("email", "No Email");
            String role = prefs.getString("role", "No Role");

            String userInfo = "ID: " + userId + "\n" +
                    "Username: " + username + "\n" +
                    "Email: " + email + "\n" +
                    "Role: " + role;
            Toast.makeText(this, "Welcome back," + username, Toast.LENGTH_LONG).show();
            tv_user.setText(userInfo);
        } else {
            // Redirect to login page if not logged in
            startActivity(new Intent(this, LoginActivity.class));
            finish();
        }
    }

    private void setupClickListeners(){

        btnCheckIn.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                SharedPreferences prefs = getSharedPreferences("user_session", MODE_PRIVATE);
                int uid = prefs.getInt("user_id", 0);
                Intent intent = new Intent(MainActivity.this, CheckInActivity.class);
                intent.putExtra("uid", uid);
                startActivity(intent);
            }
        });

        btnHelmetStatus.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Intent intent = new Intent(MainActivity.this, HelmetStatusActivity.class);
                startActivity(intent);
            }
        });

        btnCheckOut.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Intent intent = new Intent(MainActivity.this, CheckOutActivity.class);
                startActivity(intent);
            }
        });

         // Logout button

        btnLogout.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                // Clear user session information in shared preferences
                SharedPreferences.Editor editor = getSharedPreferences("user_session", MODE_PRIVATE).edit();
                editor.clear(); // Clear all stored data
                editor.apply();

                Intent intent = new Intent(MainActivity.this, LoginActivity.class);
                startActivity(intent);
                finish();
            }
        });
    }
}