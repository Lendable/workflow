<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Workflow\Api\OpenAiApi;
use Symfony\Component\Workflow\Generator\PromptGenerator;

$api = new OpenAiApi(HttpClient::create());

$promptGenerator = new PromptGenerator();

$fileData = $promptGenerator->generate('Transition');

if ($fileData === null) {
    die("No data found");
}

$request = '# Source code: ' . PHP_EOL . $fileData->getMutatedFilePath() . PHP_EOL . $fileData->getOriginalSourceCode() . PHP_EOL;
$request .= '# Diff of mutant changes:' . PHP_EOL . $fileData->getDiff() . PHP_EOL;
$request .= '# Test covering the class:' . PHP_EOL . $fileData->getTestFilePath() . PHP_EOL . $fileData->getTestFileSourceCode() . PHP_EOL;

echo "Request: " . PHP_EOL . $request . PHP_EOL;

echo $api->getResponse($request) . PHP_EOL;
