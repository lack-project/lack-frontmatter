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
            $pid = $path->getDirname() ."/" .  phore_uri(phore_uri($path->getFilename())->getFilename())->getFilename();

            if ($filterLang !== null && $filterLang !== $lang)
                continue;
            if ($filter !== "*" && ! fnmatch($filter, $pid))
                continue;


            $ret[] = new FrontmatterRepoPid($this, $pid, $lang);
        }
        return $ret;
    }





    public function export(string $filter = "*", string $filterLang=null) : array {
        $ret = [];
        foreach ($this->list($filter, $filterLang) as $pid) {
            $data = $pid->get()->header;
            $data["pid_new"] = $pid->pid;
            $ret[] = $data;
        }
        return $ret;
    }

    public function import(array $data) {
        foreach ($data as $item) {
            $pidNew = $item["pid_new"];
            $oldPid = $item["pid"];

            unset($item["pid_new"]);
            $pageId = $this->selectPid($item["pid"], $item["lang"]);
            if ( ! $pageId->exists())
                $pageId->create();

            if ($pidNew === "") {
                $pageId->remove();
                return;
            }

            $pageId->get();
            $page  = $pageId->get();
            $page->header = $item;
            $page->header["pid"] = $pidNew;
            $this->storePage($page);
            if ($oldPid !== $pidNew)
                $this->selectPid($oldPid, $item["lang"])->remove();

        }
    }



    public function storePage(FrontmatterPage $page) : void
    {
        $path = $this->rootPath->withSubPath($this->_getStoreUri($page->header["pid"], $page->header["lang"]));
        $path->getDirname()->asDirectory()->assertDirectory(true);
        $path->asFile()->set_contents($page->toString());
        $path->asFile()->chmod(0777);
    }


    public function remove(FrontmatterPage $page) {
        $path = $this->rootPath->withSubPath($this->_getStoreUri($page->header["pid"], $page->header["lang"]));
        $path->unlink();
    }

}
