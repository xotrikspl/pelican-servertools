<?php

namespace Xotriks\Servertools\Parsers;

class CfgParser
{
    public static function parseContent(string $content): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $data = [];
        $currentSection = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Handle [section] blocks
            if (preg_match('/^\[(.+)\]$/', $line, $matches)) {
                $currentSection = $matches[1];
                $data[$currentSection] = [];
                continue;
            }

            // Handle `key = value`
            if (preg_match('/^(.+?)\s*=\s*(.*)$/', $line, $matches)) {
                $key = trim($matches[1]);
                $value = trim($matches[2], '"\'');
                $target = &$data;

                if ($currentSection) {
                    $target = &$data[$currentSection];
                }

                $target[$key] = $value;
            }
        }

        return $data;
    }

    public static function writeContent(array $data): string
    {
        $content = '';

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $content .= "[{$key}]\n";
                foreach ($value as $k => $v) {
                    $content .= "{$k} = " . (is_bool($v) ? ($v ? 'true' : 'false') : $v) . "\n";
                }
                $content .= "\n";
            } else {
                $content .= "{$key} = " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "\n";
            }
        }

        return $content;
    }
}