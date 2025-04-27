package com.fyp.prototype;

import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.view.View;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

public class WarmUpActivity extends AppCompatActivity {

    private TextView txtTimer, txtPoseDescription;
    private ImageView imgWarmUpPose;
    private Button btnComplete;
    private Handler handler = new Handler();
    private Runnable countdownRunnable;

    private int timeLeft = 240;
    private boolean isTimerRunning = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_warm_up);
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        initializeViews();
        setupClickListeners();
    }

    private void initializeViews() {
        txtTimer = findViewById(R.id.txtTimer);
        txtPoseDescription = findViewById(R.id.txtPoseDescription);
        imgWarmUpPose = findViewById(R.id.imgWarmUpPose);
        btnComplete = findViewById(R.id.btnComplete);
        updateTimerDisplay();
        updateWarmUpPose();
    }

    private void setupClickListeners() {
        btnComplete.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                if (!isTimerRunning) {
                    startTimer();
                    btnComplete.setText("Pause");
                    isTimerRunning = true;
                } else {
                    pauseTimer();
                    btnComplete.setText("Resume");
                    isTimerRunning = false;
                }
            }
        });
    }
    private void pauseTimer() {
        handler.removeCallbacks(countdownRunnable);
    }
    private void startTimer() {
        countdownRunnable = new Runnable() {
            @Override
            public void run() {
                if (timeLeft > 0) {
                    timeLeft--;
                    updateTimerDisplay();
                    updateWarmUpPose();
                    handler.postDelayed(this, 1000);
                } else {
                    timerFinished();
                }
            }
        };
        handler.post(countdownRunnable);
    }
    private void timerFinished() {
        handler.removeCallbacks(countdownRunnable);
        txtTimer.setText("00:00");
        showToast("Warm-up complete!");
        btnComplete.setText("Start");
        isTimerRunning = false;
        timeLeft = 240; // Reset timer for future use
        String receiveedJson = getIntent().getStringExtra("checkInRecord");
        Intent intent = new Intent(WarmUpActivity.this, EscapeRoutesActivity.class);
        intent.putExtra("checkInRecord", receiveedJson);
        startActivity(intent);
        finish();
    }
    private void updateTimerDisplay() {
        int minutes = timeLeft / 60;
        int seconds = timeLeft % 60;
        String timeFormatted = String.format("%02d:%02d", minutes, seconds);
        txtTimer.setText(timeFormatted);
    }
    private void updateWarmUpPose() {
        if (timeLeft > 180) {
            // 第一阶段热身
            imgWarmUpPose.setImageResource(R.drawable.warm_up_pose1);
            txtPoseDescription.setText("Touch your left toe with your right hand, then touch your right toe with your left hand, 20 times each.");
        } else if (timeLeft > 120) {
            // 第二阶段热身
            imgWarmUpPose.setImageResource(R.drawable.warm_up_pose2);
            txtPoseDescription.setText("Touch your left knee to your right elbow, then your right knee to your left elbow, alternating about 20 times.");
        } else if (timeLeft > 60){
            // 第三阶段热身
            imgWarmUpPose.setImageResource(R.drawable.warm_up_pose3);
            txtPoseDescription.setText("Raise your arms to shoulder height and jump up, with your feet simultaneously spread out and landing on the ground. Repeat the above movements about 30 times");
        } else{
            imgWarmUpPose.setImageResource(R.drawable.warm_up_pose4);
            txtPoseDescription.setText("Kick your right hand with your left foot, and your left hand with your right foot, 10 times each.");
        }
    }

    private void showToast(String message) {
        Toast.makeText(this, message, Toast.LENGTH_SHORT).show();
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        handler.removeCallbacks(countdownRunnable); // 防止内存泄漏
    }
}
