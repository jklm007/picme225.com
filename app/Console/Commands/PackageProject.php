<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class PackageProject extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:package 
                            {--production : Generate a minimal ZIP without vendor and .env} 
                            {--full : Generate a complete ZIP including vendor and .env}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a clean and optimized ZIP archive of the Laravel project';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mode = $this->option('full') ? 'full' : 'production';
        
        $this->info("🚀 Starting Laravel Packaging in [{$mode}] mode...");

        // 🟢 5. OBLIGATORY VERIFICATIONS
        if (!$this->verifyStructure()) {
            return 1;
        }

        $appName = strtolower(config('app.name', 'laravel'));
        $timestamp = date('Ymd_His');
        $zipName = "{$appName}_{$timestamp}_{$mode}.zip";
        $tempPath = storage_path("app/{$zipName}");
        $finalPath = base_path($zipName);

        $this->info("🔍 Preparing to create: {$zipName}");

        $zip = new ZipArchive();

        if ($zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            $this->error("❌ Could not create ZIP file in storage.");
            return 1;
        }

        $basePath = base_path();
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $excludedPatterns = [
            '#\.save$#',
            '#\.save\.php$#',
            '#^[^/]+\.txt$#', // Exclude root .txt files (dev junk)
            '#^[^/]+\.log$#', // Exclude root .log files
            '#\.bak$#',
            '#^\.git#',
            '#^\.vscode#',
            '#^node_modules#',
            '#^tests#',
            '#^storage/logs/.+#',
            '#^storage/framework/(cache|sessions|testing|views)/.+#',
            '#^backups#',
            '#^dumps#',
            '#\.zip$#',
        ];

        if ($mode === 'production') {
            $excludedPatterns[] = '/^vendor/';
            $excludedPatterns[] = '/^\.env$/';
            $this->warn("⚠️  Mode Production: 'vendor/' and '.env' will be excluded.");
        } else {
            $this->warn("📦 Mode Full: 'vendor/' and '.env' will be included.");
        }

        $count = 0;
        foreach ($files as $name => $file) {
            $filePath = $file->getRealPath();
            $relativePath = str_replace('\\', '/', substr($filePath, strlen($basePath) + 1));

            // Skip if it matches any excluded pattern
            if ($this->shouldExclude($relativePath, $excludedPatterns)) {
                // Ensure we still keep the directory structure for storage
                if ($file->isDir() && str_starts_with($relativePath, 'storage/')) {
                    $zip->addEmptyDir($relativePath);
                }
                continue;
            }

            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($filePath, $relativePath);
                $count++;
            }
        }

        $zip->close();

        if (file_exists($tempPath)) {
            rename($tempPath, $finalPath);
            $size = number_format(filesize($finalPath) / 1048576, 2);
            $this->info("✅ Success! Archive created: {$zipName}");
            $this->info("📊 Total files: {$count}");
            $this->info("📊 Final size: {$size} MB");
        } else {
            $this->error("❌ Failed to finalize ZIP file.");
        }

        return 0;
    }

    /**
     * Verify if the current directory is a valid Laravel project.
     */
    private function verifyStructure()
    {
        $required = ['artisan', 'app', 'config', 'routes'];
        foreach ($required as $item) {
            if (!file_exists(base_path($item))) {
                $this->error("❌ Error: '{$item}' not found. This command must be run from a Laravel root.");
                return false;
            }
        }
        $this->info("✅ Structure validated.");
        return true;
    }

    /**
     * Check if a path should be excluded based on patterns.
     */
    private function shouldExclude($path, $patterns)
    {
        // Normalize path for Windows/Linux consistency
        $path = str_replace('\\', '/', $path);
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $path)) {
                return true;
            }
        }
        return false;
    }
}
