<?php
namespace Nitronet\Blog;


use Michelf\MarkdownExtra;
use Nitronet\Blog\Exceptions\BlogException;
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
            $contents = $this->content = $this->getRawContent();
            if (strpos($contents, '---/metas---') !== false) {
                $this->content = trim(substr($contents, strpos($contents, '---/metas---') + strlen('---/metas---')));
                $this->generateContents();
            }
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

            if (strpos($contents, '---/metas---') !== false) {
                $metasContents = substr($contents, 0, strpos($contents, '---/metas---'));

                if (preg_match_all('/([a-z0-9\-_]+):\s+(.+)/i', $metasContents, $matches)) {
                    foreach ($matches[0] as $idx => $infos) {
                        $metas[$matches[1][$idx]] = $matches[2][$idx];
                    }
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

    protected function generateContents() {
        if (!isset($this->content) || empty($this->content)) {
           return;
        }

        $ext = strtolower($this->fileInfo->getExtension());
        switch($ext)
        {
            case "md":
            case "markdown":
                $this->content = MarkdownExtra::defaultTransform($this->content);
                break;

            case "php":
                $tmpFile = tempnam(sys_get_temp_dir(), 'nnblog');
                if (!$tmpFile) {
                    throw new BlogException(sprintf('Unable to create temp file: %s', $tmpFile));
                }

                file_put_contents($tmpFile, $this->content);
                ob_start();
                include $tmpFile;
                $this->content = ob_get_clean();
                unlink($tmpFile);
                break;

            case "html":


        }
    }
}