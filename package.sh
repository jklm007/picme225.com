#!/bin/bash

# ==============================================================================
# Laravel Project Packager
# ==============================================================================
# Author: Antigravity AI
# Usage: ./package.sh [--production | --full]
# ==============================================================================

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Default values
MODE="production"
PROJECT_PATH=$(pwd)
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
APP_NAME=$(grep -oP "(?<=APP_NAME=).*" .env | tr -d '"' | tr -d ' ' | tr '[:upper:]' '[:lower:]' || echo "laravel")
ZIP_NAME="${APP_NAME}_${TIMESTAMP}_${MODE}.zip"

# Function to display usage
usage() {
    echo "Usage: $0 [--production | --full]"
    echo "  --production : Minimal ZIP (excludes vendor/ and .env)"
    echo "  --full       : Complete ZIP (includes vendor/ and .env)"
    exit 1
}

# Parse arguments
if [[ "$1" == "--full" ]]; then
    MODE="full"
elif [[ "$1" == "--production" || -z "$1" ]]; then
    MODE="production"
else
    usage
fi

ZIP_NAME="${APP_NAME}_${TIMESTAMP}_${MODE}.zip"

echo -e "${BLUE}🚀 Starting Laravel Packaging in [${MODE}] mode...${NC}"

# 🟢 5. OBLIGATORY VERIFICATIONS
echo -e "${BLUE}🔍 Verifying Laravel structure...${NC}"

CHECK_FILES=("artisan" "app" "config" "routes")
for item in "${CHECK_FILES[@]}"; do
    if [[ ! -e "$item" ]]; then
        echo -e "${RED}❌ Error: '$item' not found. Are you in the root of a Laravel project?${NC}"
        exit 1
    fi
done

echo -e "${GREEN}✅ Structure validated.${NC}"

# Define exclusions
# We use an array for exclusions to handle them properly
EXCLUDE_LIST=(
    "*.save"
    "*.save.php"
    "*.txt"
    "*.log"
    "*.bak"
    ".git/*"
    ".vscode/*"
    "node_modules/*"
    "tests/*"
    "storage/logs/*"
    "storage/framework/cache/data/*"
    "storage/framework/sessions/*"
    "storage/framework/testing/*"
    "storage/framework/views/*"
    "backups/*"
    "dumps/*"
    "*.zip" # Exclude existing zips including the one being created
)

# Mode specific exclusions
if [[ "$MODE" == "production" ]]; then
    EXCLUDE_LIST+=("vendor/*" ".env")
    echo -e "${YELLOW}⚠️  Mode Production: 'vendor/' and '.env' will be excluded.${NC}"
else
    echo -e "${YELLOW}📦 Mode Full: 'vendor/' and '.env' will be included.${NC}"
fi

# Build the zip command
# We use 'zip -r' and then multiple '-x' for exclusions
ZIP_CMD="zip -r \"$ZIP_NAME\" ."

# Add exclusions to the command
for pattern in "${EXCLUDE_LIST[@]}"; do
    ZIP_CMD+=" -x \"$pattern\""
done

# Execute packaging
echo -e "${BLUE}📦 Creating archive: ${ZIP_NAME}...${NC}"
eval "$ZIP_CMD" > /dev/null

if [[ $? -eq 0 ]]; then
    echo -e "${GREEN}✨ Success! Archive created: ${ZIP_NAME}${NC}"
    echo -e "${BLUE}📊 Final size: $(du -h "$ZIP_NAME" | cut -f1)${NC}"
else
    echo -e "${RED}❌ Failed to create ZIP archive. Ensure 'zip' utility is installed.${NC}"
    exit 1
fi
