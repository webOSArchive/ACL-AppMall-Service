# AppMall API Documentation

Reverse-engineered from `AppMall_OpenMobile_2.0.10.777.apk` (Android app for ACL)

## Server Endpoints

| Endpoint | URL |
|----------|-----|
| Main API | `https://api.openmobileappmall.com/appmallpipe.php` |
| Order API | `https://api.openmobileappmall.com/appmallpipeorder.php` |
| Base URL | `https://api.openmobileappmall.com` |
| Support | `http://www.openmobileappmall.com/support.php` |

## Authentication

Every request includes these base parameters:
- `Posid` - Point of Sale ID (store identifier, loaded from `assets/branding/posid2.txt`)
- `password` - Store password
- `platform` - Always "1" (Android)
- `Osid` - OS ID, always "222"

## Request Format

HTTP POST to `appmallpipe.php` with URL-encoded form parameters.

### Common Parameters

| Parameter | Description |
|-----------|-------------|
| `module` | API operation to perform |
| `Posid` | Store identifier |
| `password` | Store password |
| `platform` | Platform ID (1 = Android) |
| `Osid` | OS ID (222) |
| `Page` | Page number for pagination |
| `items` | Items per page (default 15) |
| `Lang` | Language code |
| `currency` | Currency code (default "USD") |
| `EncodeURLs` | Set to "1" to encode URLs |

### Device Parameters (sent with requests)

| Parameter | Description |
|-----------|-------------|
| `Device` | Device name |
| `deviceId` | Unique device ID |
| `dAndroidID` | Android ID |
| `dBrand` | Device brand |
| `dModel` | Device model |
| `dName` | Device name |
| `dOS` | OS version |
| `dOSLanguage` | OS language |
| `macID` | MAC address |
| `serialid` | Serial ID |
| `appstoreversion` | AppMall version |

## API Modules (Operations)

### Product Listing

| Module | Description | Extra Parameters |
|--------|-------------|------------------|
| `allprods` | All products | - |
| `ns` | New software | - |
| `bss` | Best sellers | - |
| `fs` | Free software | - |
| `fts` | Featured/Recommended software | - |
| `dod` | Deal of the day | - |

### Product Details

| Module | Description | Extra Parameters |
|--------|-------------|------------------|
| `pd` | Product details | `Pid` (product ID) |
| `listreviews` | Product reviews | `Pid` |
| `othersalsobought` | Related products | `Pid` |
| `checkversion` | Check for updates | `Pid` |

### Categories

| Module | Description | Extra Parameters |
|--------|-------------|------------------|
| `browsecategories` | List all categories | - |
| `browsesubcategories` | List subcategories | `Catid` |
| `software_by_category` | Products in category | `Catid` |
| `software_by_sub_category` | Products in subcategory | `Catid`, `Subcatid` |

### Search

| Module | Description | Extra Parameters |
|--------|-------------|------------------|
| `search` | Search products | `sString` |
| `search_by_category` | Search in category | `sString`, `Catid` |
| `search_by_sub_category` | Search in subcategory | `sString`, `Catid`, `Subcatid` |
| `searchuser` | Search users | `sString` |
| `vendorproducts` | Products by vendor | `vendorid` |

### User Account

| Module | Description | Extra Parameters |
|--------|-------------|------------------|
| `verify` | Verify field (email/screenName) | `field`, `value` |
| `newuser` | Create account | (many user fields) |
| `updateuser` | Update account | (various user fields) |
| `userdetails` | Get user details | `Email`, `customerPassword` |
| `userstats` | User statistics | `customerid` |
| `fp` | Forgot password | `Email` |

### Social Features

| Module | Description | Extra Parameters |
|--------|-------------|------------------|
| `userfollowunfollow` | Follow/unfollow user | `Email`, `customerPassword`, `followeecustomerid`, `Mode` |
| `userfollowers` | Get user's followers | `Email` |
| `userfollowing` | Get following list | `Email` |
| `recommendedusers` | Suggested users | - |
| `appversereviews` | Recent reviews | - |

### User Products

| Module | Description | Extra Parameters |
|--------|-------------|------------------|
| `userproductsreviewed` | Products user reviewed | `customerid` |
| `userproductsunreviewed` | Products user hasn't reviewed | `customerid` |
| `reviews` | Add review | `Email`, `Pid`, `review`, `rating` |
| `addreviewcomment` | Comment on review | `Email`, `customerPassword`, `reviewid`, `commentText` |
| `orderhistory` | Purchase history | `Email`, `customerPassword` |
| `productupdates` | Available updates | `Email`, `customerPassword` |

### Wishlist

| Module | Description | Extra Parameters |
|--------|-------------|------------------|
| `wishlistretrieve` | Get wishlist | `Email`, `wishlisttype` |
| `wishlistitemadd` | Add to wishlist | `Email`, `Pid`, `wishlisttype` |
| `wishlistitemremove` | Remove from wishlist | `Email`, `Pid` |

### Other

| Module | Description | Extra Parameters |
|--------|-------------|------------------|
| `gettranslatedphrases` | Localization strings | - |
| `availablecountries` | List countries | - |
| `availablelanguages` | List languages | - |
| `availablecurrencies` | List currencies | - |
| `availableageranges` | Age ranges (parental) | - |
| `privacypolicy` | Privacy policy text | - |
| `termsandconditions` | Terms of service | - |
| `giftcertificatedetails` | Gift card info | `Email`, `giftcertficatecode` |
| `ads` | Advertisements | `Pid` |
| `productsupport` | Report problem | `Email`, `Pid`, `message` |
| `appaudit` | App analytics | - |

## Response Format (XML)

The API returns XML responses. Example structure for product list:

```xml
<results>
    <module>allprods</module>
    <page>1</page>
    <moreAvailable>Yes</moreAvailable>
    <totalProducts>150</totalProducts>
    <product>
        <productName>App Name</productName>
        <shortDescription>Brief description</shortDescription>
        <longDescription>Full description with HTML</longDescription>
        <longFeatures>Feature list with HTML</longFeatures>
        <price>0.00</price>
        <rating>4</rating>
        <thumbnail>https://url/to/icon.png</thumbnail>
        <version>1.0.0</version>
        <PublisherName>Developer Name</PublisherName>
        <downloadSize>5.2 MB</downloadSize>
        <DownloadURL>https://url/to/app.apk</DownloadURL>
        <FreeTrialURL></FreeTrialURL>
        <totalScreenshots>3</totalScreenshots>
        <screenshot1>https://url/to/screenshot1.png</screenshot1>
        <screenshot2>https://url/to/screenshot2.png</screenshot2>
        <screenshot3>https://url/to/screenshot3.png</screenshot3>
        <CFScreenshotURL>https://url/to/coverflow.png</CFScreenshotURL>
        <YouTubeURL></YouTubeURL>
        <InternalProductName>com.example.app</InternalProductName>
    </product>
    <!-- more products... -->
</results>
```

### Key XML Tags

| Tag | Description |
|-----|-------------|
| `results` | Root element |
| `module` | Which module was called |
| `page` | Current page number |
| `moreAvailable` | "Yes" or "No" |
| `totalProducts` | Total count |
| `product` | Product container |
| `category` | Category container |
| `subCategory` | Subcategory container |
| `review` | Review container |
| `user` | User container |
| `error` | Error message |
| `status` | Status code |
| `statusDescription` | Status message |

### Error Response

```xml
<results>
    <error>Error message here</error>
    <status>ERROR_CODE</status>
    <statusDescription>Human readable description</statusDescription>
</results>
```

## Strategies for Revival

### Option 1: Mock Server

Create a simple HTTP server that:
1. Accepts POST requests to `/appmallpipe.php`
2. Parses the `module` parameter
3. Returns appropriate XML responses with curated app listings
4. Hosts APK files for download

Requirements:
- Web server (nginx/Apache) with PHP or any backend
- SSL certificate (app uses HTTPS)
- DNS or hosts file modification on device

### Option 2: Patch the APK

Modify the app to:
1. Change `MOBI_PIPE_BASE_URL` to point to new server
2. Optionally disable SSL certificate verification (already has `DO_NOT_VERIFY`)
3. Re-sign and distribute the patched APK

Key files to modify:
- `com/openmobile/store/common/mobipipe/MobiPipeRequestConstants.java`
  - `MOBI_PIPE_BASE_URL`
  - `MOBI_PIPE_URL`
  - `MOBI_PIPE_ORDER_URL`

### Option 3: DNS Spoofing

Point `api.openmobileappmall.com` to a local server:
1. Set up local DNS or modify `/etc/hosts` on device
2. Run mock server on that IP
3. Requires SSL certificate for the domain (or patching app to accept self-signed)

## Minimal Mock Server Endpoints

For basic functionality, implement these modules:

1. **`allprods`** - Main product listing
2. **`browsecategories`** - Category list
3. **`pd`** - Product details
4. **`search`** - Search functionality
5. **`gettranslatedphrases`** - Localization (can return empty)

All other modules can return empty `<results></results>` or static content.

## Files in Decompiled APK

- `AppMall-decompiled/sources/com/openmobile/store/common/mobipipe/MobiPipeManager.java` - Main API client
- `AppMall-decompiled/sources/com/openmobile/store/common/mobipipe/MobiPipeRequestConstants.java` - URL and parameter constants
- `AppMall-decompiled/sources/com/openmobile/store/common/mobipipe/TagNames.java` - XML tag names
- `AppMall-decompiled/sources/com/openmobile/store/common/mobipipe/Product.java` - Product model
- `AppMall-decompiled/sources/com/openmobile/store/common/mobipipe/Category.java` - Category model
