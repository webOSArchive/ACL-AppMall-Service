# AppMall Server Setup Guide - Ubuntu 20.04

This guide sets up the AppMall API server on Ubuntu 20.04 with legacy TLS support for Android 2.3.6 (HP TouchPad ACL).

## Why Ubuntu 20.04?

Ubuntu 20.04 ships with OpenSSL 1.1.1 which supports TLS 1.0 and legacy cipher suites. Modern Ubuntu (22.04+) uses OpenSSL 3.x which has TLS 1.0 disabled at compile time.

## Prerequisites

- Fresh Ubuntu 20.04 LTS server
- Domain `api.openmobileappmall.com` (or your domain)
- Access to DNS settings for the domain

---

## Step 1: Initial Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y nginx php-fpm php-xml curl git

# Verify OpenSSL version (should be 1.1.1)
openssl version
```

---

## Step 2: Create Directory Structure

```bash
# Create web root
sudo mkdir -p /var/www/appmall/api
sudo mkdir -p /var/www/appmall/config
sudo mkdir -p /var/www/appmall/apps
sudo mkdir -p /var/www/appmall/images
sudo mkdir -p /var/www/appmall/logs

# Set permissions
sudo chown -R www-data:www-data /var/www/appmall
sudo chmod -R 755 /var/www/appmall
sudo chmod 777 /var/www/appmall/logs
```

---

## Step 3: Upload Site Files

Copy these files from your local machine to the server:

```bash
# From your local machine:
scp appmall-server/api/appmallpipe.php user@server:/var/www/appmall/api/
scp appmall-server/config/apps.php user@server:/var/www/appmall/config/
scp appmall-server/config/categories.php user@server:/var/www/appmall/config/

# Also upload any APK files and images:
scp appmall-server/apps/*.apk user@server:/var/www/appmall/apps/
scp appmall-server/images/*.png user@server:/var/www/appmall/images/
```

---

## Step 4: Install acme.sh for SSL Certificates

```bash
# Install acme.sh
curl https://get.acme.sh | sh -s email=your-email@example.com

# Source the profile to get acme.sh in path
source ~/.bashrc

# Set ZeroSSL as default CA (uses Sectigo roots trusted by Android 2.3)
~/.acme.sh/acme.sh --set-default-ca --server zerossl
```

---

## Step 5: Request SSL Certificate (DNS Validation)

ZeroSSL with RSA key is required for Android 2.3.6 compatibility.

### Step 5a: Start the certificate request

```bash
~/.acme.sh/acme.sh --issue -d api.openmobileappmall.com --dns --key-type rsa --yes-I-know-dns-manual-mode-enough-go-ahead-please
```

This will output something like:

```
Domain: '_acme-challenge.api.openmobileappmall.com'
TXT value: 'some-random-string-here'
```

### Step 5b: Add DNS TXT Record

Go to your DNS provider and add a TXT record:
- **Name:** `_acme-challenge.api` (or `_acme-challenge.api.openmobileappmall.com` depending on provider)
- **Type:** TXT
- **Value:** The string from step 5a

### Step 5c: Verify DNS propagation

```bash
dig TXT _acme-challenge.api.openmobileappmall.com +short
```

Wait until it shows your TXT value.

### Step 5d: Complete certificate issuance

```bash
~/.acme.sh/acme.sh --renew -d api.openmobileappmall.com --yes-I-know-dns-manual-mode-enough-go-ahead-please
```

---

## Step 6: Install the Certificate

```bash
# Create SSL directory
sudo mkdir -p /etc/ssl/appmall

# Copy certificate files
sudo cp ~/.acme.sh/api.openmobileappmall.com/fullchain.cer /etc/ssl/appmall/fullchain.pem
sudo cp ~/.acme.sh/api.openmobileappmall.com/api.openmobileappmall.com.key /etc/ssl/appmall/key.pem

# Secure the private key
sudo chmod 600 /etc/ssl/appmall/key.pem
```

---

## Step 7: Configure Nginx

Create the nginx configuration:

```bash
sudo nano /etc/nginx/sites-available/appmall
```

Paste this configuration:

```nginx
server {
    listen 80;
    server_name api.openmobileappmall.com;

    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl;
    server_name api.openmobileappmall.com;

    # SSL Certificate
    ssl_certificate /etc/ssl/appmall/fullchain.pem;
    ssl_certificate_key /etc/ssl/appmall/key.pem;

    # Legacy TLS Support for Android 2.3.6
    ssl_protocols TLSv1 TLSv1.1 TLSv1.2 TLSv1.3;

    # Cipher suites - includes legacy RSA ciphers for Android 2.3.6
    # Order matters: prefer modern ciphers, fall back to legacy
    ssl_ciphers 'ECDHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-CHACHA20-POLY1305:ECDHE-RSA-AES128-SHA256:ECDHE-RSA-AES256-SHA384:ECDHE-RSA-AES128-SHA:ECDHE-RSA-AES256-SHA:AES128-GCM-SHA256:AES256-GCM-SHA384:AES128-SHA256:AES256-SHA256:AES128-SHA:AES256-SHA:DES-CBC3-SHA';
    ssl_prefer_server_ciphers on;

    # SSL session settings
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Document root
    root /var/www/appmall;
    index index.php;

    # Main location
    location / {
        try_files $uri $uri/ =404;
    }

    # PHP processing
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # API endpoint - handle /appmallpipe.php
    location = /appmallpipe.php {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME /var/www/appmall/api/appmallpipe.php;
        include fastcgi_params;
    }

    # Serve static files
    location /apps/ {
        alias /var/www/appmall/apps/;
    }

    location /images/ {
        alias /var/www/appmall/images/;
    }

    # Logging
    access_log /var/log/nginx/appmall_access.log;
    error_log /var/log/nginx/appmall_error.log;
}
```

Enable the site:

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/appmall /etc/nginx/sites-enabled/

# Remove default site (optional)
sudo rm /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t

# Reload nginx
sudo systemctl reload nginx
```

---

## Step 8: Update DNS

Point your domain directly to this server (bypass Cloudflare):

1. Go to your DNS provider (or Cloudflare)
2. Set `api.openmobileappmall.com` as an **A record** pointing to your new server's IP
3. If using Cloudflare, make sure the proxy is **disabled** (grey cloud, DNS only)

---

## Step 9: Test the Setup

### Test from the server:

```bash
# Test PHP
curl -X POST http://localhost/appmallpipe.php -d "module=browsecategories"

# Test HTTPS locally
curl -X POST https://api.openmobileappmall.com/appmallpipe.php -d "module=allprods"
```

### Test TLS 1.0 support:

```bash
openssl s_client -connect api.openmobileappmall.com:443 -tls1 -cipher 'AES128-SHA' </dev/null 2>&1 | grep -E "(Protocol|Cipher)"
```

Expected output:
```
Protocol  : TLSv1
Cipher    : AES128-SHA
```

### Test from external (SSL Labs):

Visit: `https://www.ssllabs.com/ssltest/analyze.html?d=api.openmobileappmall.com`

Should show TLS 1.0 supported with RSA cipher suites.

---

## Step 10: Test on HP TouchPad

Open the AppMall app on your TouchPad. It should now connect and display apps.

---

## Troubleshooting

### Check nginx error log:
```bash
sudo tail -f /var/log/nginx/appmall_error.log
```

### Check API request log:
```bash
tail -f /var/www/appmall/logs/requests.log
```

### Check PHP-FPM status:
```bash
sudo systemctl status php7.4-fpm
```

### Verify certificate chain:
```bash
openssl s_client -connect api.openmobileappmall.com:443 -servername api.openmobileappmall.com </dev/null 2>&1 | openssl x509 -noout -issuer -subject -dates
```

### Test specific cipher suites:
```bash
# Test AES128-SHA (needed for Android 2.3)
openssl s_client -connect api.openmobileappmall.com:443 -cipher 'AES128-SHA' </dev/null

# Test AES256-SHA
openssl s_client -connect api.openmobileappmall.com:443 -cipher 'AES256-SHA' </dev/null
```

---

## Certificate Renewal

The ZeroSSL certificate expires every 90 days. See `SSL-RENEWAL.md` for renewal instructions.

Quick renewal steps:
```bash
~/.acme.sh/acme.sh --renew -d api.openmobileappmall.com --yes-I-know-dns-manual-mode-enough-go-ahead-please
# Update DNS TXT record with new value
# Run the command again
sudo cp ~/.acme.sh/api.openmobileappmall.com/fullchain.cer /etc/ssl/appmall/fullchain.pem
sudo cp ~/.acme.sh/api.openmobileappmall.com/api.openmobileappmall.com.key /etc/ssl/appmall/key.pem
sudo systemctl reload nginx
```

---

## Firewall (Optional)

If using UFW:
```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

---

## Notes

- **Why ZeroSSL?** Uses Sectigo root certificates trusted since 2000. Let's Encrypt ISRG Root X1 was added in 2016, not trusted by Android 2.3.
- **Why RSA?** Android 2.3 doesn't support ECDSA certificates.
- **Why TLS 1.0?** Android 2.3.6 supports TLS 1.0-1.2 but needs legacy cipher suites only available with TLS 1.0/1.1 on some configurations.
