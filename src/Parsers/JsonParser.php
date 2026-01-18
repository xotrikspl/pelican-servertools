<?php

namespace Xotriks\Servertools\Parsers;

class JsonParser
{
    public static function parseContent(string $content): array
    {
        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : [];
    }

    public static function writeContent(array $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}