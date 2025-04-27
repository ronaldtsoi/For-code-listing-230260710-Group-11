package com.fyp.prototype;

import android.content.Intent;
import android.content.pm.PackageManager;
import android.os.Bundle;
import android.util.Log;
import android.util.SparseArray;
import android.view.SurfaceHolder;
import android.view.SurfaceView;
import android.widget.Button;
import android.widget.TextView;

import androidx.activity.EdgeToEdge;
import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.ActivityCompat;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import com.google.android.gms.vision.CameraSource;
import com.google.android.gms.vision.Detector;
import com.google.android.gms.vision.barcode.Barcode;
import com.google.android.gms.vision.barcode.BarcodeDetector;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

public class HelmetConnectorActivity extends AppCompatActivity {
    private final String TAG = "HelmetConnectorActivity";
    private SurfaceView surfaceView;
    private TextView tvUUID;
    private CameraSource cameraSource;
    private Button btnConnect;
    private String scanResult, jsonString;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_helmet_connector);
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        surfaceView = findViewById(R.id.surfaceView);
        tvUUID = findViewById(R.id.tvUUID);
        btnConnect = findViewById(R.id.btnConnect);
        jsonString = getIntent().getStringExtra("checkInRecord");

        BarcodeDetector barcodeDetector = new BarcodeDetector.Builder(this)
                .setBarcodeFormats(Barcode.QR_CODE).build();

        barcodeDetector.setProcessor(new Detector.Processor<Barcode>(){

            @Override
            public void release() {

            }

            @Override
            public void receiveDetections(@NonNull Detector.Detections<Barcode> detections) {
                final SparseArray<Barcode> qrCodes=detections.getDetectedItems();
                if(qrCodes.size()!=0){
                    scanResult = qrCodes.valueAt(0).displayValue;
                    boolean isUUID;
                    JSONObject jsonObject = null;
                    try {
                        jsonObject = new JSONObject(scanResult);
                        isUUID = checkJSONString(jsonObject);
                        if (isUUID) {
                            String serviceUUID = jsonObject.getString("service");
                            String characteristicUUID = jsonObject.getString("characteristic");
                            tvUUID.post(() -> tvUUID.setText("Service UUID: " + serviceUUID + "\nCharacteristic UUID: " + characteristicUUID));
                        } else {
                            tvUUID.post(() -> tvUUID.setText("Invalid QR code"));
                        }
                    } catch (JSONException e) {
                        tvUUID.post(() -> tvUUID.setText("Invalid QR code"));
                    }
                }
            }
        });

        cameraSource = new CameraSource.Builder(this, barcodeDetector)
                //.setRequestedPreviewSize(300, 300) // 可以自訂預覽視窗畫面內容大小
                .setAutoFocusEnabled(true) // 自動對焦
                .build();

        ShowCamera();

        btnConnect.setOnClickListener(v -> {
            // Check if the scan result is not null or in UUID json
            if (scanResult != null) {
                try {
                    JSONObject jsonObject = new JSONObject(scanResult);
                    boolean isUUID = checkJSONString(jsonObject);
                    if (isUUID) {
                        String serviceUUID = jsonObject.getString("service");
                        String characteristicUUID = jsonObject.getString("characteristic");

                        Intent intent = new Intent(HelmetConnectorActivity.this, HelmetStatusActivity.class);
                        intent.putExtra("checkInRecord", jsonString);
                        intent.putExtra("serviceUUID", serviceUUID);
                        intent.putExtra("characteristicUUID", characteristicUUID);
                        startActivity(intent);
                    } else {
                        tvUUID.setText("Invalid QR code");
                    }
                } catch (JSONException e) {
                    tvUUID.setText("Invalid QR code");
                }

            } else {
                tvUUID.setText("No QR code detected");
            }
        });
    }

    private boolean checkJSONString(JSONObject jsonObject) {
        if (jsonObject.has("service") && jsonObject.has("characteristic")) {
            return true;
        } else {
            tvUUID.setText("Invalid QR code");
            return false;
        }
    }

    private void ShowCamera() {
        surfaceView.getHolder().addCallback(new SurfaceHolder.Callback() {
            @Override
            public void surfaceCreated(@NonNull SurfaceHolder holder) {
                try {
                    if (ActivityCompat.checkSelfPermission(HelmetConnectorActivity.this, android.Manifest.permission.CAMERA) != PackageManager.PERMISSION_GRANTED) {
                        return;
                    }
                    cameraSource.start(holder);
                } catch (IOException e) {
                    Log.e(TAG, e.getMessage());

                }
            }

            @Override
            public void surfaceChanged(@NonNull SurfaceHolder holder, int format, int width, int height) {

            }

            @Override
            public void surfaceDestroyed(@NonNull SurfaceHolder holder) {
                cameraSource.stop();
            }
        });
    }

    @Override
    protected void onRestart() {
        super.onRestart();
        if (jsonString == null) {
            finish();
        }
    }
}