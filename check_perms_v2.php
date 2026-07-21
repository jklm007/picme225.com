<?php
$paths = [
    '/app/resources/views/admin',
    '/app/resources/views/admin/marketplace',
    '/app/resources/views/admin/marketplace/listings',
    '/app/resources/views/admin/marketplace/listings/index.blade.php',
];

foreach ($paths as $p) {
    echo "Path: $p\n";
    echo "Exists: " . (file_exists($p) ? 'yes' : 'no') . "\n";
    if (file_exists($p)) {
        echo "Perms: " . sprintf('%o', fileperms($p) & 0777) . "\n";
        echo "Owner ID: " . fileowner($p) . "\n";
        echo "Group ID: " . filegroup($p) . "\n";
        
        // Check if application user (1000) can read/execute
        $owner = fileowner($p);
        $group = filegroup($p);
        $perms = fileperms($p);
        
        $is_readable = false;
        if ($owner == 1000) {
            $is_readable = ($perms & 0400) ? true : false;
        } elseif ($group == 1000) {
            $is_readable = ($perms & 0040) ? true : false;
        } else {
            $is_readable = ($perms & 0004) ? true : false;
        }
        
        $is_executable = false;
        if (is_dir($p)) {
            if ($owner == 1000) {
                $is_executable = ($perms & 0100) ? true : false;
            } elseif ($group == 1000) {
                $is_executable = ($perms & 0010) ? true : false;
            } else {
                $is_executable = ($perms & 0001) ? true : false;
            }
        }
        
        echo "Readable by application (UID 1000): " . ($is_readable ? 'yes' : 'no') . "\n";
        if (is_dir($p)) {
            echo "Traversable (executable) by application (UID 1000): " . ($is_executable ? 'yes' : 'no') . "\n";
        }
    }
    echo "-------------------\n";
}
