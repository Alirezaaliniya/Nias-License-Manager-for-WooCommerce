# Nias License Manager for WooCommerce (NLMW)
# مدیریت لایسنس نیاس برای ووکامرس

**Version | نسخه:** 1.0.0  
**Author | نویسنده:** Nias Development Team  
**License | لایسنس:** GPL-2.0+

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
        $status = get_option( 'nias_my-awesome-plugin_license_
