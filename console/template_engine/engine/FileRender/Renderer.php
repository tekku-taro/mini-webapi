<?php
namespace Engine\FileRender;

use Engine\FileManager\TemplateManager;
use Engine\Conversion\Converter;
use ErrorException;

class Renderer
{
    protected $fileManager;
    public function __construct(TemplateManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    public function render(string $template, $vars)
    {
        $content = $this->getTemplate($template);

        $converter = new Converter($content, $vars, $template);

        $outcome = $converter->convert();

        return $outcome;
    }


    protected function getTemplate($template)
    {
        $content = $this->fileManager->load($template . ".twig");

        if ($content) {
            return $content;
        } else {
            throw new ErrorException("there was no template data.");
        }
    }
}
