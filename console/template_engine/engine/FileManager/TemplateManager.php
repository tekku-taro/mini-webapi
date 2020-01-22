<?php
namespace Engine\FileManager;

class TemplateManager
{
    protected $templateDir;
    protected $outputDir;

    public function __construct($templateDir, $outputDir = null)
    {
        $this->templateDir = $templateDir;
        $this->outputDir = $outputDir ? $outputDir:(dirname(__DIR__) . "/../../output");
    }

    public function load($templateName)
    {
        $filepath = $this->templateDir . "/" . $templateName;

        if (is_readable($filepath)) {
            return file_get_contents($filepath);
        }
    }

    public function save($filename, $content)
    {
        $filepath = $this->outputDir . "/" . $filename;
        file_put_contents($filepath, $content);
    }
}
