package com.fyp.prototype;

import static com.fyp.prototype.utils.NetworkUtils.sendApiRequest;

import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.ImageButton;
import android.widget.RadioButton;
import android.widget.RadioGroup;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;
import com.fyp.prototype.utils.ApiConstants;
import com.fyp.prototype.utils.NetworkUtils;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;


public class SecurityCaseActivity extends AppCompatActivity {
    private TextView tvNewsTitle, tvNewsDate, tvNewsContent, tvQuestion;
    private RadioButton rdbOption1, rdbOption2 ,rdbOption3;
    private Button btnSubmit;
    private RadioGroup answerGroup;
    private String correctAnswer;
    private ImageButton btnBack;
    private JSONObject receivedJson;
    private String jsonString;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_security_case);
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });
        initializeViews();
        setupClickListeners();
        sendGetNewsRequest();

        if (getSupportActionBar() != null) {
            getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        }
    }
    private void initializeViews(){
        tvNewsTitle = findViewById(R.id.tvNewsTitle);
        tvNewsDate = findViewById(R.id.tvNewsDate);
        tvNewsContent = findViewById(R.id.tvNewsContent);
        tvQuestion = findViewById(R.id.tvQuestion);
        answerGroup = findViewById(R.id.answerGroup);
        rdbOption1 = findViewById(R.id.rdbOption1);
        rdbOption2 = findViewById(R.id.rdbOption2);
        rdbOption3 = findViewById(R.id.rdbOption3);
        btnSubmit = findViewById(R.id.btnSubmit);
        btnBack = findViewById(R.id.btnBack);

        jsonString = getIntent().getStringExtra("checkInRecord");

        // Parse the received JSON string into a JSONObject
//        try {
//            String jsonString = getIntent().getStringExtra("checkInRecord");
//            if (jsonString != null) {
//                receivedJson = new JSONObject(jsonString); // Parse the string into a JSONObject
//            } else {
//                receivedJson = new JSONObject(); // Initialize an empty JSONObject if no data is passed
//            }
//        } catch (JSONException e) {
//            e.printStackTrace();
//            receivedJson = new JSONObject(); // Handle invalid JSON gracefully
//        }

    }
    private void setupClickListeners(){
        //Check Submit
        btnSubmit.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                checkAnswer();
            }
        });

        btnBack.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                finish();
            }
        });
    }
    private void sendGetNewsRequest(){
        // Create a Volley request queue
        RequestQueue requestQueue = Volley.newRequestQueue(this);

        // Create a GET request to fetch the latest news
        JsonObjectRequest jsonObjectRequest = new JsonObjectRequest(
                Request.Method.GET,
                ApiConstants.LATEST_NEWS_ENDPOINT,
                null,
                response -> {
                    try {
                        JSONObject newsObject = response.getJSONObject("news");

                        // Parse the JSON response
                        String title = newsObject.getString("title");
                        String date = newsObject.getString("news_date");
                        String content = newsObject.getString("content");
                        String question = newsObject.getString("question");
                        String option1 = newsObject.getString("option_a");
                        String option2 = newsObject.getString("option_b");
                        String option3 = newsObject.getString("option_c");
                        String correctAnswer = newsObject.getString("correct_answer");


                        // Display the data in TextViews
                        tvNewsTitle.setText(title);
                        tvNewsDate.setText(date);
                        tvNewsContent.setText(content);
                        tvQuestion.setText(question);
                        rdbOption1.setText(option1);
                        rdbOption2.setText(option2);
                        rdbOption3.setText(option3);
                        this.correctAnswer = correctAnswer;

                    } catch (JSONException e) {
                        e.printStackTrace();
                        showToast("Error parsing response");
                    }
                },
                error -> {
                    // Handle errors
                    showToast("Error: " + error.getMessage());
                }
        );
        // Add the request to the Volley request queue
        requestQueue.add(jsonObjectRequest);

    }
    private void checkAnswer(){
        int selectedId = answerGroup.getCheckedRadioButtonId();
        String selectedAnswer = "";

        if (selectedId == R.id.rdbOption1) {
            selectedAnswer = "option_a";
        } else if (selectedId == R.id.rdbOption2) {
            selectedAnswer = "option_b";
        } else if (selectedId == R.id.rdbOption3) {
            selectedAnswer = "option_c";
        }

        if (selectedAnswer.isEmpty()) {
            showToast("Please select an option!");
            return;
        } else if (selectedAnswer.equals(correctAnswer)) {
            showToast("Correct answer!");
            // Upload the record only if the answer is correct
            NextStep();
        } else {
            showToast("Incorrect answer. Try again!");
        }
    }

    private void NextStep(){
        // Move to HelmetDetectorActivity
        Intent intent = new Intent(this, HelmetConnectorActivity.class);

        intent.putExtra("checkInRecord", jsonString);
        startActivity(intent);
        finish();

//        String apiUrl = ApiConstants.CHECKIN_RECORD_ENDPOINT;
//
//        try{
//            if (receivedJson == null) {
//                Log.e("SecurityCaseActivity", "Received JSON is empty");
//                showToast("No data received");
//                return;
//            }
//            sendApiRequest(apiUrl, "POST", receivedJson, new NetworkUtils.ApiCallback() {
//                @Override
//                public void onSuccess(String responseBody) {
//                    try {
//                        JSONObject responseJson = new JSONObject(responseBody);
//                        boolean success = responseJson.getBoolean("success");
//                        String message = responseJson.getString("message");
//
//                        if (success) {
//                            // Show success message
//                            showToast("Check In Successfully");
//                            Intent intent = new Intent(SecurityCaseActivity.this, MainActivity.class);
//                            startActivity(intent);
//                            finish();
//                        } else {
//                            // API responded with a failure
//                            showToast("Failed to send record: " + message);
//                        }
//                    } catch (JSONException e) {
//                        e.printStackTrace();
//                        showToast("Error parsing server response: " + e.getMessage());
//                    }
//                }
//
//                @Override
//                public void onFailure(Exception e) {
//                    // Log the error and show a toast
//                    Log.e("SecurityCaseActivity", "API request failed", e);
//
//                    if (e instanceof IOException && e.getMessage().contains("HTTP error: 409")) {
//                        // Handle HTTP 409 Conflict error
//                        runOnUiThread(() -> showToast("You have already checked in today!"));
//                        Intent intent = new Intent(SecurityCaseActivity.this, MainActivity.class);
//                        startActivity(intent);
//                        finish();
//                    } else {
//                        // Handle other errors
//                        runOnUiThread(() -> showToast("Failed to send record. Error: " + e.getMessage()));
//                        Intent intent = new Intent(SecurityCaseActivity.this, MainActivity.class);
//                        startActivity(intent);
//                        finish();
//                    }
//                }
//            });
//        }catch (Exception e){
//            e.printStackTrace();
//            showToast("Error preparing request: " + e.getMessage());
//        }
    }

    private void showToast(String message) {
        Toast.makeText(this, message, Toast.LENGTH_SHORT).show();
    }
}