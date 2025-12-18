<?php
/**
 * Nias License Settings Page Class
 * کلاس صفحه تنظیمات لایسنس نیاس
 *
 * Professional settings page for license management
 * صفحه تنظیمات حرفه‌ای برای مدیریت لایسنس
 *
 * @package NLMW
 * @subpackage Admin
 * @version 1.0.0
 * @author Nias Team
 */

// Prevent direct file access
// جلوگیری از دسترسی مستقیم به فایل
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Nias_License_Settings_Page
 * 
 * Manage license settings page in WordPress admin
 * مدیریت صفحه تنظیمات لایسنس در پنل مدیریت وردپرس
 * 
 * @since 1.0.0
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
     * Menu Slug
     * شناسه منو
     * 
     * @var string
     */
    private $menu_slug = 'nias-license-manager';
    
    /**
     * Constructor
     * سازنده کلاس
     * 
     * Initialize hooks and settings
     * مقداردهی اولیه هوک‌ها و تنظیمات
     */
    public function __construct() {
        // Add admin menu
        // افزودن منوی مدیریت
        add_action('admin_menu', [$this, 'nias_add_menu']);
        
        // Register settings
        // ثبت تنظیمات
        add_action('admin_init', [$this, 'nias_register_settings']);
        
        // Enqueue assets
        // بارگذاری دارایی‌ها
        add_action('admin_enqueue_scripts', [$this, 'nias_enqueue_assets']);
        
        // Handle form submissions
        // پردازش ارسال فرم‌ها
        add_action('admin_post_nias_activate_license', [$this, 'nias_handle_activate']);
        add_action('admin_post_nias_deactivate_license', [$this, 'nias_handle_deactivate']);
        add_action('admin_post_nias_validate_license', [$this, 'nias_handle_validate']);
        
        // Show admin notices
        // نمایش اعلان‌های مدیریت
        add_action('admin_notices', [$this, 'nias_license_notice']);
        
        // Initialize license client
        // مقداردهی اولیه کلاینت لایسنس
        $this->nias_init_license_client();
    }
    
    /**
     * Initialize License Client
     * مقداردهی اولیه کلاینت لایسنس
     * 
     * Load license client with saved settings
     * بارگذاری کلاینت لایسنس با تنظیمات ذخیره شده
     */
    private function nias_init_license_client() {
        $api_url = get_option('nias_license_api_url', '');
        $consumer_key = get_option('nias_license_consumer_key', '');
        $consumer_secret = get_option('nias_license_consumer_secret', '');
        
        if (!empty($api_url)) {
            $this->license_client = new Nias_License_Manager_Client(
                $api_url,
                $consumer_key,
                $consumer_secret
            );
        }
    }
    
    /**
     * Add Admin Menu
     * افزودن منوی مدیریت
     * 
     * Register menu page in WordPress admin
     * ثبت صفحه منو در پنل مدیریت وردپرس
     */
    public function nias_add_menu() {
        add_menu_page(
            nias_translate('License Management'), // Page title
            nias_translate('NLMW License'),       // Menu title
            'manage_options',                     // Capability
            $this->menu_slug,                     // Menu slug
            [$this, 'nias_render_page'],         // Callback
            'dashicons-admin-network',            // Icon
            100                                   // Position
        );
    }
    
    /**
     * Register Settings
     * ثبت تنظیمات
     * 
     * Register plugin settings in WordPress
     * ثبت تنظیمات افزونه در وردپرس
     */
    public function nias_register_settings() {
        register_setting('nias_license_settings_group', 'nias_license_api_url');
        register_setting('nias_license_settings_group', 'nias_license_consumer_key');
        register_setting('nias_license_settings_group', 'nias_license_consumer_secret');
        register_setting('nias_license_settings_group', 'nias_license_key');
    }
    
    /**
     * Enqueue Assets
     * بارگذاری دارایی‌ها
     * 
     * Load CSS and JavaScript for settings page
     * بارگذاری CSS و JavaScript برای صفحه تنظیمات
     *
     * @param string $hook Current page hook | هوک صفحه فعلی
     */
    public function nias_enqueue_assets($hook) {
        if ($hook !== 'toplevel_page_' . $this->menu_slug) {
            return;
        }
        
        wp_enqueue_style('nias-license-settings', false);
        wp_add_inline_style('nias-license-settings', $this->nias_get_custom_css());
        
        wp_enqueue_script('nias-license-settings', false);
        wp_add_inline_script('nias-license-settings', $this->nias_get_custom_js());
    }
    
    /**
     * Render Settings Page
     * نمایش صفحه تنظیمات
     * 
     * Display license management page
     * نمایش صفحه مدیریت لایسنس
     */
    public function nias_render_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $license_key = get_option('nias_license_key', '');
        $api_url = get_option('nias_license_api_url', '');
        $is_valid = false;
        $license_info = null;
        
        if ($this->license_client && !empty($license_key)) {
            $is_valid = $this->license_client->nias_is_valid();
            
            if ($is_valid) {
                $validation = $this->license_client->nias_validate();
                if (!is_wp_error($validation) && isset($validation['data'])) {
                    $license_info = $validation['data'];
                }
            }
        }
        
        ?>
        <div class="wrap nias-license-settings-wrap">
            <h1>
                <span class="dashicons dashicons-admin-network"></span>
                <?php echo esc_html(nias_translate('NLMW License Management')); ?>
            </h1>
            
            <?php $this->nias_render_tabs(); ?>
            
            <div class="nias-license-content">
                <?php
                $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'status';
                
                switch ($active_tab) {
                    case 'status':
                        $this->nias_render_status_tab($is_valid, $license_info);
                        break;
                    case 'activate':
                        $this->nias_render_activate_tab();
                        break;
                    case 'settings':
                        $this->nias_render_settings_tab();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render Tabs
     * نمایش تب‌ها
     * 
     * Display navigation tabs
     * نمایش تب‌های ناوبری
     */
    private function nias_render_tabs() {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'status';
        ?>
        <nav class="nav-tab-wrapper">
            <a href="?page=<?php echo esc_attr($this->menu_slug); ?>&tab=status" 
               class="nav-tab <?php echo $active_tab === 'status' ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html(nias_translate('License Status')); ?>
            </a>
            <a href="?page=<?php echo esc_attr($this->menu_slug); ?>&tab=activate" 
               class="nav-tab <?php echo $active_tab === 'activate' ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html(nias_translate('Activation')); ?>
            </a>
            <a href="?page=<?php echo esc_attr($this->menu_slug); ?>&tab=settings" 
               class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html(nias_translate('API Settings')); ?>
            </a>
        </nav>
        <?php
    }
    
    /**
     * Render Status Tab
     * نمایش تب وضعیت
     * 
     * Display license status information
     * نمایش اطلاعات وضعیت لایسنس
     *
     * @param bool       $is_valid     License validity | اعتبار لایسنس
     * @param array|null $license_info License information | اطلاعات لایسنس
     */
    private function nias_render_status_tab($is_valid, $license_info) {
        ?>
        <div class="nias-license-status-container">
            <?php if ($is_valid && $license_info): ?>
                <div class="nias-license-card nias-license-active">
                    <div class="nias-license-card-header">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <h2><?php echo esc_html(nias_translate('License is Active')); ?></h2>
                    </div>
                    
                    <div class="nias-license-info-grid">
                        <div class="nias-info-item">
                            <strong><?php echo esc_html(nias_translate('License Key:')); ?></strong>
                            <code><?php echo esc_html($license_info['licenseKey']); ?></code>
                        </div>
                        
                        <?php if (!empty($license_info['expiresAt'])): ?>
                        <div class="nias-info-item">
                            <strong><?php echo esc_html(nias_translate('Expiry Date:')); ?></strong>
                            <span><?php echo esc_html($this->nias_format_date($license_info['expiresAt'])); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($license_info['timesActivatedMax']) && $license_info['timesActivatedMax'] > 0): ?>
                        <div class="nias-info-item">
                            <strong><?php echo esc_html(nias_translate('Activations:')); ?></strong>
                            <span>
                                <?php 
                                printf(
                                    esc_html(nias_translate('%d of %d')),
                                    $license_info['timesActivated'],
                                    $license_info['timesActivatedMax']
                                ); 
                                ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="nias-info-item">
                            <strong><?php echo esc_html(nias_translate('Status:')); ?></strong>
                            <span class="nias-status-badge nias-status-active">
                                <?php echo esc_html(nias_translate('Active')); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="nias-license-actions">
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <?php wp_nonce_field('nias_deactivate_license_action', 'nias_deactivate_license_nonce'); ?>
                            <input type="hidden" name="action" value="nias_deactivate_license">
                            <button type="submit" class="button button-secondary" 
                                    onclick="return confirm('<?php echo esc_js(nias_translate('Are you sure you want to deactivate the license?')); ?>')">
                                <?php echo esc_html(nias_translate('Deactivate License')); ?>
                            </button>
                        </form>
                        
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <?php wp_nonce_field('nias_validate_license_action', 'nias_validate_license_nonce'); ?>
                            <input type="hidden" name="action" value="nias_validate_license">
                            <button type="submit" class="button button-primary">
                                <?php echo esc_html(nias_translate('Recheck License')); ?>
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="nias-license-card nias-license-inactive">
                    <div class="nias-license-card-header">
                        <span class="dashicons dashicons-warning"></span>
                        <h2><?php echo esc_html(nias_translate('License is Not Active')); ?></h2>
                    </div>
                    
                    <p class="nias-license-description">
                        <?php echo esc_html(nias_translate('To use all plugin features, please activate your license.')); ?>
                    </p>
                    
                    <div class="nias-license-actions">
                        <a href="?page=<?php echo esc_attr($this->menu_slug); ?>&tab=activate" 
                           class="button button-primary button-large">
                            <?php echo esc_html(nias_translate('Activate License')); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render Activate Tab
     * نمایش تب فعال‌سازی
     * 
     * Display license activation form
     * نمایش فرم فعال‌سازی لایسنس
     */
    private function nias_render_activate_tab() {
        $license_key = get_option('nias_license_key', '');
        ?>
        <div class="nias-license-activate-container">
            <div class="nias-license-card">
                <h2><?php echo esc_html(nias_translate('Activate Your License')); ?></h2>
                
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="nias-license-form">
                    <?php wp_nonce_field('nias_activate_license_action', 'nias_activate_license_nonce'); ?>
                    <input type="hidden" name="action" value="nias_activate_license">
                    
                    <div class="nias-form-group">
                        <label for="nias_license_key">
                            <?php echo esc_html(nias_translate('License Key:')); ?>
                        </label>
                        <input type="text" 
                               id="nias_license_key" 
                               name="nias_license_key" 
                               value="<?php echo esc_attr($license_key); ?>" 
                               class="regular-text"
                               placeholder="XXXX-XXXX-XXXX-XXXX"
                               required>
                        <p class="description">
                            <?php echo esc_html(nias_translate('Enter the license key you received from the store.')); ?>
                        </p>
                    </div>
                    
                    <div class="nias-form-actions">
                        <button type="submit" class="button button-primary button-large">
                            <span class="dashicons dashicons-yes"></span>
                            <?php echo esc_html(nias_translate('Activate License')); ?>
                        </button>
                    </div>
                </form>
                
                <div class="nias-help-section">
                    <h3><?php echo esc_html(nias_translate('Help:')); ?></h3>
                    <ul>
                        <li><?php echo esc_html(nias_translate('Get your license key from purchase email or user dashboard')); ?></li>
                        <li><?php echo esc_html(nias_translate('Each license has activation limits')); ?></li>
                        <li><?php echo esc_html(nias_translate('Deactivate license before changing domain')); ?></li>
                        <li><?php echo esc_html(nias_translate('Support: support@example.com')); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render Settings Tab
     * نمایش تب تنظیمات
     * 
     * Display API settings form
     * نمایش فرم تنظیمات API
     */
    private function nias_render_settings_tab() {
        $api_url = get_option('nias_license_api_url', '');
        $consumer_key = get_option('nias_license_consumer_key', '');
        $consumer_secret = get_option('nias_license_consumer_secret', '');
        ?>
        <div class="nias-license-settings-container">
            <div class="nias-license-card">
                <h2><?php echo esc_html(nias_translate('API Connection Settings')); ?></h2>
                
                <form method="post" action="options.php" class="nias-license-form">
                    <?php 
                    settings_fields('nias_license_settings_group');
                    do_settings_sections('nias_license_settings_group');
                    ?>
                    
                    <div class="nias-form-group">
                        <label for="nias_license_api_url">
                            <?php echo esc_html(nias_translate('API URL:')); ?>
                        </label>
                        <input type="url" 
                               id="nias_license_api_url" 
                               name="nias_license_api_url" 
                               value="<?php echo esc_attr($api_url); ?>" 
                               class="regular-text"
                               placeholder="https://example.com"
                               required>
                        <p class="description">
                            <?php echo esc_html(nias_translate('Full URL of WooCommerce site with License Manager installed.')); ?>
                        </p>
                    </div>
                    
                    <div class="nias-form-group">
                        <label for="nias_license_consumer_key">
                            <?php echo esc_html(nias_translate('Consumer Key:')); ?>
                        </label>
                        <input type="text" 
                               id="nias_license_consumer_key" 
                               name="nias_license_consumer_key" 
                               value="<?php echo esc_attr($consumer_key); ?>" 
                               class="regular-text"
                               required>
                        <p class="description">
                            <?php echo esc_html(nias_translate('API consumer key from License Manager settings.')); ?>
                        </p>
                    </div>
                    
                    <div class="nias-form-group">
                        <label for="nias_license_consumer_secret">
                            <?php echo esc_html(nias_translate('Consumer Secret:')); ?>
                        </label>
                        <input type="password" 
                               id="nias_license_consumer_secret" 
                               name="nias_license_consumer_secret" 
                               value="<?php echo esc_attr($consumer_secret); ?>" 
                               class="regular-text"
                               required>
                        <p class="description">
                            <?php echo esc_html(nias_translate('API consumer secret from License Manager settings.')); ?>
                        </p>
                    </div>
                    
                    <div class="nias-form-actions">
                        <?php submit_button(nias_translate('Save Settings'), 'primary', 'submit', false); ?>
                    </div>
                </form>
                
                <div class="nias-info-box">
                    <h3><?php echo esc_html(nias_translate('How to Get API Keys:')); ?></h3>
                    <ol>
                        <li><?php echo esc_html(nias_translate('Go to seller site admin panel')); ?></li>
                        <li><?php echo esc_html(nias_translate('Navigate to WooCommerce > Settings > License Manager > REST API')); ?></li>
                        <li><?php echo esc_html(nias_translate('Click Add Key button')); ?></li>
                        <li><?php echo esc_html(nias_translate('Fill the form and generate keys')); ?></li>
                        <li><?php echo esc_html(nias_translate('Copy Consumer Key and Consumer Secret here')); ?></li>
                    </ol>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Handle License Activation
     * مدیریت فعال‌سازی لایسنس
     * 
     * Process license activation form submission
     * پردازش ارسال فرم فعال‌سازی لایسنس
     */
    public function nias_handle_activate() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html(nias_translate('Unauthorized access')));
        }
        
        check_admin_referer('nias_activate_license_action', 'nias_activate_license_nonce');
        
        $license_key = isset($_POST['nias_license_key']) ? sanitize_text_field($_POST['nias_license_key']) : '';
        
        if (empty($license_key)) {
            $this->nias_redirect_with_message('error', nias_translate('Please enter license key'));
            return;
        }
        
        $this->nias_init_license_client();
        
        if (!$this->license_client) {
            $this->nias_redirect_with_message('error', nias_translate('Please enter API settings first'));
            return;
        }
        
        $result = $this->license_client->nias_activate($license_key);
        
        if (is_wp_error($result)) {
            $this->nias_redirect_with_message('error', nias_translate('Error:') . ' ' . $result->get_error_message());
            return;
        }
        
        if (isset($result['success']) && $result['success']) {
            update_option('nias_license_key', $license_key);
            $this->nias_redirect_with_message('success', nias_translate('License activated successfully'));
        } else {
            $this->nias_redirect_with_message('error', nias_translate('License activation failed'));
        }
    }
    
    /**
     * Handle License Deactivation
     * مدیریت غیرفعال‌سازی لایسنس
     * 
     * Process license deactivation
     * پردازش غیرفعال‌سازی لایسنس
     */
    public function nias_handle_deactivate() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html(nias_translate('Unauthorized access')));
        }
        
        check_admin_referer('nias_deactivate_license_action', 'nias_deactivate_license_nonce');
        
        $this->nias_init_license_client();
        
        if (!$this->license_client) {
            $this->nias_redirect_with_message('error', nias_translate('Error loading license client'));
            return;
        }
        
        $result = $this->license_client->nias_deactivate();
        
        if (is_wp_error($result)) {
            $this->nias_redirect_with_message('error', nias_translate('Error:') . ' ' . $result->get_error_message());
            return;
        }
        
        if (isset($result['success']) && $result['success']) {
            delete_option('nias_license_key');
            $this->nias_redirect_with_message('success', nias_translate('License deactivated successfully'));
        } else {
            $this->nias_redirect_with_message('error', nias_translate('License deactivation failed'));
        }
    }
    
    /**
     * Handle License Validation
     * مدیریت اعتبارسنجی لایسنس
     * 
     * Process license validation check
     * پردازش بررسی اعتبارسنجی لایسنس
     */
    public function nias_handle_validate() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html(nias_translate('Unauthorized access')));
        }
        
        check_admin_referer('nias_validate_license_action', 'nias_validate_license_nonce');
        
        $this->nias_init_license_client();
        
        if (!$this->license_client) {
            $this->nias_redirect_with_message('error', nias_translate('Error loading license client'));
            return;
        }
        
        $this->license_client->nias_clear_cache();
        $result = $this->license_client->nias_validate(null, true);
        
        if (is_wp_error($result)) {
            $this->nias_redirect_with_message('error', nias_translate('Error:') . ' ' . $result->get_error_message());
            return;
        }
        
        if ($this->license_client->nias_is_valid()) {
            $this->nias_redirect_with_message('success', nias_translate('License is valid'));
        } else {
            $this->nias_redirect_with_message('error', nias_translate('License is invalid or expired'));
        }
    }
    
    /**
     * Redirect with Message
     * ریدایرکت با پیام
     * 
     * Redirect to settings page with status message
     * ریدایرکت به صفحه تنظیمات با پیام وضعیت
     *
     * @param string $type    Message type (success/error) | نوع پیام
     * @param string $message Message text | متن پیام
     */
    private function nias_redirect_with_message($type, $message) {
        $redirect_url = add_query_arg([
            'page' => $this->menu_slug,
            'message' => urlencode($message),
            'type' => $type,
        ], admin_url('admin.php'));
        
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Show License Notice
     * نمایش اعلان لایسنس
     * 
     * Display license status notices in admin
     * نمایش اعلان‌های وضعیت لایسنس در پنل مدیریت
     */
    public function nias_license_notice() {
        // Show form messages
        // نمایش پیام‌های فرم
        if (isset($_GET['message']) && isset($_GET['type'])) {
            $type = $_GET['type'] === 'success' ? 'success' : 'error';
            $message = urldecode($_GET['message']);
            ?>
            <div class="notice notice-<?php echo esc_attr($type); ?> is-dismissible">
                <p><?php echo esc_html($message); ?></p>
            </div>
            <?php
        }
        
        // Check license status on other pages
        // بررسی وضعیت لایسنس در سایر صفحات
        $screen = get_current_screen();
        if ($screen && $screen->id === 'toplevel_page_' . $this->menu_slug) {
            return;
        }
        
        $this->nias_init_license_client();
        
        if (!$this->license_client) {
            return;
        }
        
        if (!$this->license_client->nias_is_valid()) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php echo esc_html(nias_translate('Warning:')); ?></strong>
                    <?php echo esc_html(nias_translate('Plugin license is not active.')); ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->menu_slug)); ?>">
                        <?php echo esc_html(nias_translate('Click to activate')); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }
    
    /**
     * Format Date
     * فرمت کردن تاریخ
     * 
     * Format date string for display
     * فرمت کردن رشته تاریخ برای نمایش
     *
     * @param string $date_string Date string | رشته تاریخ
     * @return string Formatted date | تاریخ فرمت شده
     */
    private function nias_format_date($date_string) {
        $timestamp = strtotime($date_string);
        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp);
    }
    
    /**
     * Get Custom CSS
     * دریافت CSS سفارشی
     * 
     * Return inline CSS styles for settings page
     * بازگشت استایل‌های CSS داخلی برای صفحه تنظیمات
     *
     * @return string CSS code | کد CSS
     */
    private function nias_get_custom_css() {
        return '
        .nias-license-settings-wrap { max-width: 1200px; margin: 20px 0; }
        .nias-license-settings-wrap h1 { display: flex; align-items: center; gap: 10px; font-size: 24px; margin-bottom: 20px; }
        .nias-license-content { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-top: -1px; }
        .nias-license-card { background: #fff; padding: 30px; border-radius: 8px; border: 1px solid #ddd; }
        .nias-license-card-header { display: flex; align-items: center; gap: 15px; margin-bottom: 25px; }
        .nias-license-card-header .dashicons { width: 40px; height: 40px; font-size: 40px; }
        .nias-license-active .dashicons { color: #46b450; }
        .nias-license-inactive .dashicons { color: #dc3232; }
        .nias-license-card-header h2 { margin: 0; font-size: 24px; }
        .nias-license-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .nias-info-item { padding: 15px; background: #f9f9f9; border-radius: 5px; border-left: 4px solid #2271b1; }
        .nias-info-item strong { display: block; margin-bottom: 8px; color: #555; }
        .nias-info-item code { background: #fff; padding: 5px 10px; border-radius: 3px; display: inline-block; font-size: 14px; }
        .nias-status-badge { display: inline-block; padding: 5px 15px; border-radius: 15px; font-size: 13px; font-weight: 600; }
        .nias-status-active { background: #d4edda; color: #155724; }
        .nias-license-actions { display: flex; gap: 15px; flex-wrap: wrap; padding-top: 20px; border-top: 1px solid #eee; }
        .nias-license-form { max-width: 600px; }
        .nias-form-group { margin-bottom: 25px; }
        .nias-form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .nias-form-group input { width: 100%; max-width: 100%; }
        .nias-form-group .description { margin-top: 8px; color: #666; font-size: 13px; }
        .nias-form-actions { margin-top: 30px; }
        .nias-form-actions button, .nias-form-actions .button { display: inline-flex; align-items: center; gap: 8px; }
        .nias-help-section, .nias-info-box { margin-top: 30px; padding: 20px; background: #f0f6fc; border-radius: 5px; border-left: 4px solid #2271b1; }
        .nias-help-section h3, .nias-info-box h3 { margin-top: 0; color: #2271b1; }
        .nias-help-section ul, .nias-info-box ol { margin: 10px 0; padding-right: 25px; }
        .nias-help-section li, .nias-info-box li { margin: 8px 0; }
        .nias-license-description { font-size: 15px; color: #666; margin-bottom: 25px; }
        @media (max-width: 782px) {
            .nias-license-info-grid { grid-template-columns: 1fr; }
            .nias-license-actions { flex-direction: column; }
            .nias-license-actions form, .nias-license-actions button { width: 100%; }
        }
        ';
    }
    
    /**
     * Get Custom JavaScript
     * دریافت جاوااسکریپت سفارشی
     * 
     * Return inline JavaScript for settings page
     * بازگشت جاوااسکریپت داخلی برای صفحه تنظیمات
     *
     * @return string JavaScript code | کد جاوااسکریپت
     */
    private function nias_get_custom_js() {
        return '
        jQuery(document).ready(function($) {
            $(".nias-license-form").on("submit", function() {
                var requiredFields = $(this).find("[required]");
                var isValid = true;
                
                requiredFields.each(function() {
                    if ($(this).val().trim() === "") {
                        isValid = false;
                        $(this).css("border-color", "#dc3232");
                    } else {
                        $(this).css("border-color", "");
                    }
                });
                
                if (!isValid) {
                    alert("' . esc_js(nias_translate('Please fill all required fields')) . '");
                    return false;
                }
            });
        });
        ';
    }
}

// Initialize settings page
// مقداردهی اولیه صفحه تنظیمات
new Nias_License_Settings_Page();
