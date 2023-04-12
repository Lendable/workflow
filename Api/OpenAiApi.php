<?php

declare(strict_types=1);

namespace Symfony\Component\Workflow\Api;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAiApi
{
    public function __construct(
        private readonly HttpClientInterface $httpClient
    )
    {

    }

    public function getResponse(string $content): string
    {
        $response = $this->httpClient->request(
            'POST',
            'https://api.openai.com/v1/chat/completions',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => \sprintf('Bearer %s', $_ENV['OPENAI_KEY']),
                ],
                'json' => [
                    "model" => "gpt-4",
                    "messages" => [
                        [
                            "role" => "system",
                            "content" => "
                                You are a PHP coding assistant that needs to kill mutants that are a result of mutation testing. Make sure to evaluate if that mutant is not equivalent and whether it makes sense to solve it. If it doesn't, let the user know.
                                The user will supply you with the source code, the diff of the changed code, and the tests covering the changed code.
                                Do not use Reflection, only test the observable behaviour by calling and accessing public methods and properties. Private methods and properties are internal and we should not base our assertions on those.
                                If the mutant is considered equivalent or does not make sense to fix it, reply with \"EQUIVALENT\" and do not include any other sentences. If not, generate the test case method that kills the mutant, including the header with the test file name
                            "
                        ],
                        [
                            "role" => "user",
                            "content" => $content,
                        ]
                    ],
                    "temperature" => 0,
                ]
            ],
        );

        $content = \json_decode($response->getContent(), true);

        return $content['choices'][0]['message']['content'];
    }
}