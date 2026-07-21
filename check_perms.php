<?php
$files = [
    '/app/resources/views/admin/marketplace/listings/index.blade.php',
    '/app/resources/views/admin/whatsapp/index.blade.php'
];

foreach ($files as $f) {
    echo "File: $f\n";
    echo "Exists: " . (file_exists($f) ? 'yes' : 'no') . "\n";
    echo "Readable: " . (is_readable($f) ? 'yes' : 'no') . "\n";
    if (file_exists($f)) {
        echo "Perms: " . sprintf('%o', fileperms($f) & 0777) . "\n";
        echo "Owner ID: " . fileowner($f) . "\n";
        echo "Group ID: " . filegroup($f) . "\n";
    }
    echo "-------------------\n";
}
