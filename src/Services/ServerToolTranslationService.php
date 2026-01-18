<?php

namespace Xotriks\Servertools\Services;

use Illuminate\Support\Arr;
use Xotriks\Servertools\Models\ServerToolProfileTranslation;
use Xotriks\Servertools\Models\ServerToolConfiguration;

class ServerToolTranslationService
{
    protected static array $cache = [];
    protected static array $categoryCache = [];

    public static function translate(string $key, ?string $locale = null, ?string $fallbackLocale = null, ?int $profileId = null): string
    {
        if ($key === '') {
            return '';
        }

        $normalizedKey = self::normalizeKey($key);
        [$namespace, $path] = self::splitKey($normalizedKey);

        $locale = $locale ?? app()->getLocale();
        $shortLocale = self::normalizeLocale($locale);

        $fallbackLocale = $fallbackLocale ?? config('app.fallback_locale', 'en');
        $fallbackShort = self::normalizeLocale($fallbackLocale);

        if ($profileId && $namespace !== 'common') {
            $categoryId = self::getCategoryIdForProfile($profileId);
            if ($categoryId) {
                $value = self::getFromCategory($categoryId, $shortLocale, $normalizedKey);
            } else {
                $value = null;
            }
        } else {
            $value = self::getFromFile($shortLocale, $namespace, $path);
        }
        if ($value === null && $fallbackShort !== $shortLocale) {
            if ($profileId && $namespace !== 'common') {
                $categoryId = self::getCategoryIdForProfile($profileId);
                if ($categoryId) {
                    $value = self::getFromCategory($categoryId, $fallbackShort, $normalizedKey);
                } else {
                    $value = null;
                }
            } else {
                $value = self::getFromFile($fallbackShort, $namespace, $path);
            }
        }

        return is_string($value) ? $value : $key;
    }

    protected static function normalizeKey(string $key): string
    {
        if (str_starts_with($key, 'servertools::')) {
            return substr($key, strlen('servertools::'));
        }

        return $key;
    }

    protected static function splitKey(string $key): array
    {
        $parts = explode('.', $key, 2);
        $namespace = $parts[0] ?? 'common';
        $path = $parts[1] ?? '';

        return [$namespace, $path];
    }

    protected static function normalizeLocale(string $locale): string
    {
        $normalized = str_replace('_', '-', $locale);

        return explode('-', $normalized)[0] ?? $normalized;
    }

    protected static function getFromFile(string $locale, string $namespace, string $path): mixed
    {
        if ($path === '') {
            return null;
        }

        $cacheKey = $locale . '|' . $namespace;
        if (!array_key_exists($cacheKey, self::$cache)) {
            $filePath = plugin_path('servertools') . "/resources/lang/{$locale}/{$namespace}.php";
            if (is_file($filePath)) {
                $translations = include $filePath;
                self::$cache[$cacheKey] = is_array($translations) ? $translations : [];
            } else {
                self::$cache[$cacheKey] = [];
            }
        }

        return Arr::get(self::$cache[$cacheKey], $path);
    }

    protected static function getFromCategory(?int $categoryId, string $locale, string $key): mixed
    {
        if (!$categoryId || $key === '') {
            return null;
        }

        $cacheKey = "category|{$categoryId}|{$locale}";
        if (!array_key_exists($cacheKey, self::$cache)) {
            $translations = ServerToolProfileTranslation::query()
                ->where('translation_category_id', $categoryId)
                ->where('locale', $locale)
                ->pluck('value', 'key')
                ->toArray();

            self::$cache[$cacheKey] = $translations;
        }

        return self::$cache[$cacheKey][$key] ?? null;
    }

    protected static function getCategoryIdForProfile(int $profileId): ?int
    {
        if (!array_key_exists($profileId, self::$categoryCache)) {
            self::$categoryCache[$profileId] = ServerToolConfiguration::query()
                ->whereKey($profileId)
                ->value('translation_category_id');
        }

        return self::$categoryCache[$profileId] ?: null;
    }

    public static function availableKeysFromConfig(?array $config): array
    {
        if (!is_array($config)) {
            return [];
        }

        $keys = [];
        $files = $config['files'] ?? [];
        if (!is_array($files)) {
            return [];
        }

        foreach ($files as $file) {
            if (!is_array($file)) {
                continue;
            }
            $sections = $file['sections'] ?? [];
            if (!is_array($sections)) {
                continue;
            }

            foreach ($sections as $sectionKey => $fields) {
                if (is_string($sectionKey) && str_contains($sectionKey, '.')) {
                    $keys[] = $sectionKey;
                }

                if (!is_array($fields)) {
                    continue;
                }

                foreach ($fields as $field) {
                    if (!is_array($field)) {
                        continue;
                    }

                    $label = $field['label'] ?? null;
                    if (is_string($label) && str_contains($label, '.')) {
                        $keys[] = $label;
                    }

                    $options = $field['options'] ?? null;
                    if (is_array($options)) {
                        foreach ($options as $option) {
                            if (is_string($option) && str_contains($option, '.')) {
                                $keys[] = $option;
                            }
                        }
                    }
                }
            }
        }

        $keys = array_values(array_unique($keys));
        sort($keys);

        return $keys;
    }

    public static function getProfileTranslation(?int $profileId, ?string $locale, ?string $key): ?string
    {
        if (!$profileId || !$locale || !$key) {
            return null;
        }

        $shortLocale = self::normalizeLocale($locale);
        $normalizedKey = self::normalizeKey($key);

        $categoryId = self::getCategoryIdForProfile($profileId);
        if ($categoryId) {
            $value = self::getFromCategory($categoryId, $shortLocale, $normalizedKey);
        }

        return is_string($value) ? $value : null;
    }
}