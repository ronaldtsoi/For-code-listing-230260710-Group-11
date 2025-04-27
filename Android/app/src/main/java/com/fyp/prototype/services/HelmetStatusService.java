package com.fyp.prototype.services;

import android.app.Notification;
import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.app.Service;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.IBinder;
import android.util.Log;

import com.android.volley.Request;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;
import com.fyp.prototype.utils.ApiConstants;
import com.fyp.prototype.utils.HelmetStatus;
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import com.polidea.rxandroidble3.RxBleClient;
import com.polidea.rxandroidble3.RxBleDevice;
import com.polidea.rxandroidble3.scan.ScanResult;
import com.polidea.rxandroidble3.scan.ScanSettings;

import org.json.JSONException;
import org.json.JSONObject;
import org.json.JSONArray;

import java.lang.reflect.Type;
import java.util.UUID;
import java.util.ArrayList;
import java.util.List;

import io.reactivex.rxjava3.disposables.Disposable;

public class HelmetStatusService extends Service {
    private static final String TAG = "HelmetStatusService";
    private static final String CHANNEL_ID = "HelmetStatusChannel";
    private RxBleClient rxBleClient;
    private Disposable scanDisposable;
    private Disposable connectionDisposable;
    private UUID serviceUUID, characteristicUUID;
    private boolean strapFastened = false;
    private Boolean lastStrapFastened = null;
    private final List<HelmetStatus> statusList = new ArrayList<>();


    @Override
    public void onCreate() {
        super.onCreate();
        createNotificationChannel();
        startForeground(1, getNotification());

        rxBleClient = RxBleClient.create(this);
        loadStatusListFromPreferences();
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        if (intent != null) {
            serviceUUID = UUID.fromString(intent.getStringExtra("serviceUUID"));
            characteristicUUID = UUID.fromString(intent.getStringExtra("characteristicUUID"));
            Log.d(TAG, "Connection established before timeout.");
            startScan(); // 开始扫描设备
            new android.os.Handler().postDelayed(() -> {
                if (connectionDisposable == null || connectionDisposable.isDisposed()) {
                    Log.e(TAG, "Connection timeout. Stopping service.");
                    Intent intentFin = new Intent("com.fyp.prototype.FINISH_ACTIVITY");
                    intentFin.setPackage(getPackageName());
                    sendBroadcast(intentFin);
                    Log.e(TAG, "Finished intent sent.");
                } else {
                    Log.d(TAG, "Connection established before timeout.");
                }
            }, 5000);
        }
        return START_STICKY;
    }

    private void startScan() {
        Log.d(TAG, "Starting BLE scan...");
        scanDisposable = rxBleClient.scanBleDevices(new ScanSettings.Builder().build())
                .subscribe(
                        this::onDeviceFound,
                        throwable -> Log.e(TAG, "Scan error: " + throwable.getMessage())
                );
    }

    private void onDeviceFound(ScanResult scanResult) {
        if (scanResult.getScanRecord() != null && scanResult.getScanRecord().getServiceUuids() != null) {
            scanResult.getScanRecord().getServiceUuids().forEach(parcelUuid -> {
                if (parcelUuid.getUuid().equals(serviceUUID)) {
                    Log.d(TAG, "Target device found: " + scanResult.getBleDevice().getName());
                    scanDisposable.dispose(); // 停止扫描
                    connectToDevice(scanResult.getBleDevice());
                }
            });
        }
    }

    private void connectToDevice(RxBleDevice device) {
        connectionDisposable = device.establishConnection(false)
                .flatMap(rxBleConnection -> rxBleConnection.setupNotification(characteristicUUID))
                .flatMap(notificationObservable -> notificationObservable)
                .subscribe(
                        bytes -> {
                            strapFastened = bytes[0] == 1;
                            Log.d(TAG, "Strap Fastened: " + strapFastened);

                            if (lastStrapFastened == null || lastStrapFastened != strapFastened) {
                                lastStrapFastened = strapFastened;
                                Log.d(TAG, "Status changed: Strap Fastened: " + strapFastened);
                                sendStatusBroadcast(strapFastened, device.getName());
                            }
                        },
                        throwable -> Log.e(TAG, "Connection error: " + throwable.getMessage())
                );
    }

    private Notification getNotification() {
        return new Notification.Builder(this, CHANNEL_ID)
                .setContentTitle("Safety Helmet Connection")
                .setContentText("Helmet Status Service Running")
                .setSmallIcon(android.R.drawable.ic_menu_info_details)
                .build();
    }

    private void createNotificationChannel() {
        NotificationChannel channel = new NotificationChannel(
                CHANNEL_ID,
                "Helmet Status Service",
                NotificationManager.IMPORTANCE_LOW
        );
        NotificationManager manager = getSystemService(NotificationManager.class);
        if (manager != null) {
            manager.createNotificationChannel(channel);
        }
    }

    private void sendStatusBroadcast(boolean strapFastened, String deviceName) {

        HelmetStatus currentStatus = new HelmetStatus(strapFastened);
        statusList.add(currentStatus);
        Log.d(TAG, "Saved status: " + currentStatus.timestamp + ", Strap Fastened: " + currentStatus.strapFastened);

        Intent intent = new Intent("com.fyp.prototype.HELMET_STATUS_UPDATE");
        intent.setPackage(getPackageName());
        intent.putExtra("deviceName", deviceName);
        intent.putExtra("data", strapFastened ? "Strap Fastened" : "Strap Not Fastened");
        sendBroadcast(intent);
        Log.d(TAG, "Broadcast sent: " + strapFastened + ", Device: " + deviceName);
    }

    private void sendRecordRequest() {
        if (statusList.isEmpty()) {
            Log.d(TAG, "No data to upload.");
            return;
        }
        String json = new Gson().toJson(statusList);

        JSONObject payload = new JSONObject();
        try {
            SharedPreferences prefs = getSharedPreferences("user_session", MODE_PRIVATE);
            int uid = prefs.getInt("user_id", 0);
            payload.put("user_id", uid);
            payload.put("statusList", new JSONArray(json));

            Log.d(TAG, "Payload: " + payload.toString());

        } catch (JSONException e) {
            e.printStackTrace();
        }

        JsonObjectRequest request = new JsonObjectRequest(
                Request.Method.POST,
                ApiConstants.HELMET_RECORD_ENDPOINT,
                payload,
                response -> {
                    try {
                        if (response.getBoolean("success")) {
                            Log.d(TAG, "Data uploaded successfully.");
                            statusList.clear();
                            clearStatusListFromPreferences();
                        } else {
                            Log.d(TAG, "Server error: " + response.getString("message"));
                        }
                    } catch (JSONException e) {
                        Log.e(TAG, "Response parsing error: " + e.getMessage());
                    }
                },
                error -> {
                    Log.e(TAG, "Upload error: " + error.getMessage());
                }
        );

        Volley.newRequestQueue(this).add(request);
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        if (scanDisposable != null && !scanDisposable.isDisposed()) {
            scanDisposable.dispose();
        }
        if (connectionDisposable != null && !connectionDisposable.isDisposed()) {
            connectionDisposable.dispose();
        }
        HelmetStatus currentStatus = new HelmetStatus(strapFastened);
        statusList.add(currentStatus);
        Log.d(TAG, "Last Saved status: " + currentStatus.timestamp + ", Strap Fastened: " + currentStatus.strapFastened);
        saveStatusListToPreferences();
        sendRecordRequest();
    }

    @Override
    public IBinder onBind(Intent intent) {
        return null;
    }

    private void saveStatusListToPreferences() {
        SharedPreferences prefs = getSharedPreferences("helmet_status", MODE_PRIVATE);
        SharedPreferences.Editor editor = prefs.edit();
        String json = new Gson().toJson(statusList);
        editor.putString("status_list", json);
        editor.apply();
    }

    private void loadStatusListFromPreferences() {
        SharedPreferences prefs = getSharedPreferences("helmet_status", MODE_PRIVATE);
        String jsonString = prefs.getString("status_list", null);
        if (jsonString != null) {
            Type type = new TypeToken<List<HelmetStatus>>() {}.getType();
            List<HelmetStatus> savedList = new Gson().fromJson(jsonString, type);
            statusList.addAll(savedList);
        }
    }

    private void clearStatusListFromPreferences() {
        SharedPreferences prefs = getSharedPreferences("helmet_status", MODE_PRIVATE);
        SharedPreferences.Editor editor = prefs.edit();
        editor.remove("status_list");
        editor.apply();
    }
}