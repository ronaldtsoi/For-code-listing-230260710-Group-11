package com.fyp.prototype;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;
import com.fyp.prototype.utils.ApiConstants;
import com.fyp.prototype.utils.CheckinRecord;
import com.fyp.prototype.utils.CheckinHistoryAdapter;

import android.net.Uri;
import android.os.Bundle;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import androidx.appcompat.widget.Toolbar;
import android.view.MenuItem;
import android.app.DatePickerDialog;
import android.widget.DatePicker;

import org.json.*;

import java.util.*;

public class HistoryActivity extends AppCompatActivity {

    private RecyclerView rvCheckinHistory;
    private EditText etFilterWorksite, etFilterDate;
    private Button btnApplyFilters;
    private CheckinHistoryAdapter adapter;
    private int userId;
    private Button btnBack;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_history);

        userId = getIntent().getIntExtra("uid", -1);
        rvCheckinHistory = findViewById(R.id.rv_checkin_history);
        etFilterWorksite = findViewById(R.id.et_filter_worksite);
        etFilterDate = findViewById(R.id.et_filter_date);
        btnApplyFilters = findViewById(R.id.btn_apply_filters);

        rvCheckinHistory.setLayoutManager(new LinearLayoutManager(this));
        adapter = new CheckinHistoryAdapter(new ArrayList<>());
        rvCheckinHistory.setAdapter(adapter);

        btnApplyFilters.setOnClickListener(v -> fetchCheckinHistory());

        btnBack = findViewById(R.id.btn_back);
        btnBack.setOnClickListener(v -> onBackPressed());

        etFilterDate.setOnClickListener(v -> {
            final Calendar calendar = Calendar.getInstance();
            int year = calendar.get(Calendar.YEAR);
            int month = calendar.get(Calendar.MONTH);
            int day = calendar.get(Calendar.DAY_OF_MONTH);

            DatePickerDialog datePickerDialog = new DatePickerDialog(this, (view, selectedYear, selectedMonth, selectedDay) -> {
                String dateStr = String.format(Locale.getDefault(), "%04d-%02d-%02d", selectedYear, selectedMonth + 1, selectedDay);
                etFilterDate.setText(dateStr);
            }, year, month, day);

            datePickerDialog.show();
        });

        fetchCheckinHistory();
    }


    private void fetchCheckinHistory() {
        String worksiteFilter = etFilterWorksite.getText().toString().trim();
        String dateFilter = etFilterDate.getText().toString().trim(); // date input in "YYYY-MM-DD" format

        getCheckinHistoryFromDb(userId, worksiteFilter, dateFilter, new HistoryCallback() {
            @Override
            public void onSuccess(List<CheckinRecord> records) {
                runOnUiThread(() -> adapter.updateData(records));
            }

            @Override
            public void onFailure(String errorMessage) {
                runOnUiThread(() -> Toast.makeText(HistoryActivity.this, errorMessage, Toast.LENGTH_SHORT).show());
            }
        });
    }


    private void getCheckinHistoryFromDb(int userId, String worksiteName, String date, final HistoryCallback callback) {
        RequestQueue requestQueue = Volley.newRequestQueue(this);

        Uri.Builder uriBuilder = Uri.parse(ApiConstants.HISTORY_ENDPOINT).buildUpon();
        uriBuilder.appendQueryParameter("user_id", String.valueOf(userId));
        if (date != null && !date.isEmpty()) {
            uriBuilder.appendQueryParameter("date", date);  // format: "YYYY-MM-DD"
        }
        if (worksiteName != null && !worksiteName.isEmpty()) {
            uriBuilder.appendQueryParameter("worksite_name", worksiteName);
        }

        String url = uriBuilder.build().toString();

        JsonObjectRequest jsonObjectRequest = new JsonObjectRequest(
                Request.Method.GET,
                url,
                null,
                response -> {
                    try {
                        if (response.getBoolean("success")) {
                            JSONArray dataArray = response.getJSONArray("data");
                            List<CheckinRecord> records = new ArrayList<>();

                            for (int i = 0; i < dataArray.length(); i++) {
                                JSONObject obj = dataArray.getJSONObject(i);
                                String worksite = obj.getString("worksite_name");
                                String checkInAt = obj.getString("checkIn_at");

                                records.add(new CheckinRecord(worksite, checkInAt));
                            }

                            callback.onSuccess(records);
                        } else {
                            callback.onFailure(response.optString("message", "Failed to fetch data"));
                        }
                    } catch (JSONException e) {
                        e.printStackTrace();
                        callback.onFailure("JSON parsing error");
                    }
                },
                error -> {
                    error.printStackTrace();
                    callback.onFailure("Network error");
                }
        );

        requestQueue.add(jsonObjectRequest);
    }


    public interface HistoryCallback {
        void onSuccess(List<CheckinRecord> records);
        void onFailure(String errorMessage);
    }


}
