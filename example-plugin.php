<?php
/**
 * Plugin Name: My Awesome Plugin
 * Plugin Name (FA): افزونه عالی من
 * Plugin URI: https://yoursite.com/my-awesome-plugin
 * Description: Example plugin demonstrating NLMW integration
 * Description (FA): افزونه نمونه نمایش یکپارچگی NLMW
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yoursite.com
 * License: GPL-2.0+
 * Text Domain: my-awesome-plugin
 * Domain Path: /languages
 */

// Exit if accessed directly | خروج در صورت دسترسی مستقیم
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constants | تعریف ثابت‌ها
define( 'MY_PLUGIN_VERSION', '1.0.0' );
define( 'MY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'MY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MY_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Global variable to store license status | متغیر سراسری برای ذخیره وضعیت لایسنس
$my_plugin_license_valid = false;

/**
 * ================================================
 * 🔐 LICENSE CONFIGURATION | تنظیمات لایسنس
 * ================================================
 * تمام تنظیمات مرتبط با لایسنس و مدیریت را اینجا تغییر دهید
 * Modify all license and configuration settings here
 */

// Plugin Information | اطلاعات پلاگین
$PLUGIN_CONFIG = array(
    'name'          => 'My Awesome Plugin',              // نام پلاگین
    'slug'          => 'my-awesome-plugin',              // اسلاگ پلاگین (برای DB)
    'plugin_id'     => 'my-awesome-plugin',              // شناسه منحصر به فرد (جلوگیری از تداخل)
);

// Store Configuration | تنظیمات فروشگاه
$STORE_CONFIG = array(
    'url'              => 'https://example.com',           // آدرس فروشگاه
    'consumer_key'     => 'ck_test1234567890abcdef',       // کلید مصرف‌کننده API
    'consumer_secret'  => 'cs_test1234567890abcdef',       // رمز مصرف‌کننده API
);

// License Configuration | تنظیمات لایسنس
$LICENSE_CONFIG = array(
    'product_ids'  => array( 5735, 5736, 5737 ),           // شناسه محصولات معتبر (خالی = همه)
    'cache_days'   => 5,                                    // مدت کش بررسی لایسنس (روز)
);

// ================================================

// Include NLMW library files | اضافه کردن فایل‌های کتابخانه NLMW
require_once MY_PLUGIN_PATH . 'includes/license/class-license-manager-client.php';
require_once MY_PLUGIN_PATH . 'includes/license/class-license-settings-page.php';
require_once MY_PLUGIN_PATH . 'includes/license/class-license-cron-handler.php';

/**
 * Initialize Plugin on plugins_loaded hook
 * مقداردهی اولیه افزونه در هوک plugins_loaded
 */
add_action( 'plugins_loaded', function() {
    global $my_plugin_license_valid, $PLUGIN_CONFIG, $STORE_CONFIG, $LICENSE_CONFIG;
    
    $plugin_name = $PLUGIN_CONFIG['name'];
    $plugin_slug = $PLUGIN_CONFIG['slug'];
    $plugin_id = $PLUGIN_CONFIG['plugin_id'];
    $API_CONFIG = array_merge( $STORE_CONFIG, array( 'product_ids' => $LICENSE_CONFIG['product_ids'] ) );
    
    // 1. Initialize Settings Page
    // ۱. مقداردهی اولیه صفحه تنظیمات
    new Nias_License_Settings_Page(
        $plugin_name,
        $plugin_slug,
        $plugin_id,
        $API_CONFIG
    );
    
    // 2. Initialize Cron Handler (check daily)
    // ۲. مقداردهی اولیه مدیریت کرون (بررسی روزانه)
    new Nias_License_Cron_Handler(
        $plugin_slug,
        DAY_IN_SECONDS,
        $plugin_id
    );
    
    // 3. Initialize License Client with configuration
    // ۳. مقداردهی اولیه کلاینت لایسنس با تنظیمات
    $license_client = new Nias_License_Manager_Client(
        $STORE_CONFIG['url'],
        $STORE_CONFIG['consumer_key'],
        $STORE_CONFIG['consumer_secret'],
        $LICENSE_CONFIG['product_ids'],
        $LICENSE_CONFIG['cache_days'],
        $plugin_id
    );
    
    // 4. Check License Status
    // ۴. بررسی وضعیت لایسنس
    $license_key = get_option( 'nlmw_' . $plugin_slug . '_license_key', '' );
    
    if ( ! empty( $license_key ) && $license_client->nias_is_license_valid( $license_key ) ) {
        // ✅ License is valid - enable all features
        // ✅ لایسنس معتبر است - فعال‌سازی تمام ویژگی‌ها
        $my_plugin_license_valid = true;
    } else {
        // ❌ License invalid - show notice
        // ❌ لایسنس نامعتبر است - نمایش اعلان
        add_action( 'admin_notices', function() use ( $plugin_slug ) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <strong><?php _e( 'My Awesome Plugin', 'my-awesome-plugin' ); ?>:</strong>
                    <?php 
                    printf(
                        __( 'Please <a href="%s">activate your license</a> to unlock all features.', 'my-awesome-plugin' ),
                        admin_url( 'options-general.php?page=' . $plugin_slug . '-license' )
                    );
                    ?>
                </p>
            </div>
            <?php
        });
    }
    
    // Load text domain for translations | بارگذاری دامنه متن برای ترجمه‌ها
    load_plugin_textdomain(
        'my-awesome-plugin',
        false,
        dirname( MY_PLUGIN_BASENAME ) . '/languages'
    );
} );

/**
 * Plugin Activation Hook
 * هوک فعال‌سازی افزونه
 */
register_activation_hook( __FILE__, function() {
    // Set default options | تنظیم گزینه‌های پیش‌فرض
    add_option( 'my_plugin_activation_time', current_time( 'mysql' ) );
    add_option( 'my_plugin_version', MY_PLUGIN_VERSION );
    
    // Flush rewrite rules if needed | بازنویسی قوانین در صورت نیاز
    flush_rewrite_rules();
    
    error_log( 'My Awesome Plugin activated at ' . current_time( 'mysql' ) );
});

/**
 * Plugin Deactivation Hook
 * هوک غیرفعال‌سازی افزونه
 */
register_deactivation_hook( __FILE__, function() {
    global $PLUGIN_CONFIG;
    
    // Clear scheduled cron | پاک کردن کرون برنامه‌ریزی شده
    wp_clear_scheduled_hook( 'nlmw_' . $PLUGIN_CONFIG['slug'] . '_license_check' );
    
    // Flush rewrite rules | بازنویسی قوانین
    flush_rewrite_rules();
    
    error_log( 'My Awesome Plugin deactivated at ' . current_time( 'mysql' ) );
});


