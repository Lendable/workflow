<?php

declare(strict_types=1);

namespace Symfony\Component\Workflow\Mutator;

class MutatorData
{
    public function __construct(
        private readonly string $mutatedSourceCode,
        private readonly string $originalSourceCode,
        private readonly string $mutatedFilePath,
        private readonly string $diff,
        private readonly string $processOutput,
        private readonly array $testsSourceCode,
    ) {
    }

    public function getMutatedSourceCode(): string
    {
        return $this->mutatedSourceCode;
    }

    public function getOriginalSourceCode(): string
    {
        return $this->originalSourceCode;
    }

    public function getMutatedFilePath(): string
    {
        return \str_replace('/app/', '', $this->mutatedFilePath);
    }

    public function getDiff(): string
    {
        return $this->diff;
    }

    public function getProcessOutput(): string
    {
        return $this->processOutput;
    }

    /**
     * @return array<string, string>
     */
    public function getTestsSourceCode(): array
    {
        return $this->testsSourceCode;
    }
}