package com.fyp.prototype;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.util.Log;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import com.fyp.prototype.services.HelmetStatusService;

public class HelmetStatusActivity extends AppCompatActivity {
    private static final String TAG = "HelmetStatusActivity";
    private TextView statusTextView, dataTextView, nameTextView;
    private Button reconnectBtn, nextButton;
    private String jsonString;
    private boolean isCheckedIn = false;

    private final BroadcastReceiver statusReceiver = new BroadcastReceiver() {
        @Override
        public void onReceive(Context context, Intent intent) {
            Log.d(TAG, "Broadcast received");
            String action = intent.getAction();
            if (action == null) {
                Log.e(TAG, "Received null action");
                return;
            }

            switch (action)
            {
                case "com.fyp.prototype.HELMET_STATUS_UPDATE":
                    String data = intent.getStringExtra("data");
                    String deviceName = intent.getStringExtra("deviceName");

                    statusTextView.setText("Status: Helmet connecting");
                    if (data != null) {
                        dataTextView.setText(data);
                        if (!data.equals("Loading")) {
                            statusTextView.setText("Status: Helmet connected");
                        }
                    }
                    if (deviceName != null)
                        nameTextView.setText(deviceName);

                    if (jsonString != null) {
                        nextButton.setEnabled(true);
                        nextButton.setAlpha(1.0f);
                    }

                    break;

                case "com.fyp.prototype.FINISH_ACTIVITY":
                    Log.e(TAG, "Timeout received. Finishing activity.");
                    Toast.makeText(context, "Connection timeout. Please try again.", Toast.LENGTH_SHORT).show();
                    disconnectDevice();
                    break;
            }
        }
    };

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_helmet_status);
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        jsonString = getIntent().getStringExtra("checkInRecord");

        statusTextView = findViewById(R.id.statusTextView);
        dataTextView = findViewById(R.id.dataTextView);
        nameTextView = findViewById(R.id.nameTextView);

        reconnectBtn = findViewById(R.id.reconnectBtn);
        reconnectBtn.setOnClickListener(v -> {
            Intent intent = new Intent(HelmetStatusActivity.this, HelmetConnectorActivity.class);
            startActivity(intent);
            finish();
        });

        nextButton = findViewById(R.id.nextButton);
        nextButton.setOnClickListener(v -> {
            Intent intent = new Intent(HelmetStatusActivity.this, HelmetDetectorActivity.class);
            intent.putExtra("checkInRecord", jsonString);
            startActivity(intent);
        });
        nextButton.setEnabled(false);
        nextButton.setAlpha(0.5f);

        // 启动服务
        Intent serviceIntent = new Intent(this, HelmetStatusService.class);
        String serviceUUID = getIntent().getStringExtra("serviceUUID");
        String characteristicUUID = getIntent().getStringExtra("characteristicUUID");

        if (serviceUUID != null && characteristicUUID != null) {
            serviceIntent.putExtra("serviceUUID", serviceUUID);
            serviceIntent.putExtra("characteristicUUID", characteristicUUID);
            startForegroundService(serviceIntent);
            helmetServiceReceiver();
        }

        SharedPreferences prefs = getSharedPreferences("user_session", MODE_PRIVATE);
        isCheckedIn = prefs.getBoolean("is_checked_in", false);
        boolean isServiceRunning = isServiceRunning(HelmetStatusService.class);

        if (!isServiceRunning && isCheckedIn) {
            reconnectBtn.setEnabled(true);
            reconnectBtn.setAlpha(1);
        } else if (isServiceRunning) {
            helmetServiceReceiver();
        }

    }

    private void disconnectDevice() {
        Log.e(TAG, "Disconnecting device and finishing activity.");
        statusTextView.setText("Status: Helmet not connected");
        Intent serviceIntent = new Intent(this, HelmetStatusService.class);
        stopService(serviceIntent);
        finish();
    }

    private void helmetServiceReceiver() {
        IntentFilter filter = new IntentFilter();
        filter.addAction("com.fyp.prototype.HELMET_STATUS_UPDATE");
        filter.addAction("com.fyp.prototype.FINISH_ACTIVITY");
        registerReceiver(statusReceiver, filter, Context.RECEIVER_NOT_EXPORTED);

        Intent intent = new Intent("com.fyp.prototype.HELMET_STATUS_UPDATE");
        intent.setPackage(getPackageName());
        intent.putExtra("data", "Loading"); // 替换为实际数据
        intent.putExtra("deviceName", "Loading"); // 替换为实际设备名称
        sendBroadcast(intent);
    }

    private boolean isServiceRunning(Class<?> serviceClass) {
        android.app.ActivityManager manager = (android.app.ActivityManager) getSystemService(Context.ACTIVITY_SERVICE);
        for (android.app.ActivityManager.RunningServiceInfo service : manager.getRunningServices(Integer.MAX_VALUE)) {
            if (serviceClass.getName().equals(service.service.getClassName())) {
                return true;
            }
        }
        return false;
    }

    @Override
    protected void onStart() {
        super.onStart();
        helmetServiceReceiver();
    }

    @Override
    protected void onStop() {
        super.onStop();
        unregisterReceiver(statusReceiver);
    }

    @Override
    public void onBackPressed() {
        super.onBackPressed();
        finish();
    }
}