package com.fyp.prototype.utils;
/**
 * API interface address constant class
 * Contains all backend interface endpoint configurations
 * API接口地址常量类
 * 包含所有后端接口端点配置
 */
public class ApiConstants {
    public static final String BASE_URL = "https://james.sl94.i.ng/android_api/";

    // Login interface endpoint (POST)
    public static final String LOGIN_ENDPOINT = BASE_URL + "login.php";

    //Register interface endpoint (POST)
    public static final String REGISTER_ENDPOINT = BASE_URL + "register.php";

    // Latest news endpoint (GET)
    public static final String LATEST_NEWS_ENDPOINT = BASE_URL + "latest_news.php";

    public static final String MAP_IMAGE_URL = BASE_URL + "get_map_image.php";

    public static final String WORKSITES_ENDPOINT = BASE_URL + "get_worksites.php";
    public static final String HELMET_RECORD_ENDPOINT = BASE_URL + "helmet_record.php";
    public static final String CHECKIN_RECORD_ENDPOINT = BASE_URL + "check_in_record_handler.php";
    public static final String HISTORY_ENDPOINT = BASE_URL + "get_checkin_history.php";
}
