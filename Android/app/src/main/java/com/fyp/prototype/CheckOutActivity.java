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

import com.fyp.prototype.services.HelmetStatusService;

public class CheckOutActivity extends AppCompatActivity {
    private static final String TAG = "CheckOutActivity";
    private Button btnCheckOut;
    private TextView tvCheckOut;
    private boolean isCheckedIn = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_check_out);
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });


        tvCheckOut = findViewById(R.id.tvCheckOut);
        btnCheckOut = findViewById(R.id.btnCheckOut);

        SharedPreferences prefs = getSharedPreferences("user_session", MODE_PRIVATE);
        isCheckedIn = prefs.getBoolean("is_checked_in", false);
        if(!isCheckedIn) {
            tvCheckOut.setText("You have not checked in!");
        } else {
            tvCheckOut.setText("You are checked in!");
        }

        btnCheckOut.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (isCheckedIn) {
                    disconnectDevice();
                    Toast.makeText(CheckOutActivity.this, "Check out success!", Toast.LENGTH_SHORT).show();
                } else {
                    Toast.makeText(CheckOutActivity.this, "You have not checked in!", Toast.LENGTH_SHORT).show();
                }

                finish();
            }
        });
    }

    private void disconnectDevice() {
        // 停止服务
        Intent serviceIntent = new Intent(this, HelmetStatusService.class);
        stopService(serviceIntent);
        // 清除用户会话
        SharedPreferences prefs = getSharedPreferences("user_session", MODE_PRIVATE);
        SharedPreferences.Editor editor = prefs.edit();
        editor.putBoolean("is_checked_in", false);
        editor.apply();
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
    }
}