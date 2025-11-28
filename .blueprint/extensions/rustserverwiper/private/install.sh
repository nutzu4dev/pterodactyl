#!/bin/bash

set -e

echo "== Starting with custom file modifications =="

# 1. Add Wipe and WipeMap use-statements
echo "➤ Step 1: Adding use-statements to ResourceBelongsToServer.php"
FILE="app/Http/Middleware/Api/Client/Server/ResourceBelongsToServer.php"
awk -v ext="$EXTENSION_IDENTIFIER" '
/use Pterodactyl\\Models\\User;/ {
    print
    print "use Pterodactyl\\BlueprintFramework\\Extensions\\" ext "\\Models\\Wipe;"
    print "use Pterodactyl\\BlueprintFramework\\Extensions\\" ext "\\Models\\WipeMap;"
    next
}
{ print }
' "$FILE" > "$FILE.tmp" && mv "$FILE.tmp" "$FILE"

# 2. Add cases for Wipe and WipeMap
echo "➤ Step 2: Adding cases to ResourceBelongsToServer.php"
awk '
/case Schedule::class:/ {
    print
    print "                case Wipe::class:"
    print "                case WipeMap::class:"
    next
}
{ print }
' "$FILE" > "$FILE.tmp" && mv "$FILE.tmp" "$FILE"

# 3. Add wipe.manage constant in Permission.php
echo "➤ Step 3: Adding ACTION_WIPE_MANAGE to Permission.php"
FILE="app/Models/Permission.php"
awk '
/public const ACTION_ACTIVITY_READ = '\''activity.read'\'';/ {
    print
    print ""
    print "    public const ACTION_WIPE_MANAGE = '\''wipe.manage'\'';"
    next
}
{ print }
' "$FILE" > "$FILE.tmp" && mv "$FILE.tmp" "$FILE"

# 4. Add wipe permission array before 'activity'
echo "➤ Step 4: Adding wipe permission array in Permission.php (above 'activity')"
FILE="app/Models/Permission.php"
TMP="__wipe_permissions_block.tmp"

cat > "$TMP" <<'EOF'
        'wipe' => [
            'description' => 'Permissions that control a user\'s access to the rust server wiper.',
            'keys' => [
                'manage' => 'Allows a user to use the rust wiper for the server.',
            ],
        ],

EOF

sed "/'activity' => \[/e cat $TMP" "$FILE" > "$FILE.tmp" && mv "$FILE.tmp" "$FILE"
rm "$TMP"

# 5. Add Wipe and WipeMap use-statements
echo "➤ Step 5: Adding use-statements to Server.php"
FILE="app/Models/Server.php"
awk -v ext="$EXTENSION_IDENTIFIER" '
/use Pterodactyl\\Exceptions\\Http\\Server\\ServerStateConflictException;/ {
    print
    print "use Pterodactyl\\BlueprintFramework\\Extensions\\" ext "\\Models\\Wipe;"
    print "use Pterodactyl\\BlueprintFramework\\Extensions\\" ext "\\Models\\WipeMap;"
    next
}
{ print }
' "$FILE" > "$FILE.tmp" && mv "$FILE.tmp" "$FILE"

# 6. Add timezone to Server.php
echo "➤ Step 6: Adding timezone to Server.php"
FILE="app/Models/Server.php"
awk '
/'\''description'\'' => '\''string'\''/ {
    print
    print "        '\''timezone'\'' => '\''nullable|string'\'',"
    next
}
{ print }
' "$FILE" > "$FILE.tmp" && mv "$FILE.tmp" "$FILE"

# 7. Add wipes() and wipemaps() methods after egg() in Server.php
echo "➤ Step 7: Adding wipes/wipemaps methods after egg() in Server.php"
inside_egg=0
done=0

awk '
/public function egg\(\)/ {
    inside_egg = 1
}
inside_egg && /^\s*\}/ {
    egg_closed = 1
}
inside_egg && egg_closed {
    print
    print ""
    print "    /**"
    print "     * Gets all wipes associated with this server."
    print "     */"
    print "    public function wipes(): HasMany"
    print "    {"
    print "        return $this->hasMany(Wipe::class, '\''server_id'\'');"
    print "    }"
    print ""
    print "    /**"
    print "     * Gets all wipe maps associated with this server."
    print "     */"
    print "    public function wipemaps(): HasMany"
    print "    {"
    print "        return $this->hasMany(WipeMap::class, '\''server_id'\'');"
    print "    }"
    inside_egg = 0
    egg_closed = 0
    done = 1
    next
}
{ print }
' "$FILE" > "$FILE.tmp" && mv "$FILE.tmp" "$FILE"

# 8. Add wipe deletion before $server->delete() in ServerDeletionService.php
echo "➤ Step 8: Adding wipe deletion before \$server->delete() in ServerDeletionService.php"
FILE="app/Services/Servers/ServerDeletionService.php"

awk '
/\$server->delete\(\);/ {
    print "            foreach($server->wipes as $wipe) {"
    print "                $wipe->delete();"
    print "                foreach($wipe->commands as $command) {"
    print "                    $command->delete();"
    print "                }"
    print "            }"
    print ""
}
{ print }
' "$FILE" > "$FILE.tmp" && mv "$FILE.tmp" "$FILE"

# 9. Add 'timezone' to ServerTransformer.php array
echo "➤ Step 9: Adding 'timezone' to ServerTransformer.php"
FILE="app/Transformers/Api/Client/ServerTransformer.php"
awk '
/'\''node'\'' => \$server->node->name,/ {
    print
    print "            '\''timezone'\'' => $server->timezone,"
    next
}
{ print }
' "$FILE" > "$FILE.tmp" && mv "$FILE.tmp" "$FILE"

# 10. Add timezone: string; to getServer.ts type definition
echo "➤ Step 10: Adding timezone: string; to getServer.ts type definition"
FILE="resources/scripts/api/server/getServer.ts"
awk '
/node: string;/ {
    print
    print "  timezone: string;"
    next
}
{ print }
' "$FILE" > "$FILE.tmp" && mv "$FILE.tmp" "$FILE"

# 11. Add timezone: data.timezone, to getServer.ts data object
echo "➤ Step 11: Adding timezone: data.timezone, to getServer.ts data object"
FILE="resources/scripts/api/server/getServer.ts"
awk '
/node: data.node,/ {
    print
    print "  timezone: data.timezone,"
    next
}
{ print }
' "$FILE" > "$FILE.tmp" && mv "$FILE.tmp" "$FILE"

# 12. Replace import Input with Input + Textarea in Field.tsx
echo "➤ Step 12: Replacing import line in Field.tsx"
FILE="resources/scripts/components/elements/Field.tsx"

awk '
/^import Input from .?@\/components\/elements\/Input.?;/ {
    print "import Input, { Textarea } from '\''@/components/elements/Input'\'';"
    next
}
{ print }
' "$FILE" > "$FILE.tmp" && mv "$FILE.tmp" "$FILE"

# 13. Add TextareaField component after 'export default Field;'
echo "➤ Step 13: Adding TextareaField to Field.tsx"
FILE="resources/scripts/components/elements/Field.tsx"
TMP="Field.tsx.tmp"

sed "/^export default Field;/r /dev/stdin" "$FILE" > "$TMP" <<'EOF'

type TextareaProps = OwnProps & Omit<React.TextareaHTMLAttributes<HTMLTextAreaElement>, 'name'>;

export const TextareaField = forwardRef<HTMLTextAreaElement, TextareaProps>(function TextareaField(
    { id, name, light = false, label, description, validate, className, ...props },
    ref
) {
    return (
        <FormikField innerRef={ref} name={name} validate={validate}>
            {({ field, form: { errors, touched } }: FieldProps) => (
                <div className={className}>
                    {label && (
                        <Label htmlFor={id} isLight={light}>
                            {label}
                        </Label>
                    )}
                    <Textarea
                        id={id}
                        {...field}
                        {...props}
                        isLight={light}
                        hasError={!!(touched[field.name] && errors[field.name])}
                    />
                    {touched[field.name] && errors[field.name] ? (
                        <p className={'input-help error'}>
                            {(errors[field.name] as string).charAt(0).toUpperCase() +
                                (errors[field.name] as string).slice(1)}
                        </p>
                    ) : description ? (
                        <p className={'input-help'}>{description}</p>
                    ) : null}
                </div>
            )}
        </FormikField>
    );
});
TextareaField.displayName = 'TextareaField';

EOF

mv "$TMP" "$FILE"

# 14. Add 'checked?: boolean;' to Switch.tsx type definition
echo "➤ Step 14: Adding 'checked?: boolean;' to type definition in Switch.tsx"
FILE="resources/scripts/components/elements/Switch.tsx"
awk '
/children\?: React\.ReactNode;/ {
    print
    print "    checked?: boolean;"
    next
}
{ print }
' "$FILE" > "$FILE.tmp" && mv "$FILE.tmp" "$FILE"

# 15. Add 'checked' to props destructuring in Switch component
echo "➤ Step 15: Adding 'checked' to Switch component function header"
FILE="resources/scripts/components/elements/Switch.tsx"
awk '
/^const Switch = \(\{ name, label, description, defaultChecked, readOnly, onChange, children }:\s*SwitchProps\) => \{/ {
    print "const Switch = ({ name, label, description, defaultChecked, readOnly, onChange, children, checked }: SwitchProps) => {"
    next
}
{ print }
' "$FILE" > "$FILE.tmp" && mv "$FILE.tmp" "$FILE"

# 16. Add 'checked={checked}' below 'disabled={readOnly}'
echo "➤ Step 16: Adding 'checked={checked}' below 'disabled={readOnly}' in Switch.tsx"
FILE="resources/scripts/components/elements/Switch.tsx"
awk '
/disabled=\{readOnly\}/ {
    print
    print "                        checked={checked}"
    next
}
{ print }
' "$FILE" > "$FILE.tmp" && mv "$FILE.tmp" "$FILE"

echo "✅ All changes have been applied successfully."
