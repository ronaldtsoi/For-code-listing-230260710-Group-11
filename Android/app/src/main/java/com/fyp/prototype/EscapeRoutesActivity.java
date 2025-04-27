package com.fyp.prototype;


import static com.fyp.prototype.utils.NetworkUtils.sendApiRequest;

import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.RadioButton;
import android.widget.RadioGroup;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import com.fyp.prototype.utils.*;
import com.squareup.picasso.Picasso;

import org.json.JSONObject;

import java.util.concurrent.TimeUnit;

import okhttp3.OkHttpClient;

public class EscapeRoutesActivity extends AppCompatActivity {
    private TextView tvQuestion;
    private Button btnSubmit;
    private RadioButton rdbOption1, rdbOption2 ,rdbOption3;
    private RadioGroup answerGroup;
    private ImageButton btnBack;
    private ImageView imvMapImage;
    private String receivedJson;
    private String correctAnswer;
    private static final OkHttpClient httpClient = new OkHttpClient.Builder()
            .connectTimeout(10, TimeUnit.SECONDS) // 设置连接超时时间
            .readTimeout(30, TimeUnit.SECONDS)    // 设置读取超时时间
            .writeTimeout(10, TimeUnit.SECONDS)   // 设置写入超时时间
            .build();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_escape_routes);
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        initializeViews();
        sendEscapeRouteRequest();
        setupClickListeners();
    }

    private void setupClickListeners() {
        btnBack.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                finish();
            }
        });

        btnSubmit.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                // Get the selected RadioButton ID
                int selectedId = answerGroup.getCheckedRadioButtonId();
                String selectedAnswer = "";
                if (selectedId == -1) {
                    // No option selected
                    showToast("Please select an answer");
                    return;
                }
                // Find the selected RadioButton
                RadioButton selectedRadioButton = findViewById(selectedId);

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
                    showToast("Correct answer!");
                    Intent intent = new Intent(EscapeRoutesActivity.this, SecurityCaseActivity.class);
                    intent.putExtra("checkInRecord", receivedJson);
                    startActivity(intent);
                    finish();
                } else {
                    showToast("Incorrect answer. Try again!");
                }
            }
        });
    }

    private void initializeViews() {
        // Return to previous page
        if (getSupportActionBar() != null) {
            getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        }
        btnBack = findViewById(R.id.btnBack);
        btnSubmit = findViewById(R.id.btnSubmit);
        imvMapImage = findViewById(R.id.imvMapImage);
        tvQuestion = findViewById(R.id.tvQuestion);
        answerGroup = findViewById(R.id.answerGroup);
        rdbOption1 = findViewById(R.id.rdbOption1);
        rdbOption2 = findViewById(R.id.rdbOption2);
        rdbOption3 = findViewById(R.id.rdbOption3);

        receivedJson = getIntent().getStringExtra("checkInRecord");
    }


    private void sendEscapeRouteRequest() {
        String apiUrl = ApiConstants.MAP_IMAGE_URL;

        try {
            // 检查 receivedJson 是否为空
            if (receivedJson == null || receivedJson.isEmpty()) {
                Log.e("EscapeRoutesActivity", "Received JSON is empty");
                showToast("No data received");
                return;
            }

            // 从 JSON 中提取参数
            JSONObject receivedData = new JSONObject(receivedJson);
            String worksite_name = receivedData.getString("worksite_name");
            double latitude = receivedData.getDouble("latitude");
            double longitude = receivedData.getDouble("longitude");

            // 构建请求体
            JSONObject requestBody = new JSONObject();
            requestBody.put("worsite_name", worksite_name);
            requestBody.put("latitude", latitude);
            requestBody.put("longitude", longitude);

            // 发送 API 请求
            sendApiRequest(apiUrl, "POST", requestBody, new NetworkUtils.ApiCallback() {


                private String correctAnswer;

                @Override
                public void onSuccess(String responseBody) {
                    try {
                        JSONObject responseJson = new JSONObject(responseBody);
                        boolean success = responseJson.getBoolean("success");

                        if (success) {
                            // 成功返回数据
                            JSONObject data = responseJson.getJSONObject("data");
                            String question = data.getString("question");
                            String option1 = data.getString("option_a");
                            String option2 = data.getString("option_b");
                            String option3 = data.getString("option_c");
                            EscapeRoutesActivity.this.correctAnswer = data.getString("correct_answer");
                            String imageUrl = data.getString("image_path");

                            // 更新 UI
                            runOnUiThread(() -> {
                                tvQuestion.setText(question);
                                rdbOption1.setText(option1);
                                rdbOption2.setText(option2);
                                rdbOption3.setText(option3);

                                Picasso.get()
                                        .load(imageUrl)
                                        .placeholder(android.R.drawable.ic_menu_gallery)
                                        .error(android.R.drawable.ic_delete)
                                        .into(imvMapImage);
                            });
                        } else {
                            // 处理服务器返回的错误消息
                            String errorMessage = responseJson.getString("message");
                            runOnUiThread(() -> showToast(errorMessage));
                        }
                    } catch (Exception e) {
                        e.printStackTrace();
                        runOnUiThread(() -> showToast("Error parsing response: " + e.getMessage()));
                    }
                }

                @Override
                public void onFailure(Exception e) {
                    // 处理请求失败的情况
                    e.printStackTrace();
                    runOnUiThread(() -> showToast("Request failed: " + e.getMessage()));
                    Intent intent = new Intent(EscapeRoutesActivity.this, SecurityCaseActivity.class);
                    intent.putExtra("checkInRecord", receivedJson);
                    startActivity(intent);
                    finish();
                }
            });
        } catch (Exception e) {
            e.printStackTrace();
            showToast("Error preparing request: " + e.getMessage());
        }
    }

    private void showToast(String message) {
        Toast.makeText(this, message, Toast.LENGTH_SHORT).show();
    }

}