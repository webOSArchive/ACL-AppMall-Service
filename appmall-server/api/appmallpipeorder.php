<?php
/**
 * AppMall Mock Server - Order/Purchase Endpoint
 *
 * Handles order/purchase requests from the OpenMobile AppMall Android app.
 * Since this is a free archive, all purchases return success immediately.
 */

header('Content-Type: application/xml; charset=utf-8');

// Load app catalog to find download URL
require_once __DIR__ . '/../config/apps.php';

// Get product ID - try multiple parameter names (app sends lowercase)
$productId = $_POST['pid'] ?? $_GET['pid'] ?? $_POST['Pid'] ?? $_GET['Pid'] ?? '';
$email = $_POST['email'] ?? $_GET['email'] ?? '';

// Log requests for debugging
$logFile = __DIR__ . '/../logs/orders.log';
$logEntry = date('Y-m-d H:i:s') . " | PID: $productId | Email: $email | GET: " . json_encode($_GET) . " | POST: " . json_encode($_POST) . "\n";
@file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

// Find the product
$product = null;
foreach ($APP_CATALOG as $app) {
    if (($app['id'] ?? '') == $productId || ($app['package_name'] ?? '') == $productId) {
        $product = $app;
        break;
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo "<response>\n";

if ($product && !empty($product['download_url'])) {
    echo "  <status>OK</status>\n";
    echo "  <statusDescription>Purchase successful</statusDescription>\n";
    echo "  <downloadURL>" . htmlspecialchars($product['download_url'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</downloadURL>\n";
    echo "  <external>0</external>\n";
    echo "  <productId>" . htmlspecialchars($product['id'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</productId>\n";
    echo "  <productName>" . htmlspecialchars($product['name'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</productName>\n";
    echo "  <customerMessage>Thank you for your download!</customerMessage>\n";
} else {
    echo "  <status>Error</status>\n";
    echo "  <statusDescription>Product not found: " . htmlspecialchars($productId, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</statusDescription>\n";
}

echo "</response>";
