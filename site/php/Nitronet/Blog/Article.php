<?php
namespace Nitronet\Blog;


use Symfony\Component\Finder\SplFileInfo;

class Article
{
    const TYPE_DRAFT = 1;
    const TYPE_ARTICLE = 2;

    /**
     * @var SplFileInfo
     */
    protected $fileInfo;
    protected $type;
    protected $metas;
    protected $content;

    public function __construct(SplFileInfo $fileInfo, $type = self::TYPE_ARTICLE)
    {
        $this->fileInfo = $fileInfo;
        $this->type = (int)$type;
    }

    public function isPublished()
    {
        return ($this->type == self::TYPE_ARTICLE);
    }

    public function isDraft()
    {
        return ($this->type == self::TYPE_DRAFT);
    }

    public function getPublicationDate()
    {
        if ($this->isDraft()) {
            return null;
        }

        $filename = $this->fileInfo->getBasename();
        if (preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2})\-/', $filename, $matches)) {
            return \DateTime::createFromFormat('Y-m-d', $matches[1]);
        }

        return null;
    }

    public function getContent()
    {
        if (!isset($this->content)) {
            $contents = $this->getRawContent();
            $this->content = trim(substr($contents, strpos($contents, '---/metas---')+strlen('---/metas---')));
        }

        return $this->content;
    }


    public function meta($metaName, $default = false)
    {
        $metas = $this->getMetas();

        return array_key_exists($metaName, $metas) ? $metas[$metaName] : $default;
    }

    protected function getMetas()
    {
        if (!isset($this->metas)) {
            $metas = array();
            $contents = $this->getRawContent();
            $metasContents = substr($contents, 0, strpos($contents, '---/metas---'));

            if (preg_match_all('/([a-z0-9\-_]+):\s+(.+)/i', $metasContents, $matches)) {
                foreach ($matches[0] as $idx => $infos) {
                    $metas[$matches[1][$idx]] = $matches[2][$idx];
                }
            }

            $this->metas = $metas;
        }

        return $this->metas;
    }

    protected function getRawContent()
    {
        return $this->fileInfo->getContents();
    }
}