package com.fyp.prototype.utils;

public class CheckinRecord {
    private String worksiteName;
    private String checkInAt;  // Store as formatted String or Date

    public CheckinRecord(String worksiteName, String checkInAt) {
        this.worksiteName = worksiteName;
        this.checkInAt = checkInAt;
    }

    public String getWorksiteName() {
        return worksiteName;
    }

    public String getCheckInAt() {
        return checkInAt;
    }
}
