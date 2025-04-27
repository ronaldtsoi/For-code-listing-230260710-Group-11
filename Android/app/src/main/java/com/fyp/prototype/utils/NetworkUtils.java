package com.fyp.prototype.utils;

import org.json.JSONObject;

import java.io.IOException;

import okhttp3.MediaType;
import okhttp3.OkHttpClient;
import okhttp3.Request;
import okhttp3.RequestBody;
import okhttp3.Response;

public class NetworkUtils {
    private static final OkHttpClient httpClient = new OkHttpClient();

    /**
     * Sends an API request to the specified URL.
     *
     * @param url         The API endpoint.
     * @param method      The HTTP method (GET, POST, etc.).
     * @param requestBody The request body for POST/PUT requests (in JSON format).
     * @param callback    A callback interface to handle the response or errors.
     */
    public static void sendApiRequest(String url, String method, JSONObject requestBody, ApiCallback callback) {
        // Build the request
        Request.Builder requestBuilder = new Request.Builder().url(url);

        if ("POST".equalsIgnoreCase(method) && requestBody != null) {
            // Add JSON body for POST requests
            RequestBody body = RequestBody.create(
                    MediaType.parse("application/json; charset=utf-8"),
                    requestBody.toString()
            );
            requestBuilder.post(body);
        } else if (!"GET".equalsIgnoreCase(method)) {
            // Handle unsupported methods
            callback.onFailure(new IllegalArgumentException("Unsupported HTTP method: " + method));
            return;
        }

        // Execute the request in a background thread
        new Thread(() -> {
            try {
                Response response = httpClient.newCall(requestBuilder.build()).execute();

                if (response.isSuccessful()) {
                    // Pass the response body to the success callback
                    String responseBody = response.body().string();
                    callback.onSuccess(responseBody);
                } else {
                    // Handle HTTP errors
                    callback.onFailure(new IOException("HTTP error: " + response.code()));
                }
            } catch (Exception e) {
                // Handle network or parsing errors
                callback.onFailure(e);
            }
        }).start();
    }

    /**
     * A callback interface for handling API responses.
     */
    public interface ApiCallback {
        void onSuccess(String responseBody);

        void onFailure(Exception e);
    }
}