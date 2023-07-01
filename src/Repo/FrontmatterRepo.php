<?php

namespace Lack\Frontmatter\Repo;

use Lack\Frontmatter\FrontmatterPage;
use Phore\FileSystem\PhoreDirectory;

class FrontmatterRepo
{

    public function __construct(public string|PhoreDirectory $rootPath) {
        $this->rootPath = phore_dir($rootPath);
    }

    /**
     * Retrieve the relative path to the file
     *
     * @param string $pid
     * @param string $lang
     * @return string
     * @throws \Phore\FileSystem\Exception\PathOutOfBoundsException
     * @internal
     */
    public function getStoreUri(string $pid, string $lang) : string
    {
        return $this->rootPath->withSubPath($pid . ".{$lang}.md")->getUri();
    }

    public function selectPid(string $pid, string $lang) : FrontmatterRepoPid
    {
        return new FrontmatterRepoPid($this, $pid, $lang);
    }


    public function storePage(FrontmatterPage $page) : void
    {
        $path = $this->rootPath->withSubPath($this->getStoreUri($page->header["pid"], $page->header["lang"]));
        $path->set_contents($page->toString());
    }


    public function remove(FrontmatterPage $page) {
        $path = $this->rootPath->withSubPath($this->getStoreUri($page->header["pid"], $page->header["lang"]));
        $path->unlink();
    }

}
