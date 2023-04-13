<?php

declare(strict_types=1);

namespace Symfony\Component\Workflow\Manager;

class FileManager {
    private string $codePattern = '/```php\s+(.*?)\s+```/s';

    private string $fileNamePattern = '/Filename:\s*(.*)/';

    /**
     * @var array $files
     */
    private array $files = [];

    public function __construct() {}

    public function writeFunction(string $content) {
        preg_match($this->fileNamePattern, $content, $fileNameMatches);

        $fileName = \str_replace('/app/', './', $fileNameMatches[1]);

        $file = file_get_contents($fileName) or die('file not found ' . $fileName);

        $this->files[$fileName] = $file;

        $file = rtrim($file, '}');

        $file = $this->removeLastBrace($file);

        preg_match($this->codePattern, $content, $matches);

        $method = str_replace("\n", "\n\t", $matches[1].PHP_EOL);;
        $method = substr($method, 0, -1);


        file_put_contents($fileName, '');

        $data =<<<EOF
$file   $method}

EOF;

        file_put_contents($fileName, $data);

        return $fileName;
    }

    public function restoreFile(string $fileName) {
        if (isset($this->files[$fileName])) {
            file_put_contents($fileName, $this->files[$fileName]);
        }
    }

    public function acceptFile(string $fileName) {
        if (isset($this->files[$fileName])) {
            unset($this->files[$fileName]);
        }
    }

    public function removeLastBrace(string $file) {
        $pattern = '/\}(?=[^\}]*$)/';
        $replacement = '$1';

        return preg_replace($pattern, $replacement, $file);
    }
}