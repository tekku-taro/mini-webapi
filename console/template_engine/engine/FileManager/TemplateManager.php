<?php
namespace Engine\FileManager;

/**
 * TemplateManager class
 *
 * テンプレートファイル入出力クラス
 */
class TemplateManager
{
    /**
     * テンプレートフォルダのパス
     *
     * @var string
     */
    protected $templateDir;

    /**
     * 出力フォルダのパス
     *
     * @var string
     */
    protected $outputDir;

    /**
     * テンプレートと出力フォルダのパスを設定
     *
     * @param string $templateDir
     * @param string $outputDir
     */
    public function __construct($templateDir, $outputDir = null)
    {
        $this->templateDir = $templateDir;
        $this->outputDir = $outputDir ? $outputDir:(dirname(__DIR__) . "/../../output");
    }

    /**
     * ファイルの内容を読み込む
     *
     * @param string $templateName
     * @return mixed
     */
    public function load($templateName)
    {
        $filepath = $this->templateDir . "/" . $templateName;

        if (is_readable($filepath)) {
            return file_get_contents($filepath);
        }

        return false;
    }

    /**
     * $contentの内容をファイルに保存する
     *
     * @param string $filename
     * @param string $content
     * @return mixed
     */
    public function save($filename, $content)
    {
        $filepath = $this->outputDir . "/" . $filename;
        return file_put_contents($filepath, $content);
    }
}
