#!/usr/bin/env bash

set -euo pipefail

readonly LITE_PATH="${LITE_PATH:-../wpdatatables-lite}"

require_command() {
    local command_name="$1"

    if ! command -v "$command_name" &> /dev/null; then
        echo "Error: $command_name not found. Please install it first."
        exit 1
    fi
}

extract_pot() {
    local source_directory="$1"
    local output_file="$2"
    local file_list="$3"

    if [ ! -d "$source_directory" ]; then
        echo "Error: Source directory not found: $source_directory"
        exit 1
    fi

    (
        cd "$source_directory"
        find . -type f -name "*.php" \
            -not -path "./.git/*" \
            -not -path "./assets/*" \
            -not -path "./lib/*" \
            -not -path "./node_modules/*" \
            -not -path "./vendor/*" \
            -print0 > "$file_list"

        if [ ! -s "$file_list" ]; then
            echo "Error: No PHP files found in $source_directory"
            exit 1
        fi

        xargs -0 xgettext \
            --output="$output_file" \
            --language=php \
            --from-code=UTF-8 \
            --keyword=__ \
            --keyword=_e \
            --keyword=esc_html__ \
            --keyword=esc_html_e \
            --keyword=esc_attr__ \
            --keyword=esc_attr_e \
            --keyword=gettext \
            --keyword=gettext_noop \
            --add-comments=TRANSLATORS: < "$file_list"
    )
}

require_command xgettext
require_command msgcat
require_command msgmerge
require_command localazy

temporary_directory="$(mktemp -d)"
trap 'rm -rf "$temporary_directory"' EXIT

premium_pot="$temporary_directory/wpdatatables-premium.po"
lite_pot="$temporary_directory/wpdatatables-lite.po"
merged_pot="$temporary_directory/wpdatatables-merged.po"
premium_files="$temporary_directory/premium-files"
lite_files="$temporary_directory/lite-files"

echo "Generating POT file from wpDataTables Premium source..."
extract_pot "." "$premium_pot" "$premium_files"

echo "Generating POT file from wpDataTables Lite source..."
extract_pot "$LITE_PATH" "$lite_pot" "$lite_files"

echo "Merging Premium and Lite source strings..."
msgcat --use-first "$premium_pot" "$lite_pot" -o "$merged_pot"

echo "Updating source language file..."
msgmerge "languages/en_US/wpdatatables-en_US.po" "$merged_pot" -o "languages/en_US/wpdatatables-en_US.po"

echo "Uploading source language to Localazy..."
localazy upload || exit 1
echo "Upload complete."
