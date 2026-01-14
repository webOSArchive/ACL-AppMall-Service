# AppMall Mock Server

A PHP-based mock server for the OpenMobile AppMall API, enabling the AppMall app on ACL (HP TouchPad) to function again.

## Overview

The original OpenMobile AppMall servers are defunct. This project provides:
- A mock PHP server that responds to the app's API requests
- A patched APK that works with HTTP (required for legacy Android TLS compatibility)

**Current deployment:** `http://aclappmall.webosarchive.org`

## Directory Structure

```
appmall-server/
├── api/
│   ├── appmallpipe.php      # Main API endpoint
│   └── appmallpipeorder.php # Order/download endpoint
├── apps/                     # APK files for download
├── config/
│   ├── apps.php             # App catalog configuration
│   └── categories.php       # Category configuration
├── images/                   # App icons and screenshots
├── logs/                     # Request logs (optional)
├── .htaccess                # Apache URL rewriting
├── index.html               # Landing page
└── README.md
```

## Requirements

- PHP 7.0+ (works with PHP 5.6)
- Apache with mod_rewrite OR nginx
- HTTP access (HTTPS has TLS compatibility issues with Android 2.3)

## Why HTTP Instead of HTTPS?

The original ACL (Android Compatibility Layer) runs Android 2.3.6, which only supports TLS 1.0 with older cipher suites. Modern servers have deprecated these ciphers for security reasons. Rather than maintaining a complex TLS termination setup (stunnel, etc.), the patched APK uses plain HTTP.

## Deployment

### 1. Domain Setup

Point your DNS to your server:
- `aclappmall.webosarchive.org` → your server IP

Or use any domain and update the patched APK accordingly.

### 2. Web Server Configuration

#### Apache

Example configuration for `/etc/apache2/sites-available/appmall.conf`:

```apache
<VirtualHost *:80>
    ServerName aclappmall.webosarchive.org

    DocumentRoot /var/www/appmall-server

    <Directory /var/www/appmall-server>
        AllowOverride All
        Require all granted
    </Directory>

    # MIME type for APK files (required for app installation)
    AddType application/vnd.android.package-archive .apk

    ErrorLog ${APACHE_LOG_DIR}/appmall_error.log
    CustomLog ${APACHE_LOG_DIR}/appmall_access.log combined
</VirtualHost>
```

Enable the site:

```bash
sudo a2ensite appmall
sudo a2enmod rewrite headers
sudo systemctl reload apache2
```

#### nginx

Example configuration:

```nginx
server {
    listen 80;
    server_name aclappmall.webosarchive.org;

    root /var/www/appmall-server;
    index index.html index.php;

    # MIME type for APK files (required for app installation)
    types {
        application/vnd.android.package-archive apk;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 3. APK MIME Type (Important!)

The server MUST return the correct MIME type for APK files, otherwise Android will fail to install downloaded apps with "Error parsing package".

**Apache** - Add to `.htaccess` or virtual host config:
```apache
AddType application/vnd.android.package-archive .apk
```

**nginx** - Add to server block:
```nginx
types {
    application/vnd.android.package-archive apk;
}
```

Verify it's working:
```bash
curl -I http://aclappmall.webosarchive.org/apps/yourapp.apk | grep -i content-type
# Should show: Content-Type: application/vnd.android.package-archive
```

### 4. File Permissions

```bash
sudo chown -R www-data:www-data /var/www/appmall-server
sudo chmod -R 755 /var/www/appmall-server
sudo chmod -R 777 /var/www/appmall-server/logs  # For request logging
```

### 5. Upload Files

Copy this directory to your server:

```bash
rsync -avz appmall-server/ user@yourserver:/var/www/appmall-server/
```

## Adding Apps

Edit `config/apps.php` to add apps to the catalog. Each app entry should include:

```php
[
    'id' => 'unique-id',
    'package_name' => 'com.example.app',
    'name' => 'App Name',
    'short_description' => 'Brief description',
    'long_description' => '<p>Full HTML description</p>',
    'features' => '<ul><li>Feature 1</li></ul>',
    'version' => '1.0.0',
    'price' => '0.00',
    'rating' => 4,
    'publisher' => 'Developer Name',
    'size' => '5.2 MB',
    'icon_url' => 'http://aclappmall.webosarchive.org/images/app-icon.png',
    'download_url' => 'http://aclappmall.webosarchive.org/apps/app.apk',
    'screenshots' => ['http://aclappmall.webosarchive.org/images/screenshot1.png'],
    'category_id' => 'tools',
    'subcategory_id' => 'utilities',
    'tags' => ['featured', 'new', 'bestseller'],
],
```

Then upload the APK to `/apps/` and icon to `/images/`.

## Testing

Test the API:

```bash
# Test product listing
curl -X POST http://aclappmall.webosarchive.org/appmallpipe.php -d "module=allprods&Page=1"

# Test categories
curl -X POST http://aclappmall.webosarchive.org/appmallpipe.php -d "module=browsecategories"

# Test search
curl -X POST http://aclappmall.webosarchive.org/appmallpipe.php -d "module=search&sString=vlc"

# Test product details
curl -X POST http://aclappmall.webosarchive.org/appmallpipe.php -d "module=pd&Pid=vlc"

# Test APK MIME type
curl -I http://aclappmall.webosarchive.org/apps/yourapp.apk | grep -i content-type
```

## API Modules

The server implements these API modules:

| Module | Description |
|--------|-------------|
| `allprods` | All products |
| `ns` | New software |
| `bss` | Best sellers |
| `fs` | Free software |
| `fts` | Featured |
| `dod` | Deal of the day |
| `pd` | Product details |
| `browsecategories` | List categories |
| `browsesubcategories` | List subcategories |
| `software_by_category` | Products in category |
| `search` | Search products |
| `userdetails` | User login (accepts any credentials) |
| `verify` | Email verification (always succeeds) |
| `gettranslatedphrases` | Localization |

## Patched APK Notes

The patched APK (`AppMall_webosarchive_*.apk`) has these modifications:

1. **HTTP instead of HTTPS** - URLs changed from `https://api.openmobileappmall.com` to `http://aclappmall.webosarchive.org`

2. **HTTP connection handling** - Fixed `HttpsURLConnection` cast that broke HTTP connections

3. **No welcome popup** - The initial login/signup popup is disabled

4. **No login required for downloads** - Downloads start immediately without requiring account creation

5. **Cleartext traffic enabled** - AndroidManifest.xml updated for Android 9+ compatibility

## Troubleshooting

### App shows "Connection Error"

1. Verify DNS is pointing to your server
2. Ensure web server is running and accessible via HTTP
3. Check server error logs

### App shows empty product list

1. Check `config/apps.php` has valid entries
2. Verify PHP is working: `php -v`
3. Test API directly with curl

### Downloads fail or show "Error parsing package"

1. Verify APK files exist in `/apps/` directory
2. Check file permissions
3. **Ensure correct MIME type** - Server must return `application/vnd.android.package-archive` for `.apk` files
4. Test with: `curl -I http://yourserver/apps/app.apk | grep -i content-type`

### App crashes when starting download

1. Check server logs for 404 errors
2. Verify `download_url` in apps.php points to existing file
3. Check logcat for detailed error: `adb logcat | grep -i exception`

## Logging

Request logging is enabled by default. Logs are written to:
- `logs/requests.log` - API requests
- `logs/orders.log` - Download/order requests

To disable, comment out the logging sections in the PHP files.
