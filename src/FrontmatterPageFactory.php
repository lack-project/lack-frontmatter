<?php

namespace Lack\Frontmatter;

class FrontmatterPageFactory
{

    public function parseString(string $string) : FrontmatterPage
    {
        // If the file does not start with "---", assume no frontmatter and return everything as body
        if (substr($string, 0, 3) !== "---") {
            return new FrontmatterPage([], $string);
        }

        // Split the content into parts by "---"
        $parts = preg_split('/(^|\n)---\n/', $string, 3);

        // Extract header and body
        $header = trim($parts[1]);
        $body = isset($parts[2]) ? trim($parts[2]) : null;

        ini_set("yaml.decode_php", 0);
        // Parse header from YAML format to PHP associative array
        $parsedHeader = yaml_parse($header);

        return new FrontmatterPage($parsedHeader, $body);
    }

}
