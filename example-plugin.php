<?php
/**
 * Plugin Name: My Awesome Plugin with NLMW
 * Plugin Name (FA): افزونه عالی من با NLMW
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

/**
 * Main Plugin Class
 * کلاس اصلی افزونه
 */
class My_Awesome_Plugin {

    /**
     * Plugin Version
     * نسخه افزونه
     */
    const VERSION = '1.0.0';

    /**
     * Plugin Slug
     * اسلاگ افزونه
     */
    const PLUGIN_SLUG = 'my-awesome-plugin';

    /**
     * Plugin Name
     * نام افزونه
     */
    const PLUGIN_NAME = 'My Awesome Plugin';

    /**
     * License Client Instance
     * نمونه کلاینت لایسنس
     * 
     * @var Nias_License_Manager_Client
     */
    private $license_client;

    /**
     * License is Valid Flag
     * پرچم معتبر بودن لایسنس
     * 
     * @var bool
     */
    private $license_valid = false;

    /**
     * Constructor
     * سازنده
     */
    public function __construct() {
        // Define plugin constants | تعریف ثابت‌های افزونه
        $this->define_constants();

        // Include required files | اضافه کردن فایل‌های مورد نیاز
        $this->includes();

        // Initialize license system | مقداردهی اولیه سیستم لایسنس
        $this->init_license_system();

        // Initialize plugin | مقداردهی اولیه افزونه
        $this->init();

        // Register hooks | ثبت هوک‌ها
        $this->register_hooks();
    }

    /**
     * Define Plugin Constants
     * تعریف ثابت‌های افزونه
     */
    private function define_constants() {
        define( 'MY_PLUGIN_VERSION', self::VERSION );
        define( 'MY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
        define( 'MY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
        define( 'MY_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
    }

    /**
     * Include Required Files
     * اضافه کردن فایل‌های مورد نیاز
     */
    private function includes() {
        // Include NLMW library files
        // اضافه کردن فایل‌های کتابخانه NLMW
        require_once MY_PLUGIN_PATH . 'includes/license/class-license-manager-client.php';
        require_once MY_PLUGIN_PATH . 'includes/license/class-license-settings-page.php';
        require_once MY_PLUGIN_PATH . 'includes/license/class-license-cron-handler.php';

        // Include other plugin files (if any)
        // اضافه کردن سایر فایل‌های افزونه (در صورت وجود)
        // require_once MY_PLUGIN_PATH . 'includes/class-my-feature.php';
    }

    /**
     * Initialize License System
     * مقداردهی اولیه سیستم لایسنس
     */
    private function init_license_system() {
        if ( ! class_exists( 'Nias_License_Manager_Client' ) ) {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                _e( 'NLMW library not found! Please install the license files.', 'my-awesome-plugin' );
                echo '</p></div>';
            });
            return;
        }

        // Initialize settings page
        // مقداردهی اولیه صفحه تنظیمات
        new Nias_License_Settings_Page(
            self::PLUGIN_NAME,
            self::PLUGIN_SLUG
        );

        // Initialize cron handler (check daily)
        // مقداردهی اولیه مدیریت کرون (بررسی روزانه)
        new Nias_License_Cron_Handler(
            self::PLUGIN_SLUG,
            DAY_IN_SECONDS // Check every 24 hours | بررسی هر 24 ساعت
        );

        // Initialize license client
        // مقداردهی اولیه کلاینت لایسنس
        $this->init_license_client();
    }

    /**
     * Initialize License Client
     * مقداردهی اولیه کلاینت لایسنس
     */
    private function init_license_client() {
        $store_url = get_option( 'nias_' . self::PLUGIN_SLUG . '_store_url', '' );
        $consumer_key = get_option( 'nias_' . self::PLUGIN_SLUG . '_consumer_key', '' );
        $consumer_secret = get_option( 'nias_' . self::PLUGIN_SLUG . '_consumer_secret', '' );

        if ( ! empty( $store_url ) && ! empty( $consumer_key ) && ! empty( $consumer_secret ) ) {
            $this->license_client = new Nias_License_Manager_Client(
                $store_url,
                $consumer_key,
                $consumer_secret
            );
        }
    }

    /**
     * Initialize Plugin
     * مقداردهی اولیه افزونه
     */
    private function init() {
        // Check license status
        // بررسی وضعیت لایسنس
        $this->check_license();

        // Load text domain for translations
        // بارگذاری دامنه متن برای ترجمه‌ها
        load_plugin_textdomain(
            'my-awesome-plugin',
            false,
            dirname( MY_PLUGIN_BASENAME ) . '/languages'
        );
    }

    /**
     * Register Plugin Hooks
     * ثبت هوک‌های افزونه
     */
    private function register_hooks() {
        // Activation hook | هوک فعال‌سازی
        register_activation_hook( __FILE__, array( $this, 'on_activation' ) );

        // Deactivation hook | هوک غیرفعال‌سازی
        register_deactivation_hook( __FILE__, array( $this, 'on_deactivation' ) );

        // Admin hooks | هوک‌های مدیریتی
        add_action( 'admin_init', array( $this, 'admin_init' ) );
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_notices', array( $this, 'show_license_notices' ) );

        // Plugin links | لینک‌های افزونه
        add_filter( 'plugin_action_links_' . MY_PLUGIN_BASENAME, array( $this, 'add_action_links' ) );

        // Frontend hooks (only if license is valid)
        // هوک‌های فرانت‌اند (فقط اگر لایسنس معتبر باشد)
        if ( $this->license_valid ) {
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            add_shortcode( 'my_awesome_shortcode', array( $this, 'render_shortcode' ) );
        }
    }

    /**
     * Check License Status
     * بررسی وضعیت لایسنس
     */
    private function check_license() {
        $license_key = get_option( 'nias_' . self::PLUGIN_SLUG . '_license_key', '' );
        $license_status = get_option( 'nias_' . self::PLUGIN_SLUG . '_license_status', 'inactive' );

        // Quick check from cached status
        // بررسی سریع از وضعیت کش شده
        if ( $license_status === 'active' && ! empty( $license_key ) ) {
            // Verify with transient cache (check every hour)
            // تأیید با کش موقت (بررسی هر ساعت)
            $cache_key = 'my_plugin_license_valid_' . md5( $license_key );
            $cached_valid = get_transient( $cache_key );

            if ( false !== $cached_valid ) {
                $this->license_valid = (bool) $cached_valid;
            } else {
                // Check with API
                // بررسی با API
                if ( $this->license_client ) {
                    $this->license_valid = $this->license_client->nias_is_license_valid( $license_key );
                    set_transient( $cache_key, $this->license_valid, HOUR_IN_SECONDS );
                }
            }
        }
    }

    /**
     * Plugin Activation Hook
     * هوک فعال‌سازی افزونه
     */
    public function on_activation() {
        // Set default options
        // تنظیم گزینه‌های پیش‌فرض
        add_option( 'my_plugin_activation_time', current_time( 'mysql' ) );
        add_option( 'my_plugin_version', self::VERSION );

        // Flush rewrite rules if needed
        // بازنویسی قوانین در صورت نیاز
        flush_rewrite_rules();

        // Log activation
        // ثبت فعال‌سازی
        error_log( 'My Awesome Plugin activated at ' . current_time( 'mysql' ) );
    }

    /**
     * Plugin Deactivation Hook
     * هوک غیرفعال‌سازی افزونه
     */
    public function on_deactivation() {
        // Clear scheduled cron
        // پاک کردن کرون برنامه‌ریزی شده
        wp_clear_scheduled_hook( 'nias_' . self::PLUGIN_SLUG . '_license_check' );

        // Clear transients
        // پاک کردن کش موقت
        delete_transient( 'my_plugin_license_valid_*' );

        // Flush rewrite rules
        // بازنویسی قوانین
        flush_rewrite_rules();

        // Log deactivation
        // ثبت غیرفعال‌سازی
        error_log( 'My Awesome Plugin deactivated at ' . current_time( 'mysql' ) );
    }

    /**
     * Admin Init
     * مقداردهی اولیه مدیریت
     */
    public function admin_init() {
        // Register settings if needed
        // ثبت تنظیمات در صورت نیاز
    }

    /**
     * Add Admin Menu
     * افزودن منوی مدیریتی
     */
    public function add_admin_menu() {
        // Only show if license is valid or in settings page
        // فقط نمایش در صورت معتبر بودن لایسنس یا در صفحه تنظیمات
        add_menu_page(
            __( 'My Awesome Plugin', 'my-awesome-plugin' ),
            __( 'My Plugin', 'my-awesome-plugin' ),
            'manage_options',
            'my-awesome-plugin',
            array( $this, 'render_main_page' ),
            'dashicons-admin-plugins',
            30
        );

        // Add submenu items
        // افزودن آیتم‌های زیرمنو
        add_submenu_page(
            'my-awesome-plugin',
            __( 'Dashboard', 'my-awesome-plugin' ),
            __( 'Dashboard', 'my-awesome-plugin' ),
            'manage_options',
            'my-awesome-plugin',
            array( $this, 'render_main_page' )
        );

        if ( $this->license_valid ) {
            // Premium features submenu (only if licensed)
            // زیرمنوی ویژگی‌های پریمیوم (فقط با لایسنس)
            add_submenu_page(
                'my-awesome-plugin',
                __( 'Premium Features', 'my-awesome-plugin' ),
                __( 'Premium Features', 'my-awesome-plugin' ),
                'manage_options',
                'my-awesome-plugin-premium',
                array( $this, 'render_premium_page' )
            );
        }
    }

    /**
     * Show License Notices
     * نمایش اعلان‌های لایسنس
     */
    public function show_license_notices() {
        $screen = get_current_screen();
        
        // Don't show on license settings page
        // نمایش نده در صفحه تنظیمات لایسنس
        if ( strpos( $screen->id, 'license' ) !== false ) {
            return;
        }

        $license_status = get_option( 'nias_' . self::PLUGIN_SLUG . '_license_status', 'inactive' );

        if ( $license_status !== 'active' ) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <strong><?php _e( 'My Awesome Plugin', 'my-awesome-plugin' ); ?>:</strong>
                    <?php 
                    printf(
                        __( 'Please <a href="%s">activate your license</a> to unlock all features.', 'my-awesome-plugin' ),
                        admin_url( 'options-general.php?page=' . self::PLUGIN_SLUG . '-license' )
                    );
                    ?>
                </p>
            </div>
            <?php
        } else {
            // Check for expiration warning
            // بررسی هشدار انقضا
            $license_data = get_option( 'nias_' . self::PLUGIN_SLUG . '_license_data', array() );
            
            if ( isset( $license_data['expiresAt'] ) && ! empty( $license_data['expiresAt'] ) ) {
                $days_left = ceil( ( strtotime( $license_data['expiresAt'] ) - time() ) / DAY_IN_SECONDS );
                
                if ( $days_left <= 7 && $days_left > 0 ) {
                    ?>
                    <div class="notice notice-warning is-dismissible">
                        <p>
                            <strong><?php _e( 'My Awesome Plugin', 'my-awesome-plugin' ); ?>:</strong>
                            <?php 
                            printf(
                                __( 'Your license will expire in %d days. Please renew to continue receiving updates.', 'my-awesome-plugin' ),
                                $days_left
                            );
                            ?>
                        </p>
                    </div>
                    <?php
                }
            }
        }
    }

    /**
     * Add Plugin Action Links
     * افزودن لینک‌های اکشن افزونه
     */
    public function add_action_links( $links ) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url( 'options-general.php?page=' . self::PLUGIN_SLUG . '-license' ),
            __( 'License', 'my-awesome-plugin' )
        );

        array_unshift( $links, $settings_link );

        if ( $this->license_valid ) {
            $dashboard_link = sprintf(
                '<a href="%s" style="color: #46b450; font-weight: 600;">%s</a>',
                admin_url( 'admin.php?page=my-awesome-plugin' ),
                __( 'Dashboard', 'my-awesome-plugin' )
            );
            array_unshift( $links, $dashboard_link );
        }

        return $links;
    }

    /**
     * Enqueue Scripts and Styles
     * بارگذاری اسکریپت‌ها و استایل‌ها
     */
    public function enqueue_scripts() {
        if ( ! $this->license_valid ) {
            return; // Only load if licensed | فقط با لایسنس بارگذاری شود
        }

        // Enqueue CSS
        wp_enqueue_style(
            'my-awesome-plugin-style',
            MY_PLUGIN_URL . 'assets/css/style.css',
            array(),
            self::VERSION
        );

        // Enqueue JavaScript
        wp_enqueue_script(
            'my-awesome-plugin-script',
            MY_PLUGIN_URL . 'assets/js/script.js',
            array( 'jquery' ),
            self::VERSION,
            true
        );

        // Localize script
        wp_localize_script(
            'my-awesome-plugin-script',
            'myPluginData',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'my_plugin_nonce' ),
                'licensed' => $this->license_valid
            )
        );
    }

    /**
     * Render Main Admin Page
     * رندر صفحه اصلی مدیریت
     */
    public function render_main_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <?php if ( ! $this->license_valid ): ?>
                <div class="notice notice-warning">
                    <p>
                        <?php 
                        printf(
                            __( 'Please <a href="%s">activate your license</a> to use this plugin.', 'my-awesome-plugin' ),
                            admin_url( 'options-general.php?page=' . self::PLUGIN_SLUG . '-license' )
                        );
                        ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="card">
                    <h2><?php _e( 'Welcome to My Awesome Plugin!', 'my-awesome-plugin' ); ?></h2>
                    <p><?php _e( 'Your license is active. Enjoy all premium features!', 'my-awesome-plugin' ); ?></p>
                    
                    <h3><?php _e( 'Features:', 'my-awesome-plugin' ); ?></h3>
                    <ul>
                        <li>✅ <?php _e( 'Feature 1 - Enabled', 'my-awesome-plugin' ); ?></li>
                        <li>✅ <?php _e( 'Feature 2 - Enabled', 'my-awesome-plugin' ); ?></li>
                        <li>✅ <?php _e( 'Feature 3 - Enabled', 'my-awesome-plugin' ); ?></li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render Premium Features Page
     * رندر صفحه ویژگی‌های پریمیوم
     */
    public function render_premium_page() {
        if ( ! $this->license_valid ) {
            wp_die( __( 'Unauthorized access. Please activate your license.', 'my-awesome-plugin' ) );
        }

        ?>
        <div class="wrap">
            <h1><?php _e( 'Premium Features', 'my-awesome-plugin' ); ?></h1>
            
            <div class="card">
                <h2><?php _e( 'Advanced Settings', 'my-awesome-plugin' ); ?></h2>
                <p><?php _e( 'Configure your premium features here.', 'my-awesome-plugin' ); ?></p>
                
                <!-- Your premium features settings here -->
                <!-- تنظیمات ویژگی‌های پریمیوم شما در اینجا -->
            </div>
        </div>
        <?php
    }

    /**
     * Render Shortcode
     * رندر شورت‌کد
     * 
     * Usage: [my_awesome_shortcode]
     */
    public function render_shortcode( $atts ) {
        if ( ! $this->license_valid ) {
            return '<p>' . __( 'Please activate your license to use this feature.', 'my-awesome-plugin' ) . '</p>';
        }

        $atts = shortcode_atts( array(
            'title' => __( 'Default Title', 'my-awesome-plugin' ),
            'content' => __( 'Default Content', 'my-awesome-plugin' ),
        ), $atts );

        ob_start();
        ?>
        <div class="my-awesome-plugin-shortcode">
            <h3><?php echo esc_html( $atts['title'] ); ?></h3>
            <p><?php echo esc_html( $atts['content'] ); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get License Status
     * دریافت وضعیت لایسنس
     * 
     * @return bool
     */
    public function is_licensed() {
        return $this->license_valid;
    }

    /**
     * Get License Client
     * دریافت کلاینت لایسنس
     * 
     * @return Nias_License_Manager_Client|null
     */
    public function get_license_client() {
        return $this->license_client;
    }
}

/**
 * Initialize Plugin
 * مقداردهی اولیه افزونه
 */
function my_awesome_plugin_init() {
    return new My_Awesome_Plugin();
}

// Start the plugin | شروع افزونه
add_action( 'plugins_loaded', 'my_awesome_plugin_init' );

/**
 * Helper function to get plugin instance
 * تابع کمکی برای دریافت نمونه افزونه
 * 
 * @return My_Awesome_Plugin
 */
function my_awesome_plugin() {
    static $instance = null;
    
    if ( null === $instance ) {
        $instance = my_awesome_plugin_init();
    }
    
    return $instance;
}
