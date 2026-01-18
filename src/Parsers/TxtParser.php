<?php

namespace Xotriks\Servertools\Parsers;

class TxtParser
{
    public static function parseContent(string $content): array
    {
        return CfgParser::parseContent($content);
    }

    public static function writeContent(array $data): string
    {
        return CfgParser::writeContent($data);
    }
}