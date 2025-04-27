package com.fyp.prototype.utils;

import android.view.LayoutInflater;
import android.view.*;
import android.widget.TextView;

import androidx.recyclerview.widget.RecyclerView;
import androidx.annotation.NonNull;

import com.fyp.prototype.*;

import java.util.*;

public class CheckinHistoryAdapter extends RecyclerView.Adapter<CheckinHistoryAdapter.ViewHolder> {

    private List<CheckinRecord> records;

    public CheckinHistoryAdapter(List<CheckinRecord> records) {
        this.records = records;
    }

    public void updateData(List<CheckinRecord> newRecords) {
        this.records = newRecords;
        notifyDataSetChanged();
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_checkin_record, parent, false);
        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        CheckinRecord record = records.get(position);

        holder.tvWorksiteName.setText("Worksite name: " + record.getWorksiteName());

        String timestamp = record.getCheckInAt();
        String date = "";
        String time = "";

        if (timestamp != null && timestamp.contains(" ")) {
            String[] parts = timestamp.split(" ");
            date = parts[0];
            time = parts[1];
        }

        holder.tvDate.setText("Date: " + date);
        holder.tvTime.setText("Time: " + time);
    }

    @Override
    public int getItemCount() {
        return records.size();
    }

    static class ViewHolder extends RecyclerView.ViewHolder {
        TextView tvWorksiteName, tvDate, tvTime;

        ViewHolder(@NonNull View itemView) {
            super(itemView);
            tvWorksiteName = itemView.findViewById(R.id.tv_worksite_name);
            tvDate = itemView.findViewById(R.id.tv_date);
            tvTime = itemView.findViewById(R.id.tv_time);
        }
    }
}

