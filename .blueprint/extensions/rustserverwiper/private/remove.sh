#!/bin/bash

set -e

echo "== Starting restoration of modified files =="

# 16. Remove 'checked={checked}' from Switch.tsx
echo "➤ Revert 16: Remove 'checked={checked}' from Switch.tsx"
FILE="resources/scripts/components/elements/Switch.tsx"
sed -i "/checked={checked}/d" "$FILE"

# 15. Restore function header of Switch component
echo "➤ Revert 15: Restore Switch component function header"
sed -i 's/({ name, label, description, defaultChecked, readOnly, onChange, children, checked }/({ name, label, description, defaultChecked, readOnly, onChange, children }/' "$FILE"

# 14. Remove 'checked?: boolean;' from type definition
echo "➤ Revert 14: Remove 'checked?: boolean;' from type definition"
sed -i '/^[[:space:]]*checked\s*?\s*:\s*boolean\s*;/d' "$FILE"

# 13. Remove TextareaField component
echo "➤ Revert 13: Remove TextareaField component from Field.tsx"
FILE="resources/scripts/components/elements/Field.tsx"
sed -i '/^type TextareaProps =/,/^TextareaField.displayName/d' "$FILE"

# 12. Restore import line
echo "➤ Revert 12: Restore import line in Field.tsx"
sed -i "s/import Input, { Textarea } from/import Input from/" "$FILE"

# 11. Remove 'timezone: data.timezone,'
echo "➤ Revert 11: Remove 'timezone: data.timezone,'"
FILE="resources/scripts/api/server/getServer.ts"
sed -i '/timezone: data.timezone,/d' "$FILE"

# 10. Remove 'timezone: string;' from type definition
echo "➤ Revert 10: Remove 'timezone: string;' from type definition"
sed -i '/timezone: string;/d' "$FILE"

# 9. Remove 'timezone' from ServerTransformer.php
echo "➤ Revert 9: Remove 'timezone' from ServerTransformer.php"
FILE="app/Transformers/Api/Client/ServerTransformer.php"
sed -i "/'timezone' => \$server->timezone,/d" "$FILE"

# 8. Remove wipe deletion logic from ServerDeletionService.php
echo "➤ Revert 8: Remove wipe deletion logic"
FILE="app/Services/Servers/ServerDeletionService.php"
awk '
  BEGIN { skip=0; depth=0 }
  /foreach\(\$server->wipes as \$wipe\) {/ { skip=1; depth=1; next }
  skip {
    if (/\{/) depth++
    if (/\}/) depth--
    if (depth == 0) { skip=0; next }
    next
  }
  { print }
' "$FILE" > "$FILE.tmp" && mv "$FILE.tmp" "$FILE"

# 7. Remove wipes() and wipemaps() methods from Server.php
echo "➤ Revert 7: Remove wipes() and wipemaps() methods"
FILE="app/Models/Server.php"
awk '
  BEGIN { skipping = 0 }

  /^\s*\/\*\*/ { buffer = ""; docblock = 1 }

  docblock {
    buffer = buffer $0 "\n"
    if ($0 ~ /^\s*\*\/$/) {
      docblock = 0
      if (getline nextLine > 0) {
        if (nextLine ~ /public function wipes\(\): HasMany/ || nextLine ~ /public function wipemaps\(\): HasMany/) {
          skipping = 1
          funcEnd = 0
        } else {
          printf "%s", buffer
          print nextLine
        }
      }
      next
    }
    next
  }

  skipping {
    if ($0 ~ /^\s*}/) {
      funcEnd++
      if (funcEnd == 1) {
        skipping = 0
      }
    }
    next
  }

  { print }
' "$FILE" > tmp && mv tmp "$FILE"

# 6. Remove timezone from Server.php fillable fields
echo "➤ Revert 6: Remove timezone from Server.php"
sed -i "/'timezone' => 'nullable|string',/d" "$FILE"

# 5. Remove Wipe and WipeMap use-statements from Server.php
echo "➤ Revert 5: Remove use-statements from Server.php"
sed -i '/use Pterodactyl\\BlueprintFramework\\Extensions.*\\Models\\WipeMap;/d' "$FILE"
sed -i '/use Pterodactyl\\BlueprintFramework\\Extensions.*\\Models\\Wipe;/d' "$FILE"

# 4. Remove wipe permission array from Permission.php
echo "➤ Revert 4: Remove wipe permission array"
FILE="app/Models/Permission.php"
awk '
  BEGIN { skip = 0; level = 0 }

  /^\s*'\''wipe'\'' => \[/ {
    skip = 1
    level = 1
    next
  }

  skip {
    if ($0 ~ /\[/) level++
    if ($0 ~ /\]/) level--
    if (level == 0) {
      skip = 0
      next
    }
    next
  }

  { print }
' "$FILE" > tmp && mv tmp "$FILE"

# 3. Remove ACTION_WIPE_MANAGE constant
echo "➤ Revert 3: Remove ACTION_WIPE_MANAGE constant"
sed -i "/public const ACTION_WIPE_MANAGE = 'wipe.manage';/d" "$FILE"

# 2. Remove cases for Wipe and WipeMap from switch
echo "➤ Revert 2: Remove switch cases for Wipe and WipeMap"
FILE="app/Http/Middleware/Api/Client/Server/ResourceBelongsToServer.php"
sed -i '/case Wipe::class:/d' "$FILE"
sed -i '/case WipeMap::class:/d' "$FILE"

# 1. Remove use-statements for Wipe and WipeMap in middleware
echo "➤ Revert 1: Remove use-statements from ResourceBelongsToServer.php"
grep -vF "use Pterodactyl\BlueprintFramework\Extensions\\${EXTENSION_IDENTIFIER}\Models\WipeMap;" "$FILE" > "$FILE.tmp" && mv "$FILE.tmp" "$FILE"
grep -vF "use Pterodactyl\BlueprintFramework\Extensions\\${EXTENSION_IDENTIFIER}\Models\Wipe;" "$FILE" > "$FILE.tmp" && mv "$FILE.tmp" "$FILE"

echo "✅ All modifications have been successfully reverted."
