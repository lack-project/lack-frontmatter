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



    public function exists() : bool
    {
        $path = $this->repo->rootPath->withSubPath($this->repo->getStoreUri($this->pid, $this->lang));
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

    public function get() : FrontmatterPage
    {
        $path = $this->repo->rootPath->withSubPath($this->repo->getStoreUri($this->pid, $this->lang));
        if ( ! $path->exists())
            throw new \InvalidArgumentException("Cannot find page: " . $path->__toString());
        $content = $path->get_contents();
        $page = (new FrontmatterPageFactory())->parseString($content);
        $page->header["pid"] = $this->pid;
        $page->header["lang"] = $this->lang;
        $page->meta["orig_pid"] = $this->pid;
        return $page;
    }

}
