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

### Installation | نصب

```bash
# Download files to your plugin
# دانلود فایل‌ها در افزونه خود
your-plugin/
├── includes/
│   ├── license/
│   │   ├── class-license-manager-client.php
│   │   ├── class-license-settings-page.php
│   │   ├── class-license-cron-handler.php
│   │   └── translations.php
```

### Basic Setup | راه‌اندازی پایه

```php
// 1. Include files | اضافه کردن فایل‌ها
require_once 'includes/license/class-license-manager-client.php';
require_once 'includes/license/class-license-settings-page.php';
require_once 'includes/license/class-license-cron-handler.php';

// 2. Initialize settings page | مقداردهی اولیه صفحه تنظیمات
new Nias_License_Settings_Page( 'My Plugin', 'my-plugin' );

// 3. Initialize cron (daily checks) | مقداردهی اولیه کرون (بررسی روزانه)
new Nias_License_Cron_Handler( 'my-plugin', DAY_IN_SECONDS );

// 4. Check license before features | بررسی لایسنس قبل از ویژگی‌ها
$client = new Nias_License_Manager_Client(
    get_option( 'nias_my-plugin_store_url' ),
    get_option( 'nias_my-plugin_consumer_key' ),
    get_option( 'nias_my-plugin_consumer_secret' )
);

$license_key = get_option( 'nias_my-plugin_license_key' );

if ( $client->nias_is_license_valid( $license_key ) ) {
    // ✅ License valid - enable features
    // ✅ لایسنس معتبر - فعال‌سازی ویژگی‌ها
} else {
    // ❌ License invalid - restrict features
    // ❌ لایسنس نامعتبر - محدودسازی ویژگی‌ها
}
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

- [Installation Guide](#installation) | راهنمای نصب
- [API Reference](NLMW-Documentation.md#api-reference) | مرجع API
- [Usage Examples](NLMW-Documentation.md#usage) | نمونه‌های استفاده
- [Troubleshooting](NLMW-Documentation.md#troubleshooting) | رفع مشکلات
- [FAQ](NLMW-Documentation.md#faq) | سوالات متداول

---

## 🔧 Configuration | پیکربندی

### 1. Get API Credentials | دریافت اطلاعات API

On your WooCommerce store:
1. Go to `License Manager > Settings > REST API`
2. Click "Add API Key"
3. Set permissions: Read & Write
4. Copy Consumer Key & Secret

### 2. Configure in WordPress | پیکربندی در وردپرس

1. Navigate to `Settings > Your Plugin License`
2. Enter Store URL, Consumer Key, Consumer Secret
3. Save API Settings
4. Enter License Key
5. Click "Activate License"

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

## 🎨 Customization | سفارشی‌سازی

### Translations | ترجمه‌ها

Edit `translations.php`:

```php
return array(
    'License Key' => 'Your Translation | ترجمه شما',
    'Activate' => 'Your Translation | ترجمه شما',
    // ...
);
```

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

### v1.0.0 (2024-12-18)

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

### فارسی:
مشارکت شما خوشامد است! لطفا:
1. مخزن را Fork کنید
2. یک branch ویژگی ایجاد کنید
3. تغییرات را commit کنید
4. به branch خود push کنید
5. یک Pull Request باز کنید

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

### فارسی:
- ساخته شده برای [License Manager for WooCommerce](https://licensemanager.at/)
- ایجاد شده توسط تیم نیاس
- با ❤️ برای جامعه وردپرس

---

**⭐ If this library helped you, please give it a star!**

**⭐ اگر این کتابخانه به شما کمک کرد، لطفا یک ستاره بدهید!**
*
