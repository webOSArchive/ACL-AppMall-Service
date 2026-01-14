# SSL Certificate Renewal Guide

## Current Certificate Info

- **Domain:** api.openmobileappmall.com
- **Issuer:** ZeroSSL RSA Domain Secure Site CA
- **Type:** RSA 2048-bit (required for Android 2.0 compatibility)
- **Expires:** April 14, 2026
- **Certificate location:** `/etc/ssl/appmall/`

## Why ZeroSSL?

The AppMall app runs on HP TouchPad via Android Compatibility Layer (Android 2.0). Android 2.0 doesn't trust Let's Encrypt's ISRG Root X1 (added to trust stores in 2016). ZeroSSL uses Sectigo roots which have been trusted since ~2000.

## Renewal Process

### Step 1: Request renewal

```bash
~/.acme.sh/acme.sh --renew -d api.openmobileappmall.com --yes-I-know-dns-manual-mode-enough-go-ahead-please
```

### Step 2: Update DNS TXT record

The command will output something like:

```
Domain: '_acme-challenge.api.openmobileappmall.com'
TXT value: 'some-new-random-string-here'
```

Update the TXT record for `_acme-challenge.api.openmobileappmall.com` with the new value.

### Step 3: Verify DNS propagation

```bash
dig TXT _acme-challenge.api.openmobileappmall.com +short
```

Wait until it shows the new value.

### Step 4: Complete renewal

Run the same command again:

```bash
~/.acme.sh/acme.sh --renew -d api.openmobileappmall.com --yes-I-know-dns-manual-mode-enough-go-ahead-please
```

### Step 5: Install the new certificate

```bash
sudo cp ~/.acme.sh/api.openmobileappmall.com/fullchain.cer /etc/ssl/appmall/fullchain.pem
sudo cp ~/.acme.sh/api.openmobileappmall.com/api.openmobileappmall.com.key /etc/ssl/appmall/key.pem
sudo chmod 600 /etc/ssl/appmall/key.pem
sudo systemctl reload nginx
```

### Step 6: Verify

```bash
echo | openssl s_client -connect api.openmobileappmall.com:443 2>/dev/null | openssl x509 -noout -dates
```

## Important Notes

- **Renew before April 14, 2026** - set a calendar reminder for early April
- **Must use RSA key** - ECDSA certificates don't work with Android 2.0
- **Manual DNS mode** - requires updating TXT record each renewal
- Certificate files on server:
  - `/etc/ssl/appmall/fullchain.pem` - full certificate chain
  - `/etc/ssl/appmall/key.pem` - private key

## Automatic Renewal (Optional)

To avoid manual DNS updates, you can use a DNS provider with API support (Cloudflare, DigitalOcean, Route53, etc.) and configure acme.sh with API credentials. See: https://github.com/acmesh-official/acme.sh/wiki/dnsapi
