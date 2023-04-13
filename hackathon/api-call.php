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

$currentFile = 'StateMachine';

//$fileData = $promptGenerator->generate($currentFile);

shell_exec("vendor/bin/infection --filter=".$currentFile);
$fileData = $promptGenerator->generate($currentFile);

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

$unitResult = shell_exec("vendor/bin/phpunit --filter=".$currentFile);
$infectionResult = shell_exec("vendor/bin/infection --filter=".$currentFile);

$phpunitPattern = '/OK\s+\((\d+)\s+tests,\s+(\d+)\s+assertions\)/';
$infectionPattern = '/(\d+)\s+mutants\s+were\s+killed/';

preg_match($infectionPattern, $infectionResult, $infectionMatches);
preg_match($phpunitPattern, $unitResult, $unitMatches);

$testsBefore = $unitMatches[1];
$assertionsBefore = $unitMatches[2];
$mutationsBefore = $infectionMatches[1];

echo PHP_EOL;
echo "Tests before running AI:" . PHP_EOL;
echo \sprintf('PHPUNIT tests: %d, Assertions: %d', $testsBefore, $assertionsBefore) . PHP_EOL;
echo \sprintf('INFECTION mutators killed: %d', $mutationsBefore) . PHP_EOL;
echo PHP_EOL;

echo "Running AI..." . PHP_EOL;
$content = $api->getResponse($request) . PHP_EOL;

if (str_contains($content, 'EQUIVALENT')) {
    die('AI Could not make it any better, we are safe!');
}

$fileName = $fileManager->writeFunction($content);
echo "Added test to " . $fileName . "Running tests" . PHP_EOL;

$unitResult = shell_exec("vendor/bin/phpunit --filter=".$currentFile);
$infectionResult = shell_exec("vendor/bin/infection --filter=".$currentFile);

preg_match($infectionPattern, $infectionResult, $infectionMatches);
preg_match($phpunitPattern, $unitResult, $unitMatches);

if (!array_key_exists(1, $unitMatches)) {
    echo "Something went wrong, reverting changes";
    $fileManager->restoreFile($fileName);
    die();
}

$tests = $unitMatches[1];
$assertions = $unitMatches[2];
$mutations = $infectionMatches[1];

echo "Tests after running AI:" . PHP_EOL;
echo \sprintf('PHPUNIT tests: %d, Assertions: %d', $tests, $assertions) . PHP_EOL;
echo \sprintf('INFECTION mutators killed: %d', $mutations) . PHP_EOL;

if ($assertionsBefore < $assertions) {
    echo sprintf('assertions increased from %d to %d', $assertionsBefore, $assertions) . PHP_EOL;
}

if ($mutations > $mutationsBefore) {
    echo sprintf('Mutators amount increased from %d to %d, accepting changes', $mutationsBefore, $mutations) . PHP_EOL;
    // TODO commit changes XD

    $fileManager->acceptFile($fileName);
} else {
    echo "Mutators amount didnt change, reverting changes";
    $fileManager->restoreFile($fileName);
}
