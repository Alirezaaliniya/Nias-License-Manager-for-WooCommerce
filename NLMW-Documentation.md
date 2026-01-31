# Nias License Manager for WooCommerce (NLMW)
# مدیریت لایسنس نیاس برای ووکامرس

**Version | نسخه:** 1.0.0  
**Author | نویسنده:** Nias Development Team  
**License | لایسنس:** GPL-2.0+


## 🔒 قفل‌گذاری بخش‌ها در پلاگین/قالب (فارسی)

### الگوی پایه راه‌اندازی

```php
$client = new Nias_License_Manager_Client(
    get_option( 'nlmw_my-plugin_store_url' ),
    get_option( 'nlmw_my-plugin_consumer_key' ),
    get_option( 'nlmw_my-plugin_consumer_secret' )
);
$license_key = get_option( 'nlmw_my-plugin_license_key' );
```

### 1) مخفی‌سازی عناصر رابط کاربری (Frontend/UI)

```php
if ( ! $client->nias_is_license_valid( $license_key ) ) {
    // نمایش نده
    return;
}

echo '<section class="premium-area">محتوای ویژه</section>';
```

### 2) نمایش پیام فعال‌سازی (ادمین و فرانت‌اند)

```php
if ( ! $client->nias_is_license_valid( $license_key ) ) {
    echo '<div class="notice notice-warning"><p>برای دسترسی به این بخش، لایسنس را فعال کنید.</p></div>';
    // یا توقف عملیات حساس:
    // wp_die( 'برای استفاده از این قابلیت، لایسنس را فعال کنید.' );
}
```

### 3) ریدایرکت به صفحه ثبت/فعال‌سازی لایسنس (ادمین)

```php
add_action( 'admin_init', function() {
    $client = new Nias_License_Manager_Client(
        get_option( 'nlmw_my-plugin_store_url' ),
        get_option( 'nlmw_my-plugin_consumer_key' ),
        get_option( 'nlmw_my-plugin_consumer_secret' )
    );
    $license_key = get_option( 'nlmw_my-plugin_license_key' );

    if ( ! $client->nias_is_license_valid( $license_key ) ) {
        $page = 'my-plugin-license'; // اسلاگ صفحه: {plugin_slug}-license
        wp_safe_redirect( admin_url( 'options-general.php?page=' . $page ) );
        exit;
    }
} );
```

### 4) ریدایرکت در فرانت‌اند به صفحه سفارشی

```php
add_action( 'template_redirect', function() {
    $client = new Nias_License_Manager_Client( $url, $key, $secret );
    $license_key = get_option( 'nlmw_my-plugin_license_key' );

    if ( is_page( 'premium-content' ) && ! $client->nias_is_license_valid( $license_key ) ) {
        wp_safe_redirect( home_url( '/register-license' ) );
        exit;
    }
} );
```

### 5) قفل با شورت‌کد

```php
add_shortcode( 'premium_box', function( $atts, $content = '' ) {
    $client = new Nias_License_Manager_Client( $url, $key, $secret );
    $license_key = get_option( 'nlmw_my-plugin_license_key' );

    if ( ! $client->nias_is_license_valid( $license_key ) ) {
        return '<div class="notice notice-warning"><p>برای مشاهده این بخش، لایسنس را فعال کنید.</p></div>';
    }

    return '<div class="premium-box">' . do_shortcode( $content ) . '</div>';
} );
```

### 6) قفل کردن منوها/صفحات مدیریت افزونه

```php
add_action( 'admin_menu', function() {
    $client = new Nias_License_Manager_Client( $url, $key, $secret );
    $license_key = get_option( 'nlmw_my-plugin_license_key' );

    if ( ! $client->nias_is_license_valid( $license_key ) ) {
        remove_submenu_page( 'options-general.php', 'my-plugin-premium' );
    }
} );
```

### 7) محافظت از اکشن‌های حساس (نمونه عملی)

```php
add_action( 'admin_post_my_plugin_generate_report', function() {
    $client = new Nias_License_Manager_Client( $url, $key, $secret );
    $license_key = get_option( 'nlmw_my-plugin_license_key' );

    if ( ! $client->nias_is_license_valid( $license_key ) ) {
        wp_die( 'برای تولید گزارش، ابتدا لایسنس را فعال کنید.' );
    }

    // ادامه فرایند تولید گزارش...
} );
```

---

## 📋 Table of Contents | فهرست مطالب

1. [Introduction | معرفی](#introduction)
2. [Features | ویژگی‌ها](#features)
3. [Requirements | پیش‌نیازها](#requirements)
4. [Installation | نصب](#installation)
5. [Configuration | پیکربندی](#configuration)
6. [Usage | استفاده](#usage)
7. [API Reference | مرجع API](#api-reference)
8. [Cron Jobs | کرون‌ها](#cron-jobs)
9. [Hooks & Filters | هوک‌ها و فیلترها](#hooks-filters)
10. [Troubleshooting | رفع مشکلات](#troubleshooting)
11. [FAQ | سوالات متداول](#faq)
12. [Changelog | تغییرات](#changelog)

---

## 🎯 Introduction | معرفی

**NLMW (Nias License Manager for WooCommerce)** is a professional PHP library designed to integrate seamlessly with the free version of "License Manager for WooCommerce" plugin. This library provides a complete solution for license validation, activation, and management in your WordPress plugins.

**NLMW** یک کتابخانه حرفه‌ای PHP است که برای یکپارچگی کامل با نسخه رایگان افزونه "License Manager for WooCommerce" طراحی شده است. این کتابخانه راه‌حل کاملی برای اعتبارسنجی، فعال‌سازی و مدیریت لایسنس در افزونه‌های وردپرس شما فراهم می‌کند.

### Key Components | اجزای کلیدی:

- **License Manager Client** - API communication handler | مدیریت ارتباط با API
- **Settings Page** - Professional admin interface | رابط مدیریتی حرفه‌ای
- **Cron Handler** - Automatic license validation | اعتبارسنجی خودکار لایسنس
- **Translation System** - Bilingual support (EN/FA) | پشتیبانی دو زبانه (انگلیسی/فارسی)

---

## ✨ Features | ویژگی‌ها

### Core Features | ویژگی‌های اصلی

✅ **License Validation** - Real-time license verification  
✅ **اعتبارسنجی لایسنس** - تأیید لایسنس در زمان واقعی

✅ **Activation Management** - Track and manage activations  
✅ **مدیریت فعال‌سازی** - پیگیری و مدیریت فعال‌سازی‌ها

✅ **Automatic Checks** - Scheduled cron jobs for validation  
✅ **بررسی خودکار** - کرون‌های زمان‌بندی شده برای اعتبارسنجی

✅ **Expiration Warnings** - Email notifications before expiry  
✅ **هشدارهای انقضا** - اعلان‌های ایمیل قبل از انقضا

✅ **Professional UI** - Beautiful admin settings page  
✅ **رابط حرفه‌ای** - صفحه تنظیمات مدیریتی زیبا

✅ **Error Handling** - Comprehensive error management  
✅ **مدیریت خطا** - مدیریت جامع خطاها

✅ **Logging System** - Debug and activity logs  
✅ **سیستم لاگ** - لاگ‌های دیباگ و فعالیت

✅ **Translation Ready** - Fully customizable translations  
✅ **آماده ترجمه** - ترجمه‌های کاملاً قابل سفارشی‌سازی

---

## 📦 Requirements | پیش‌نیازها

### Server Requirements | پیش‌نیازهای سرور

- **WordPress:** 5.0 or higher | 5.0 یا بالاتر
- **PHP:** 7.2 or higher | 7.2 یا بالاتر
- **MySQL:** 5.6 or higher | 5.6 یا بالاتر

### Required Plugins | افزونه‌های مورد نیاز

- **License Manager for WooCommerce** (Free Version)
- **WooCommerce** (For license store)

### PHP Extensions | افزونه‌های PHP

- `curl` - For API requests | برای درخواست‌های API
- `json` - For data parsing | برای پردازش داده‌ها
- `openssl` - For secure connections | برای اتصالات امن

---

## 🚀 Installation | نصب

### Step 1: Download Files | مرحله 1: دانلود فایل‌ها

Download all library files to your plugin directory:

```
your-plugin/
├── includes/
│   ├── license/
│   │   ├── class-license-manager-client.php
│   │   ├── class-license-settings-page.php
│   │   ├── class-license-cron-handler.php
│   │   └── translations.php
```

### Step 2: Include Files | مرحله 2: اضافه کردن فایل‌ها

Add to your main plugin file:

```php
// Include NLMW library
// اضافه کردن کتابخانه NLMW
require_once plugin_dir_path( __FILE__ ) . 'includes/license/class-license-manager-client.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/license/class-license-settings-page.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/license/class-license-cron-handler.php';
```

### Step 3: Initialize | مرحله 3: مقداردهی اولیه

```php
// Initialize NLMW
// مقداردهی اولیه NLMW
if ( class_exists( 'Nias_License_Manager_Client' ) ) {
    
    // Initialize settings page
    // مقداردهی اولیه صفحه تنظیمات
    $nias_license_settings = new Nias_License_Settings_Page(
        'My Awesome Plugin',  // Plugin name | نام افزونه
        'my-awesome-plugin'   // Plugin slug | اسلاگ افزونه
    );
    
    // Initialize cron handler
    // مقداردهی اولیه مدیریت کرون
    $nias_license_cron = new Nias_License_Cron_Handler(
        'my-awesome-plugin',  // Plugin slug | اسلاگ افزونه
        DAY_IN_SECONDS        // Check interval (daily) | فاصله بررسی (روزانه)
    );
}
```

---

## ⚙️ Configuration | پیکربندی

### Getting API Credentials | دریافت اطلاعات API

1. **Install License Manager** on your WooCommerce store  
   **نصب مدیریت لایسنس** در فروشگاه ووکامرس خود

2. Navigate to: `License Manager > Settings > REST API`  
   مراجعه به: `License Manager > Settings > REST API`

3. Click **"Add API Key"** | کلیک روی **"افزودن کلید API"**

4. Set permissions to **Read & Write** | تنظیم مجوزها به **خواندن و نوشتن**

5. Copy the **Consumer Key** and **Consumer Secret**  
   کپی **کلید مصرف‌کننده** و **رمز مصرف‌کننده**

### WordPress Admin Setup | راه‌اندازی مدیریت وردپرس

1. Go to `Settings > Your Plugin License`  
   مراجعه به `تنظیمات > لایسنس افزونه شما`

2. Enter **API Configuration**:
   - Store URL: `https://yourstore.com`
   - Consumer Key: Your API key
   - Consumer Secret: Your API secret

3. Click **Save API Settings** | کلیک روی **ذخیره تنظیمات API**

4. Enter your **License Key** | وارد کردن **کلید لایسنس** خود

5. Click **Activate License** | کلیک روی **فعال‌سازی لایسنس**

---

## 💻 Usage | استفاده

### Basic Usage | استفاده پایه

#### Check if License is Valid | بررسی معتبر بودن لایسنس

```php
$client = new Nias_License_Manager_Client(
    'https://yourstore.com',
    'ck_xxxxx',
    'cs_xxxxx'
);

$license_key = 'YOUR-LICENSE-KEY';

if ( $client->nias_is_license_valid( $license_key ) ) {
    // License is valid - enable features
    // لایسنس معتبر است - فعال‌سازی ویژگی‌ها
    echo 'License is active!';
} else {
    // License is invalid - show message
    // لایسنس نامعتبر است - نمایش پیام
    echo 'Please activate your license.';
}
```

#### Get License Details | دریافت جزئیات لایسنس

```php
$license_data = $client->nias_get_license_details( $license_key );

if ( $license_data ) {
    echo 'Product ID: ' . $license_data['productId'];
    echo 'Expires At: ' . $license_data['expiresAt'];
    echo 'Activations: ' . $license_data['timesActivated'] . '/' . $license_data['timesActivatedMax'];
}
```

#### Get Remaining Activations | دریافت فعال‌سازی‌های باقی‌مانده

```php
$remaining = $client->nias_get_remaining_activations( $license_key );

if ( $remaining === -1 ) {
    echo 'Unlimited activations | فعال‌سازی نامحدود';
} elseif ( $remaining > 0 ) {
    echo 'Remaining: ' . $remaining . ' | باقی‌مانده: ' . $remaining;
} else {
    echo 'No activations left | فعال‌سازی باقی نمانده';
}
```

### Advanced Usage | استفاده پیشرفته

#### Manual Activation | فعال‌سازی دستی

```php
$result = $client->nias_activate_license( $license_key, array(
    'label' => 'Production Server',
    'meta_data' => array(
        'server' => $_SERVER['SERVER_NAME'],
        'ip' => $_SERVER['SERVER_ADDR']
    )
));

if ( $result ) {
    $token = $result['activationData']['token'];
    echo 'Activated! Token: ' . $token;
}
```

#### Manual Deactivation | غیرفعال‌سازی دستی

```php
$result = $client->nias_deactivate_license( $license_key, $activation_token );

if ( $result ) {
    echo 'License deactivated successfully!';
}
```

#### Force License Check | بررسی اجباری لایسنس

```php
$cron_handler = new Nias_License_Cron_Handler( 'my-plugin' );
$status = $cron_handler->nias_force_check_now();

echo 'Current status: ' . $status; // 'active' or 'inactive'
```

---

## 📚 API Reference | مرجع API

### Nias_License_Manager_Client Class

#### Constructor | سازنده

```php
__construct( $store_url, $consumer_key, $consumer_secret )
```

**Parameters | پارامترها:**
- `$store_url` (string) - Your WooCommerce store URL | آدرس فروشگاه ووکامرس
- `$consumer_key` (string) - API Consumer Key | کلید مصرف‌کننده API
- `$consumer_secret` (string) - API Consumer Secret | رمز مصرف‌کننده API

#### Methods | متدها

##### nias_validate_license()

Validates a license and returns full data.  
اعتبارسنجی لایسنس و بازگشت اطلاعات کامل.

```php
nias_validate_license( $license_key )
```

**Returns | بازگشت:** `array|false` - License data or false

---

##### nias_activate_license()

Activates a license and creates activation token.  
فعال‌سازی لایسنس و ایجاد توکن فعال‌سازی.

```php
nias_activate_license( $license_key, $params = array() )
```

**Parameters:**
- `$license_key` (string) - License key
- `$params` (array) - Optional parameters:
  - `label` (string) - Activation label
  - `meta_data` (array) - Custom metadata

**Returns:** `array|false` - Activation data or false

---

##### nias_deactivate_license()

Deactivates a license using activation token.  
غیرفعال‌سازی لایسنس با استفاده از توکن فعال‌سازی.

```php
nias_deactivate_license( $license_key, $token = '' )
```

**Parameters:**
- `$license_key` (string) - License key
- `$token` (string) - Activation token (optional)

**Returns:** `array|false` - Deactivation data or false

---

##### nias_is_license_valid()

Quick check if license is valid and active.  
بررسی سریع اعتبار و فعال بودن لایسنس.

```php
nias_is_license_valid( $license_key )
```

**Returns:** `bool` - True if valid

---

##### nias_get_remaining_activations()

Gets number of remaining activations.  
دریافت تعداد فعال‌سازی‌های باقی‌مانده.

```php
nias_get_remaining_activations( $license_key )
```

**Returns:** `int|false` - Number of remaining activations or -1 for unlimited

---

##### nias_get_license_expiry()

Gets license expiration date.  
دریافت تاریخ انقضای لایسنس.

```php
nias_get_license_expiry( $license_key )
```

**Returns:** `string|false` - Expiry date or false

---

##### nias_get_last_error()

Gets the last error message.  
دریافت آخرین پیام خطا.

```php
nias_get_last_error()
```

**Returns:** `string|null` - Error message

---

### Nias_License_Cron_Handler Class

#### Constructor | سازنده

```php
__construct( $plugin_slug, $check_interval = DAY_IN_SECONDS )
```

**Parameters:**
- `$plugin_slug` (string) - Your plugin slug
- `$check_interval` (int) - Check interval in seconds (default: daily)

#### Methods | متدها

##### nias_force_check_now()

Manually triggers license validation.  
فعال‌سازی دستی اعتبارسنجی لایسنس.

```php
nias_force_check_now()
```

**Returns:** `string` - License status ('active' or 'inactive')

---

##### nias_get_last_check_time()

Gets timestamp of last license check.  
دریافت تایم‌استمپ آخرین بررسی لایسنس.

```php
nias_get_last_check_time()
```

**Returns:** `int|false` - Timestamp or false

---

##### nias_get_next_check_time()

Gets timestamp of next scheduled check.  
دریافت تایم‌استمپ بررسی برنامه‌ریزی شده بعدی.

```php
nias_get_next_check_time()
```

**Returns:** `int|false` - Timestamp or false

---

##### nias_get_logs()

Retrieves license check logs.  
دریافت لاگ‌های بررسی لایسنس.

```php
nias_get_logs( $limit = 50 )
```

**Parameters:**
- `$limit` (int) - Number of logs to retrieve

**Returns:** `array` - Array of log entries

---

##### nias_clear_logs()

Clears all stored logs.  
پاک کردن تمام لاگ‌های ذخیره شده.

```php
nias_clear_logs()
```

---

## ⏰ Cron Jobs | کرون‌ها

### Automatic License Checks | بررسی‌های خودکار لایسنس

The library automatically schedules cron jobs to validate licenses periodically.

این کتابخانه به صورت خودکار کرون‌هایی را برای اعتبارسنجی دوره‌ای لایسنس برنامه‌ریزی می‌کند.

### Default Schedule | برنامه پیش‌فرض

- **Interval | فاصله:** Daily (24 hours) | روزانه (24 ساعت)
- **First Run | اجرای اول:** On plugin activation | در فعال‌سازی افزونه
- **Hook Name | نام هوک:** `nias_{plugin_slug}_license_check`

### Custom Intervals | فاصله‌های سفارشی

You can customize the check interval:

```php
// Check every 12 hours | بررسی هر 12 ساعت
$cron = new Nias_License_Cron_Handler( 'my-plugin', 12 * HOUR_IN_SECONDS );

// Check every week | بررسی هر هفته
$cron = new Nias_License_Cron_Handler( 'my-plugin', WEEK_IN_SECONDS );
```

### What Happens During Cron? | چه اتفاقی در کرون می‌افتد؟

1. **Validates license** with API | اعتبارسنجی لایسنس با API
2. **Checks expiration** date | بررسی تاریخ انقضا
3. **Updates status** in database | به‌روزرسانی وضعیت در پایگاه داده
4. **Sends email warnings** (30, 14, 7, 3, 1 days before expiry)  
   ارسال هشدارهای ایمیل (30، 14، 7، 3، 1 روز قبل از انقضا)
5. **Logs activity** for debugging | ثبت فعالیت برای دیباگ

### Manual Cron Execution | اجرای دستی کرون

```php
// Get cron handler instance
$cron = new Nias_License_Cron_Handler( 'my-plugin' );

// Force immediate check
$status = $cron->nias_force_check_now();

// View last check time
$last_check = $cron->nias_get_last_check_time();
echo date( 'Y-m-d H:i:s', $last_check );

// View next scheduled time
$next_check = $cron->nias_get_next_check_time();
echo date( 'Y-m-d H:i:s', $next_check );
```

---

## 🔌 Hooks & Filters | هوک‌ها و فیلترها

### Action Hooks | هوک‌های اکشن

#### nias_{plugin_slug}_license_invalid

Fired when license becomes invalid.  
فعال می‌شود وقتی لایسنس نامعتبر می‌شود.

```php
add_action( 'nias_my_plugin_license_invalid', function( $license_data, $reason ) {
    // Disable premium features
    // غیرفعال کردن ویژگی‌های پریمیوم
    update_option( 'my_plugin_premium_enabled', false );
    
    // Send custom notification
    // ارسال اعلان سفارشی
    wp_mail( get_option( 'admin_email' ), 'License Invalid', $reason );
}, 10, 2 );
```

### Filter Hooks | هوک‌های فیلتر

#### nias_{plugin_slug}_api_request_args

Modify API request arguments.  
تغییر آرگومان‌های درخواست API.

```php
add_filter( 'nias_my_plugin_api_request_args', function( $args ) {
    $args['timeout'] = 60; // Increase timeout
    return $args;
});
```

#### nias_{plugin_slug}_license_data

Modify license data before storage.  
تغییر اطلاعات لایسنس قبل از ذخیره.

```php
add_filter( 'nias_my_plugin_license_data', function( $data ) {
    // Add custom data
    // افزودن داده سفارشی
    $data['custom_field'] = 'custom_value';
    return $data;
});
```

---

## 🔧 Troubleshooting | رفع مشکلات

### Common Issues | مشکلات رایج

#### 1. License Activation Fails | فعال‌سازی لایسنس ناموفق است

**Problem:** "API credentials not configured"

**Solution:**
1. Check Store URL format: `https://yourstore.com` (no trailing slash)
2. Verify Consumer Key and Secret
3. Ensure License Manager plugin is active on store
4. Check API permissions (Read & Write)

---

#### 2. Cron Not Running | کرون اجرا نمی‌شود

**Problem:** License checks not happening automatically

**Solution:**
```php
// Manually reschedule cron
wp_clear_scheduled_hook( 'nias_my_plugin_license_check' );
$cron = new Nias_License_Cron_Handler( 'my-plugin' );
$cron->nias_schedule_license_check();
```

Or setup real cron:
```bash
*/12 * * * * wget -q -O - https://yoursite.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1
```

---

#### 3. SSL Certificate Errors | خطاهای گواهینامه SSL

**Problem:** "SSL certificate problem"

**Solution:**
```php
// Add to wp-config.php (NOT RECOMMENDED for production)
define( 'CURLOPT_SSL_VERIFYPEER', false );
define( 'CURLOPT_SSL_VERIFYHOST', false );
```

Better solution: Fix SSL certificate on server.

---

#### 4. License Shows Inactive But Is Valid | لایسنس غیرفعال نشان می‌دهد اما معتبر است

**Problem:** Status mismatch

**Solution:**
```php
// Clear and re-check license
delete_option( 'nias_my_plugin_license_status' );
delete_option( 'nias_my_plugin_license_data' );

$client = new Nias_License_Manager_Client( /* ... */ );
$result = $client->nias_activate_license( $license_key );
```

---

### Debug Mode | حالت دیباگ

Enable debug logging:

```php
// Add to wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

View logs:
```php
$cron = new Nias_License_Cron_Handler( 'my-plugin' );
$logs = $cron->nias_get_logs( 50 );

foreach ( $logs as $log ) {
    echo $log['time'] . ' [' . $log['type'] . '] ' . $log['message'];
}
```

---

## ❓ FAQ | سوالات متداول

### Q1: Can I use this with the PRO version? | آیا می‌توانم با نسخه PRO استفاده کنم؟

**A:** Yes! This library works with both Free and PRO versions of License Manager for WooCommerce.

بله! این کتابخانه با هر دو نسخه رایگان و PRO از License Manager for WooCommerce کار می‌کند.

---

### Q2: How do I customize translations? | چگونه ترجمه‌ها را سفارشی کنم؟

**A:** Edit the `translations.php` file:

```php
'License Key' => 'کلید لایسنس من',
'Activate' => 'فعال‌سازی',
```

---

### Q3: Can I change the cron interval? | آیا می‌توانم فاصله کرون را تغییر دهم؟

**A:** Yes, pass interval to constructor:

```php
new Nias_License_Cron_Handler( 'my-plugin', 12 * HOUR_IN_SECONDS );
```

---

### Q4: How do I disable automatic checks? | چگونه بررسی‌های خودکار را غیرفعال کنم؟

**A:** Don't initialize the cron handler, or clear it:

```php
wp_clear_scheduled_hook( 'nias_my_plugin_license_check' );
```

---

### Q5: Is the license key stored securely? | آیا کلید لایسنس به صورت امن ذخیره می‌شود؟

**A:** Yes, stored in WordPress options table. For extra security, consider encryption:

```php
// Before saving
$encrypted = base64_encode( $license_key );
update_option( 'my_license', $encrypted );

// When reading
$license_key = base64_decode( get_option( 'my_license' ) );
```

---

## 📝 Best Practices | بهترین شیوه‌ها

### 1. Check License Before Critical Operations

```php
if ( ! $client->nias_is_license_valid( $license_key ) ) {
    wp_die( 'Please activate your license to use this feature.' );
}

// Continue with operation...
```

### 2. Handle Expiration Gracefully

```php
$expiry = $client->nias_get_license_expiry( $license_key );
$days_left = ceil( ( strtotime( $expiry ) - time() ) / DAY_IN_SECONDS );

if ( $days_left <= 7 ) {
    add_action( 'admin_notices', function() use ( $days_left ) {
        echo '<div class="notice notice-warning">';
        echo '<p>Your license expires in ' . $days_left . ' days!</p>';
        echo '</div>';
    });
}
```

### 3. Provide Clear Error Messages

```php
$result = $client->nias_activate_license( $license_key );

if ( ! $result ) {
    $error = $client->nias_get_last_error();
    
    if ( strpos( $error, 'activation limit' ) !== false ) {
        echo 'Too many activations. Deactivate from another site first.';
    } else {
        echo 'Activation failed: ' . $error;
    }
}
```

### 4. Cache License Status

```php
$cache_key = 'license_status_' . md5( $license_key );
$status = get_transient( $cache_key );

if ( false === $status ) {
    $status = $client->nias_is_license_valid( $license_key );
    set_transient( $cache_key, $status, HOUR_IN_SECONDS );
}

return $status;
```

---

## 📖 Example Implementation | پیاده‌سازی نمونه

Complete plugin example:

```php
<?php
/**
 * Plugin Name: My Awesome Plugin
 * Version: 1.0.0
 */

// Include NLMW
require_once __DIR__ . '/includes/license/class-license-manager-client.php';
require_once __DIR__ . '/includes/license/class-license-settings-page.php';
require_once __DIR__ . '/includes/license/class-license-cron-handler.php';

class My_Awesome_Plugin {
    
    private $license_client;
    
    public function __construct() {
        // Initialize license system
        $this->init_license();
        
        // Check license before running features
        add_action( 'plugins_loaded', array( $this, 'check_license' ) );
    }
    
    private function init_license() {
        // Settings page
        new Nias_License_Settings_Page( 'My Awesome Plugin', 'my-awesome-plugin' );
        
        // Cron handler (check daily)
        new Nias_License_Cron_Handler( 'my-awesome-plugin', DAY_IN_SECONDS );
        
        // Initialize client
        $store_url = get_option( 'nias_my-awesome-plugin_store_url' );
        $consumer_key = get_option( 'nias_my-awesome-plugin_consumer_key' );
        $consumer_secret = get_option( 'nias_my-awesome-plugin_consumer_secret' );
        
        $this->license_client = new Nias_License_Manager_Client(
            $store_url,
            $consumer_key,
            $consumer_secret
        );
    }
    
    public function check_license() {
        $license_key = get_option( 'nias_my-awesome-plugin_license_key' );
        $status = get_option( 'nias_my-awesome-plugin_license_status' );
        
        if ( $status !== 'active' ) {
            // Disable premium features
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-warning">';
                echo '<p>Please activate your license to use premium features.</p>';
                echo '</div>';
            });
            return;
        }
        
        // Enable premium features
        $this->load_premium_features();
    }
    
    private function load_premium_features() {
        // Your premium features here
    }
}

// Initialize plugin
new My_Awesome_Plugin();
```

---

## 🔒 Security Recommendations | توصیه‌های امنیتی

### 1. Validate User Input | اعتبارسنجی ورودی کاربر

```php
// Always sanitize
$license_key = sanitize_text_field( $_POST['license_key'] );

// Validate format
if ( ! preg_match( '/^[A-Z0-9-]+$/', $license_key ) ) {
    wp_die( 'Invalid license key format' );
}
```

### 2. Check Capabilities | بررسی قابلیت‌ها

```php
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Unauthorized access' );
}
```

### 3. Use Nonces | استفاده از Nonce

```php
// Create nonce
wp_nonce_field( 'nias_license_action', 'nias_license_nonce' );

// Verify nonce
if ( ! check_admin_referer( 'nias_license_action', 'nias_license_nonce' ) ) {
    wp_die( 'Security check failed' );
}
```

### 4. Secure API Credentials | ایمن‌سازی اطلاعات API

```php
// Option 1: Use constants in wp-config.php
define( 'NIAS_STORE_URL', 'https://yourstore.com' );
define( 'NIAS_CONSUMER_KEY', 'ck_xxxxx' );
define( 'NIAS_CONSUMER_SECRET', 'cs_xxxxx' );

// Option 2: Encrypt sensitive data
function nias_encrypt( $data ) {
    $key = wp_salt( 'auth' );
    return base64_encode( openssl_encrypt( $data, 'AES-128-CBC', $key, 0, substr( $key, 0, 16 ) ) );
}

function nias_decrypt( $data ) {
    $key = wp_salt( 'auth' );
    return openssl_decrypt( base64_decode( $data ), 'AES-128-CBC', $key, 0, substr( $key, 0, 16 ) );
}
```

### 5. Rate Limiting | محدودسازی نرخ

```php
function nias_check_rate_limit( $action ) {
    $transient_key = 'nias_rate_limit_' . $action . '_' . get_current_user_id();
    $attempts = get_transient( $transient_key );
    
    if ( $attempts && $attempts >= 5 ) {
        wp_die( 'Too many attempts. Please try again later.' );
    }
    
    set_transient( $transient_key, ( $attempts ? $attempts + 1 : 1 ), HOUR_IN_SECONDS );
}

// Usage
nias_check_rate_limit( 'license_activation' );
```

---

## 🎨 Customization Examples | نمونه‌های سفارشی‌سازی

### Custom Admin Notice Styling | استایل سفارشی اعلان‌های مدیریت

```php
add_action( 'admin_head', function() {
    ?>
    <style>
        .nias-license-notice {
            border-left: 4px solid #00a0d2;
            background: #fff;
            padding: 15px;
            margin: 15px 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .nias-license-notice.error {
            border-color: #dc3232;
        }
        .nias-license-notice.success {
            border-color: #46b450;
        }
    </style>
    <?php
});
```

### Custom Email Template | قالب سفارشی ایمیل

```php
add_filter( 'nias_my_plugin_expiry_email_body', function( $message, $days_left ) {
    $html = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #ff6600;">License Expiring Soon!</h2>
        <p>Your license will expire in <strong>' . $days_left . ' days</strong>.</p>
        <p>Renew now to continue receiving:</p>
        <ul>
            <li>✅ Updates and bug fixes</li>
            <li>✅ Premium support</li>
            <li>✅ New features</li>
        </ul>
        <a href="https://yourstore.com/renew" style="display: inline-block; background: #ff6600; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 4px;">Renew Now</a>
    </div>
    ';
    return $html;
}, 10, 2 );
```

### Dashboard Widget | ویجت داشبورد

```php
add_action( 'wp_dashboard_setup', function() {
    wp_add_dashboard_widget(
        'nias_license_widget',
        'Plugin License Status',
        'nias_render_license_widget'
    );
});

function nias_render_license_widget() {
    $status = get_option( 'nias_my_plugin_license_status' );
    $data = get_option( 'nias_my_plugin_license_data' );
    
    if ( $status === 'active' && $data ) {
        $expiry = date( 'F j, Y', strtotime( $data['expiresAt'] ) );
        $days_left = ceil( ( strtotime( $data['expiresAt'] ) - time() ) / DAY_IN_SECONDS );
        
        echo '<div style="text-align: center; padding: 20px;">';
        echo '<div style="font-size: 48px; color: #46b450;">✓</div>';
        echo '<h3 style="margin: 10px 0;">License Active</h3>';
        echo '<p>Expires: <strong>' . $expiry . '</strong></p>';
        echo '<p>(<strong>' . $days_left . '</strong> days remaining)</p>';
        echo '</div>';
    } else {
        echo '<div style="text-align: center; padding: 20px;">';
        echo '<div style="font-size: 48px; color: #dc3232;">✗</div>';
        echo '<h3 style="margin: 10px 0;">License Inactive</h3>';
        echo '<a href="' . admin_url( 'options-general.php?page=my-plugin-license' ) . '" class="button button-primary">Activate Now</a>';
        echo '</div>';
    }
}
```

### Custom Validation Logic | منطق اعتبارسنجی سفارشی

```php
add_filter( 'nias_my_plugin_validate_license', function( $is_valid, $license_data ) {
    // Add custom validation rules
    
    // Example: Check if license is for correct product
    if ( $license_data['productId'] !== 123 ) {
        return false;
    }
    
    // Example: Check if user has specific role
    $user = wp_get_current_user();
    if ( ! in_array( 'administrator', $user->roles ) ) {
        return false;
    }
    
    return $is_valid;
}, 10, 2 );
```

---

## 📊 Monitoring & Analytics | نظارت و تحلیل

### Track License Usage | پیگیری استفاده از لایسنس

```php
function nias_track_license_event( $event_type, $license_key, $details = array() ) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'nias_license_events';
    
    $wpdb->insert(
        $table_name,
        array(
            'event_type' => $event_type,
            'license_key' => $license_key,
            'details' => wp_json_encode( $details ),
            'user_id' => get_current_user_id(),
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'created_at' => current_time( 'mysql' )
        )
    );
}

// Usage
nias_track_license_event( 'activation', $license_key, array(
    'server' => $_SERVER['SERVER_NAME'],
    'php_version' => PHP_VERSION,
    'wp_version' => get_bloginfo( 'version' )
));
```

### Generate Usage Reports | تولید گزارش‌های استفاده

```php
function nias_get_license_report( $license_key ) {
    $client = new Nias_License_Manager_Client( /* ... */ );
    $data = $client->nias_validate_license( $license_key );
    
    $report = array(
        'key' => $license_key,
        'status' => $data['status'] == 2 ? 'Active' : 'Inactive',
        'product' => get_the_title( $data['productId'] ),
        'activations' => $data['timesActivated'] . '/' . $data['timesActivatedMax'],
        'expires' => $data['expiresAt'],
        'days_remaining' => ceil( ( strtotime( $data['expiresAt'] ) - time() ) / DAY_IN_SECONDS )
    );
    
    return $report;
}
```

---

## 🧪 Testing | تست

### Unit Testing Example | نمونه تست واحد

```php
class NLMW_Test extends WP_UnitTestCase {
    
    private $client;
    private $test_license = 'TEST-LICENSE-KEY';
    
    public function setUp() {
        parent::setUp();
        
        $this->client = new Nias_License_Manager_Client(
            'https://test-store.com',
            'ck_test',
            'cs_test'
        );
    }
    
    public function test_validate_license() {
        $result = $this->client->nias_validate_license( $this->test_license );
        
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'status', $result );
        $this->assertEquals( 2, $result['status'] );
    }
    
    public function test_activate_license() {
        $result = $this->client->nias_activate_license( $this->test_license );
        
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'activationData', $result );
    }
    
    public function test_is_license_valid() {
        $is_valid = $this->client->nias_is_license_valid( $this->test_license );
        
        $this->assertTrue( $is_valid );
    }
    
    public function test_invalid_license() {
        $result = $this->client->nias_validate_license( 'INVALID-KEY' );
        
        $this->assertFalse( $result );
    }
}
```

### Manual Testing Checklist | چک‌لیست تست دستی

- [ ] Install and activate plugin
- [ ] Configure API credentials
- [ ] Activate valid license key
- [ ] Check license status in admin
- [ ] Deactivate license
- [ ] Try activating expired license
- [ ] Test with invalid license key
- [ ] Verify cron job scheduling
- [ ] Check email notifications
- [ ] Test manual license check
- [ ] Verify activation limits
- [ ] Test license expiration warning
- [ ] Check error handling
- [ ] Verify logs are working
- [ ] Test in different user roles

---

## 🌐 Multilingual Support | پشتیبانی چندزبانه

### Adding New Languages | افزودن زبان‌های جدید

1. **Copy translations.php** | کپی فایل ترجمه‌ها

```bash
cp translations.php translations-de.php
```

2. **Edit translations** | ویرایش ترجمه‌ها

```php
// translations-de.php
return array(
    'License' => 'Lizenz',
    'Active' => 'Aktiv',
    'Inactive' => 'Inaktiv',
    // ...
);
```

3. **Load translations** | بارگذاری ترجمه‌ها

```php
function nias_load_translations() {
    $locale = get_locale();
    $translations_file = plugin_dir_path( __FILE__ ) . 'translations-' . $locale . '.php';
    
    if ( file_exists( $translations_file ) ) {
        return include $translations_file;
    }
    
    return include plugin_dir_path( __FILE__ ) . 'translations.php';
}

// Usage
$translations = nias_load_translations();
echo $translations['License'];
```

---

## 🔄 Migration Guide | راهنمای مهاجرت

### From Other License Systems | از سیستم‌های لایسنس دیگر

#### Step 1: Export existing licenses | مرحله 1: خروجی لایسنس‌های موجود

```php
function nias_export_old_licenses() {
    global $wpdb;
    
    $licenses = $wpdb->get_results(
        "SELECT * FROM {$wpdb->prefix}old_licenses"
    );
    
    $export = array();
    foreach ( $licenses as $license ) {
        $export[] = array(
            'key' => $license->license_key,
            'status' => $license->status,
            'expires' => $license->expiry_date
        );
    }
    
    return $export;
}
```

#### Step 2: Import to NLMW | مرحله 2: وارد کردن به NLMW

```php
function nias_import_licenses( $licenses ) {
    $client = new Nias_License_Manager_Client( /* ... */ );
    
    foreach ( $licenses as $license ) {
        // Validate each license
        $result = $client->nias_validate_license( $license['key'] );
        
        if ( $result ) {
            // Store in database
            update_option( 'nias_license_' . $license['key'], array(
                'status' => 'active',
                'data' => $result
            ));
        }
    }
}
```

---

## 📈 Performance Optimization | بهینه‌سازی عملکرد

### 1. Caching | کش کردن

```php
// Cache license validation for 1 hour
function nias_cached_validate( $license_key ) {
    $cache_key = 'nias_license_' . md5( $license_key );
    $cached = get_transient( $cache_key );
    
    if ( false !== $cached ) {
        return $cached;
    }
    
    $client = new Nias_License_Manager_Client( /* ... */ );
    $result = $client->nias_validate_license( $license_key );
    
    set_transient( $cache_key, $result, HOUR_IN_SECONDS );
    
    return $result;
}
```

### 2. Lazy Loading | بارگذاری تنبل

```php
// Only load license classes when needed
function nias_lazy_load_license() {
    static $client = null;
    
    if ( null === $client ) {
        require_once 'class-license-manager-client.php';
        $client = new Nias_License_Manager_Client( /* ... */ );
    }
    
    return $client;
}
```

### 3. Database Optimization | بهینه‌سازی پایگاه داده

```php
// Create index for faster queries
global $wpdb;
$wpdb->query(
    "CREATE INDEX idx_license_key ON {$wpdb->prefix}options(option_name) 
    WHERE option_name LIKE 'nias_%_license_%'"
);
```

---

## 🎯 Changelog | تغییرات

### Version 1.0.0 (2024-12-18)

#### Added | اضافه شده
- ✨ Initial release | انتشار اولیه
- ✅ License validation functionality | قابلیت اعتبارسنجی لایسنس
- ✅ Activation/Deactivation system | سیستم فعال‌سازی/غیرفعال‌سازی
- ✅ Automatic cron checks | بررسی‌های خودکار کرون
- ✅ Professional admin settings page | صفحه تنظیمات مدیریتی حرفه‌ای
- ✅ Email notifications | اعلان‌های ایمیل
- ✅ Comprehensive logging | لاگ‌گیری جامع
- ✅ Bilingual support (EN/FA) | پشتیبانی دو زبانه
- ✅ Full API integration | یکپارچگی کامل API
- ✅ Error handling | مدیریت خطا
- ✅ Customizable translations | ترجمه‌های قابل سفارشی‌سازی

---

## 📞 Support | پشتیبانی

### Getting Help | دریافت کمک

**Documentation:** https://docs.yoursite.com  
**Email:** support@yoursite.com  
**Forum:** https://forum.yoursite.com  
**GitHub:** https://github.com/yourname/nlmw

### Reporting Bugs | گزارش باگ‌ها

Please include:
- WordPress version
- PHP version
- License Manager version
- Error messages
- Steps to reproduce

---

## 📄 License | لایسنس

This library is licensed under GPL-2.0+

این کتابخانه تحت لایسنس GPL-2.0+ منتشر شده است

---

## 👏 Credits | اعتبار

**Developed by:** Nias Development Team  
**توسعه توسط:** تیم توسعه نیاس

**Built for:** License Manager for WooCommerce  
**ساخته شده برای:** مدیریت لایسنس ووکامرس

**Special Thanks:**
- WordPress Community
- WooCommerce Team
- License Manager Plugin Developers

---

## 🚀 What's Next? | مرحله بعد چیست؟

After setting up NLMW, you can:

پس از راه‌اندازی NLMW، می‌توانید:

1. **Customize the UI** - Match your brand colors  
   **سفارشی‌سازی رابط** - تطبیق با رنگ‌های برند شما

2. **Add Premium Features** - Restrict based on license  
   **افزودن ویژگی‌های پریمیوم** - محدودسازی بر اساس لایسنس

3. **Setup Email Templates** - Custom expiry notifications  
   **راه‌اندازی قالب‌های ایمیل** - اعلان‌های سفارشی انقضا

4. **Integrate Analytics** - Track license usage  
   **یکپارچه‌سازی تحلیل** - پیگیری استفاده از لایسنس

5. **Build Dashboard** - License management interface  
   **ساخت داشبورد** - رابط مدیریت لایسنس

---

**Happy Coding! 🎉**  
**برنامه‌نویسی خوبی داشته باشید! 🎉**
