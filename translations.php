<?php
/**
 * Nias License Manager Translations
 * ترجمه‌های مدیریت لایسنس نیاس
 *
 * @package NLMW
 * @version 1.0.0
 * 
 * Description: Customizable translations for the license manager
 * توضیحات: ترجمه‌های قابل سفارشی‌سازی برای مدیریت لایسنس
 * 
 * Instructions: Edit the values in the array below to customize translations
 * دستورالعمل: مقادیر آرایه زیر را برای سفارشی‌سازی ترجمه‌ها ویرایش کنید
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Custom Translations Array
 * آرایه ترجمه‌های سفارشی
 * 
 * Format: 'english_text' => 'your_translation'
 * فرمت: 'متن_انگلیسی' => 'ترجمه_شما'
 */
return array(
    
    // General Messages | پیام‌های عمومی
    'License' => 'License | لایسنس',
    'Active' => 'Active | فعال',
    'Inactive' => 'Inactive | غیرفعال',
    'Expired' => 'Expired | منقضی شده',
    'Invalid' => 'Invalid | نامعتبر',
    'Valid' => 'Valid | معتبر',
    'Unknown' => 'Unknown | نامشخص',
    
    // Settings Page | صفحه تنظیمات
    'License Settings' => 'License Settings | تنظیمات لایسنس',
    'API Configuration' => 'API Configuration | پیکربندی API',
    'License Activation' => 'License Activation | فعال‌سازی لایسنس',
    'Store URL' => 'Store URL | آدرس فروشگاه',
    'Consumer Key' => 'Consumer Key | کلید مصرف‌کننده',
    'Consumer Secret' => 'Consumer Secret | رمز مصرف‌کننده',
    'License Key' => 'License Key | کلید لایسنس',
    'Save Settings' => 'Save Settings | ذخیره تنظیمات',
    'Save API Settings' => 'Save API Settings | ذخیره تنظیمات API',
    
    // Instructions | دستورالعمل‌ها
    'Enter your WooCommerce store URL where License Manager is installed.' => 
        'Enter your WooCommerce store URL where License Manager is installed. | آدرس فروشگاه ووکامرس خود را که مدیریت لایسنس نصب شده وارد کنید.',
    
    'Get this from License Manager > Settings > REST API.' => 
        'Get this from License Manager > Settings > REST API. | این را از مدیریت لایسنس > تنظیمات > REST API دریافت کنید.',
    
    'Enter your license key to activate the plugin.' => 
        'Enter your license key to activate the plugin. | کلید لایسنس خود را برای فعال‌سازی افزونه وارد کنید.',
    
    // Actions | اقدامات
    'Activate License' => 'Activate License | فعال‌سازی لایسنس',
    'Deactivate License' => 'Deactivate License | غیرفعال‌سازی لایسنس',
    'Check License' => 'Check License | بررسی لایسنس',
    'Refresh License' => 'Refresh License | به‌روزرسانی لایسنس',
    
    // License Information | اطلاعات لایسنس
    'License Active' => 'License Active | لایسنس فعال',
    'Your license is currently active and valid.' => 
        'Your license is currently active and valid. | لایسنس شما در حال حاضر فعال و معتبر است.',
    
    'Expires At' => 'Expires At | تاریخ انقضا',
    'Activations' => 'Activations | فعال‌سازی‌ها',
    '%d of %d used' => '%d of %d used | %d از %d استفاده شده',
    
    // Success Messages | پیام‌های موفقیت
    'License activated successfully!' => 
        'License activated successfully! | لایسنس با موفقیت فعال شد!',
    
    'License deactivated successfully!' => 
        'License deactivated successfully! | لایسنس با موفقیت غیرفعال شد!',
    
    'License validated successfully.' => 
        'License validated successfully. | لایسنس با موفقیت اعتبارسنجی شد.',
    
    'Settings saved successfully!' => 
        'Settings saved successfully! | تنظیمات با موفقیت ذخیره شد!',
    
    // Error Messages | پیام‌های خطا
    'Please enter a license key.' => 
        'Please enter a license key. | لطفاً کلید لایسنس را وارد کنید.',
    
    'API credentials not configured.' => 
        'API credentials not configured. | اطلاعات API پیکربندی نشده است.',
    
    'License activation failed: %s' => 
        'License activation failed: %s | فعال‌سازی لایسنس ناموفق بود: %s',
    
    'License deactivation failed: %s' => 
        'License deactivation failed: %s | غیرفعال‌سازی لایسنس ناموفق بود: %s',
    
    'License validation failed: %s' => 
        'License validation failed: %s | اعتبارسنجی لایسنس ناموفق بود: %s',
    
    'Unknown error' => 'Unknown error | خطای نامشخص',
    'Unknown reason' => 'Unknown reason | دلیل نامشخص',
    
    'API request failed with status code: %d' => 
        'API request failed with status code: %d | درخواست API با کد وضعیت %d ناموفق بود',
    
    // Expiration Warnings | هشدارهای انقضا
    'Your license has expired! Please renew to continue receiving updates.' => 
        'Your license has expired! Please renew to continue receiving updates. | لایسنس شما منقضی شده است! لطفاً برای دریافت به‌روزرسانی‌ها، آن را تمدید کنید.',
    
    'Your license will expire in %d days.' => 
        'Your license will expire in %d days. | لایسنس شما %d روز دیگر منقضی می‌شود.',
    
    'Your license for %s will expire in %d days. Please renew to continue receiving updates and support.' =>
        'Your license for %s will expire in %d days. Please renew to continue receiving updates and support. | لایسنس شما برای %s %d روز دیگر منقضی می‌شود. لطفاً برای ادامه دریافت به‌روزرسانی‌ها و پشتیبانی، آن را تمدید کنید.',
    
    // Cron Messages | پیام‌های کرون
    'License check cron scheduled.' => 
        'License check cron scheduled. | کرون بررسی لایسنس برنامه‌ریزی شد.',
    
    'License check cron cleared.' => 
        'License check cron cleared. | کرون بررسی لایسنس پاک شد.',
    
    'Every %s seconds' => 'Every %s seconds | هر %s ثانیه',
    
    'License marked as invalid: %s' => 
        'License marked as invalid: %s | لایسنس به عنوان نامعتبر علامت‌گذاری شد: %s',
    
    'License marked as inactive after multiple validation failures.' => 
        'License marked as inactive after multiple validation failures. | لایسنس پس از چندین شکست اعتبارسنجی به عنوان غیرفعال علامت‌گذاری شد.',
    
    'Expiry notification sent: %d days remaining' => 
        'Expiry notification sent: %d days remaining | اعلان انقضا ارسال شد: %d روز باقی‌مانده',
    
    // Status Reasons | دلایل وضعیت
    'License status is not active' => 
        'License status is not active | وضعیت لایسنس فعال نیست',
    
    'License has expired' => 
        'License has expired | لایسنس منقضی شده است',
    
    'Your license for %s is no longer valid. Reason: %s' =>
        'Your license for %s is no longer valid. Reason: %s | لایسنس شما برای %s دیگر معتبر نیست. دلیل: %s',
    
    // Email Subjects | موضوعات ایمیل
    '[%s] License Expiring Soon' => 
        '[%s] License Expiring Soon | [%s] لایسنس در حال منقضی شدن',
    
    '[%s] License Invalid' => 
        '[%s] License Invalid | [%s] لایسنس نامعتبر',
    
    // Additional Info | اطلاعات اضافی
    'Last Check' => 'Last Check | آخرین بررسی',
    'Next Check' => 'Next Check | بررسی بعدی',
    'Status' => 'Status | وضعیت',
    'Product ID' => 'Product ID | شناسه محصول',
    'Order ID' => 'Order ID | شناسه سفارش',
    'Created At' => 'Created At | تاریخ ایجاد',
    'Updated At' => 'Updated At | تاریخ به‌روزرسانی',
    
    // Time Formats | فرمت‌های زمان
    'Never' => 'Never | هرگز',
    'Just now' => 'Just now | همین الان',
    '%d minutes ago' => '%d minutes ago | %d دقیقه پیش',
    '%d hours ago' => '%d hours ago | %d ساعت پیش',
    '%d days ago' => '%d days ago | %d روز پیش',
    
    // Activation Data | اطلاعات فعال‌سازی
    'Activation Token' => 'Activation Token | توکن فعال‌سازی',
    'IP Address' => 'IP Address | آدرس IP',
    'User Agent' => 'User Agent | عامل کاربر',
    'Activated At' => 'Activated At | زمان فعال‌سازی',
    'Deactivated At' => 'Deactivated At | زمان غیرفعال‌سازی',
    
    // Buttons & Links | دکمه‌ها و لینک‌ها
    'View Details' => 'View Details | مشاهده جزئیات',
    'Hide Details' => 'Hide Details | پنهان کردن جزئیات',
    'Copy' => 'Copy | کپی',
    'Copied!' => 'Copied! | کپی شد!',
    'Download' => 'Download | دانلود',
    'Support' => 'Support | پشتیبانی',
    'Documentation' => 'Documentation | مستندات',
    
    // Validation States | حالت‌های اعتبارسنجی
    'Checking...' => 'Checking... | در حال بررسی...',
    'Validating...' => 'Validating... | در حال اعتبارسنجی...',
    'Activating...' => 'Activating... | در حال فعال‌سازی...',
    'Deactivating...' => 'Deactivating... | در حال غیرفعال‌سازی...',
    
    // Permissions | مجوزها
    'Unlimited' => 'Unlimited | نامحدود',
    'Limited' => 'Limited | محدود',
    'Remaining' => 'Remaining | باقی‌مانده',
    
    // Help Text | متن راهنما
    'Need help?' => 'Need help? | نیاز به کمک دارید؟',
    'Contact Support' => 'Contact Support | تماس با پشتیبانی',
    'Visit our documentation for more information.' => 
        'Visit our documentation for more information. | برای اطلاعات بیشتر به مستندات ما مراجعه کنید.',
);
