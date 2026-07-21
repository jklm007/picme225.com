<?php
$dir = 'c:/Users/HP/Desktop/Nouveau dossier/TDR/picme225.com_backend/vendor/phpunit/phpunit/src';

function processDir($dir) {
    if (!is_dir($dir)) return;
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            processDir($path);
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $content = file_get_contents($path);
            if (strpos($content, 'DefaultOutputPrinter') !== false) {
                $newContent = str_replace(
                    'DefaultOutputPrinter',
                    'DefaultPrinter',
                    $content
                );
                file_put_contents($path, $newContent);
                echo "Fixed side effect: $path\n";
            }
        }
    }
}

processDir($dir);
