<?php

namespace Lack\Frontmatter;

class FrontmatterPage
{


    public function __construct(
        public array $header,
        public string $body,
        public array $meta = []
    )
    {

    }


    public function toString() : string {

        $header = yaml_emit($this->header, YAML_UTF8_ENCODING);
        // remove trailing --- and ... from yaml
        $header = substr($header, 4, strlen($header) - 8);
        return "---\n$header---\n{$this->body}";
    }

}
