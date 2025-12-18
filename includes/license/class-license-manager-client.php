<?php
/**
 * Nias License Manager Client Class
 * کلاس کلاینت مدیریت لایسنس نیاس
 *
 * @package NLMW
 * @version 1.0.0
 * 
 * Description: Main client class for interacting with License Manager for WooCommerce API
 * توضیحات: کلاس اصلی برای تعامل با API مدیریت لایسنس ووکامرس
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly | خروج در صورت دسترسی مستقیم
}

/**
 * Class Nias_License_Manager_Client
 * 
 * Handles all API communications with License Manager for WooCommerce
 * مدیریت تمام ارتباطات API با مدیریت لایسنس ووکامرس
 */
class Nias_License_Manager_Client {

    /**
     * API Store URL
     * آدرس فروشگاه API
     * 
     * @var string
     */
    private $store_url;

    /**
     * API Consumer Key
     * کلید مصرف‌کننده API
     * 
     * @var string
     */
    private $consumer_key;

    /**
     * API Consumer Secret
     * رمز مصرف‌کننده API
     * 
     * @var string
     */
    private $consumer_secret;

    /**
     * API Version
     * نسخه API
     * 
     * @var string
     */
    private $api_version = 'v2';

    /**
     * Last Error Message
     * آخرین پیام خطا
     * 
     * @var string|null
     */
    private $last_error = null;

    /**
     * Constructor
     * سازنده کلاس
     * 
     * @param string $store_url Store URL | آدرس فروشگاه
     * @param string $consumer_key Consumer Key | کلید مصرف‌کننده
     * @param string $consumer_secret Consumer Secret | رمز مصرف‌کننده
     */
    public function __construct( $store_url = '', $consumer_key = '', $consumer_secret = '' ) {
        $this->store_url = rtrim( $store_url, '/' );
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
    }

    /**
     * Validate License Key
     * اعتبارسنجی کلید لایسنس
     * 
     * Checks if the license is valid and returns full license data
     * بررسی اعتبار لایسنس و بازگشت اطلاعات کامل لایسنس
     * 
     * @param string $license_key License key to validate | کلید لایسنس برای اعتبارسنجی
     * @return array|false License data or false on failure | اطلاعات لایسنس یا false در صورت خطا
     */
    public function nias_validate_license( $license_key ) {
        $endpoint = sprintf( '/licenses/validate/%s', sanitize_text_field( $license_key ) );
        $response = $this->nias_make_request( $endpoint, 'GET' );

        if ( $response && isset( $response['success'] ) && $response['success'] === true ) {
            return $response['data'];
        }

        return false;
    }

    /**
     * Activate License
     * فعال‌سازی لایسنس
     * 
     * Activates a license and increments activation count
     * فعال‌سازی لایسنس و افزایش تعداد فعال‌سازی‌ها
     * 
     * @param string $license_key License key to activate | کلید لایسنس برای فعال‌سازی
     * @param array $params Additional parameters (label, meta_data) | پارامترهای اضافی
     * @return array|false Activation data or false on failure | اطلاعات فعال‌سازی یا false در صورت خطا
     */
    public function nias_activate_license( $license_key, $params = array() ) {
        $endpoint = sprintf( '/licenses/activate/%s', sanitize_text_field( $license_key ) );
        $response = $this->nias_make_request( $endpoint, 'GET', $params );

        if ( $response && isset( $response['success'] ) && $response['success'] === true ) {
            // Store activation token for later deactivation
            // ذخیره توکن فعال‌سازی برای غیرفعال‌سازی بعدی
            if ( isset( $response['data']['activationData']['token'] ) ) {
                update_option( 'nias_activation_token_' . md5( $license_key ), $response['data']['activationData']['token'] );
            }
            return $response['data'];
        }

        return false;
    }

    /**
     * Deactivate License
     * غیرفعال‌سازی لایسنس
     * 
     * Deactivates a license using activation token
     * غیرفعال‌سازی لایسنس با استفاده از توکن فعال‌سازی
     * 
     * @param string $license_key License key to deactivate | کلید لایسنس برای غیرفعال‌سازی
     * @param string $token Activation token (optional) | توکن فعال‌سازی (اختیاری)
     * @return array|false Deactivation data or false on failure | اطلاعات غیرفعال‌سازی یا false در صورت خطا
     */
    public function nias_deactivate_license( $license_key, $token = '' ) {
        $endpoint = sprintf( '/licenses/deactivate/%s', sanitize_text_field( $license_key ) );
        
        // If no token provided, try to get stored token
        // اگر توکن ارائه نشده، سعی در دریافت توکن ذخیره شده
        if ( empty( $token ) ) {
            $token = get_option( 'nias_activation_token_' . md5( $license_key ), '' );
        }

        $params = array();
        if ( ! empty( $token ) ) {
            $params['token'] = $token;
        }

        $response = $this->nias_make_request( $endpoint, 'GET', $params );

        if ( $response && isset( $response['success'] ) && $response['success'] === true ) {
            // Clear stored token
            // پاک کردن توکن ذخیره شده
            delete_option( 'nias_activation_token_' . md5( $license_key ) );
            return $response['data'];
        }

        return false;
    }

    /**
     * Get License Details
     * دریافت جزئیات لایسنس
     * 
     * Retrieves full license information
     * دریافت اطلاعات کامل لایسنس
     * 
     * @param string $license_key License key | کلید لایسنس
     * @return array|false License details or false on failure | جزئیات لایسنس یا false در صورت خطا
     */
    public function nias_get_license_details( $license_key ) {
        $endpoint = sprintf( '/licenses/%s', sanitize_text_field( $license_key ) );
        $response = $this->nias_make_request( $endpoint, 'GET' );

        if ( $response && isset( $response['success'] ) && $response['success'] === true ) {
            return $response['data'];
        }

        return false;
    }

    /**
     * Check License Status
     * بررسی وضعیت لایسنس
     * 
     * Quick check if license is valid and active
     * بررسی سریع اعتبار و فعال بودن لایسنس
     * 
     * @param string $license_key License key | کلید لایسنس
     * @return bool True if valid and active | درست در صورت معتبر و فعال بودن
     */
    public function nias_is_license_valid( $license_key ) {
        $license_data = $this->nias_validate_license( $license_key );

        if ( ! $license_data ) {
            return false;
        }

        // Check if license is active (status = 2 means active)
        // بررسی فعال بودن لایسنس (وضعیت 2 به معنای فعال است)
        if ( isset( $license_data['status'] ) && $license_data['status'] == 2 ) {
            // Check expiration date
            // بررسی تاریخ انقضا
            if ( isset( $license_data['expiresAt'] ) && ! empty( $license_data['expiresAt'] ) ) {
                $expiry_date = strtotime( $license_data['expiresAt'] );
                if ( $expiry_date < time() ) {
                    return false; // Expired | منقضی شده
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Get Remaining Activations
     * دریافت تعداد فعال‌سازی‌های باقی‌مانده
     * 
     * @param string $license_key License key | کلید لایسنس
     * @return int|false Number of remaining activations or false | تعداد فعال‌سازی‌های باقی‌مانده یا false
     */
    public function nias_get_remaining_activations( $license_key ) {
        $license_data = $this->nias_validate_license( $license_key );

        if ( ! $license_data ) {
            return false;
        }

        $activated = isset( $license_data['timesActivated'] ) ? intval( $license_data['timesActivated'] ) : 0;
        $max = isset( $license_data['timesActivatedMax'] ) ? intval( $license_data['timesActivatedMax'] ) : 0;

        if ( $max === 0 ) {
            return -1; // Unlimited | نامحدود
        }

        return max( 0, $max - $activated );
    }

    /**
     * Get License Expiry Date
     * دریافت تاریخ انقضای لایسنس
     * 
     * @param string $license_key License key | کلید لایسنس
     * @return string|false Expiry date or false | تاریخ انقضا یا false
     */
    public function nias_get_license_expiry( $license_key ) {
        $license_data = $this->nias_validate_license( $license_key );

        if ( ! $license_data ) {
            return false;
        }

        return isset( $license_data['expiresAt'] ) ? $license_data['expiresAt'] : false;
    }

    /**
     * Make API Request
     * انجام درخواست API
     * 
     * Core method for making HTTP requests to the API
     * متد اصلی برای انجام درخواست‌های HTTP به API
     * 
     * @param string $endpoint API endpoint | نقطه پایانی API
     * @param string $method HTTP method (GET, POST, PUT, DELETE) | متد HTTP
     * @param array $data Request data | داده‌های درخواست
     * @return array|false Response data or false on failure | داده‌های پاسخ یا false در صورت خطا
     */
    private function nias_make_request( $endpoint, $method = 'GET', $data = array() ) {
        $this->last_error = null;

        if ( empty( $this->store_url ) || empty( $this->consumer_key ) || empty( $this->consumer_secret ) ) {
            $this->last_error = __( 'API credentials not configured.', 'nias-lmw' ) . ' | ' . 
                                __( 'اطلاعات API پیکربندی نشده است.', 'nias-lmw' );
            return false;
        }

        $url = sprintf(
            '%s/wp-json/lmfwc/%s%s',
            $this->store_url,
            $this->api_version,
            $endpoint
        );

        // Add authentication
        // افزودن احراز هویت
        $url = add_query_arg(
            array(
                'consumer_key' => $this->consumer_key,
                'consumer_secret' => $this->consumer_secret,
            ),
            $url
        );

        // Add query parameters for GET requests
        // افزودن پارامترهای کوئری برای درخواست‌های GET
        if ( $method === 'GET' && ! empty( $data ) ) {
            $url = add_query_arg( $data, $url );
            $data = array();
        }

        $args = array(
            'method' => $method,
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        );

        if ( ! empty( $data ) ) {
            $args['body'] = wp_json_encode( $data );
        }

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            $this->last_error = $response->get_error_message();
            return false;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        $decoded = json_decode( $body, true );

        if ( $response_code < 200 || $response_code >= 300 ) {
            $this->last_error = isset( $decoded['message'] ) ? $decoded['message'] : 
                                sprintf( __( 'API request failed with status code: %d', 'nias-lmw' ), $response_code );
            return false;
        }

        return $decoded;
    }

    /**
     * Get Last Error
     * دریافت آخرین خطا
     * 
     * @return string|null Last error message | آخرین پیام خطا
     */
    public function nias_get_last_error() {
        return $this->last_error;
    }

    /**
     * Clear Last Error
     * پاک کردن آخرین خطا
     */
    public function nias_clear_error() {
        $this->last_error = null;
    }

    /**
     * Set API Credentials
     * تنظیم اطلاعات API
     * 
     * @param string $store_url Store URL | آدرس فروشگاه
     * @param string $consumer_key Consumer Key | کلید مصرف‌کننده
     * @param string $consumer_secret Consumer Secret | رمز مصرف‌کننده
     */
    public function nias_set_credentials( $store_url, $consumer_key, $consumer_secret ) {
        $this->store_url = rtrim( $store_url, '/' );
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
    }
}
