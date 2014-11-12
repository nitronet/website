<?php
namespace Nitronet\Blog;

use FwkWWW\DataSource;
use Nitronet\Blog\Exceptions\BlogException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class BlogDataSource implements DataSource
{
    const DIR_DRAFTS = 'drafts';
    const DIR_ARTICLES = 'articles';
    const DIR_DATA = 'data';

    protected $blogDir;

    public function __construct($blogDir)
    {
        $this->blogDir = realpath($blogDir);
        if ($this->blogDir === false || !is_dir($this->blogDir)) {
            throw new BlogException(sprintf('Invalid blog path: %s', $blogDir));
        }
    }

    public function fetch(array $options = array())
    {
        $finder = new Finder();
        $finder->ignoreVCS(true)
                ->files()
                ->sortByName()
                ->name('*.md')
                ->name('*.markdown')
                ->name('*.php')
                ->name('*.html');

        if (isset($options['category'])) {
            $finder->contains('/category:\s+'. $options['category'] .'/');
        } else if (isset($options['slug'])) {
            $finder->contains('/slug:\s+'. $options['slug'] .'/i');
        }

        $articles = array();
        foreach ($finder->in($this->pathUtil(array(self::DIR_ARTICLES))) as $file) {
            $articles[] = $this->fileToArticle($file);
        }

        return $articles;
    }

    public function one($slug)
    {
        $results = $this->fetch(array('slug' => $slug));
        foreach ($results as $article) {
            return array($article);
        }

        return array();
    }

    public function data($fileName)
    {
        return Yaml::parse($this->pathUtil(array(self::DIR_DATA, $fileName)));
    }

    protected function pathUtil(array $paths)
    {
        return $this->blogDir . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $paths);
    }

    protected function fileToArticle(SplFileInfo $fileInfo)
    {
        return new Article($fileInfo);
    }
}