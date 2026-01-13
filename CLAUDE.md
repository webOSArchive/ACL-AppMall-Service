# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

AppMall is a revival server for the OpenMobile AppMall app bundled with ACL (Android Compatibility Layer) on HP TouchPad. The original servers are dead; this project provides a mock PHP server that responds to the app's API requests.

## Architecture

The server is a simple PHP application:
- `appmall-server/api/appmallpipe.php` - Main API endpoint handling all module requests
- `appmall-server/config/apps.php` - App catalog (add apps here)
- `appmall-server/config/categories.php` - Category definitions

**Request flow**: The AppMall Android app POSTs to `/appmallpipe.php` with a `module` parameter. The server routes the request and returns XML responses.

**Key modules**: `allprods`, `browsecategories`, `pd` (product details), `search`, `fs` (free software), `bss` (best sellers)

## Testing the API

```bash
# Test locally (requires PHP server running)
curl -X POST http://localhost/appmallpipe.php -d "module=allprods&Page=1"
curl -X POST http://localhost/appmallpipe.php -d "module=browsecategories"
curl -X POST http://localhost/appmallpipe.php -d "module=search&sString=firefox"
curl -X POST http://localhost/appmallpipe.php -d "module=pd&Pid=opera-mini"
```

## Adding Apps

Edit `appmall-server/config/apps.php`. Required fields per app:
- `id`, `package_name`, `name`, `short_description`, `version`, `price`, `rating`, `publisher`, `size`
- `icon_url`, `download_url`, `category_id`
- `tags` array: `featured`, `new`, `bestseller` control which listings the app appears in

Upload APKs to `/apps/` and icons to `/images/`.

## Deployment

Requires Apache with mod_rewrite, PHP 7.0+, and SSL certificate. Point `api.openmobileappmall.com` DNS to your server. See `appmall-server/README.md` for full deployment instructions.
