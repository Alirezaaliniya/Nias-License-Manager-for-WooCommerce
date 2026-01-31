# Nias License Manager for WooCommerce (NLMW)
# مدیریت لایسنس نیاس برای ووکامرس

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![License](https://img.shields.io/badge/license-GPL--2.0+-green.svg)
![PHP](https://img.shields.io/badge/php-7.2%2B-purple.svg)
![WordPress](https://img.shields.io/badge/wordpress-5.0%2B-blue.svg)

A professional PHP library for seamless integration with License Manager for WooCommerce (Free Version).

کتابخانه حرفه‌ای PHP برای یکپارچگی با مدیریت لایسنس ووکامرس (نسخه رایگان).

---

## 🚀 Quick Start | شروع سریع

# NLMW Quick Start Guide
# راهنمای شروع سریع NLMW

## 🚀 Installation in 3 Minutes | نصب در 3 دقیقه

### Step 1: Copy Files | مرحله 1: کپی فایل‌ها

```
your-plugin/
├── includes/
│   └── license/
│       ├── class-license-manager-client.php
│       ├── class-license-settings-page.php
│       ├── class-license-cron-handler.php
│       └── translations.php
```

### Step 2: Include in Your Plugin | مرحله 2: اضافه کردن به افزونه

```php
<?php
/**
 * Plugin Name: My Awesome Plugin
 * Version: 1.0.0
 */

// Include NLMW files
require_once plugin_dir_path( __FILE__ ) . 'includes/license/class-license-manager-client.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/license/class-license-settings-page.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/license/class-license-cron-handler.php';

// Initialize on plugins_loaded
add_action( 'plugins_loaded', function() {
    
    // 1. Settings Page
    new Nias_License_Settings_Page(
        'My Awesome Plugin',  // Your plugin name
        'my-awesome-plugin'   // Your plugin slug
    );
    
    // 2. Cron Handler (check daily)
    new Nias_License_Cron_Handler(
        'my-awesome-plugin',  // Your plugin slug
        DAY_IN_SECONDS        // Check interval
    );
    
    // 3. License Client with Product IDs
    $client = new Nias_License_Manager_Client(
        get_option( 'nias_my-awesome-plugin_store_url' ),
        get_option( 'nias_my-awesome-plugin_consumer_key' ),
        get_option( 'nias_my-awesome-plugin_consumer_secret' ),
        get_option( 'nias_my-awesome-plugin_product_ids', array() ),  // ← Product IDs
        get_option( 'nias_my-awesome-plugin_cache_days', 5 )
    );
    
    // 4. Check License
    $license_key = get_option( 'nias_my-awesome-plugin_license_key' );
    
    if ( $client->nias_is_license_valid( $license_key ) ) {
        // ✅ License valid - load premium features
        require_once 'includes/premium-features.php';
    } else {
        // ❌ License invalid - show notice
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-warning"><p>';
            echo 'Please activate your license to use premium features.';
            echo '</p></div>';
        });
    }
});
```

---

## 📦 Understanding Product IDs | درک شناسه محصولات

### Why Product IDs? | چرا شناسه محصولات؟

اگر **چند محصول** با لایسنس در فروشگاه دارید:

```
Your WooCommerce Store:
├── 📦 Product ID: 5735 → "SEO Plugin Pro"
├── 📦 Product ID: 5736 → "Analytics Plugin"
└── 📦 Product ID: 5737 → "Backup Plugin"
```

هر پلاگین باید **فقط** لایسنس مربوط به خودش را قبول کند!

### Example Scenario | مثال عملی

**Customer buys all 3 plugins:**
```
License KEY-111 → Product 5735 (SEO Plugin)
License KEY-222 → Product 5736 (Analytics)
License KEY-333 → Product 5737 (Backup)
```

 با این تنظیمات:
```php
$client = new Nias_License_Manager_Client(
    'https://yourstore.com',
    'ck_xxxxx',
    'cs_xxxxx',
    array( 5735 ),  // ← Only accept Product ID 5735
    5
);
```

حالا:
- ✅ `KEY-111` با Product ID 5735 → **Valid** (قبول می‌شود)
- ❌ `KEY-222` با Product ID 5736 → **Invalid** (رد می‌شود)
- ❌ `KEY-333` با Product ID 5737 → **Invalid** (رد می‌شود)

---



## 💡 Multiple Products Support | پشتیبانی چند محصول

### One Plugin = One Product | یک افزونه = یک محصول

```php
new Nias_License_Manager_Client(
    $store_url,
    $consumer_key,
    $consumer_secret,
    array( 5735 ),  // Single product
    5
);
```

### One Plugin = Multiple Products | یک افزونه = چند محصول

مثلاً اگر یک Bundle دارید:

```php
new Nias_License_Manager_Client(
    $store_url,
    $consumer_key,
    $consumer_secret,
    array( 5735, 5736, 5737 ),  // Multiple products
    5
);
```

این لایسنس‌های **هر سه محصول** را قبول می‌کند!

### Accept Any Product | قبول هر محصول

```php
new Nias_License_Manager_Client(
    $store_url,
    $consumer_key,
    $consumer_secret,
    array(),  // Empty = accept any product
    5
);
```

---

## 🔍 How Validation Works | نحوه اعتبارسنجی

```
┌─────────────────────────────────────────┐
│ User enters License Key                 │
└────────────────┬────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────┐
│ Plugin calls API:                       │
│ nias_validate_license( $license_key )   │
└────────────────┬────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────┐
│ License Manager API returns:            │
│ {                                       │
│   "licenseKey": "KEY-111",             │
│   "productId": 5735,                   │
│   "status": 2,                         │
│   "expiresAt": "2025-12-31",          │
│   ...                                  │
│ }                                      │
└────────────────┬────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────┐
│ NLMW checks:                            │
│ ✓ Is status = 2? (active)             │
│ ✓ Is productId in allowed list?       │ ← NEW CHECK!
│ ✓ Is not expired?                      │
│ ✓ Has remaining activations?           │
└────────────────┬────────────────────────┘
                 │
        ┌────────┴────────┐
        │                 │
        ▼                 ▼
    ✅ Valid          ❌ Invalid
```

---

## 📊 Real-World Examples | مثال‌های واقعی

### Example 1: Single Product Plugin | پلاگین تک محصول

```php
// My SEO Plugin
$client = new Nias_License_Manager_Client(
    'https://mystore.com',
    'ck_xxxxx',
    'cs_xxxxx',
    array( 5735 ),  // Only SEO Plugin licenses
    5
);
```

### Example 2: Plugin Suite | مجموعه پلاگین

```php
// My Marketing Suite (accepts 3 different products)
$client = new Nias_License_Manager_Client(
    'https://mystore.com',
    'ck_xxxxx',
    'cs_xxxxx',
    array( 5735, 5736, 5737 ),  // SEO, Analytics, Social
    5
);
```

### Example 3: Development Testing | تست توسعه

```php
// Accept any product during development
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    $product_ids = array();  // Accept all
} else {
    $product_ids = array( 5735 );  // Production only
}

$client = new Nias_License_Manager_Client(
    $store_url,
    $consumer_key,
    $consumer_secret,
    $product_ids,
    5
);
```

---

## 🎓 Best Practices | بهترین شیوه‌ها

### ✅ DO | انجام دهید

```php
// ✅ Store product IDs in constants
define( 'MY_PLUGIN_PRODUCT_IDS', array( 5735, 5736 ) );

// ✅ Validate on plugin load
add_action( 'plugins_loaded', 'check_license' );

// ✅ Cache results for performance
$cache_days = 5; // Good default

// ✅ Show clear error messages
if ( ! $valid ) {
    echo 'Wrong product license. Please use a license for ' . MY_PLUGIN_NAME;
}
```

### ❌ DON'T | انجام ندهید

```php
// ❌ Hardcode credentials
$client = new Nias_License_Manager_Client(
    'https://mystore.com',  // Bad! Use options
    'ck_hardcoded',          // Bad! Security risk
    'cs_hardcoded',          // Bad! Security risk
    array( 5735 ),
    5
);

// ❌ Check on every page load
// Use caching and cron instead!

// ❌ Empty product IDs for released plugin
$product_ids = array();  // Anyone can use any license!
```

---

## 🐛 Common Issues | مشکلات رایج

### Issue 1: Wrong Product Error | خطای محصول اشتباه

**Problem:**
```
License is not valid for this product. Expected: 5735, Got: 5736
```

**Solution:**
```php
// Check your Product ID in WooCommerce:
// Products → Edit Product → Get ID from URL
// https://store.com/wp-admin/post.php?post=5735&action=edit
//                                          ^^^^

// Update in code:
$client->nias_set_product_ids( array( 5735 ) );  // Correct ID
```

### Issue 2: License Works Everywhere | لایسنس همه جا کار می‌کند

**Problem:**
```php
$product_ids = array();  // Empty!
```

**Solution:**
```php
$product_ids = array( 5735 );  // Specify your product
```

### Issue 3: Multiple Plugins Same Store | چند پلاگین یک فروشگاه

**Problem:**
All plugins accept each other's licenses

**Solution:**
```php
// Plugin A
$client_a = new Nias_License_Manager_Client(
    $store_url, $key, $secret,
    array( 5735 ),  // Only Product A
    5
);

// Plugin B
$client_b = new Nias_License_Manager_Client(
    $store_url, $key, $secret,
    array( 5736 ),  // Only Product B
    5
);
```

---


## ✨ Features | ویژگی‌ها

| Feature | Description |
|---------|-------------|
| ✅ **License Validation** | Real-time license verification via API<br>اعتبارسنجی لایسنس در زمان واقعی |
| ✅ **Auto Activation** | One-click license activation<br>فعال‌سازی یک کلیکی لایسنس |
| ✅ **Cron Checks** | Automatic daily validation<br>اعتبارسنجی خودکار روزانه |
| ✅ **Email Alerts** | Expiry warnings (30, 14, 7, 3, 1 days)<br>هشدارهای انقضا |
| ✅ **Professional UI** | Beautiful admin settings page<br>صفحه تنظیمات زیبا |
| ✅ **Error Handling** | Comprehensive error management<br>مدیریت جامع خطا |
| ✅ **Logging** | Debug and activity logs<br>لاگ دیباگ و فعالیت |
| ✅ **Bilingual** | English & Persian support<br>پشتیبانی انگلیسی و فارسی |

---

## 📋 Requirements | پیش‌نیازها

- WordPress 5.0+
- PHP 7.2+
- License Manager for WooCommerce (Free)
- WooCommerce (on license server)

---

## 📖 Documentation | مستندات

**Full documentation available in:** `NLMW-Documentation.md`

**مستندات کامل موجود در:** `NLMW-Documentation.md`

### Quick Links | لینک‌های سریع

- [Installation Guide](#installation--نصب) | راهنمای نصب
- [API Reference](NLMW-Documentation.md#-api-reference--مرجع-api) | مرجع API
- [Usage Examples](NLMW-Documentation.md#-usage--استفاده) | نمونه‌های استفاده
- [Troubleshooting](NLMW-Documentation.md#-troubleshooting--رفع-مشکلات) | رفع مشکلات
- [FAQ](NLMW-Documentation.md#-faq--سوالات-متداول) | سوالات متداول

---


## 💡 Usage Examples | نمونه‌های استفاده

### Check License Status | بررسی وضعیت لایسنس

```php
$client = new Nias_License_Manager_Client( $url, $key, $secret );

if ( $client->nias_is_license_valid( $license_key ) ) {
    echo 'License is active! | لایسنس فعال است!';
}
```

### Get License Details | دریافت جزئیات لایسنس

```php
$data = $client->nias_get_license_details( $license_key );
echo 'Expires: ' . $data['expiresAt'];
echo 'Activations: ' . $data['timesActivated'] . '/' . $data['timesActivatedMax'];
```

### Activate License | فعال‌سازی لایسنس

```php
$result = $client->nias_activate_license( $license_key );
if ( $result ) {
    echo 'License activated! | لایسنس فعال شد!';
}
```

### Force Cron Check | بررسی اجباری کرون

```php
$cron = new Nias_License_Cron_Handler( 'my-plugin' );
$status = $cron->nias_force_check_now();
echo 'Status: ' . $status;
```

---

## 🧩 استفاده در پلاگین و قالب (فارسی)

### داخل پلاگین وردپرس

```php
// فایل‌های کتابخانه را اضافه کنید (داخل افزونه خودتان)
require_once plugin_dir_path( __FILE__ ) . 'includes/license/class-license-manager-client.php';

// ساخت کلاینت با اطلاعات ذخیره‌شده در تنظیمات
$client = new Nias_License_Manager_Client(
    get_option( 'nlmw_my-plugin_store_url' ),
    get_option( 'nlmw_my-plugin_consumer_key' ),
    get_option( 'nlmw_my-plugin_consumer_secret' )
);

$license_key = get_option( 'nlmw_my-plugin_license_key' );

if ( $client->nias_is_license_valid( $license_key ) ) {
    // بخش‌های پریمیوم را بارگذاری کنید
    // require_once 'includes/premium-features.php';
} else {
    add_action( 'admin_notices', function() {
        echo '<div class="notice notice-warning"><p>برای استفاده از ویژگی‌های پریمیوم، لایسنس را فعال کنید.</p></div>';
    } );
}
```

### داخل قالب وردپرس (theme)

```php
// داخل functions.php قالب
require_once get_stylesheet_directory() . '/includes/license/class-license-manager-client.php';

add_action( 'after_setup_theme', function() {
    $client = new Nias_License_Manager_Client(
        get_option( 'nlmw_my-theme_store_url' ),
        get_option( 'nlmw_my-theme_consumer_key' ),
        get_option( 'nlmw_my-theme_consumer_secret' )
    );

    $license_key = get_option( 'nlmw_my-theme_license_key' );

    // نمونه استفاده: نمایش پیام در پیشخوان برای مدیر
    if ( is_admin() && ! $client->nias_is_license_valid( $license_key ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error"><p>قالب فعال نیست. لطفاً لایسنس را ثبت و فعال کنید.</p></div>';
        } );
    }
} );
```

---

## 🔒 قفل کردن بخش‌ها (چند روش کاربردی)

### 1) مخفی کردن نمایش (Frontend/UI)

```php
$client = new Nias_License_Manager_Client( $url, $key, $secret );
$license_key = get_option( 'nlmw_my-plugin_license_key' );

if ( ! $client->nias_is_license_valid( $license_key ) ) {
    // محتوای پریمیوم را نمایش نده
    return;
}

// محتوای پریمیوم
echo '<div class="premium-box">محتوای ویژه کاربران دارای لایسنس</div>';
```

### 2) نمایش پیام فعال‌سازی

```php
if ( ! $client->nias_is_license_valid( $license_key ) ) {
    echo '<div class="notice notice-warning"><p>برای دسترسی به این بخش، لایسنس را فعال کنید.</p></div>';
    // یا توقف عملیات حساس:
    // wp_die( 'برای استفاده از این ویژگی، لایسنس را فعال کنید.' );
}
```

### 3) ریدایرکت به صفحه ثبت/فعال‌سازی لایسنس

```php
add_action( 'admin_init', function() {
    $client = new Nias_License_Manager_Client( $url, $key, $secret );
    $license_key = get_option( 'nlmw_my-plugin_license_key' );

    if ( ! $client->nias_is_license_valid( $license_key ) ) {
        // ریدایرکت به صفحه تنظیمات لایسنس افزونه
        $page = 'my-plugin-license'; // اسلاگ صفحه: {plugin_slug}-license
        wp_safe_redirect( admin_url( 'options-general.php?page=' . $page ) );
        exit;
    }
} );

// ریدایرکت در فرانت‌اند به صفحه سفارشی
add_action( 'template_redirect', function() {
    $client = new Nias_License_Manager_Client( $url, $key, $secret );
    $license_key = get_option( 'nlmw_my-plugin_license_key' );

    if ( is_page( 'premium-content' ) && ! $client->nias_is_license_valid( $license_key ) ) {
        wp_safe_redirect( home_url( '/register-license' ) );
        exit;
    }
} );
```

### 4) قفل بر اساس شورت‌کد

```php
add_shortcode( 'premium_box', function( $atts, $content = '' ) {
    $client = new Nias_License_Manager_Client(
        get_option( 'nlmw_my-plugin_store_url' ),
        get_option( 'nlmw_my-plugin_consumer_key' ),
        get_option( 'nlmw_my-plugin_consumer_secret' )
    );
    $license_key = get_option( 'nlmw_my-plugin_license_key' );

    if ( ! $client->nias_is_license_valid( $license_key ) ) {
        return '<div class="notice notice-warning"><p>برای مشاهده این بخش، لایسنس را فعال کنید.</p></div>';
    }

    return '<div class="premium-box">' . do_shortcode( $content ) . '</div>';
} );

// استفاده در محتوا: [premium_box]محتوای پریمیوم[/premium_box]
```

### 5) قفل کردن صفحه‌های مدیریت افزونه

```php
add_action( 'admin_menu', function() {
    $client = new Nias_License_Manager_Client( $url, $key, $secret );
    $license_key = get_option( 'nlmw_my-plugin_license_key' );

    if ( ! $client->nias_is_license_valid( $license_key ) ) {
        // حذف زیرمنوها یا پنهان‌سازی صفحات تنظیمات پریمیوم
        remove_submenu_page( 'options-general.php', 'my-plugin-premium' );
    }
} );
```

---

## 🎨 Customization | سفارشی‌سازی


### Cron Interval | فاصله کرون

```php
// Check every 12 hours | بررسی هر 12 ساعت
new Nias_License_Cron_Handler( 'my-plugin', 12 * HOUR_IN_SECONDS );

// Check weekly | بررسی هفتگی
new Nias_License_Cron_Handler( 'my-plugin', WEEK_IN_SECONDS );
```

### Custom Actions | اکشن‌های سفارشی

```php
// Hook when license becomes invalid
add_action( 'nias_my_plugin_license_invalid', function( $data, $reason ) {
    // Your custom code
    // کد سفارشی شما
}, 10, 2 );
```

---

## 🐛 Troubleshooting | رفع مشکلات

### Common Issues | مشکلات رایج

**License won't activate | لایسنس فعال نمی‌شود**
```
✓ Check Store URL format (no trailing slash)
✓ Verify API credentials
✓ Ensure License Manager is active on store
✓ Check API permissions (Read & Write)
```

**Cron not running | کرون اجرا نمی‌شود**
```
✓ Verify WP-Cron is enabled
✓ Check server cron jobs
✓ Test with: wp_cron.php?doing_wp_cron
```

**SSL errors | خطاهای SSL**
```
✓ Update SSL certificate
✓ Check server PHP/cURL settings
✓ Enable SSL verification
```

### Enable Debug Mode | فعال‌سازی حالت دیباگ

```php
// Add to wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );

// View logs
$cron = new Nias_License_Cron_Handler( 'my-plugin' );
$logs = $cron->nias_get_logs( 50 );
print_r( $logs );
```

---

## 📊 API Methods | متدهای API

### Client Methods | متدهای کلاینت

| Method | Description |
|--------|-------------|
| `nias_validate_license()` | Validate license and get data<br>اعتبارسنجی لایسنس و دریافت داده |
| `nias_activate_license()` | Activate license<br>فعال‌سازی لایسنس |
| `nias_deactivate_license()` | Deactivate license<br>غیرفعال‌سازی لایسنس |
| `nias_is_license_valid()` | Quick validity check<br>بررسی سریع اعتبار |
| `nias_get_remaining_activations()` | Get remaining slots<br>دریافت فعال‌سازی‌های باقی‌مانده |
| `nias_get_license_expiry()` | Get expiry date<br>دریافت تاریخ انقضا |
| `nias_get_last_error()` | Get error message<br>دریافت پیام خطا |

### Cron Methods | متدهای کرون

| Method | Description |
|--------|-------------|
| `nias_force_check_now()` | Manual license check<br>بررسی دستی لایسنس |
| `nias_get_last_check_time()` | Last check timestamp<br>تایم‌استمپ آخرین بررسی |
| `nias_get_next_check_time()` | Next check timestamp<br>تایم‌استمپ بررسی بعدی |
| `nias_get_logs()` | Get activity logs<br>دریافت لاگ‌های فعالیت |
| `nias_clear_logs()` | Clear all logs<br>پاک کردن تمام لاگ‌ها |

---

## 🔒 Security | امنیت

### Best Practices | بهترین شیوه‌ها

✅ **Validate user input** | اعتبارسنجی ورودی کاربر  
✅ **Use nonces** | استفاده از Nonce  
✅ **Check capabilities** | بررسی قابلیت‌ها  
✅ **Sanitize data** | پاک‌سازی داده‌ها  
✅ **Encrypt sensitive info** | رمزنگاری اطلاعات حساس  

```php
// Always sanitize
$license_key = sanitize_text_field( $_POST['license'] );

// Check nonce
check_admin_referer( 'nias_license_action', 'nias_nonce' );

// Check capabilities
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Unauthorized' );
}
```

---

## 📝 Changelog | تغییرات

### v1.0.0

**Added:**
- ✨ Initial release
- ✅ License validation
- ✅ Activation system
- ✅ Cron automation
- ✅ Admin UI
- ✅ Email notifications
- ✅ Logging system
- ✅ Bilingual support

---

## 🤝 Contributing | مشارکت

### English:
Contributions are welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

---

## 📧 Support | پشتیبانی

### English:
- **Email**: info@nias.ir
- **Documentation**: [Full Docs](https://github.com/Alirezaaliniya/Nias-License-Manager-for-WooCommerce/blob/main/NLMW-Documentation.md)
- **Issues**: [GitHub Issues](https://github.com/Alirezaaliniya/Nias-License-Manager-for-WooCommerce/issues)
- **License Manager Docs**: [Official Docs](https://licensemanager.at/docs/)
---

## 📄 License | مجوز

This project is licensed under the GPL-2.0+ License.

این پروژه تحت مجوز GPL-2.0+ منتشر شده است.

---

## 🙏 Credits | تشکر

### English:
- Built for [License Manager for WooCommerce](https://licensemanager.at/)
- Created by Nias Team
- With ❤️ for WordPress Community


---

**⭐ If this library helped you, please give it a star!**

**⭐ اگر این کتابخانه به شما کمک کرد، لطفا یک ستاره بدهید!**
*
