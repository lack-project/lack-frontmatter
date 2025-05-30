<?php

namespace Lack\Frontmatter\Repo;

use Lack\Frontmatter\FrontmatterPage;
use Lack\Frontmatter\FrontmatterPageFactory;
use Phore\FileSystem\PhoreDirectory;

class FrontmatterRepoPid
{

    /**
     * Use FrontmatterRepo::selectPid() to create an instance
     *
     * @param FrontmatterRepo $repo
     * @param string $pid
     * @param string $lang
     * @internal
     */
    public function __construct(public FrontmatterRepo $repo, public string $pid, public string $lang)
    {

    }

    public function getPid() : string {
        return $this->pid;
    }

    public function getLang() : string {
        return $this->lang;
    }


    public function getAvailLangs()
    {
        $langs = [];
        $pids = $this->repo->list($this->pid);
        foreach ($pids as $page) {
            $langs[] = $page->lang;
        }
        return array_values(array_unique($langs));
    }

    public function __toString()
    {
        return $this->pid;
    }


    /**
     * Retrieve coneten from _elements.md files in this directory
     * and parent directories
     *
     * @return string
     */
    public function getElementsDef() : string {
        $curDir = $this->repo->rootPath->withSubPath($this->repo->_getStoreUri($this->pid, $this->lang))->getDirname();
        $ret = "";
        while (true) {
            if ($curDir->withFileName("_elements.md")->exists()) {
                $ret .= $curDir->withFileName("_elements.md")->get_contents();
            }
            if ((string)$curDir->getDirname()->abs() === (string)$this->repo->rootPath->getDirname()->abs())
                break;
            $curDir = $curDir->withParentDir();
        }
        return $ret;
    }


    public function getDefault() : FrontmatterPage {
        $dirname = $this->repo->rootPath->withSubPath($this->repo->_getStoreUri($this->pid, $this->lang))->getDirname();

        $checkFiles = [
            $dirname->withSubPath("_default.{$this->lang}.md"),
            $dirname->withSubPath("_default.md"),
            $dirname->withRelativePath("..")->withSubPath("_default.{$this->lang}.md"),
            $dirname->withRelativePath("..")->withSubPath("_default.md"),
        ];

        foreach ($checkFiles as $file) {
            $file = phore_file($file);
            if ($file->exists()) {
                $content = $file->get_contents();
                $page = (new FrontmatterPageFactory())->parseString($content);
                $page->header["pid"] = $this->pid;
                $page->header["lang"] = $this->lang;
                $page->meta["orig_pid"] = null;
                return $page;
            }
        }
        throw new \InvalidArgumentException("Cannot find _default page for pid: '" . $this->pid . "'" );
    }

    public function exists() : bool
    {
        $path = $this->repo->rootPath->withSubPath($this->repo->_getStoreUri($this->pid, $this->lang));
        return $path->exists();
    }

    public function create() : FrontmatterPage
    {
        $page = new FrontmatterPage([], "");
        $page->header["pid"] = $this->pid;
        $page->header["lang"] = $this->lang;
        $page->meta["orig_pid"] = null;
        return $page;
    }

    public function remove() : void {
        $path = $this->repo->rootPath->withSubPath($this->repo->_getStoreUri($this->pid, $this->lang));
        if ( ! $path->exists())
            return;
        $path->asFile()->unlink();
    }



    public function get(bool $returnDefault = false) : FrontmatterPage
    {
        $path = $this->repo->rootPath->withSubPath($this->repo->_getStoreUri($this->pid, $this->lang));
        if ( ! $path->exists()) {
            if ($returnDefault)
                return $this->getDefault();
            throw new \InvalidArgumentException("Cannot find page: '" . $path->__toString() . "'");
        }
        $content = $path->assertFile()->get_contents();
        try {
            $page = (new FrontmatterPageFactory())->parseString($content);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Cannot parse page: '" . $path->__toString() . "': " . $e->getMessage());
        }
        $page->header["pid"] = $this->pid;
        $page->header["lang"] = $this->lang;
        $page->meta["orig_pid"] = $this->pid;
        return $page;
    }


    public function getAbsoluteStoreUri() : string
    {
        return $this->repo->rootPath . "/". $this->repo->_getStoreUri($this->pid, $this->lang);
    }


    public function isSystemPid() : bool
    {
        // Pid starts witch ~ _ or .
        return preg_match("/^[~_\.]/", $this->pid);
    }

    public function hasTmp() : bool {
        $path = $this->repo->rootPath->withSubPath($this->repo->_getStoreUri($this->pid, $this->lang, "~"));
        return $path->exists();
    }


    public function getStorageDir() : PhoreDirectory {
        return phore_dir($this->repo->rootPath->withSubPath(dirname($this->repo->_getStoreUri($this->pid, $this->lang))));
    }


    public function getTmp() : FrontmatterPage {
        $path = $this->repo->rootPath->withSubPath($this->repo->_getStoreUri($this->pid, $this->lang, "~"));
        if ( ! $path->exists()) {
            throw new \InvalidArgumentException("Cannot find page: " . $path->__toString());
        }
        $content = $path->assertFile()->get_contents();
        $page = (new FrontmatterPageFactory())->parseString($content);
        $page->header["pid"] = $this->pid;
        $page->header["lang"] = $this->lang;
        $page->meta["orig_pid"] = $this->pid;
        return $page;
    }

    public function setTmp(FrontmatterPage|null $page = null) : void {
        $path = $this->repo->rootPath->withSubPath($this->repo->_getStoreUri($this->pid, $this->lang, "~"));
        if ($page === null)
            $path->asFile()->unlink();
        else
            $path->asFile()->set_contents($page->toString());
    }


}
