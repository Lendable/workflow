<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Workflow\Api\OpenAiApi;
use Symfony\Component\Workflow\Generator\PromptGenerator;

$api = new OpenAiApi(HttpClient::create());

$promptGenerator = new PromptGenerator();

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

echo $api->getResponse($request) . PHP_EOL;
