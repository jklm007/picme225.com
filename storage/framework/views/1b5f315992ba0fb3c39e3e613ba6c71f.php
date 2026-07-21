<?php echo '<?' . 'xml version="1.0" encoding="UTF-8"?' . '>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <?php $__currentLoopData = $urls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <url>
        <loc><?php echo e($url['loc']); ?></loc>
        <lastmod><?php echo e(date('Y-m-d')); ?></lastmod>
        <changefreq><?php echo e($url['changefreq'] ?? 'weekly'); ?></changefreq>
        <priority><?php echo e($url['priority'] ?? '0.5'); ?></priority>
    </url>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</urlset>
<?php /**PATH /app/resources/views/marketing/sitemap.blade.php ENDPATH**/ ?>