<?php declare(strict_types=1);

use Chiiya\CodeStyle\CodeStyle;
use Rector\Config\RectorConfig;

require __DIR__.'/vendor/autoload.php';

return static function (RectorConfig $config): void {
    $config->import(CodeStyle::RECTOR);
    $config->paths([
        __DIR__.'/src',
    ]);
    $config->importNames();
};
