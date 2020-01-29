<?php
namespace Engine\FileRender;

use Engine\FileManager\TemplateManager;
use Engine\Conversion\Converter;
use ErrorException;

/**
 * Renderer class
 *
 * テンプレートのレンダリングクラス
 */
class Renderer
{
    /**
     * TemplateManagerオブジェクト
     *
     * @var TemplateManager
     */
    protected $fileManager;

    /**
     * TemplateManagerオブジェクトを取得
     *
     * @param TemplateManager $fileManager
     */
    public function __construct(TemplateManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    /**
     * テンプレートを取得し、変数で変換、結果を返す
     *
     * @param string $template
     * @param array $vars
     * @return string $outcome
     */
    public function render(string $template, $vars)
    {
        $content = $this->getTemplate($template);

        $converter = new Converter($content, $vars, $template, $this->fileManager);

        $outcome = $converter->convert();

        return $outcome;
    }


    /**
     * テンプレートの内容を取得
     *
     * @param string $template
     * @return mixed
     */
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
