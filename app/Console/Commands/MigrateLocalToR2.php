<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class MigrateLocalToR2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:migrate-r2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate local public and storage files to R2';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting migration to R2...');

        $s3 = Storage::disk('s3');

        // Migrate storage/app/public
        $storagePath = storage_path('app/public');
        if (File::exists($storagePath)) {
            $files = File::allFiles($storagePath);
            $this->info('Found ' . count($files) . ' files in storage/app/public');
            foreach ($files as $file) {
                $relativePath = $file->getRelativePathname();
                $this->info("Uploading {$relativePath}...");
                $s3->put($relativePath, file_get_contents($file->getRealPath()), 'public');
            }
        }

        // Migrate public/uploads
        $publicUploadsPath = public_path('uploads');
        if (File::exists($publicUploadsPath)) {
            $files = File::allFiles($publicUploadsPath);
            $this->info('Found ' . count($files) . ' files in public/uploads');
            foreach ($files as $file) {
                $relativePath = 'uploads/' . $file->getRelativePathname();
                $this->info("Uploading {$relativePath}...");
                $s3->put($relativePath, file_get_contents($file->getRealPath()), 'public');
            }
        }

        $this->info('Migration completed successfully!');
        return 0;
    }
}
