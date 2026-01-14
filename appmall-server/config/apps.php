<?php
/**
 * AppMall App Catalog
 *
 * Add apps to this array. Each app needs:
 * - id: Unique identifier
 * - package_name: Android package name (com.example.app)
 * - name: Display name
 * - short_description: Brief description
 * - long_description: Full description (HTML allowed)
 * - features: Feature list (HTML allowed)
 * - version: Version string
 * - price: Price (use "0.00" for free)
 * - rating: 1-5 star rating
 * - publisher: Developer/publisher name
 * - size: Download size string
 * - icon_url: URL to app icon
 * - download_url: Direct URL to APK file
 * - screenshots: Array of screenshot URLs
 * - category_id: Category ID (see categories.php)
 * - subcategory_id: Subcategory ID (optional)
 * - tags: Array of tags (featured, new, bestseller)
 * - youtube_url: Optional YouTube video URL
 * - trial_url: Optional trial/lite version URL
 */

// Base URL for locally hosted files (change to your domain)
$BASE_URL = 'http://aclappmall.webosarchive.org';

$APP_CATALOG = [

    // =========================================================================
    // ESSENTIAL APPS - Apps that help users set up their system
    // =========================================================================

    [
        'id' => 'es-file-explorer',
        'package_name' => 'com.estrongs.android.pop',
        'name' => 'ES File Explorer',
        'short_description' => 'Powerful file manager with cloud support and root access',
        'long_description' => '<p>ES File Explorer is a full-featured file manager for Android. It includes:</p><ul><li>File management (copy, move, rename, delete)</li><li>Root explorer for system files</li><li>Cloud storage support</li><li>App manager</li><li>FTP and network shares</li></ul>',
        'features' => '<ul><li>Browse and manage files</li><li>Access root filesystem</li><li>Connect to cloud storage</li><li>Built-in viewers for images, video, music</li><li>App backup and restore</li></ul>',
        'version' => '3.2.5.5',
        'price' => '0.00',
        'rating' => 5,
        'publisher' => 'ES Global',
        'size' => '4.2 MB',
        'icon_url' => $BASE_URL . '/images/es-file-explorer-icon.png',
        'download_url' => $BASE_URL . '/apps/es-file-explorer-3.2.5.5.apk',
        'screenshots' => [
            $BASE_URL . '/images/es-file-explorer-1.png',
            $BASE_URL . '/images/es-file-explorer-2.png',
        ],
        'category_id' => 'tools',
        'subcategory_id' => 'file-managers',
        'tags' => ['featured', 'bestseller'],
    ],

    [
        'id' => 'apk-installer',
        'package_name' => 'com.rhythm.apkinstaller',
        'name' => 'APK Installer',
        'short_description' => 'Easily install APK files from your device storage',
        'long_description' => '<p>Simple tool to find and install APK files on your device. Scans your storage for APK files and lets you install them with one tap.</p>',
        'features' => '<ul><li>Scan for APK files</li><li>One-tap install</li><li>Batch install multiple APKs</li></ul>',
        'version' => '4.1.1',
        'price' => '0.00',
        'rating' => 4,
        'publisher' => 'Rhythm Software',
        'size' => '1.1 MB',
        'icon_url' => $BASE_URL . '/images/apk-installer-icon.png',
        'download_url' => $BASE_URL . '/apps/apk-installer-4.1.1.apk',
        'screenshots' => [],
        'category_id' => 'tools',
        'subcategory_id' => 'utilities',
        'tags' => ['featured', 'new'],
    ],

    [
        'id' => 'settings-app',
        'package_name' => 'com.android.settings',
        'name' => 'Android Settings',
        'short_description' => 'Access Android system settings within ACL',
        'long_description' => '<p>Access the Android settings menu to configure your ACL environment. Manage apps, storage, accounts, and more.</p>',
        'features' => '<ul><li>Manage installed apps</li><li>Configure accounts</li><li>Storage management</li><li>Developer options</li></ul>',
        'version' => '2.3.7',
        'price' => '0.00',
        'rating' => 4,
        'publisher' => 'Android',
        'size' => '0.5 MB',
        'icon_url' => $BASE_URL . '/images/settings-icon.png',
        'download_url' => $BASE_URL . '/apps/settings.apk',
        'screenshots' => [],
        'category_id' => 'tools',
        'subcategory_id' => 'system',
        'tags' => ['featured'],
    ],

    // =========================================================================
    // BROWSERS
    // =========================================================================

    [
        'id' => 'opera-mini',
        'package_name' => 'com.opera.mini.android',
        'name' => 'Opera Mini',
        'short_description' => 'Fast, lightweight browser with data compression',
        'long_description' => '<p>Opera Mini is a lightweight mobile browser that uses Opera servers to compress pages before sending them to your device. This makes browsing faster and uses less data.</p>',
        'features' => '<ul><li>Data compression saves bandwidth</li><li>Fast page loading</li><li>Tabbed browsing</li><li>Bookmarks sync</li></ul>',
        'version' => '7.5.4',
        'price' => '0.00',
        'rating' => 4,
        'publisher' => 'Opera Software',
        'size' => '1.8 MB',
        'icon_url' => $BASE_URL . '/images/opera-mini-icon.png',
        'download_url' => $BASE_URL . '/apps/opera-mini-7.5.4.apk',
        'screenshots' => [],
        'category_id' => 'communication',
        'subcategory_id' => 'browsers',
        'tags' => ['featured', 'bestseller'],
    ],

    [
        'id' => 'firefox',
        'package_name' => 'org.mozilla.firefox',
        'name' => 'Firefox',
        'short_description' => 'Mozilla Firefox web browser for Android',
        'long_description' => '<p>Firefox for Android brings the Firefox experience to your mobile device. Features include tabbed browsing, sync with desktop Firefox, and add-on support.</p>',
        'features' => '<ul><li>Sync with Firefox desktop</li><li>Add-on support</li><li>Private browsing</li><li>Customizable</li></ul>',
        'version' => '23.0',
        'price' => '0.00',
        'rating' => 4,
        'publisher' => 'Mozilla',
        'size' => '18.5 MB',
        'icon_url' => $BASE_URL . '/images/firefox-icon.png',
        'download_url' => $BASE_URL . '/apps/firefox-23.0.apk',
        'screenshots' => [],
        'category_id' => 'communication',
        'subcategory_id' => 'browsers',
        'tags' => ['bestseller'],
    ],

    // =========================================================================
    // MEDIA & ENTERTAINMENT
    // =========================================================================

    [
        'id' => 'vlc',
        'package_name' => 'org.videolan.vlc',
        'name' => 'VLC for Android',
        'short_description' => 'Play any video or audio file',
        'long_description' => '<p>VLC for Android is a full port of VLC media player. It can play most multimedia files, network streams, and DVD ISOs.</p>',
        'features' => '<ul><li>Plays all formats</li><li>No codec packs needed</li><li>Subtitle support</li><li>Audio and video playlists</li><li>Network stream support</li></ul>',
        'version' => '0.9.10',
        'price' => '0.00',
        'rating' => 5,
        'publisher' => 'VideoLAN',
        'size' => '12.4 MB',
        'icon_url' => $BASE_URL . '../images/vlc-icon.png',
        'download_url' => $BASE_URL . '../apps/vlc-0.9.10.apk',
        'screenshots' => [],
        'category_id' => 'media',
        'subcategory_id' => 'video-players',
        'tags' => ['featured', 'bestseller'],
    ],

    [
        'id' => 'mx-player',
        'package_name' => 'com.mxtech.videoplayer.ad',
        'name' => 'MX Player',
        'short_description' => 'Powerful video player with hardware acceleration',
        'long_description' => '<p>MX Player is a powerful video player with advanced hardware acceleration and subtitle support. It can play almost any video format.</p>',
        'features' => '<ul><li>Hardware acceleration</li><li>Multi-core decoding</li><li>Pinch to zoom</li><li>Subtitle gestures</li><li>Kids lock</li></ul>',
        'version' => '1.7.40',
        'price' => '0.00',
        'rating' => 5,
        'publisher' => 'J2 Interactive',
        'size' => '8.7 MB',
        'icon_url' => $BASE_URL . '/images/mx-player-icon.png',
        'download_url' => $BASE_URL . '/apps/mx-player-1.7.40.apk',
        'screenshots' => [],
        'category_id' => 'media',
        'subcategory_id' => 'video-players',
        'tags' => ['featured', 'bestseller'],
    ],

    // =========================================================================
    // GAMES
    // =========================================================================

    [
        'id' => 'angry-birds',
        'package_name' => 'com.rovio.angrybirds',
        'name' => 'Angry Birds',
        'short_description' => 'The classic bird-flinging physics game',
        'long_description' => '<p>The survival of the Angry Birds is at stake! Dish out revenge on the greedy pigs who stole their eggs. Use the unique powers of each bird to destroy the pigs\' defenses.</p>',
        'features' => '<ul><li>Hundreds of levels</li><li>Multiple bird types</li><li>Physics-based gameplay</li><li>Challenging boss pigs</li></ul>',
        'version' => '4.1.0',
        'price' => '0.00',
        'rating' => 5,
        'publisher' => 'Rovio',
        'size' => '42.3 MB',
        'icon_url' => $BASE_URL . '/images/angry-birds-icon.png',
        'download_url' => $BASE_URL . '/apps/angry-birds-4.1.0.apk',
        'screenshots' => [],
        'category_id' => 'games',
        'subcategory_id' => 'casual',
        'tags' => ['featured', 'bestseller'],
    ],

    [
        'id' => 'cut-the-rope',
        'package_name' => 'com.zeptolab.ctr.free',
        'name' => 'Cut the Rope',
        'short_description' => 'Physics puzzle game - feed candy to Om Nom!',
        'long_description' => '<p>Cut the Rope is a physics-based puzzle game. Cut ropes to release candy and feed it to the adorable Om Nom creature. Collect stars along the way!</p>',
        'features' => '<ul><li>Physics-based puzzles</li><li>Multiple worlds</li><li>Collect stars</li><li>Unlock new levels</li></ul>',
        'version' => '2.5.2',
        'price' => '0.00',
        'rating' => 5,
        'publisher' => 'ZeptoLab',
        'size' => '38.1 MB',
        'icon_url' => $BASE_URL . '/images/cut-the-rope-icon.png',
        'download_url' => $BASE_URL . '/apps/cut-the-rope-2.5.2.apk',
        'screenshots' => [],
        'category_id' => 'games',
        'subcategory_id' => 'puzzle',
        'tags' => ['featured'],
    ],

    // =========================================================================
    // Add more apps here...
    // =========================================================================

];
