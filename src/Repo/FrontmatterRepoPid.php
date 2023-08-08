<?php

namespace Lack\Frontmatter\Repo;

use Lack\Frontmatter\FrontmatterPage;
use Lack\Frontmatter\FrontmatterPageFactory;

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


    public function __toString()
    {
        return $this->pid . " (Lang: " . $this->lang . ")";
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
        throw new \InvalidArgumentException("Cannot find _default page for pid: " . $this->pid);
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

    public function get(bool $returnDefault = false) : FrontmatterPage
    {
        $path = $this->repo->rootPath->withSubPath($this->repo->_getStoreUri($this->pid, $this->lang));
        if ( ! $path->exists()) {
            if ($returnDefault)
                return $this->getDefault();
            throw new \InvalidArgumentException("Cannot find page: " . $path->__toString());
        }
        $content = $path->assertFile()->get_contents();
        $page = (new FrontmatterPageFactory())->parseString($content);
        $page->header["pid"] = $this->pid;
        $page->header["lang"] = $this->lang;
        $page->meta["orig_pid"] = $this->pid;
        return $page;
    }
    
    
    public function hasTmp() : bool {
        $path = $this->repo->rootPath->withSubPath($this->repo->_getStoreUri($this->pid, $this->lang, "~"));
        return $path->exists();
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
