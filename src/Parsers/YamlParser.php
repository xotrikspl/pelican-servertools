<?php

namespace Xotriks\Servertools\Parsers;

use Symfony\Component\Yaml\Yaml;

class YamlParser
{
    public static function parseContent(string $content): array
    {
        return Yaml::parse($content) ?: [];
    }

    public static function writeContent(array $data): string
    {
        return Yaml::dump($data, 4, 2);
    }
}