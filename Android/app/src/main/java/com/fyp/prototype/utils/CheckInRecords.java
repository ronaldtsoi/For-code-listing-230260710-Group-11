package com.fyp.prototype.utils;

public class CheckInRecords {
    private int userId;
    private String worksite_name;
    private String checkInAt;
    private double latitude,longitude;

    // Getters and setters

    public CheckInRecords(int userId, String worksite_name, String checkInAt, double latitude, double longitude) {
        this.userId = userId;
        this.worksite_name = worksite_name;
        this.checkInAt = checkInAt;
        this.latitude = latitude;
        this.longitude = longitude;
    }
}