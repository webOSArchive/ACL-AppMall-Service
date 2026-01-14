#!/bin/bash
# Setup script to decompile and extract the AppMall APK for development
# Requires: apktool, unzip

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APK_FILE="$SCRIPT_DIR/AppMall_webosarchive.apk"

if [ ! -f "$APK_FILE" ]; then
    echo "Error: AppMall_webosarchive.apk not found in project root"
    exit 1
fi

echo "=== AppMall Development Setup ==="

# Decompile APK with apktool (for smali editing)
echo ""
echo "Decompiling APK to AppMall-decompiled..."
rm -rf "$SCRIPT_DIR/AppMall-decompiled"
apktool d "$APK_FILE" -o "$SCRIPT_DIR/AppMall-decompiled"
echo "Done: AppMall-decompiled/"

# Extract APK contents (for reference)
echo ""
echo "Extracting APK to AppMall-extracted..."
rm -rf "$SCRIPT_DIR/AppMall-extracted"
mkdir -p "$SCRIPT_DIR/AppMall-extracted"
unzip -q "$APK_FILE" -d "$SCRIPT_DIR/AppMall-extracted"
echo "Done: AppMall-extracted/"

# Create keystore if it doesn't exist
if [ ! -f "$SCRIPT_DIR/appmall-key.keystore" ]; then
    echo ""
    echo "Creating signing keystore..."
    keytool -genkey -v -keystore "$SCRIPT_DIR/appmall-key.keystore" \
        -alias appmall -keyalg RSA -keysize 2048 -validity 10000 \
        -storepass appmall123 -keypass appmall123 \
        -dname "CN=AppMall, OU=webOS Archive, O=webOS Archive, L=Unknown, ST=Unknown, C=US"
    echo "Done: appmall-key.keystore"
fi

echo ""
echo "=== Setup Complete ==="
echo ""
echo "To rebuild the APK after making changes:"
echo "  apktool b AppMall-decompiled -o AppMall_webosarchive.apk"
echo "  apksigner sign --ks appmall-key.keystore --ks-pass pass:appmall123 \\"
echo "    --v1-signing-enabled true --v2-signing-enabled false AppMall_webosarchive.apk"
