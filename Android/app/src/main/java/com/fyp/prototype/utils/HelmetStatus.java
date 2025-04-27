package com.fyp.prototype.utils;

import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Locale;

public class HelmetStatus {
    public boolean strapFastened;
    public String timestamp;

    public HelmetStatus(boolean strapFastened) {
        this.timestamp = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.getDefault()).format(new Date());
        this.strapFastened = strapFastened;
    }
}

