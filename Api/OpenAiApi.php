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