<?php
/**
 * AppMall Mock Server - API Endpoint
 *
 * Handles requests from the OpenMobile AppMall Android app.
 * Returns XML responses with curated app listings.
 */

// Enable error reporting for debugging (disable in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

header('Content-Type: application/xml; charset=utf-8');

// Load configuration
require_once __DIR__ . '/../config/apps.php';
require_once __DIR__ . '/../config/categories.php';

// Get request parameters
$module = $_POST['module'] ?? $_GET['module'] ?? '';
$page = intval($_POST['Page'] ?? $_GET['Page'] ?? 1);
$itemsPerPage = intval($_POST['items'] ?? $_GET['items'] ?? 15);
$productId = $_POST['Pid'] ?? $_GET['Pid'] ?? '';
$categoryId = $_POST['Catid'] ?? $_GET['Catid'] ?? '';
$subCategoryId = $_POST['Subcatid'] ?? $_GET['Subcatid'] ?? '';
$searchString = $_POST['sString'] ?? $_GET['sString'] ?? '';

// Log requests for debugging (optional)
$logFile = __DIR__ . '/../logs/requests.log';
$logEntry = date('Y-m-d H:i:s') . " | Module: $module | Page: $page | Pid: $productId | Search: $searchString | GET: " . json_encode($_GET) . " | POST: " . json_encode($_POST) . " | URI: " . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
@file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

// Route to appropriate handler
switch ($module) {
    // Product listings
    case 'allprods':
        echo getAllProducts($page, $itemsPerPage);
        break;

    case 'ns': // New software
        echo getProductsByTag('new', $page, $itemsPerPage);
        break;

    case 'bss': // Best sellers
        echo getProductsByTag('bestseller', $page, $itemsPerPage);
        break;

    case 'fs': // Free software
        echo getFreeProducts($page, $itemsPerPage);
        break;

    case 'fts': // Featured/Recommended
        echo getProductsByTag('featured', $page, $itemsPerPage);
        break;

    case 'dod': // Deal of the day
        echo getProductsByTag('featured', 1, 1);
        break;

    // Product details
    case 'pd':
        echo getProductDetails($productId);
        break;

    // Categories
    case 'browsecategories':
        echo getCategories();
        break;

    case 'browsesubcategories':
        echo getSubCategories($categoryId);
        break;

    case 'software_by_category':
        echo getProductsByCategory($categoryId, $page, $itemsPerPage);
        break;

    case 'software_by_sub_category':
        echo getProductsBySubCategory($categoryId, $subCategoryId, $page, $itemsPerPage);
        break;

    // Search
    case 'search':
        echo searchProducts($searchString, $categoryId, $subCategoryId, $page, $itemsPerPage);
        break;

    // Reviews (return empty for now)
    case 'listreviews':
    case 'appversereviews':
        echo emptyResults($module);
        break;

    // Localization
    case 'gettranslatedphrases':
        echo getLocalization();
        break;

    // Static content
    case 'availablecountries':
        echo getCountries();
        break;

    case 'availablelanguages':
        echo getLanguages();
        break;

    case 'availablecurrencies':
        echo getCurrencies();
        break;

    case 'privacypolicy':
        echo getPrivacyPolicy();
        break;

    case 'termsandconditions':
        echo getTermsAndConditions();
        break;

    case 'availableageranges':
        echo getAgeRanges();
        break;

    // User-related (return success/empty)
    case 'verify':
    case 'userdetails':
    case 'userstats':
    case 'orderhistory':
    case 'productupdates':
    case 'userproductsreviewed':
    case 'userproductsunreviewed':
    case 'userfollowers':
    case 'userfollowing':
    case 'recommendedusers':
    case 'searchuser':
    case 'wishlistretrieve':
        echo emptyResults($module);
        break;

    // Check version (always return no update needed)
    case 'checkversion':
        echo noUpdateNeeded();
        break;

    // Ads (return empty)
    case 'ads':
        echo emptyResults($module);
        break;

    default:
        echo emptyResults($module ?: 'unknown');
        break;
}

// ============================================================================
// Helper Functions
// ============================================================================

function xmlHeader() {
    return '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
}

function escapeXml($str) {
    return htmlspecialchars($str ?? '', ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function emptyResults($module) {
    return xmlHeader() . "<results><module>" . escapeXml($module) . "</module></results>";
}

// ============================================================================
// Product Functions
// ============================================================================

function getAllProducts($page, $itemsPerPage) {
    global $APP_CATALOG;
    return buildProductListXml('allprods', $APP_CATALOG, $page, $itemsPerPage);
}

function getFreeProducts($page, $itemsPerPage) {
    global $APP_CATALOG;
    $freeApps = array_filter($APP_CATALOG, function($app) {
        return floatval($app['price'] ?? 0) == 0;
    });
    return buildProductListXml('fs', array_values($freeApps), $page, $itemsPerPage);
}

function getProductsByTag($tag, $page, $itemsPerPage) {
    global $APP_CATALOG;
    $filtered = array_filter($APP_CATALOG, function($app) use ($tag) {
        $tags = $app['tags'] ?? [];
        return in_array($tag, $tags);
    });
    return buildProductListXml($tag, array_values($filtered), $page, $itemsPerPage);
}

function getProductsByCategory($categoryId, $page, $itemsPerPage) {
    global $APP_CATALOG;
    $filtered = array_filter($APP_CATALOG, function($app) use ($categoryId) {
        return ($app['category_id'] ?? '') == $categoryId;
    });
    return buildProductListXml('software_by_category', array_values($filtered), $page, $itemsPerPage);
}

function getProductsBySubCategory($categoryId, $subCategoryId, $page, $itemsPerPage) {
    global $APP_CATALOG;
    $filtered = array_filter($APP_CATALOG, function($app) use ($categoryId, $subCategoryId) {
        return ($app['category_id'] ?? '') == $categoryId &&
               ($app['subcategory_id'] ?? '') == $subCategoryId;
    });
    return buildProductListXml('software_by_sub_category', array_values($filtered), $page, $itemsPerPage);
}

function searchProducts($query, $categoryId, $subCategoryId, $page, $itemsPerPage) {
    global $APP_CATALOG;

    $query = strtolower(trim($query));
    if (empty($query)) {
        return emptyResults('search');
    }

    $filtered = array_filter($APP_CATALOG, function($app) use ($query, $categoryId, $subCategoryId) {
        // Search in name and description
        $searchable = strtolower(
            ($app['name'] ?? '') . ' ' .
            ($app['short_description'] ?? '') . ' ' .
            ($app['publisher'] ?? '') . ' ' .
            ($app['package_name'] ?? '')
        );

        if (strpos($searchable, $query) === false) {
            return false;
        }

        // Filter by category if specified
        if (!empty($categoryId) && ($app['category_id'] ?? '') != $categoryId) {
            return false;
        }

        if (!empty($subCategoryId) && ($app['subcategory_id'] ?? '') != $subCategoryId) {
            return false;
        }

        return true;
    });

    return buildProductListXml('search', array_values($filtered), $page, $itemsPerPage);
}

function buildProductListXml($module, $products, $page, $itemsPerPage) {
    $total = count($products);
    $offset = ($page - 1) * $itemsPerPage;
    $pageProducts = array_slice($products, $offset, $itemsPerPage);
    $moreAvailable = ($offset + count($pageProducts)) < $total ? 'Yes' : 'No';

    $xml = xmlHeader();
    $xml .= "<results>\n";
    $xml .= "  <module>" . escapeXml($module) . "</module>\n";
    $xml .= "  <page>$page</page>\n";
    $xml .= "  <moreAvailable>$moreAvailable</moreAvailable>\n";
    $xml .= "  <totalProducts>$total</totalProducts>\n";

    foreach ($pageProducts as $app) {
        $xml .= buildProductXml($app, false);
    }

    $xml .= "</results>";
    return $xml;
}

function getProductDetails($productId) {
    global $APP_CATALOG;

    $product = null;
    foreach ($APP_CATALOG as $app) {
        if (($app['id'] ?? '') == $productId || ($app['package_name'] ?? '') == $productId) {
            $product = $app;
            break;
        }
    }

    if (!$product) {
        return xmlHeader() . "<results><module>pd</module><error>Product not found</error></results>";
    }

    $xml = xmlHeader();
    $xml .= "<results>\n";
    $xml .= "  <module>pd</module>\n";
    $xml .= buildProductXml($product, true);
    $xml .= "</results>";
    return $xml;
}

function buildProductXml($app, $detailed = false) {
    $id = escapeXml($app['id'] ?? '');
    $xml = "  <product id=\"$id\">\n";
    $xml .= "    <productName>" . escapeXml($app['name'] ?? '') . "</productName>\n";
    $xml .= "    <shortDescription>" . escapeXml($app['short_description'] ?? '') . "</shortDescription>\n";
    $xml .= "    <price>" . escapeXml($app['price'] ?? '0.00') . "</price>\n";
    $xml .= "    <usersRating>" . intval($app['rating'] ?? 0) . "</usersRating>\n";
    $xml .= "    <thumbnail>" . escapeXml($app['icon_url'] ?? '') . "</thumbnail>\n";
    $xml .= "    <version>" . escapeXml($app['version'] ?? '1.0') . "</version>\n";
    $xml .= "    <PublisherName>" . escapeXml($app['publisher'] ?? '') . "</PublisherName>\n";
    $xml .= "    <downloadSize>" . escapeXml($app['size'] ?? '') . "</downloadSize>\n";
    $xml .= "    <InternalProductName>" . escapeXml($app['package_name'] ?? '') . "</InternalProductName>\n";

    if ($detailed) {
        $xml .= "    <longDescription>" . escapeXml($app['long_description'] ?? $app['short_description'] ?? '') . "</longDescription>\n";
        $xml .= "    <longFeatures>" . escapeXml($app['features'] ?? '') . "</longFeatures>\n";
        $xml .= "    <DownloadURL>" . escapeXml($app['download_url'] ?? '') . "</DownloadURL>\n";
        $xml .= "    <FreeTrialURL>" . escapeXml($app['trial_url'] ?? '') . "</FreeTrialURL>\n";
        $xml .= "    <YouTubeURL>" . escapeXml($app['youtube_url'] ?? '') . "</YouTubeURL>\n";

        // Screenshots
        $screenshots = $app['screenshots'] ?? [];
        $xml .= "    <totalScreenshots>" . count($screenshots) . "</totalScreenshots>\n";
        foreach ($screenshots as $i => $url) {
            $num = $i + 1;
            $xml .= "    <screenshot$num>" . escapeXml($url) . "</screenshot$num>\n";
        }

        // Coverflow image (use first screenshot or icon)
        $cfUrl = $screenshots[0] ?? $app['icon_url'] ?? '';
        $xml .= "    <CFScreenshotURL>" . escapeXml($cfUrl) . "</CFScreenshotURL>\n";
    }

    $xml .= "  </product>\n";
    return $xml;
}

// ============================================================================
// Category Functions
// ============================================================================

function getCategories() {
    global $CATEGORIES;

    $xml = xmlHeader();
    $xml .= "<results>\n";
    $xml .= "  <module>browsecategories</module>\n";

    foreach ($CATEGORIES as $cat) {
        $xml .= "  <category>\n";
        $xml .= "    <catLabel>" . escapeXml($cat['id']) . "</catLabel>\n";
        $xml .= "    <category>" . escapeXml($cat['name']) . "</category>\n";
        $xml .= "    <thumbnail>" . escapeXml($cat['icon'] ?? '') . "</thumbnail>\n";

        // Include subcategory IDs
        if (!empty($cat['subcategories'])) {
            $subIds = array_column($cat['subcategories'], 'id');
            $xml .= "    <subCategoryIds>" . escapeXml(implode(',', $subIds)) . "</subCategoryIds>\n";
        }

        $xml .= "  </category>\n";
    }

    $xml .= "</results>";
    return $xml;
}

function getSubCategories($categoryId) {
    global $CATEGORIES;

    $xml = xmlHeader();
    $xml .= "<results>\n";
    $xml .= "  <module>browsesubcategories</module>\n";

    foreach ($CATEGORIES as $cat) {
        if ($cat['id'] == $categoryId && !empty($cat['subcategories'])) {
            $xml .= "  <subcategories>\n";
            foreach ($cat['subcategories'] as $sub) {
                $xml .= "    <subCategory>\n";
                $xml .= "      <subCatLabel>" . escapeXml($sub['id']) . "</subCatLabel>\n";
                $xml .= "      <subCategory>" . escapeXml($sub['name']) . "</subCategory>\n";
                $xml .= "    </subCategory>\n";
            }
            $xml .= "  </subcategories>\n";
            break;
        }
    }

    $xml .= "</results>";
    return $xml;
}

// ============================================================================
// Static Content Functions
// ============================================================================

function getLocalization() {
    // Return empty localization - app will use built-in strings
    return xmlHeader() . "<results><module>gettranslatedphrases</module></results>";
}

function getCountries() {
    $xml = xmlHeader();
    $xml .= "<results>\n";
    $xml .= "  <module>availablecountries</module>\n";
    $xml .= "  <country><description>United States</description><country>US</country></country>\n";
    $xml .= "</results>";
    return $xml;
}

function getLanguages() {
    $xml = xmlHeader();
    $xml .= "<results>\n";
    $xml .= "  <module>availablelanguages</module>\n";
    $xml .= "  <language><languageid>en</languageid><languageName>English</languageName></language>\n";
    $xml .= "</results>";
    return $xml;
}

function getCurrencies() {
    $xml = xmlHeader();
    $xml .= "<results>\n";
    $xml .= "  <module>availablecurrencies</module>\n";
    $xml .= "  <currency><currencyCode>USD</currencyCode><description>US Dollar</description><symbol>$</symbol></currency>\n";
    $xml .= "</results>";
    return $xml;
}

function getAgeRanges() {
    $xml = xmlHeader();
    $xml .= "<results>\n";
    $xml .= "  <module>availableageranges</module>\n";
    $xml .= "  <agerange><description>Everyone</description><minage>0</minage><maxage>99</maxage></agerange>\n";
    $xml .= "</results>";
    return $xml;
}

function getPrivacyPolicy() {
    $xml = xmlHeader();
    $xml .= "<results>\n";
    $xml .= "  <module>privacypolicy</module>\n";
    $xml .= "  <privacypolicy><![CDATA[";
    $xml .= "<h2>Privacy Policy</h2>";
    $xml .= "<p>This is a community-maintained archive of Android apps compatible with ACL (Application Compatibility Layer) for HP TouchPad.</p>";
    $xml .= "<p>We do not collect any personal information.</p>";
    $xml .= "]]></privacypolicy>\n";
    $xml .= "</results>";
    return $xml;
}

function getTermsAndConditions() {
    $xml = xmlHeader();
    $xml .= "<results>\n";
    $xml .= "  <module>termsandconditions</module>\n";
    $xml .= "  <termsandconditions><![CDATA[";
    $xml .= "<h2>Terms of Service</h2>";
    $xml .= "<p>This service is provided as-is for archival and preservation purposes.</p>";
    $xml .= "<p>Apps are provided for personal use on ACL-enabled devices.</p>";
    $xml .= "]]></termsandconditions>\n";
    $xml .= "</results>";
    return $xml;
}

function noUpdateNeeded() {
    $xml = xmlHeader();
    $xml .= "<results>\n";
    $xml .= "  <module>checkversion</module>\n";
    $xml .= "  <status>OK</status>\n";
    $xml .= "  <messageForUser></messageForUser>\n";
    $xml .= "</results>";
    return $xml;
}
