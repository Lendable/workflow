<?php

namespace Symfony\Component\Workflow\Generator;

use Symfony\Component\Workflow\Mutator\MutatorData;

class PromptGenerator
{
    public function generate(string $className): ?MutatorData
    {
        /** @var array{
         *   escaped: non-empty-list<
         *    array{
         *     mutator: array{
         *      mutatorName: string,
         *      originalSourceCode: string,
         *      mutatedSourceCode: string,
         *      originalFilePath: string,
         *      originalStartLine: int
         *     },
         *     diff: string,
         *     processOutput: string
         *    }
         *   >
         *  } $mutationData
         */

        $mutationData = json_decode(file_get_contents(__DIR__ . '/../infection-log.json'), true, 512, \JSON_THROW_ON_ERROR);

        $coverageData = include __DIR__ . '/../coverage.php';

        foreach ($mutationData['escaped'] as $index => $mutant) {
            if ($index <= 0) {
                continue;
            }

            if (\str_ends_with($mutant['mutator']['originalFilePath'], $className . '.php')) {
                $lineCoverage = $coverageData->getData()->lineCoverage();
                $data = [];

                if (isset($lineCoverage[$mutant['mutator']['originalFilePath']])) {
                    if (isset($lineCoverage[$mutant['mutator']['originalFilePath']][$mutant['mutator']['originalStartLine']])) {
                        $data = $lineCoverage[$mutant['mutator']['originalFilePath']][$mutant['mutator']['originalStartLine']];
                    } else {
                        foreach ($lineCoverage[$mutant['mutator']['originalFilePath']] as $line => $data2) {
                            $data += $data2;
                        }
                    }

                    $data = \array_unique($data);
                }

                $testClass = new \ReflectionClass(\explode('::', $data[0])[0]);

                $testsSourceCode[$testClass->getFileName()] = file_get_contents($testClass->getFileName());

                foreach ($testClass->getTraits() as $trait) {
                    $testsSourceCode[$trait->getFileName()] = file_get_contents($trait->getFileName());
                }

                return new MutatorData(
                    $mutant['mutator']['originalSourceCode'],
                    $mutant['mutator']['mutatedSourceCode'],
                    $mutant['mutator']['originalFilePath'],
                    $mutant['diff'],
                    $mutant['processOutput'],
                    $testsSourceCode,
                );
            }
        }

        return null;
    }
}