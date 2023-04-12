<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Workflow\Api\OpenAiApi;

$api = new OpenAiApi(HttpClient::create());

echo $api->getResponse('Hello, how are you?');
