<?php

namespace Xotriks\Servertools\Parsers;

class IniParser
{
    public static function parseContent(string $content): array
    {
        // Minecraft server.properties format: key = value (ze spacjami)
        // Parse manually instead of using parse_ini_string()
        
        $result = [];
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip empty lines and comments
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            
            // Szukaj separatora =
            if (strpos($line, '=') === false) {
                continue;
            }
            
            // Split into key and value
            $parts = explode('=', $line, 2);
            $key = trim($parts[0]);
            $value = trim($parts[1] ?? '');
            
            // Skip lines without a key
            if (empty($key)) {
                continue;
            }
            
            // Convert boolean values
            if (in_array(strtolower($value), ['true', 'false'])) {
                $value = strtolower($value) === 'true';
            } elseif (is_numeric($value)) {
                $value = intval($value);
            }
            
            $result[$key] = $value;
        }
        
        return $result;
    }

    public static function writeContent(array $data): string
    {
        $content = self::buildIni($data);
        
        // Minecraft server.properties format: [query]= zamiast query=
        // For keys that used the [name]= format, revert it
        $specialKeys = ['query', 'rcon'];
        foreach ($specialKeys as $key) {
            $content = preg_replace('/^' . preg_quote($key) . '=/m', '[' . $key . ']=', $content);
        }
        
        return $content;
    }

    private static function buildIni(array $data): string
    {
        $content = '';
        $currentSection = null;

        foreach ($data as $key => $value) {
            // Check if this is a section (nested array with string keys)
            if (is_array($value) && !empty($value) && array_keys($value) !== range(0, count($value) - 1)) {
                // This is a section
                $content .= "[{$key}]\n";
                foreach ($value as $subKey => $subValue) {
                    $content .= self::formatValue($subKey, $subValue);
                }
                $content .= "\n";
            } else {
                // This is a top-level key (outside a section)
                $content .= self::formatValue($key, $value);
            }
        }

        return $content;
    }

    private static function formatValue(string $key, mixed $value): string
    {
        if (is_array($value)) {
            // Array — multiple values for a single key
            $lines = '';
            foreach ($value as $item) {
                $lines .= "{$key} = " . self::escapeValue($item) . "\n";
            }
            return $lines;
        } else {
            // Single value — use the spaced format: key = value
            return "{$key} = " . self::escapeValue($value) . "\n";
        }
    }

    private static function escapeValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (is_null($value)) {
            return '';
        } else {
            return (string)$value;
        }
    }
}
