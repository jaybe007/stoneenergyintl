<?php
/**
 * STONE ENERGY INT'L LTD
 * Dynamic Site Settings Repository
 */

declare(strict_types=1);

class Settings {
    private static ?array $settings = null;

    /**
     * Load all settings into static cache
     */
    public static function load(): void {
        if (self::$settings === null) {
            self::$settings = [];
            try {
                $rows = Database::fetchAll("SELECT `setting_key`, `setting_value` FROM `site_settings`");
                foreach ($rows as $row) {
                    self::$settings[$row['setting_key']] = $row['setting_value'];
                }
            } catch (Exception $e) {
                error_log("Settings Load Error: " . $e->getMessage());
            }
        }
    }

    /**
     * Get a setting by key
     */
    public static function get(string $key, string $default = ''): string {
        self::load();
        return self::$settings[$key] ?? $default;
    }

    /**
     * Get a boolean setting
     */
    public static function getBool(string $key, bool $default = false): bool {
        self::load();
        $val = self::$settings[$key] ?? null;
        if ($val === null) {
            return $default;
        }
        return $val === '1' || $val === 'true' || $val === true;
    }

    /**
     * Update or insert a setting
     */
    public static function set(string $key, ?string $value, string $group = 'general', string $label = '', string $type = 'text'): bool {
        self::load();
        $existing = Database::fetchOne("SELECT id FROM `site_settings` WHERE `setting_key` = :k", [':k' => $key]);
        if ($existing) {
            Database::update('site_settings', ['setting_value' => $value], '`setting_key` = :k', [':k' => $key]);
        } else {
            Database::insert('site_settings', [
                'setting_key' => $key,
                'setting_value' => $value,
                'setting_group' => $group,
                'label' => $label ?: ucfirst(str_replace('_', ' ', $key)),
                'field_type' => $type
            ]);
        }
        self::$settings[$key] = $value;
        return true;
    }

    /**
     * Fetch settings grouped by group name
     */
    public static function getByGroup(string $group): array {
        return Database::fetchAll(
            "SELECT * FROM `site_settings` WHERE `setting_group` = :g ORDER BY `id` ASC",
            [':g' => $group]
        );
    }

    /**
     * Get all settings with meta
     */
    public static function all(): array {
        return Database::fetchAll("SELECT * FROM `site_settings` ORDER BY `setting_group`, `id` ASC");
    }
}

// Procedural helper
function setting(string $key, string $default = ''): string {
    return Settings::get($key, $default);
}
