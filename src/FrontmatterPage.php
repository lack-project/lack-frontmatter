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

    /**
     * Return the permalink of the page /path/to/page.de.html
     * 
     * @return string
     */
    public function getLink () : string {
        return $this->header["permalink"] ?? "/" .  $this->header["pid"] . "." . $this->header["lang"] . ".html";
    }

    public function toString() : string {

        $headerVar = $this->header;
        
        // Unset permalink if empty
        if (trim($headerVar["permalink"] ?? "") === "") {
            unset ($headerVar["permalink"]);
        }
        
        
        $header = yaml_emit($headerVar, YAML_UTF8_ENCODING);
        // remove trailing --- and ... from yaml
        $header = substr($header, 4, strlen($header) - 8);
        return "---\n$header---\n{$this->body}";
    }

}
