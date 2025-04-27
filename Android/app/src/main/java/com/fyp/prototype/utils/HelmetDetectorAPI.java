package com.fyp.prototype.utils;

import android.content.Context;
import android.util.Base64;
import android.util.Log;

import com.android.volley.DefaultRetryPolicy;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.ByteArrayOutputStream;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import android.graphics.Bitmap;

public class HelmetDetectorAPI {
    private static final String TAG = "ApiHelper";
    private static final String SERVER_URL = "http://230260710.xyz:5000/classify";

    private RequestQueue requestQueue;

    public HelmetDetectorAPI(Context context) {
        requestQueue = Volley.newRequestQueue(context);
    }

    public interface ApiCallback {
        void onSuccess(boolean isWorn);
        void onError(String error);
    }

    // Convert Bitmap to Base64 string
    private String encodeImageToBase64(Bitmap bitmap) {
        ByteArrayOutputStream byteArrayOutputStream = new ByteArrayOutputStream();
        bitmap.compress(Bitmap.CompressFormat.JPEG, 90, byteArrayOutputStream);
        byte[] imageBytes = byteArrayOutputStream.toByteArray();
        return Base64.encodeToString(imageBytes, Base64.DEFAULT);
    }

    // Upload image to Flask server
    public void uploadImage(Bitmap bitmap, ApiCallback callback) {

        String base64Image = encodeImageToBase64(resizeImage(bitmap));

        try {
            JSONObject jsonRequest = new JSONObject();
            jsonRequest.put("image", base64Image);

            JsonObjectRequest jsonObjectRequest = new JsonObjectRequest(Request.Method.POST, SERVER_URL, jsonRequest,
                    new Response.Listener<JSONObject>() {
                        @Override
                        public void onResponse(JSONObject response) {
                            try {
                                // 解析 API 返回的 label
                                String label = response.getString("label");
                                boolean isWorn = false;
                                //no_helmet, not_strapped, strapped
                                switch (label) {
                                    case "no_helmet":
                                    case "not_strapped":
                                        break;
                                    default:
                                        isWorn = true;
                                }
                                callback.onSuccess(isWorn);
                            } catch (JSONException e) {
                                callback.onError("Response Parsing Error：" + e.getMessage());
                                Log.e(TAG, "Response Parsing Error", e);
                            }
                        }
                    },
                    new Response.ErrorListener() {
                        @Override
                        public void onErrorResponse(VolleyError error) {
                            callback.onError("uploadImage Error：" + error.getMessage());
                            Log.e(TAG, "uploadImage Error", error);
                        }
                    }) {
                @Override
                public Map<String, String> getHeaders() {
                    Map<String, String> headers = new HashMap<>();
                    headers.put("Content-Type", "application/json");
                    return headers;
                }
            };

            // Increase timeout settings
            jsonObjectRequest.setRetryPolicy(new DefaultRetryPolicy(
                    30000, // 30 seconds timeout
                    DefaultRetryPolicy.DEFAULT_MAX_RETRIES,
                    DefaultRetryPolicy.DEFAULT_BACKOFF_MULT));

            requestQueue.add(jsonObjectRequest);
        } catch (JSONException e) {
            e.printStackTrace();
            callback.onError("JSON Error：" + e.getMessage());
        }
    }

    private Bitmap resizeImage(Bitmap original) {
        int maxSize = 640; // Maximum size for width or height

        int width = original.getWidth();
        int height = original.getHeight();

        if (width <= maxSize && height <= maxSize) {
            return original; // No resizing needed
        }

        float scale = Math.min((float) maxSize / width, (float) maxSize / height);
        int newWidth = Math.round(width * scale);
        int newHeight = Math.round(height * scale);

        return Bitmap.createScaledBitmap(original, newWidth, newHeight, true);
    }
}
