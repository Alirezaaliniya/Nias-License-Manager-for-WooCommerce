<?php
/**
 * Nias License Cron Handler Class
 * کلاس مدیریت کرون لایسنس نیاس
 *
 * @package NLMW
 * @version 1.0.0
 * 
 * Description: Handles automatic license validation checks via WordPress cron
 * توضیحات: مدیریت بررسی خودکار اعتبار لایسنس از طریق کرون وردپرس
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly | خروج در صورت دسترسی مستقیم
}

/**
 * Class Nias_License_Cron_Handler
 * 
 * Manages automatic license validation checks
 * مدیریت بررسی‌های خودکار اعتبار لایسنس
 */
class Nias_License_Cron_Handler {

    /**
     * License Client Instance
     * نمونه کلاینت لایسنس
     * 
     * @var Nias_License_Manager_Client
     */
    private $license_client;

    /**
     * Plugin Slug
     * اسلاگ افزونه
     * 
     * @var string
     */
    private $plugin_slug;

    /**
     * Cron Hook Name
     * نام هوک کرون
     * 
     * @var string
     */
    private $cron_hook;

    /**
     * Check Interval in Seconds (default: daily)
     * فاصله بررسی به ثانیه (پیش‌فرض: روزانه)
     * 
     * @var int
     */
    private $check_interval;

    /**
     * Plugin ID
     * شناسه پلاگین
     * 
     * @var string
     */
    private $plugin_id = '';

    /**
     * Constructor
     * سازنده کلاس
     * 
     * @param string $plugin_slug Plugin slug | اسلاگ افزونه
     * @param int $check_interval Check interval in seconds | فاصله بررسی به ثانیه
     * @param string $plugin_id Unique plugin identifier | شناسه منحصر به فرد پلاگین
     */
    public function __construct( $plugin_slug = 'my-plugin', $check_interval = DAY_IN_SECONDS, $plugin_id = '' ) {
        $this->plugin_slug = $plugin_slug;
        $this->plugin_id = sanitize_key( $plugin_id );
        $this->cron_hook = 'nlmw_' . $this->plugin_slug . '_license_check';
        $this->check_interval = $check_interval;

        // Initialize license client
        // مقداردهی اولیه کلاینت لایسنس
        $this->nias_init_license_client();

        // Register hooks
        // ثبت هوک‌ها
        add_action( $this->cron_hook, array( $this, 'nias_check_license_status' ) );
        add_action( 'admin_init', array( $this, 'nias_schedule_license_check' ) );
        
        // Add custom cron interval
        // افزودن فاصله سفارشی کرون
        add_filter( 'cron_schedules', array( $this, 'nias_add_custom_cron_interval' ) );

        // Clear cron on plugin deactivation
        // پاک کردن کرون در غیرفعال‌سازی افزونه
        register_deactivation_hook( __FILE__, array( $this, 'nias_clear_scheduled_check' ) );
    }

    /**
     * Initialize License Client
     * مقداردهی اولیه کلاینت لایسنس
     */
    private function nias_init_license_client() {
        $store_url = get_option( 'nlmw_' . $this->plugin_slug . '_store_url', '' );
        $consumer_key = get_option( 'nlmw_' . $this->plugin_slug . '_consumer_key', '' );
        $consumer_secret = get_option( 'nlmw_' . $this->plugin_slug . '_consumer_secret', '' );
        $product_ids = get_option( 'nlmw_' . $this->plugin_slug . '_product_ids', array() );
        $cache_days = get_option( 'nlmw_' . $this->plugin_slug . '_cache_days', 5 );

        $this->license_client = new Nias_License_Manager_Client( 
            $store_url, 
            $consumer_key, 
            $consumer_secret,
            $product_ids,
            $cache_days,
            $this->plugin_id
        );
    }

    /**
     * Add Custom Cron Interval
     * افزودن فاصله سفارشی کرون
     * 
     * @param array $schedules Existing schedules | برنامه‌های موجود
     * @return array Modified schedules | برنامه‌های اصلاح شده
     */
    public function nias_add_custom_cron_interval( $schedules ) {
        $interval_name = 'nlmw_' . $this->plugin_slug . '_interval';
        
        if ( ! isset( $schedules[ $interval_name ] ) ) {
            $schedules[ $interval_name ] = array(
                'interval' => $this->check_interval,
                'display' => sprintf( 
                    __( 'Every %s seconds', 'nias-lmw' ), 
                    number_format( $this->check_interval ) 
                ) . ' | ' . 
                sprintf( 
                    __( 'هر %s ثانیه', 'nias-lmw' ), 
                    number_format( $this->check_interval ) 
                ),
            );
        }

        return $schedules;
    }

    /**
     * Schedule License Check
     * برنامه‌ریزی بررسی لایسنس
     * 
     * Sets up the cron job if it's not already scheduled
     * راه‌اندازی کرون در صورتی که قبلاً برنامه‌ریزی نشده باشد
     */
    public function nias_schedule_license_check() {
        if ( ! wp_next_scheduled( $this->cron_hook ) ) {
            $interval_name = 'nlmw_' . $this->plugin_slug . '_interval';
            wp_schedule_event( time(), $interval_name, $this->cron_hook );
            
            // Log the scheduling
            // ثبت برنامه‌ریزی
            $this->nias_log_message( 
                __( 'License check cron scheduled.', 'nias-lmw' ) . ' | ' . 
                __( 'کرون بررسی لایسنس برنامه‌ریزی شد.', 'nias-lmw' ) 
            );
        }
    }

    /**
     * Check License Status (Cron Callback)
     * بررسی وضعیت لایسنس (فراخوانی کرون)
     * 
     * Main function that runs on cron schedule
     * تابع اصلی که در برنامه کرون اجرا می‌شود
     */
    public function nias_check_license_status() {
        $license_key = get_option( 'nlmw_' . $this->plugin_slug . '_license_key', '' );
        $license_status = get_option( 'nlmw_' . $this->plugin_slug . '_license_status', 'inactive' );

        // Only check if license is supposedly active
        // فقط در صورتی بررسی کن که لایسنس ظاهراً فعال است
        if ( $license_status !== 'active' || empty( $license_key ) ) {
            return;
        }

        // Re-initialize client to ensure fresh credentials
        // مقداردهی مجدد کلاینت برای اطمینان از اعتبارنامه‌های جدید
        $this->nias_init_license_client();

        // Validate license
        // اعتبارسنجی لایسنس
        $license_data = $this->license_client->nias_validate_license( $license_key );

        if ( $license_data ) {
            // Check if license is still valid
            // بررسی معتبر بودن لایسنس
            $is_valid = $this->nias_verify_license_data( $license_data );

            if ( $is_valid ) {
                // Update license data
                // به‌روزرسانی اطلاعات لایسنس
                update_option( 'nlmw_' . $this->plugin_slug . '_license_data', $license_data );
                update_option( 'nlmw_' . $this->plugin_slug . '_license_status', 'active' );
                update_option( 'nlmw_' . $this->plugin_slug . '_last_check', time() );

                $this->nias_log_message( 
                    __( 'License validated successfully.', 'nias-lmw' ) . ' | ' . 
                    __( 'لایسنس با موفقیت اعتبارسنجی شد.', 'nias-lmw' ) 
                );

                // Check for expiration warnings
                // بررسی هشدارهای انقضا
                $this->nias_check_expiration_warning( $license_data );

            } else {
                // License is invalid
                // لایسنس نامعتبر است
                $this->nias_handle_invalid_license( $license_data );
            }
        } else {
            // Failed to validate license
            // اعتبارسنجی لایسنس ناموفق بود
            $error = $this->license_client->nias_get_last_error();
            $this->nias_log_message( 
                sprintf( 
                    __( 'License validation failed: %s', 'nias-lmw' ), 
                    $error ? $error : __( 'Unknown error', 'nias-lmw' ) 
                ) . ' | ' . 
                sprintf( 
                    __( 'اعتبارسنجی لایسنس ناموفق بود: %s', 'nias-lmw' ), 
                    $error ? $error : __( 'خطای نامشخص', 'nias-lmw' ) 
                ),
                'error'
            );

            // Mark license as inactive after multiple failures
            // علامت‌گذاری لایسنس به عنوان غیرفعال پس از چند شکست
            $this->nias_increment_failure_count();
        }
    }

    /**
     * Verify License Data
     * تأیید اطلاعات لایسنس
     * 
     * Checks if license data indicates a valid, active license
     * بررسی اینکه آیا اطلاعات لایسنس نشان‌دهنده لایسنس معتبر و فعال است
     * 
     * @param array $license_data License data from API | اطلاعات لایسنس از API
     * @return bool True if valid | درست در صورت معتبر بودن
     */
    private function nias_verify_license_data( $license_data ) {
        // Check sellable status (block only "not for sale")
        // بررسی وضعیت قابل فروش (فقط «غیر قابل فروش» بلوکه شود)
        if ( ! $this->license_client->nias_is_data_sellable( $license_data ) ) {
            return false;
        }

        // Check productId matches configured product IDs if any
        // بررسی تطابق شناسه محصول با شناسه‌های پیکربندی‌شده
        $allowed_products = $this->license_client->nias_get_product_ids();
        if ( ! empty( $allowed_products ) && isset( $license_data['productId'] ) ) {
            if ( ! in_array( $license_data['productId'], $allowed_products ) ) {
                return false;
            }
        }

        // Check expiration date
        // بررسی تاریخ انقضا
        if ( isset( $license_data['expiresAt'] ) && ! empty( $license_data['expiresAt'] ) ) {
            $expiry_date = strtotime( $license_data['expiresAt'] );
            if ( $expiry_date < time() ) {
                return false; // Expired | منقضی شده
            }
        }

        // Check activation limit
        // بررسی محدودیت فعال‌سازی
        if ( isset( $license_data['timesActivatedMax'] ) && $license_data['timesActivatedMax'] > 0 ) {
            $activated = isset( $license_data['timesActivated'] ) ? intval( $license_data['timesActivated'] ) : 0;
            $max = intval( $license_data['timesActivatedMax'] );
            
            if ( $activated > $max ) {
                return false; // Activation limit reached | محدودیت فعال‌سازی به پایان رسیده
            }
        }

        return true;
    }

    /**
     * Check Expiration Warning
     * بررسی هشدار انقضا
     * 
     * Sends admin notice if license is expiring soon
     * ارسال اعلان مدیریتی در صورت نزدیک بودن به انقضا
     * 
     * @param array $license_data License data | اطلاعات لایسنس
     */
    private function nias_check_expiration_warning( $license_data ) {
        if ( ! isset( $license_data['expiresAt'] ) || empty( $license_data['expiresAt'] ) ) {
            return; // No expiration date | بدون تاریخ انقضا
        }

        $expiry_date = strtotime( $license_data['expiresAt'] );
        $days_until_expiry = ceil( ( $expiry_date - time() ) / DAY_IN_SECONDS );

        // Warning thresholds: 30, 14, 7, 3, 1 days
        // آستانه‌های هشدار: 30، 14، 7، 3، 1 روز
        $warning_days = array( 30, 14, 7, 3, 1 );

        foreach ( $warning_days as $warning_day ) {
            if ( $days_until_expiry <= $warning_day ) {
                $last_warning = get_option( 'nlmw_' . $this->plugin_slug . '_last_expiry_warning', 0 );
                $warning_key = 'day_' . $warning_day;

                // Send warning once per threshold
                // ارسال هشدار یک بار برای هر آستانه
                if ( $last_warning !== $warning_key ) {
                    $this->nias_send_expiry_notification( $days_until_expiry );
                    update_option( 'nlmw_' . $this->plugin_slug . '_last_expiry_warning', $warning_key );
                }
                break;
            }
        }
    }

    /**
     * Handle Invalid License
     * مدیریت لایسنس نامعتبر
     * 
     * Takes action when license is found to be invalid
     * اقدام در زمان نامعتبر بودن لایسنس
     * 
     * @param array $license_data License data | اطلاعات لایسنس
     */
    private function nias_handle_invalid_license( $license_data ) {
        $reason = __( 'Unknown reason', 'nias-lmw' ) . ' | ' . __( 'دلیل نامشخص', 'nias-lmw' );

        if ( isset( $license_data['status'] ) && $license_data['status'] != 2 ) {
            $reason = __( 'License status is not active', 'nias-lmw' ) . ' | ' . 
                      __( 'وضعیت لایسنس فعال نیست', 'nias-lmw' );
        } elseif ( isset( $license_data['expiresAt'] ) && ! empty( $license_data['expiresAt'] ) ) {
            $expiry_date = strtotime( $license_data['expiresAt'] );
            if ( $expiry_date < time() ) {
                $reason = __( 'License has expired', 'nias-lmw' ) . ' | ' . 
                          __( 'لایسنس منقضی شده است', 'nias-lmw' );
            }
        }

        // Update license status to inactive
        // به‌روزرسانی وضعیت لایسنس به غیرفعال
        update_option( 'nlmw_' . $this->plugin_slug . '_license_status', 'inactive' );
        update_option( 'nlmw_' . $this->plugin_slug . '_license_invalid_reason', $reason );

        $this->nias_log_message( 
            sprintf( __( 'License marked as invalid: %s', 'nias-lmw' ), $reason ),
            'warning'
        );

        // Send notification to admin
        // ارسال اعلان به مدیر
        $this->nias_send_invalid_license_notification( $reason );

        // Optional: Disable plugin functionality
        // اختیاری: غیرفعال کردن عملکرد افزونه
        do_action( 'nias_' . $this->plugin_slug . '_license_invalid', $license_data, $reason );
    }

    /**
     * Increment Failure Count
     * افزایش شمارنده شکست‌ها
     * 
     * Tracks consecutive validation failures
     * پیگیری شکست‌های پی‌درپی اعتبارسنجی
     */
    private function nias_increment_failure_count() {
        $failure_count = get_option( 'nlmw_' . $this->plugin_slug . '_validation_failures', 0 );
        $failure_count++;
        update_option( 'nlmw_' . $this->plugin_slug . '_validation_failures', $failure_count );

        // After 3 consecutive failures, mark as inactive
        // پس از 3 شکست پی‌درپی، علامت‌گذاری به عنوان غیرفعال
        if ( $failure_count >= 3 ) {
            update_option( 'nlmw_' . $this->plugin_slug . '_license_status', 'inactive' );
            $this->nias_log_message( 
                __( 'License marked as inactive after multiple validation failures.', 'nias-lmw' ) . ' | ' . 
                __( 'لایسنس پس از چندین شکست اعتبارسنجی به عنوان غیرفعال علامت‌گذاری شد.', 'nias-lmw' ),
                'error'
            );
        }
    }

    /**
     * Send Expiry Notification
     * ارسال اعلان انقضا
     * 
     * @param int $days_remaining Days until expiry | روزهای باقی‌مانده تا انقضا
     */
    private function nias_send_expiry_notification( $days_remaining ) {
        $admin_email = get_option( 'admin_email' );
        $site_name = get_bloginfo( 'name' );
        
        $subject = sprintf( 
            __( '[%s] License Expiring Soon', 'nias-lmw' ), 
            $site_name 
        );
        
        $message = sprintf(
            __( 'Your license for %s will expire in %d days. Please renew to continue receiving updates and support.', 'nias-lmw' ),
            $this->plugin_slug,
            $days_remaining
        );
        
        $message .= "\n\n" . sprintf(
            __( 'لایسنس شما برای %s %d روز دیگر منقضی می‌شود. لطفاً برای ادامه دریافت به‌روزرسانی‌ها و پشتیبانی، آن را تمدید کنید.', 'nias-lmw' ),
            $this->plugin_slug,
            $days_remaining
        );

        wp_mail( $admin_email, $subject, $message );

        $this->nias_log_message( 
            sprintf( 
                __( 'Expiry notification sent: %d days remaining', 'nias-lmw' ), 
                $days_remaining 
            )
        );
    }

    /**
     * Send Invalid License Notification
     * ارسال اعلان لایسنس نامعتبر
     * 
     * @param string $reason Reason for invalidity | دلیل نامعتبر بودن
     */
    private function nias_send_invalid_license_notification( $reason ) {
        $admin_email = get_option( 'admin_email' );
        $site_name = get_bloginfo( 'name' );
        
        $subject = sprintf( 
            __( '[%s] License Invalid', 'nias-lmw' ), 
            $site_name 
        );
        
        $message = sprintf(
            __( 'Your license for %s is no longer valid. Reason: %s', 'nias-lmw' ),
            $this->plugin_slug,
            $reason
        );

        wp_mail( $admin_email, $subject, $message );
    }

    /**
     * Clear Scheduled Check
     * پاک کردن بررسی برنامه‌ریزی شده
     * 
     * Removes the cron job (called on plugin deactivation)
     * حذف کرون (فراخوانی در غیرفعال‌سازی افزونه)
     */
    public function nias_clear_scheduled_check() {
        $timestamp = wp_next_scheduled( $this->cron_hook );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, $this->cron_hook );
            $this->nias_log_message( 
                __( 'License check cron cleared.', 'nias-lmw' ) . ' | ' . 
                __( 'کرون بررسی لایسنس پاک شد.', 'nias-lmw' ) 
            );
        }
    }

    /**
     * Force Check Now
     * اجبار بررسی فوری
     * 
     * Manually trigger license check (useful for testing)
     * فعال‌سازی دستی بررسی لایسنس (مفید برای تست)
     */
    public function nias_force_check_now() {
        $this->nias_check_license_status();
        return get_option( 'nias_' . $this->plugin_slug . '_license_status', 'inactive' );
    }

    /**
     * Get Last Check Time
     * دریافت زمان آخرین بررسی
     * 
     * @return int|false Timestamp or false | تایم‌استمپ یا false
     */
    public function nias_get_last_check_time() {
        return get_option( 'nias_' . $this->plugin_slug . '_last_check', false );
    }

    /**
     * Get Next Scheduled Check Time
     * دریافت زمان بررسی برنامه‌ریزی شده بعدی
     * 
     * @return int|false Timestamp or false | تایم‌استمپ یا false
     */
    public function nias_get_next_check_time() {
        return wp_next_scheduled( $this->cron_hook );
    }

    /**
     * Log Message
     * ثبت پیام
     * 
     * @param string $message Message to log | پیام برای ثبت
     * @param string $type Log type (info, warning, error) | نوع لاگ
     */
    private function nias_log_message( $message, $type = 'info' ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
            error_log( sprintf( '[NLMW %s] %s: %s', strtoupper( $type ), $this->plugin_slug, $message ) );
        }

        // Store in database for admin review
        // ذخیره در پایگاه داده برای بررسی مدیر
        $logs = get_option( 'nias_' . $this->plugin_slug . '_logs', array() );
        $logs[] = array(
            'time' => current_time( 'mysql' ),
            'type' => $type,
            'message' => $message,
        );

        // Keep only last 50 logs
        // نگه داشتن تنها 50 لاگ آخر
        if ( count( $logs ) > 50 ) {
            $logs = array_slice( $logs, -50 );
        }

        update_option( 'nlmw_' . $this->plugin_slug . '_logs', $logs );
    }

    /**
     * Get Logs
     * دریافت لاگ‌ها
     * 
     * @param int $limit Number of logs to retrieve | تعداد لاگ‌ها برای دریافت
     * @return array Logs | لاگ‌ها
     */
    public function nias_get_logs( $limit = 50 ) {
        $logs = get_option( 'nlmw_' . $this->plugin_slug . '_logs', array() );
        return array_slice( $logs, -$limit );
    }

    /**
     * Clear Logs
     * پاک کردن لاگ‌ها
     */
    public function nias_clear_logs() {
        delete_option( 'nlmw_' . $this->plugin_slug . '_logs' );
    }
}
