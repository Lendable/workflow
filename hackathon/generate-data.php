<?php

declare(strict_types=1);

`XDEBUG_MODE=coverage phpunit --filter="Validator/*" --coverage-php="coverage.php"`;

`infection --filter="Validator/"`;

