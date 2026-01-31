<?php
/**
 * Nias License Settings Page Class
 * کلاس صفحه تنظیمات لایسنس نیاس
 *
 * @package NLMW
 * @version 1.0.0
 * 
 * Description: Admin settings page for license configuration
 * توضیحات: صفحه تنظیمات مدیریت برای پیکربندی لایسنس
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly | خروج در صورت دسترسی مستقیم
}

/**
 * Class Nias_License_Settings_Page
 * 
 * Creates and manages the license settings page in WordPress admin
 * ایجاد و مدیریت صفحه تنظیمات لایسنس در پنل مدیریت وردپرس
 */
class Nias_License_Settings_Page {

    /**
     * License Client Instance
     * نمونه کلاینت لایسنس
     * 
     * @var Nias_License_Manager_Client
     */
    private $license_client;

    /**
     * Plugin Name
     * نام افزونه
     * 
     * @var string
     */
    private $plugin_name;

    /**
     * Plugin Slug
     * اسلاگ افزونه
     * 
     * @var string
     */
    private $plugin_slug;

    /**
     * Translations
     * ترجمه‌ها
     * 
     * @var array
     */
    // private $translations = array();

    /**
     * Plugin ID
     * شناسه پلاگین
     * 
     * @var string
     */
    private $plugin_id = '';

    /**
     * API Configuration
     * تنظیمات API
     * 
     * @var array
     */
    private $api_config = array();

    /**
     * Constructor
     * سازنده کلاس
     * 
     * @param string $plugin_name Plugin display name | نام نمایشی افزونه
     * @param string $plugin_slug Plugin slug for settings | اسلاگ افزونه برای تنظیمات
     * @param string $plugin_id Unique plugin identifier | شناسه منحصر به فرد پلاگین
     * @param array $api_config API Configuration (url, consumer_key, consumer_secret) | تنظیمات API
     */
    public function __construct( $plugin_name = 'My Plugin', $plugin_slug = 'my-plugin', $plugin_id = '', $api_config = array() ) {
        $this->plugin_name = $plugin_name;
        $this->plugin_slug = $plugin_slug;
        $this->plugin_id = sanitize_key( $plugin_id );
        $this->api_config = $api_config;
        
        // Load translations | بارگذاری ترجمه‌ها
        // $this->load_translations();
        
        // Initialize license client | مقداردهی اولیه کلاینت لایسنس
        $this->nias_init_license_client();

        // Add admin hooks | افزودن هوک‌های مدیریتی
        add_action( 'admin_menu', array( $this, 'nias_add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'nias_register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'nias_enqueue_admin_styles' ) );
        add_action( 'admin_notices', array( $this, 'nias_show_license_notices' ) );
    }
    

    /**
     * Initialize License Client
     * مقداردهی اولیه کلاینت لایسنس
     */
    private function nias_init_license_client() {
        $store_url = ! empty( $this->api_config['url'] ) ? $this->api_config['url'] : get_option( 'nlmw_' . $this->plugin_slug . '_store_url', '' );
        $consumer_key = ! empty( $this->api_config['consumer_key'] ) ? $this->api_config['consumer_key'] : get_option( 'nlmw_' . $this->plugin_slug . '_consumer_key', '' );
        $consumer_secret = ! empty( $this->api_config['consumer_secret'] ) ? $this->api_config['consumer_secret'] : get_option( 'nlmw_' . $this->plugin_slug . '_consumer_secret', '' );
        $store_url = rtrim( trim( $store_url, " \t\n\r\0\x0B`\"'" ), '/' );
        $consumer_key = trim( $consumer_key );
        $consumer_secret = trim( $consumer_secret );

        $product_ids = ! empty( $this->api_config['product_ids'] ) ? (array) $this->api_config['product_ids'] : get_option( 'nlmw_' . $this->plugin_slug . '_product_ids', array() );
        $cache_days = get_option( 'nlmw_' . $this->plugin_slug . '_cache_days', 5 );

        $this->license_client = new Nias_License_Manager_Client( 
            $store_url, 
            $consumer_key, 
            $consumer_secret,
            $product_ids,
            $cache_days,
            $this->plugin_id
        );
        $this->license_client->nias_set_strict_validation( true );
        $this->license_client->nias_set_enforce_product_ids( true );
    }

    /**
     * Add Settings Page to Admin Menu
     * افزودن صفحه تنظیمات به منوی مدیریت
     */
    public function nias_add_settings_page() {
        add_options_page(
            sprintf( 'لایسنس %s', $this->plugin_name ),
            sprintf( 'لایسنس %s', $this->plugin_name ),
            'manage_options',
            $this->plugin_slug . '-license',
            array( $this, 'nias_render_settings_page' )
        );
    }

    /**
     * Register Settings
     * ثبت تنظیمات
     */
    public function nias_register_settings() {
        // Register settings | ثبت تنظیمات
        register_setting( 'nlmw_' . $this->plugin_slug . '_license_group', 'nlmw_' . $this->plugin_slug . '_store_url' );
        register_setting( 'nlmw_' . $this->plugin_slug . '_license_group', 'nlmw_' . $this->plugin_slug . '_consumer_key' );
        register_setting( 'nlmw_' . $this->plugin_slug . '_license_group', 'nlmw_' . $this->plugin_slug . '_consumer_secret' );
        register_setting( 'nlmw_' . $this->plugin_slug . '_license_group', 'nlmw_' . $this->plugin_slug . '_product_ids', array(
            'sanitize_callback' => array( $this, 'sanitize_product_ids' )
        ) );
        register_setting( 'nlmw_' . $this->plugin_slug . '_license_group', 'nlmw_' . $this->plugin_slug . '_cache_days' );
        register_setting( 'nlmw_' . $this->plugin_slug . '_license_group', 'nlmw_' . $this->plugin_slug . '_license_key' );
        register_setting( 'nlmw_' . $this->plugin_slug . '_license_group', 'nlmw_' . $this->plugin_slug . '_license_status' );
        register_setting( 'nlmw_' . $this->plugin_slug . '_license_group', 'nlmw_' . $this->plugin_slug . '_license_data' );

        // Handle license activation/deactivation | مدیریت فعال‌سازی/غیرفعال‌سازی لایسنس
        if ( isset( $_POST['nlmw_activate_license'] ) ) {
            $this->nias_handle_license_activation();
        }

        if ( isset( $_POST['nlmw_deactivate_license'] ) ) {
            $this->nias_handle_license_deactivation();
        }

        if ( isset( $_POST['nlmw_check_license'] ) ) {
            $this->nias_handle_license_check();
        }
    }

    /**
     * Sanitize Product IDs
     * پاک‌سازی شناسه محصولات
     * 
     * @param string $input Input string | رشته ورودی
     * @return array Array of product IDs | آرایه شناسه محصولات
     */
    public function sanitize_product_ids( $input ) {
        if ( empty( $input ) ) {
            return array();
        }

        // Convert comma-separated string to array
        // تبدیل رشته جدا شده با کاما به آرایه
        $ids = array_map( 'trim', explode( ',', $input ) );
        $ids = array_map( 'absint', $ids );
        $ids = array_filter( $ids ); // Remove zeros

        return $ids;
    }

    /**
     * Enqueue Admin Styles
     * بارگذاری استایل‌های مدیریتی
     */
    public function nias_enqueue_admin_styles( $hook ) {
        if ( $hook !== 'settings_page_' . $this->plugin_slug . '-license' ) {
            return;
        }

        wp_add_inline_style( 'wp-admin', '
            .nias-license-container {
                margin: 20px 0;
            }
            .nias-license-box {
                background: #fff;
                border: 1px solid #c3c4c7;
                border-radius: 4px;
                padding: 25px;
                margin-bottom: 20px;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .nias-license-header {
                border-bottom: 1px solid #dcdcde;
                padding-bottom: 15px;
                margin-bottom: 25px;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            .nias-license-header h2 {
                margin: 0;
                font-size: 18px;
                font-weight: 600;
                color: #1d2327;
            }
            .nias-form-row {
                margin-bottom: 20px;
            }
            .nias-form-row label {
                display: block;
                margin-bottom: 8px;
                font-weight: 600;
                color: #1d2327;
                font-size: 14px;
            }
            .nias-form-row input[type="text"],
            .nias-form-row input[type="password"] {
                width: 100%;
                max-width: 600px;
                padding: 8px 12px;
                font-size: 14px;
                line-height: 1.5;
                border: 1px solid #8c8f94;
                border-radius: 4px;
                background-color: #fff;
                transition: border-color 0.2s;
            }
            .nias-form-row input[type="text"]:focus,
            .nias-form-row input[type="password"]:focus {
                border-color: #2271b1;
                outline: 2px solid transparent;
                box-shadow: 0 0 0 1px #2271b1;
            }
            .nias-form-row small {
                display: block;
                margin-top: 5px;
                color: #646970;
                font-size: 13px;
                line-height: 1.5;
            }
            .nias-status-badge {
                display: inline-block;
                padding: 6px 14px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .nias-status-active {
                background: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            .nias-status-inactive {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            .nias-status-expired {
                background: #fff3cd;
                color: #856404;
                border: 1px solid #ffeaa7;
            }
            .nias-license-info {
                background: #f6f7f7;
                border: 1px solid #dcdcde;
                border-radius: 4px;
                padding: 20px;
                margin: 20px 0;
            }
            .nias-license-info-row {
                display: flex;
                justify-content: space-between;
                padding: 10px 0;
                border-bottom: 1px solid #dcdcde;
            }
            .nias-license-info-row:last-child {
                border-bottom: none;
            }
            .nias-license-info-label {
                font-weight: 600;
                color: #1d2327;
                font-size: 14px;
            }
            .nias-license-info-value {
                color: #50575e;
                font-size: 14px;
                text-align: right;
            }
            .nias-btn-group {
                display: flex;
                gap: 10px;
                margin-top: 20px;
                flex-wrap: wrap;
            }
            .nias-btn {
                padding: 8px 16px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
                font-weight: 500;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s ease;
                min-height: 36px;
            }
            .nias-btn-primary {
                background: #2271b1;
                color: #fff;
                border: 1px solid #2271b1;
            }
            .nias-btn-primary:hover {
                background: #135e96;
                border-color: #135e96;
                color: #fff;
            }
            .nias-btn-secondary {
                background: #fff;
                color: #2271b1;
                border: 1px solid #2271b1;
            }
            .nias-btn-secondary:hover {
                background: #f6f7f7;
                color: #135e96;
                border-color: #135e96;
            }
            .nias-btn-danger {
                background: #d63638;
                color: #fff;
                border: 1px solid #d63638;
            }
            .nias-btn-danger:hover {
                background: #b32d2e;
                border-color: #b32d2e;
                color: #fff;
            }
            .nias-notice {
                padding: 15px 20px;
                margin: 15px 0;
                border-left: 4px solid;
                border-radius: 4px;
                display: flex;
                align-items: flex-start;
                gap: 12px;
            }
            .nias-notice-icon {
                font-size: 20px;
                line-height: 1;
                flex-shrink: 0;
            }
            .nias-notice-content {
                flex: 1;
            }
            .nias-notice-success {
                background: #edfaed;
                border-color: #46b450;
                color: #1e4620;
            }
            .nias-notice-error {
                background: #fcf0f1;
                border-color: #d63638;
                color: #3c1518;
            }
            .nias-notice-warning {
                background: #fcf9e8;
                border-color: #dba617;
                color: #614200;
            }
            .nias-notice-info {
                background: #e5f5fa;
                border-color: #72aee6;
                color: #1d2327;
            }
            .nias-license-key-display {
                font-family: "Courier New", monospace;
                background: #f6f7f7;
                padding: 8px 12px;
                border-radius: 4px;
                font-size: 13px;
                word-break: break-all;
            }
            .nias-progress-bar {
                width: 100%;
                height: 6px;
                background: #f0f0f1;
                border-radius: 3px;
                overflow: hidden;
                margin-top: 8px;
            }
            .nias-progress-fill {
                height: 100%;
                background: linear-gradient(90deg, #2271b1 0%, #72aee6 100%);
                transition: width 0.3s ease;
            }
            .nias-card-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 15px;
                margin: 20px 0;
            }
            .nias-info-card {
                background: #fff;
                border: 1px solid #dcdcde;
                border-radius: 4px;
                padding: 15px;
                text-align: center;
            }
            .nias-info-card-icon {
                font-size: 32px;
                margin-bottom: 10px;
            }
            .nias-info-card-title {
                font-size: 12px;
                color: #646970;
                text-transform: uppercase;
                margin-bottom: 8px;
                font-weight: 600;
                letter-spacing: 0.5px;
            }
            .nias-info-card-value {
                font-size: 24px;
                font-weight: 600;
                color: #1d2327;
            }
            @media (max-width: 782px) {
                .nias-license-container {
                    max-width: 100%;
                }
                .nias-license-info-row {
                    flex-direction: column;
                    gap: 5px;
                }
                .nias-license-info-value {
                    text-align: left;
                }
                .nias-btn-group {
                    flex-direction: column;
                }
                .nias-btn {
                    width: 100%;
                }
            }
        ' );
    }

    /**
     * Render Settings Page
     * رندر صفحه تنظیمات
     */
    public function nias_render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $license_key = get_option( 'nlmw_' . $this->plugin_slug . '_license_key', '' );
        $license_status = get_option( 'nlmw_' . $this->plugin_slug . '_license_status', 'inactive' );
        $license_data = get_option( 'nlmw_' . $this->plugin_slug . '_license_data', array() );

        ?>
        <div class="wrap">
            <h1><?php echo esc_html( sprintf( 'تنظیمات لایسنس %s', $this->plugin_name ) ); ?></h1>
            
            <div class="nias-license-container">
                
                <!-- License Activation Box -->
                <div class="nias-license-box">
                    <div class="nias-license-header">
                        <h2>🎫 <?php echo 'فعال‌سازی لایسنس'; ?></h2>
                        <?php if ( $license_status === 'active' ): ?>
                            <span class="nias-status-badge nias-status-active">✓ <?php echo 'فعال'; ?></span>
                        <?php else: ?>
                            <span class="nias-status-badge nias-status-inactive">✗ <?php echo 'غیرفعال'; ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if ( $license_status === 'active' && ! empty( $license_data ) ): ?>
                        <!-- Active License Display -->
                        <div class="nias-notice nias-notice-success">
                            <span class="nias-notice-icon">✓</span>
                            <div class="nias-notice-content">
                                <strong><?php echo 'لایسنس فعال'; ?></strong>
                                <p style="margin: 5px 0 0 0;"><?php echo 'لایسنس شما در حال حاضر فعال و معتبر است.'; ?></p>
                            </div>
                        </div>

                        <div class="nias-license-info">
                            <div class="nias-license-info-row">
                                <span class="nias-license-info-label">🎫 <?php echo 'کلید لایسنس'; ?>:</span>
                                <span class="nias-license-info-value nias-license-key-display"><?php echo esc_html( $license_key ); ?></span>
                            </div>
                            
                            <?php if ( isset( $license_data['expiresAt'] ) && ! empty( $license_data['expiresAt'] ) ): 
                                $expiry_timestamp = strtotime( $license_data['expiresAt'] );
                                $days_remaining = ceil( ( $expiry_timestamp - time() ) / DAY_IN_SECONDS );
                            ?>
                            <div class="nias-license-info-row">
                                <span class="nias-license-info-label">📅 <?php echo 'تاریخ انقضا'; ?>:</span>
                                <span class="nias-license-info-value">
                                    <?php echo esc_html( date_i18n( 'F j, Y', $expiry_timestamp ) ); ?>
                                    <br>
                                    <small style="color: <?php echo $days_remaining <= 7 ? '#d63638' : '#50575e'; ?>;">
                                        (<?php echo $days_remaining; ?> <?php echo 'روز باقی مانده'; ?>)
                                    </small>
                                </span>
                            </div>
                            <?php endif; ?>

                            <?php if ( isset( $license_data['timesActivatedMax'] ) && $license_data['timesActivatedMax'] > 0 ): 
                                $activated = isset( $license_data['timesActivated'] ) ? intval( $license_data['timesActivated'] ) : 0;
                                $max = intval( $license_data['timesActivatedMax'] );
                                $percentage = $max > 0 ? ( $activated / $max ) * 100 : 0;
                            ?>
                            <div class="nias-license-info-row">
                                <span class="nias-license-info-label">🔄 <?php echo 'فعال‌سازی‌ها'; ?>:</span>
                                <span class="nias-license-info-value">
                                    <?php echo sprintf( '%1$d از %2$d استفاده شده', $activated, $max ); ?>
                                    <div class="nias-progress-bar">
                                        <div class="nias-progress-fill" style="width: <?php echo esc_attr( $percentage ); ?>%;"></div>
                                    </div>
                                </span>
                            </div>
                            <?php endif; ?>

                            <?php if ( isset( $license_data['productId'] ) ): ?>
                            <div class="nias-license-info-row">
                                <span class="nias-license-info-label">📦 <?php echo 'شناسه محصول'; ?>:</span>
                                <span class="nias-license-info-value"><?php echo esc_html( $license_data['productId'] ); ?></span>
                            </div>
                            <?php 
                                $allowed_products = ! empty( $this->api_config['product_ids'] ) 
                                    ? (array) $this->api_config['product_ids'] 
                                    : (array) get_option( 'nlmw_' . $this->plugin_slug . '_product_ids', array() );
                                $allowed_text = empty( $allowed_products ) ? 'همه' : implode( ', ', array_map( 'intval', $allowed_products ) );
                                $mismatch = ! empty( $allowed_products ) && ! in_array( intval( $license_data['productId'] ), array_map( 'intval', $allowed_products ), true );
                            ?>
                            <div class="nias-license-info-row">
                                <span class="nias-license-info-label">✅ <?php echo 'شناسه‌های مجاز'; ?>:</span>
                                <span class="nias-license-info-value">
                                    <?php echo esc_html( $allowed_text ); ?>
                                    <?php if ( $mismatch ): ?>
                                        <br><small style="color:#d63638;">❗ <?php echo 'شناسه این لایسنس با شناسه‌های مجاز مطابقت ندارد'; ?></small>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Quick Stats Cards -->
                        <?php if ( isset( $license_data['timesActivatedMax'] ) && isset( $license_data['expiresAt'] ) ): ?>
                        <div class="nias-card-grid">
                            <div class="nias-info-card">
                                <div class="nias-info-card-icon">📊</div>
                                <div class="nias-info-card-title"><?php echo 'وضعیت'; ?></div>
                                <div class="nias-info-card-value" style="color: #46b450;">
                                    <?php echo 'فعال'; ?>
                                </div>
                            </div>
                            <div class="nias-info-card">
                                <div class="nias-info-card-icon">⏳</div>
                                <div class="nias-info-card-title"><?php echo 'روز باقی مانده'; ?></div>
                                <div class="nias-info-card-value" style="color: <?php echo $days_remaining <= 7 ? '#d63638' : '#2271b1'; ?>;">
                                    <?php echo $days_remaining; ?>
                                </div>
                            </div>
                            <div class="nias-info-card">
                                <div class="nias-info-card-icon">🔓</div>
                                <div class="nias-info-card-title"><?php echo 'اسلات‌های موجود'; ?></div>
                                <div class="nias-info-card-value">
                                    <?php echo max( 0, $max - $activated ); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <form method="post" class="nias-btn-group">
                            <?php wp_nonce_field( 'nlmw_license_action', 'nlmw_license_nonce' ); ?>
                            <button type="submit" name="nlmw_check_license" class="nias-btn nias-btn-secondary">
                                🔄 <?php echo 'بررسی لایسنس'; ?>
                            </button>
                            <button type="submit" name="nlmw_deactivate_license" class="nias-btn nias-btn-danger" 
                                    onclick="return confirm('<?php echo esc_js( 'آیا مطمئن هستید که می‌خواهید این لایسنس را غیرفعال کنید؟' ); ?>');">
                                ✗ <?php echo 'غیرفعال‌سازی لایسنس'; ?>
                            </button>
                        </form>

                    <?php else: ?>
                        <!-- License Activation Form -->
                        <?php if ( ! empty( $license_key ) && $license_status !== 'active' ): ?>
                        <div class="nias-notice nias-notice-warning">
                            <span class="nias-notice-icon">⚠</span>
                            <div class="nias-notice-content">
                                <strong><?php echo 'لایسنس فعال نیست'; ?></strong>
                                <p style="margin: 5px 0 0 0;"><?php echo 'لطفاً برای استفاده از ویژگی‌های پریمیوم، لایسنس خود را فعال کنید.'; ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <form method="post">
                            <?php wp_nonce_field( 'nlmw_license_action', 'nlmw_license_nonce' ); ?>
                            
                            <div class="nias-form-row">
                                <label for="license_key">
                                    🎫 <?php echo 'کلید لایسنس'; ?>
                                </label>
                                <input type="text" 
                                       id="license_key" 
                                       name="nlmw_license_key" 
                                       value="<?php echo esc_attr( $license_key ); ?>" 
                                       class="regular-text" 
                                       placeholder="XXXX-XXXX-XXXX-XXXX"
                                       required
                                       style="font-family: 'Courier New', monospace;">
                                <small>
                                    <?php echo 'کلید لایسنس خود را برای فعال‌سازی افزونه وارد کنید.'; ?>
                                </small>
                            </div>

                            <div class="nias-btn-group">
                                <button type="submit" name="nlmw_activate_license" class="nias-btn nias-btn-primary">
                                    ✓ <?php echo 'فعال‌سازی لایسنس'; ?>
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>

            </div>
        </div>
        <?php
    }

    /**
     * Handle License Activation
     * مدیریت فعال‌سازی لایسنس
     */
    private function nias_handle_license_activation() {
        if ( ! check_admin_referer( 'nlmw_license_action', 'nlmw_license_nonce' ) ) {
            return;
        }

        $license_key = isset( $_POST['nlmw_license_key'] ) ? sanitize_text_field( $_POST['nlmw_license_key'] ) : '';

        if ( empty( $license_key ) ) {
            add_settings_error(
                'nias_license',
                'empty_license',
                'لطفاً کلید لایسنس را وارد کنید.',
                'error'
            );
            return;
        }

        $this->nias_init_license_client();

        $params = array(
            'label' => sanitize_text_field( get_bloginfo( 'name' ) ),
            'meta_data' => wp_json_encode( array(
                'site_url' => home_url(),
                'plugin' => $this->plugin_slug,
                'plugin_id' => $this->plugin_id,
            ) ),
        );
        $result = $this->license_client->nias_activate_license( $license_key, $params );

        if ( $result ) {
            update_option( 'nlmw_' . $this->plugin_slug . '_license_key', $license_key );
            update_option( 'nlmw_' . $this->plugin_slug . '_license_status', 'active' );
            update_option( 'nlmw_' . $this->plugin_slug . '_license_data', $result );
            update_option( 'nlmw_' . $this->plugin_slug . '_last_check', time() );

            add_settings_error(
                'nias_license',
                'license_activated',
                'لایسنس با موفقیت فعال شد!',
                'success'
            );
        } else {
            $error = $this->license_client->nias_get_last_error();
            $error_msg = $error ? $error : 'خطای نامشخص';
            update_option( 'nlmw_' . $this->plugin_slug . '_license_status', 'inactive' );
            delete_option( 'nlmw_' . $this->plugin_slug . '_license_data' );
            add_settings_error(
                'nias_license',
                'activation_failed',
                sprintf( 
                    'فعال‌سازی ناموفق: %1$s', 
                    $error_msg
                ),
                'error'
            );
        }
    }

    /**
     * Handle License Deactivation
     * مدیریت غیرفعال‌سازی لایسنس
     */
    private function nias_handle_license_deactivation() {
        if ( ! check_admin_referer( 'nlmw_license_action', 'nlmw_license_nonce' ) ) {
            return;
        }

        $license_key = get_option( 'nlmw_' . $this->plugin_slug . '_license_key', '' );

        if ( empty( $license_key ) ) {
            return;
        }

        // Re-initialize client
        // مقداردهی مجدد کلاینت
        $this->nias_init_license_client();

        $result = $this->license_client->nias_deactivate_license( $license_key );

        if ( $result ) {
            update_option( 'nlmw_' . $this->plugin_slug . '_license_status', 'inactive' );
            delete_option( 'nlmw_' . $this->plugin_slug . '_license_data' );

            add_settings_error(
                'nias_license',
                'license_deactivated',
                'لایسنس با موفقیت غیرفعال شد!',
                'success'
            );
        } else {
            $error = $this->license_client->nias_get_last_error();
            $error_msg = $error ? $error : 'خطای نامشخص';
            add_settings_error(
                'nias_license',
                'deactivation_failed',
                sprintf( 
                    'غیرفعال‌سازی ناموفق: %1$s', 
                    $error_msg
                ),
                'error'
            );
        }
    }

    /**
     * Handle License Check
     * مدیریت بررسی لایسنس
     */
    private function nias_handle_license_check() {
        if ( ! check_admin_referer( 'nlmw_license_action', 'nlmw_license_nonce' ) ) {
            return;
        }

        $license_key = get_option( 'nlmw_' . $this->plugin_slug . '_license_key', '' );

        if ( empty( $license_key ) ) {
            add_settings_error(
                'nias_license',
                'no_license',
                'کلید لایسنس یافت نشد.',
                'error'
            );
            return;
        }

        // Re-initialize client
        // مقداردهی مجدد کلاینت
        $this->nias_init_license_client();

        $result = $this->license_client->nias_validate_license( $license_key );

        if ( $result ) {
            // Check if still valid
            // بررسی معتبر بودن
            $is_valid = $this->license_client->nias_is_license_valid( $license_key );
            
            if ( $is_valid ) {
                update_option( 'nlmw_' . $this->plugin_slug . '_license_status', 'active' );
                update_option( 'nlmw_' . $this->plugin_slug . '_license_data', $result );
                update_option( 'nlmw_' . $this->plugin_slug . '_last_check', time() );

                add_settings_error(
                    'nias_license',
                    'license_valid',
                    'لایسنس معتبر و فعال است!',
                    'success'
                );
            } else {
                update_option( 'nlmw_' . $this->plugin_slug . '_license_status', 'inactive' );
                
                add_settings_error(
                    'nias_license',
                    'license_invalid',
                    'لایسنس فعال نیست یا منقضی شده است.',
                    'warning'
                );
            }
        } else {
            $error = $this->license_client->nias_get_last_error();
            $error_msg = $error ? $error : 'خطای نامشخص';
            add_settings_error(
                'nias_license',
                'check_failed',
                sprintf( 
                    'بررسی لایسنس ناموفق: %s', 
                    $error_msg
                ),
                'error'
            );
        }
    }

    /**
     * Show License Notices
     * نمایش اعلان‌های لایسنس
     */
    public function nias_show_license_notices() {
        $screen = get_current_screen();
        if ( $screen->id !== 'settings_page_' . $this->plugin_slug . '-license' ) {
            return;
        }

        settings_errors( 'nias_license' );

        // Check for expired license
        // بررسی انقضای لایسنس
        $license_data = get_option( 'nlmw_' . $this->plugin_slug . '_license_data', array() );
        $license_status = get_option( 'nlmw_' . $this->plugin_slug . '_license_status', 'inactive' );

        if ( $license_status === 'active' && isset( $license_data['expiresAt'] ) && ! empty( $license_data['expiresAt'] ) ) {
            $expiry_date = strtotime( $license_data['expiresAt'] );
            $days_until_expiry = ceil( ( $expiry_date - time() ) / DAY_IN_SECONDS );

            if ( $days_until_expiry <= 0 ) {
                echo '<div class="notice notice-error"><p>';
                echo '<strong>' . 'لایسنس منقضی شده است!' . '</strong> ';
                echo 'لایسنس شما منقضی شده است! لطفاً برای دریافت به‌روزرسانی‌ها، آن را تمدید کنید.';
                echo '</p></div>';
            } elseif ( $days_until_expiry <= 30 ) {
                echo '<div class="notice notice-warning"><p>';
                echo '<strong>' . 'لایسنس به زودی منقضی می‌شود!' . '</strong> ';
                echo sprintf( '%1$d روز دیگر منقضی می‌شود.', $days_until_expiry );
                echo '</p></div>';
            }
        }
    }
}

