#!/usr/bin/env bash

base_directory="./languages"

# Download translations from Localazy
if command -v localazy &> /dev/null; then
    echo "Downloading translations from Localazy..."
    localazy download || exit 1

    # Rename hyphenated locale directories to underscore format
    echo "Renaming locale directories..."
    for dir in "$base_directory"/*/ ; do
        if [ -d "$dir" ]; then
            dirname=$(basename "$dir")
            newdirname="${dirname//-/_}"
            # Replace # with _ for Localazy format (e.g., sr#Cyrl)
            newdirname="${newdirname//#/_}"

            # Special locale mappings
            case "$newdirname" in
                "zh_Hant_TW") newdirname="zh_TW" ;;
                "sr") newdirname="sr_RS" ;;
            esac

            if [ "$dirname" != "$newdirname" ]; then
                # Create target directory if it doesn't exist
                mkdir -p "$base_directory/$newdirname"
                # Move all files from hyphenated to underscore directory
                mv "$dir"* "$base_directory/$newdirname/" 2>/dev/null
                # Remove the hyphenated directory
                rm -rf "$dir"
            fi
        fi
    done

    # Ensure consistent file naming: wpdatatables-{locale}.po
    echo "Normalizing file names..."
    for dir in "$base_directory"/*/ ; do
        if [ -d "$dir" ]; then
            locale=$(basename "$dir")
            for file in "$dir"*.po; do
                if [ -f "$file" ]; then
                    filename=$(basename "$file")
                    # Convert any wpdatatables_{locale} or wpdatatables-{locale-with-dash} to wpdatatables-{locale_with_underscore}
                    expected_name="wpdatatables-${locale}.po"
                    if [ "$filename" != "$expected_name" ]; then
                        mv "$file" "$dir$expected_name"
                    fi
                fi
            done
        fi
    done

    # Create fallback locale directories from source locales
    echo "Creating fallback locales..."
    declare -A fallback_locales=(
        ["de_CH"]="de_DE"
        ["de_DE_formal"]="de_DE"
        ["nl_NL_formal"]="nl_NL"
    )

    for target_locale in "${!fallback_locales[@]}"; do
        source_locale="${fallback_locales[$target_locale]}"
        if [ -d "$base_directory/$source_locale" ]; then
            # Remove existing target directory if it exists
            rm -rf "$base_directory/$target_locale"
            # Copy source to target
            cp -a "$base_directory/$source_locale" "$base_directory/$target_locale"
            # Rename files to match the new locale
            for file in "$base_directory/$target_locale"/wpdatatables-$source_locale.*; do
                if [ -f "$file" ]; then
                    ext="${file##*.}"
                    mv "$file" "$base_directory/$target_locale/wpdatatables-$target_locale.$ext"
                fi
            done
        fi
    done

    # Clear msgstr if it equals msgid (except for en_US)
    echo "Cleaning up translations..."

    for dir in "$base_directory"/*/ ; do
        if [ -d "$dir" ]; then
            locale=$(basename "$dir")
            if [ "$locale" != "en_US" ]; then
                for file in "$dir"*.po; do
                    if [ -f "$file" ]; then
                        # Use awk to process the file
                        awk '
                        function get_content(line) {
                            match(line, /".*"/)
                            if (RLENGTH > 2) {
                                return substr(line, RSTART+1, RLENGTH-2)
                            }
                            return ""
                        }

                        function flush() {
                            if (count == 0) return

                            if (has_msgid && has_msgstr && !is_plural && msgid == msgstr) {
                                replaced = 0
                                for (i = 0; i < count; i++) {
                                    l = lines[i]
                                    if (l ~ /^msgstr/) {
                                        print "msgstr \"\""
                                        replaced = 1
                                    } else if (replaced && l ~ /^[[:space:]]*"/) {
                                        # Skip continuation lines of the replaced msgstr
                                        continue
                                    } else {
                                        print l
                                    }
                                }
                            } else {
                                for (i = 0; i < count; i++) {
                                    print lines[i]
                                }
                            }

                            # Reset state
                            count = 0
                            delete lines
                            msgid = ""
                            msgstr = ""
                            has_msgid = 0
                            has_msgstr = 0
                            is_plural = 0
                            in_msgid = 0
                            in_msgstr = 0
                        }

                        BEGIN {
                            count = 0
                            in_msgid = 0
                            in_msgstr = 0
                        }

                        {
                            if ($0 ~ /^[[:space:]]*$/) {
                                flush()
                                print $0
                                next
                            }

                            if ($0 ~ /^#/ && has_msgstr) {
                                flush()
                            }

                            if ($0 ~ /^msgid_plural/) {
                                is_plural = 1
                                in_msgid = 0
                                in_msgstr = 0
                            } else if ($0 ~ /^msgid /) {
                                if (has_msgstr) {
                                    flush()
                                }
                                has_msgid = 1
                                in_msgid = 1
                                in_msgstr = 0
                                msgid = get_content($0)
                            } else if ($0 ~ /^msgstr /) {
                                has_msgstr = 1
                                in_msgstr = 1
                                in_msgid = 0
                                msgstr = get_content($0)
                            } else if ($0 ~ /^[[:space:]]*"/) {
                                val = get_content($0)
                                if (in_msgid) {
                                    msgid = msgid val
                                } else if (in_msgstr) {
                                    msgstr = msgstr val
                                }
                            }

                            lines[count++] = $0
                        }

                        END {
                            flush()
                        }
                        ' "$file" > "${file}.tmp" && mv "${file}.tmp" "$file"
                    fi
                done
            fi
        fi
    done

    # Remove source code references from .po files
    echo "Removing source code references..."
    for dir in "$base_directory"/*/ ; do
        if [ -d "$dir" ]; then
            for file in "$dir"*.po; do
                if [ -f "$file" ]; then
                    msgattrib --no-location "$file" -o "${file}.tmp" && mv "${file}.tmp" "$file"
                fi
            done
        fi
    done

    echo "Download complete."

    # Compile translation files
    echo "Compiling translation files..."
    find "./languages" -type f -name '*.po' | while read -r file; do
        file_without_ext="${file%.po}"
        msgfmt "$file" -o "$file_without_ext.mo"
    done
    echo "Compilation complete."
else
    echo "Error: Localazy CLI not found. Please install it first."
    echo "Installation instructions: https://localazy.com/docs/cli/installation"
    exit 1
fi
