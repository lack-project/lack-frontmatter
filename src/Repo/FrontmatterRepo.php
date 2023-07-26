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
    public function _getStoreUri(string $pid, string $lang, string $prefix="") : string
    {
        $pid = explode("/", $pid);
        // add prefix to last element of pid
        $pid[count($pid)-1] = $prefix . $pid[count($pid)-1];
        $pid = implode("/", $pid);
        return $pid . ".{$lang}.md";
    }

    public function selectPid(string $pid, string $lang) : FrontmatterRepoPid
    {
        return new FrontmatterRepoPid($this, $pid, $lang);
    }


    /**
     * @param $filter
     * @return FrontmatterRepoPid[]
     */
    public function list (string $filter = "*", string $filterLang = null) : array {
        $ret = [];
        foreach (phore_dir($this->rootPath)->genWalk("*.md", true) as $file) {

            $path = phore_uri($file->getRelPath());
            $lang = phore_uri($path->getFilename())->getExtension();
            $pid = $path->getDirname() ."/" .  phore_uri($path->getFilename())->getFilename();

            if ($filterLang !== null && $filterLang !== $lang)
                continue;
            if ($filter !== "*" && ! fnmatch($filter, $pid))
                continue;

            $ret[] = new FrontmatterRepoPid($this, $pid, $lang);
        }
        return $ret;
    }


    public function storePage(FrontmatterPage $page) : void
    {
        $path = $this->rootPath->withSubPath($this->_getStoreUri($page->header["pid"], $page->header["lang"]));
        $path->getDirname()->asDirectory()->assertDirectory(true);
        $path->asFile()->set_contents($page->toString());
    }


    public function remove(FrontmatterPage $page) {
        $path = $this->rootPath->withSubPath($this->_getStoreUri($page->header["pid"], $page->header["lang"]));
        $path->unlink();
    }

}
