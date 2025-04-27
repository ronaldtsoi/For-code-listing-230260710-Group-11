package com.fyp.prototype.utils;

import android.app.Service;
import android.content.Context;
import android.content.Intent;
import android.location.Address;
import android.location.Geocoder;
import android.location.Location;
import android.location.LocationListener;
import android.location.LocationManager;
import android.os.Bundle;
import android.os.IBinder;
import android.widget.Toast;

import java.util.List;
import java.util.Locale;

public class LocationFetcher extends Service implements LocationListener {

    private final Context con;

    boolean GPSEnable=false;
    boolean NetWorkEnabled=false;
    boolean LocationEnabled=false;
    private static final long MIN_DISTANCE_TO_REQUEST_LOCATION=1;
    private static final long MIN_TIME_FOR_UPDATES=1000*1;

    Location location;
    double latitude,longitude;
    String address;
    LocationManager locationManager;


    public LocationFetcher(Context context )
    {
        this.con=context;
        checkIfLocationAvailable();
    }

    public Location checkIfLocationAvailable()
    {
        try
        {
            locationManager=(LocationManager)con.getSystemService(LOCATION_SERVICE);

            GPSEnable=locationManager.isProviderEnabled(LocationManager.GPS_PROVIDER);

            NetWorkEnabled=locationManager.isProviderEnabled(LocationManager.NETWORK_PROVIDER);
            if(!GPSEnable && !NetWorkEnabled)
            {
                LocationEnabled=false;

                Toast.makeText(con,"Provider Not Enabled", Toast.LENGTH_SHORT).show();
            }
            else {
                LocationEnabled=true;

                if(NetWorkEnabled)
                {
                    locationManager.requestLocationUpdates(LocationManager.NETWORK_PROVIDER,MIN_TIME_FOR_UPDATES,MIN_DISTANCE_TO_REQUEST_LOCATION,this);
                    if(locationManager!=null)
                    {
                        location=locationManager.getLastKnownLocation(LocationManager.NETWORK_PROVIDER);
                        if(location!=null)
                        {
                            latitude=location.getLatitude();
                            longitude=location.getLongitude();
                        }
                    }
                }
                if(GPSEnable)
                {
                    locationManager.requestLocationUpdates(LocationManager.GPS_PROVIDER,MIN_TIME_FOR_UPDATES,MIN_DISTANCE_TO_REQUEST_LOCATION,this);
                    if(locationManager!=null)
                    {
                        location=locationManager.getLastKnownLocation(LocationManager.GPS_PROVIDER);
                        if(location!=null)
                        {
                            latitude=location.getLatitude();
                            longitude=location.getLongitude();
                        }
                    }
                }
            }
        }catch (Exception e)
        {
        }
        return location;
    }


    public double getLatitude()
    {
        if(location!=null)
        {
            latitude=location.getLatitude();
        }
        return latitude;
    }

    public double getLongitude()
    {
        if(location!=null)
        {
            longitude=location.getLongitude();
        }
        return longitude;
    }


    public String getAddress()
    {
        if(location!=null)
        {
            Geocoder geocoder = new Geocoder(con, Locale.getDefault());
            try {
                List<Address> addresses = geocoder.getFromLocation(location.getLatitude(), location.getLongitude(),1);
                address = addresses.get(0).getAddressLine(0);
            }
            catch (Exception e)
            {
                address = "Address not found";

            }
        }
        return address;
    }


    @Override
    public void onLocationChanged(Location location) {
    }
    @Override
    public void onProviderDisabled(String provider) {
    }
    @Override
    public void onProviderEnabled(String provider) {
    }
    @Override
    public void onStatusChanged(String provider, int status, Bundle extras) {
    }
    @Override
    public IBinder onBind(Intent intent) {
        return null;
    }

}
