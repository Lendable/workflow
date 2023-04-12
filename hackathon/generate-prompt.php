<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

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

use SebastianBergmann\CodeCoverage\CodeCoverage;

$mutationData = json_decode(file_get_contents(__DIR__ . '/../infection-log.json'), true, 512, \JSON_THROW_ON_ERROR);

$mutant = current($mutationData['escaped']);

/** @var CodeCoverage $coverageData */
$coverageData = include __DIR__ . '/../coverage.php';

$tests = $coverageData->getData()->lineCoverage()[$mutant['mutator']['originalFilePath']][$mutant['mutator']['originalStartLine'] ?? []];

echo $mutant['mutator']['mutatorName'] . PHP_EOL;