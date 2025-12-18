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
    private $translations = array();

    /**
     * Constructor
     * سازنده کلاس
     * 
     * @param string $plugin_name Plugin display name | نام نمایشی افزونه
     * @param string $plugin_slug Plugin slug for settings | اسلاگ افزونه برای تنظیمات
     */
    public function __construct( $plugin_name = 'My Plugin', $plugin_slug = 'my-plugin' ) {
        $this->plugin_name = $plugin_name;
        $this->plugin_slug = $plugin_slug;
        
        // Load translations | بارگذاری ترجمه‌ها
        $this->load_translations();
        
        // Initialize license client | مقداردهی اولیه کلاینت لایسنس
        $this->nias_init_license_client();

        // Add admin hooks | افزودن هوک‌های مدیریتی
        add_action( 'admin_menu', array( $this, 'nias_add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'nias_register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'nias_enqueue_admin_styles' ) );
        add_action( 'admin_notices', array( $this, 'nias_show_license_notices' ) );
    }

    /**
     * Load Translations
     * بارگذاری ترجمه‌ها
     */
    private function load_translations() {
        $translations_file = dirname( __FILE__ ) . '/translations.php';
        
        if ( file_exists( $translations_file ) ) {
            $this->translations = include $translations_file;
        }
    }

    /**
     * Get Translation
     * دریافت ترجمه
     * 
     * @param string $key Translation key | کلید ترجمه
     * @return string Translated text | متن ترجمه شده
     */
    private function __( $key ) {
        if ( isset( $this->translations[ $key ] ) ) {
            return $this->translations[ $key ];
        }
        return $key;
    }

    /**
     * Initialize License Client
     * مقداردهی اولیه کلاینت لایسنس
     */
    private function nias_init_license_client() {
        $store_url = get_option( 'nias_' . $this->plugin_slug . '_store_url', '' );
        $consumer_key = get_option( 'nias_' . $this->plugin_slug . '_consumer_key', '' );
        $consumer_secret = get_option( 'nias_' . $this->plugin_slug . '_consumer_secret', '' );
        $product_ids = get_option( 'nias_' . $this->plugin_slug . '_product_ids', array() );
        $cache_days = get_option( 'nias_' . $this->plugin_slug . '_cache_days', 5 );

        $this->license_client = new Nias_License_Manager_Client( 
            $store_url, 
            $consumer_key, 
            $consumer_secret,
            $product_ids,
            $cache_days
        );
    }

    /**
     * Add Settings Page to Admin Menu
     * افزودن صفحه تنظیمات به منوی مدیریت
     */
    public function nias_add_settings_page() {
        add_options_page(
            sprintf( $this->__( '%s License' ), $this->plugin_name ),
            sprintf( $this->__( '%s License' ), $this->plugin_name ),
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
        register_setting( 'nias_' . $this->plugin_slug . '_license_group', 'nias_' . $this->plugin_slug . '_store_url' );
        register_setting( 'nias_' . $this->plugin_slug . '_license_group', 'nias_' . $this->plugin_slug . '_consumer_key' );
        register_setting( 'nias_' . $this->plugin_slug . '_license_group', 'nias_' . $this->plugin_slug . '_consumer_secret' );
        register_setting( 'nias_' . $this->plugin_slug . '_license_group', 'nias_' . $this->plugin_slug . '_product_ids', array(
            'sanitize_callback' => array( $this, 'sanitize_product_ids' )
        ) );
        register_setting( 'nias_' . $this->plugin_slug . '_license_group', 'nias_' . $this->plugin_slug . '_cache_days' );
        register_setting( 'nias_' . $this->plugin_slug . '_license_group', 'nias_' . $this->plugin_slug . '_license_key' );
        register_setting( 'nias_' . $this->plugin_slug . '_license_group', 'nias_' . $this->plugin_slug . '_license_status' );
        register_setting( 'nias_' . $this->plugin_slug . '_license_group', 'nias_' . $this->plugin_slug . '_license_data' );

        // Handle license activation/deactivation | مدیریت فعال‌سازی/غیرفعال‌سازی لایسنس
        if ( isset( $_POST['nias_activate_license'] ) ) {
            $this->nias_handle_license_activation();
        }

        if ( isset( $_POST['nias_deactivate_license'] ) ) {
            $this->nias_handle_license_deactivation();
        }

        if ( isset( $_POST['nias_check_license'] ) ) {
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
                max-width: 900px;
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

        $license_key = get_option( 'nias_' . $this->plugin_slug . '_license_key', '' );
        $license_status = get_option( 'nias_' . $this->plugin_slug . '_license_status', 'inactive' );
        $license_data = get_option( 'nias_' . $this->plugin_slug . '_license_data', array() );

        ?>
        <div class="wrap">
            <h1><?php echo esc_html( sprintf( $this->__( '%s License Settings' ), $this->plugin_name ) ); ?></h1>
            
            <div class="nias-license-container">
                
                <!-- API Configuration Box -->
                <div class="nias-license-box">
                    <div class="nias-license-header">
                        <h2>⚙️ <?php echo $this->__( 'API Configuration' ); ?></h2>
                    </div>
                    
                    <form method="post" action="options.php">
                        <?php settings_fields( 'nias_' . $this->plugin_slug . '_license_group' ); ?>
                        
                        <div class="nias-form-row">
                            <label for="store_url">
                                🌐 <?php echo $this->__( 'Store URL' ); ?>
                            </label>
                            <input type="text" 
                                   id="store_url" 
                                   name="nias_<?php echo $this->plugin_slug; ?>_store_url" 
                                   value="<?php echo esc_attr( get_option( 'nias_' . $this->plugin_slug . '_store_url', '' ) ); ?>" 
                                   placeholder="https://yourstore.com"
                                   class="regular-text">
                            <small>
                                <?php echo $this->__( 'Enter your WooCommerce store URL where License Manager is installed.' ); ?>
                            </small>
                        </div>

                        <div class="nias-form-row">
                            <label for="consumer_key">
                                🔑 <?php echo $this->__( 'Consumer Key' ); ?>
                            </label>
                            <input type="text" 
                                   id="consumer_key" 
                                   name="nias_<?php echo $this->plugin_slug; ?>_consumer_key" 
                                   value="<?php echo esc_attr( get_option( 'nias_' . $this->plugin_slug . '_consumer_key', '' ) ); ?>" 
                                   class="regular-text"
                                   placeholder="ck_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                            <small>
                                <?php echo $this->__( 'Get this from License Manager > Settings > REST API.' ); ?>
                            </small>
                        </div>

                        <div class="nias-form-row">
                            <label for="consumer_secret">
                                🔐 <?php echo $this->__( 'Consumer Secret' ); ?>
                            </label>
                            <input type="password" 
                                   id="consumer_secret" 
                                   name="nias_<?php echo $this->plugin_slug; ?>_consumer_secret" 
                                   value="<?php echo esc_attr( get_option( 'nias_' . $this->plugin_slug . '_consumer_secret', '' ) ); ?>" 
                                   class="regular-text"
                                   placeholder="cs_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                            <small>
                                <?php echo $this->__( 'Get this from License Manager > Settings > REST API.' ); ?>
                            </small>
                        </div>

                        <div class="nias-form-row">
                            <label for="product_ids">
                                📦 <?php echo $this->__( 'Product IDs' ); ?>
                            </label>
                            <?php 
                            $product_ids = get_option( 'nias_' . $this->plugin_slug . '_product_ids', array() );
                            $product_ids_string = is_array( $product_ids ) ? implode( ', ', $product_ids ) : '';
                            ?>
                            <input type="text" 
                                   id="product_ids" 
                                   name="nias_<?php echo $this->plugin_slug; ?>_product_ids" 
                                   value="<?php echo esc_attr( $product_ids_string ); ?>" 
                                   class="regular-text"
                                   placeholder="5735, 5736, 5737">
                            <small>
                                <?php echo $this->__( 'Enter product IDs from your store (comma-separated). Leave empty to accept any product.' ); ?>
                                <br>
                                <?php echo $this->__( 'شناسه محصولات فروشگاه خود را وارد کنید (با کاما جدا شوند). برای پذیرش هر محصولی، خالی بگذارید.' ); ?>
                            </small>
                        </div>

                        <div class="nias-form-row">
                            <label for="cache_days">
                                ⏱️ <?php echo $this->__( 'Cache Duration (Days)' ); ?>
                            </label>
                            <input type="number" 
                                   id="cache_days" 
                                   name="nias_<?php echo $this->plugin_slug; ?>_cache_days" 
                                   value="<?php echo esc_attr( get_option( 'nias_' . $this->plugin_slug . '_cache_days', 5 ) ); ?>" 
                                   min="1"
                                   max="30"
                                   class="regular-text"
                                   placeholder="5">
                            <small>
                                <?php echo $this->__( 'How many days to cache license validation results (1-30 days).' ); ?>
                                <br>
                                <?php echo $this->__( 'چند روز نتایج اعتبارسنجی لایسنس کش شوند (1 تا 30 روز).' ); ?>
                            </small>
                        </div>

                        <?php submit_button( $this->__( 'Save API Settings' ), 'primary', 'submit', false ); ?>
                    </form>
                </div>

                <!-- License Activation Box -->
                <div class="nias-license-box">
                    <div class="nias-license-header">
                        <h2>🎫 <?php echo $this->__( 'License Activation' ); ?></h2>
                        <?php if ( $license_status === 'active' ): ?>
                            <span class="nias-status-badge nias-status-active">✓ <?php echo $this->__( 'Active' ); ?></span>
                        <?php else: ?>
                            <span class="nias-status-badge nias-status-inactive">✗ <?php echo $this->__( 'Inactive' ); ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if ( $license_status === 'active' && ! empty( $license_data ) ): ?>
                        <!-- Active License Display -->
                        <div class="nias-notice nias-notice-success">
                            <span class="nias-notice-icon">✓</span>
                            <div class="nias-notice-content">
                                <strong><?php echo $this->__( 'License Active' ); ?></strong>
                                <p style="margin: 5px 0 0 0;"><?php echo $this->__( 'Your license is currently active and valid.' ); ?></p>
                            </div>
                        </div>

                        <div class="nias-license-info">
                            <div class="nias-license-info-row">
                                <span class="nias-license-info-label">🎫 <?php echo $this->__( 'License Key' ); ?>:</span>
                                <span class="nias-license-info-value nias-license-key-display"><?php echo esc_html( $license_key ); ?></span>
                            </div>
                            
                            <?php if ( isset( $license_data['expiresAt'] ) && ! empty( $license_data['expiresAt'] ) ): 
                                $expiry_timestamp = strtotime( $license_data['expiresAt'] );
                                $days_remaining = ceil( ( $expiry_timestamp - time() ) / DAY_IN_SECONDS );
                            ?>
                            <div class="nias-license-info-row">
                                <span class="nias-license-info-label">📅 <?php echo $this->__( 'Expires At' ); ?>:</span>
                                <span class="nias-license-info-value">
                                    <?php echo esc_html( date_i18n( 'F j, Y', $expiry_timestamp ) ); ?>
                                    <br>
                                    <small style="color: <?php echo $days_remaining <= 7 ? '#d63638' : '#50575e'; ?>;">
                                        (<?php echo $days_remaining; ?> <?php echo $this->__( 'days remaining' ); ?>)
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
                                <span class="nias-license-info-label">🔄 <?php echo $this->__( 'Activations' ); ?>:</span>
                                <span class="nias-license-info-value">
                                    <?php echo sprintf( $this->__( '%d of %d used' ), $activated, $max ); ?>
                                    <div class="nias-progress-bar">
                                        <div class="nias-progress-fill" style="width: <?php echo esc_attr( $percentage ); ?>%;"></div>
                                    </div>
                                </span>
                            </div>
                            <?php endif; ?>

                            <?php if ( isset( $license_data['productId'] ) ): ?>
                            <div class="nias-license-info-row">
                                <span class="nias-license-info-label">📦 <?php echo $this->__( 'Product ID' ); ?>:</span>
                                <span class="nias-license-info-value"><?php echo esc_html( $license_data['productId'] ); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Quick Stats Cards -->
                        <?php if ( isset( $license_data['timesActivatedMax'] ) && isset( $license_data['expiresAt'] ) ): ?>
                        <div class="nias-card-grid">
                            <div class="nias-info-card">
                                <div class="nias-info-card-icon">📊</div>
                                <div class="nias-info-card-title"><?php echo $this->__( 'Status' ); ?></div>
                                <div class="nias-info-card-value" style="color: #46b450;">
                                    <?php echo $this->__( 'Active' ); ?>
                                </div>
                            </div>
                            <div class="nias-info-card">
                                <div class="nias-info-card-icon">⏳</div>
                                <div class="nias-info-card-title"><?php echo $this->__( 'Days Remaining' ); ?></div>
                                <div class="nias-info-card-value" style="color: <?php echo $days_remaining <= 7 ? '#d63638' : '#2271b1'; ?>;">
                                    <?php echo $days_remaining; ?>
                                </div>
                            </div>
                            <div class="nias-info-card">
                                <div class="nias-info-card-icon">🔓</div>
                                <div class="nias-info-card-title"><?php echo $this->__( 'Available Slots' ); ?></div>
                                <div class="nias-info-card-value">
                                    <?php echo max( 0, $max - $activated ); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <form method="post" class="nias-btn-group">
                            <?php wp_nonce_field( 'nias_license_action', 'nias_license_nonce' ); ?>
                            <button type="submit" name="nias_check_license" class="nias-btn nias-btn-secondary">
                                🔄 <?php echo $this->__( 'Check License' ); ?>
                            </button>
                            <button type="submit" name="nias_deactivate_license" class="nias-btn nias-btn-danger" 
                                    onclick="return confirm('<?php echo esc_js( $this->__( 'Are you sure you want to deactivate this license?' ) ); ?>');">
                                ✗ <?php echo $this->__( 'Deactivate License' ); ?>
                            </button>
                        </form>

                    <?php else: ?>
                        <!-- License Activation Form -->
                        <?php if ( ! empty( $license_key ) && $license_status !== 'active' ): ?>
                        <div class="nias-notice nias-notice-warning">
                            <span class="nias-notice-icon">⚠</span>
                            <div class="nias-notice-content">
                                <strong><?php echo $this->__( 'License Not Active' ); ?></strong>
                                <p style="margin: 5px 0 0 0;"><?php echo $this->__( 'Please activate your license to use premium features.' ); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <form method="post">
                            <?php wp_nonce_field( 'nias_license_action', 'nias_license_nonce' ); ?>
                            
                            <div class="nias-form-row">
                                <label for="license_key">
                                    🎫 <?php echo $this->__( 'License Key' ); ?>
                                </label>
                                <input type="text" 
                                       id="license_key" 
                                       name="nias_license_key" 
                                       value="<?php echo esc_attr( $license_key ); ?>" 
                                       class="regular-text" 
                                       placeholder="XXXX-XXXX-XXXX-XXXX"
                                       required
                                       style="font-family: 'Courier New', monospace;">
                                <small>
                                    <?php echo $this->__( 'Enter your license key to activate the plugin.' ); ?>
                                </small>
                            </div>

                            <div class="nias-btn-group">
                                <button type="submit" name="nias_activate_license" class="nias-btn nias-btn-primary">
                                    ✓ <?php echo $this->__( 'Activate License' ); ?>
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Help Box -->
                <div class="nias-license-box" style="background: #f0f6fc; border-color: #c5d9ed;">
                    <div class="nias-license-header" style="border-color: #c5d9ed;">
                        <h2>❓ <?php echo $this->__( 'Need Help?' ); ?></h2>
                    </div>
                    <p><?php echo $this->__( 'Visit our documentation for more information.' ); ?></p>
                    <div class="nias-btn-group">
                        <a href="https://licensemanager.at/docs" target="_blank" class="nias-btn nias-btn-secondary">
                            📚 <?php echo $this->__( 'Documentation' ); ?>
                        </a>
                        <a href="<?php echo admin_url( 'admin.php?page=' . $this->plugin_slug ); ?>" class="nias-btn nias-btn-secondary">
                            🏠 <?php echo $this->__( 'Back to Dashboard' ); ?>
                        </a>
                    </div>
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
        if ( ! check_admin_referer( 'nias_license_action', 'nias_license_nonce' ) ) {
            return;
        }

        $license_key = isset( $_POST['nias_license_key'] ) ? sanitize_text_field( $_POST['nias_license_key'] ) : '';

        if ( empty( $license_key ) ) {
            add_settings_error(
                'nias_license',
                'empty_license',
                $this->__( 'Please enter a license key.' ),
                'error'
            );
            return;
        }

        // Re-initialize client with current settings
        // مقداردهی مجدد کلاینت با تنظیمات فعلی
        $this->nias_init_license_client();

        $result = $this->license_client->nias_activate_license( $license_key );

        if ( $result ) {
            update_option( 'nias_' . $this->plugin_slug . '_license_key', $license_key );
            update_option( 'nias_' . $this->plugin_slug . '_license_status', 'active' );
            update_option( 'nias_' . $this->plugin_slug . '_license_data', $result );
            update_option( 'nias_' . $this->plugin_slug . '_last_check', time() );

            add_settings_error(
                'nias_license',
                'license_activated',
                $this->__( 'License activated successfully!' ),
                'success'
            );
        } else {
            $error = $this->license_client->nias_get_last_error();
            add_settings_error(
                'nias_license',
                'activation_failed',
                sprintf( 
                    $this->__( 'License activation failed: %s' ), 
                    $error ? $error : $this->__( 'Unknown error' )
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
        if ( ! check_admin_referer( 'nias_license_action', 'nias_license_nonce' ) ) {
            return;
        }

        $license_key = get_option( 'nias_' . $this->plugin_slug . '_license_key', '' );

        if ( empty( $license_key ) ) {
            return;
        }

        // Re-initialize client
        // مقداردهی مجدد کلاینت
        $this->nias_init_license_client();

        $result = $this->license_client->nias_deactivate_license( $license_key );

        if ( $result ) {
            update_option( 'nias_' . $this->plugin_slug . '_license_status', 'inactive' );
            delete_option( 'nias_' . $this->plugin_slug . '_license_data' );

            add_settings_error(
                'nias_license',
                'license_deactivated',
                $this->__( 'License deactivated successfully!' ),
                'success'
            );
        } else {
            $error = $this->license_client->nias_get_last_error();
            add_settings_error(
                'nias_license',
                'deactivation_failed',
                sprintf( 
                    $this->__( 'License deactivation failed: %s' ), 
                    $error ? $error : $this->__( 'Unknown error' )
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
        if ( ! check_admin_referer( 'nias_license_action', 'nias_license_nonce' ) ) {
            return;
        }

        $license_key = get_option( 'nias_' . $this->plugin_slug . '_license_key', '' );

        if ( empty( $license_key ) ) {
            add_settings_error(
                'nias_license',
                'no_license',
                $this->__( 'No license key found.' ),
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
                update_option( 'nias_' . $this->plugin_slug . '_license_status', 'active' );
                update_option( 'nias_' . $this->plugin_slug . '_license_data', $result );
                update_option( 'nias_' . $this->plugin_slug . '_last_check', time() );

                add_settings_error(
                    'nias_license',
                    'license_valid',
                    $this->__( 'License is valid and active!' ),
                    'success'
                );
            } else {
                update_option( 'nias_' . $this->plugin_slug . '_license_status', 'inactive' );
                
                add_settings_error(
                    'nias_license',
                    'license_invalid',
                    $this->__( 'License is not active or has expired.' ),
                    'warning'
                );
            }
        } else {
            $error = $this->license_client->nias_get_last_error();
            add_settings_error(
                'nias_license',
                'check_failed',
                sprintf( 
                    $this->__( 'License check failed: %s' ), 
                    $error ? $error : $this->__( 'Unknown error' )
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
        $license_data = get_option( 'nias_' . $this->plugin_slug . '_license_data', array() );
        $license_status = get_option( 'nias_' . $this->plugin_slug . '_license_status', 'inactive' );

        if ( $license_status === 'active' && isset( $license_data['expiresAt'] ) && ! empty( $license_data['expiresAt'] ) ) {
            $expiry_date = strtotime( $license_data['expiresAt'] );
            $days_until_expiry = ceil( ( $expiry_date - time() ) / DAY_IN_SECONDS );

            if ( $days_until_expiry <= 0 ) {
                echo '<div class="notice notice-error"><p>';
                echo '<strong>' . $this->__( 'License Expired!' ) . '</strong> ';
                echo $this->__( 'Your license has expired! Please renew to continue receiving updates.' );
                echo '</p></div>';
            } elseif ( $days_until_expiry <= 30 ) {
                echo '<div class="notice notice-warning"><p>';
                echo '<strong>' . $this->__( 'License Expiring Soon!' ) . '</strong> ';
                echo sprintf( $this->__( 'Your license will expire in %d days.' ), $days_until_expiry );
                echo '</p></div>';
            }
        }
    }
}
