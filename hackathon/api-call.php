<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Workflow\Api\OpenAiApi;
use Symfony\Component\Workflow\Generator\PromptGenerator;
use Symfony\Component\Workflow\Manager\FileManager;

$api = new OpenAiApi(HttpClient::create());

$promptGenerator = new PromptGenerator();
$fileManager = new FileManager();

$fileData = $promptGenerator->generate('DefinitionBuilder');

if ($fileData === null) {
    die("No data found");
}

$request = <<<EOF
# Source code of mutated `{$fileData->getMutatedFilePath()}` file

```php
{$fileData->getOriginalSourceCode()}
```

# Diff of mutant changes is

```php
{$fileData->getDiff()}
```

# Current unit tests for this class
EOF;

foreach ($fileData->getTestsSourceCode() as $filename => $sourceCode) {
    $request .= <<<EOF

Filename {$filename} 

```php
{$sourceCode}
```

EOF;
}

file_put_contents('request.md', $request);

//$content = file_get_contents('response.txt');
$content = $api->getResponse($request) . PHP_EOL;

if (str_contains($content, 'EQUIVALENT')) {
    die('EQUIVALENT');
}

$fileName = $fileManager->writeFunction($content);

die('Added test to ' . $fileName);
// run tests
//if test failed restore file $fileManager->restoreFile($fileName);
//if test passed accept file $fileManager->acceptFile($fileName);
