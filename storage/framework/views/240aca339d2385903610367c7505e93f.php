<?php
    $allImgs = [];
    if ($listing->cover_image) {
        $src = $listing->cover_image;
        if (!str_starts_with($src,'data:') && !str_starts_with($src,'http')) $src = img($src);
        $allImgs[] = $src;
    }
    if (is_array($listing->images)) {
        foreach ($listing->images as $img) {
            if (!$img || $img === $listing->cover_image) continue;
            $src = $img;
            if (!str_starts_with($src,'data:') && !str_starts_with($src,'http')) $src = img($src);
            $allImgs[] = $src;
        }
    }
    $totalImgs = count($allImgs);
?>
<tr>
    <td><input type="checkbox" class="row-checkbox" value="<?php echo e($listing->id); ?>"></td>
    <td><?php echo e($listing->id); ?></td>
    <td><?php echo e($listing->type); ?></td>
    <td style="min-width:100px;">
        <?php if($totalImgs > 0): ?>
            <div style="position:relative; width:80px; height:70px; display:inline-block; border-radius:8px; overflow:hidden; border:2px solid #e2e8f0;">
                <img src="<?php echo e($allImgs[0]); ?>" alt="Aperçu"
                     style="width:100%; height:100%; object-fit:cover;"
                     onerror="this.style.display='none'">
                <?php if($totalImgs > 1): ?>
                <span style="position:absolute; bottom:4px; right:4px; background:rgba(0,0,0,0.7); color:#fff; font-size:10px; font-weight:700; padding:2px 6px; border-radius:10px; line-height:1; z-index:10;">
                    <i class="fa fa-camera"></i> <?php echo e($totalImgs); ?>

                </span>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <span class="text-muted">Aucune</span>
        <?php endif; ?>
    </td>
    <td>
        <a href="/marketplace/<?php echo e($listing->id); ?>" target="_blank" style="color:inherit;font-weight:600;"><?php echo e($listing->title); ?></a>
        <br>
        <small class="text-muted">Par: 
            <?php if($listing->user): ?>
                <?php echo e($listing->user->first_name . ' ' . $listing->user->last_name); ?>

            <?php elseif($listing->owner_phone): ?>
                <?php echo e(($listing->whatsappMessage && $listing->whatsappMessage->sender) ? $listing->whatsappMessage->sender->name . ' (' . $listing->owner_phone . ')' : $listing->owner_phone); ?>

            <?php else: ?>
                Inconnu
            <?php endif; ?>
        </small>
    </td>
    <td><?php echo e($listing->category); ?></td>
    <td><?php echo e(number_format((float)$listing->price, 0, ',', ' ')); ?> FCFA</td>
    <td><?php echo e($listing->created_at->format('d/m/Y H:i')); ?></td>
    <td>
        <form action="<?php echo e(route('admin.marketplace-listings.destroy', $listing->id)); ?>" method="POST" style="display:inline-block;">
            <?php echo e(csrf_field()); ?>

            <?php echo e(method_field('DELETE')); ?>

            <a href="<?php echo e(route('admin.marketplace-listings.edit', $listing->id)); ?>" class="btn btn-info btn-sm"><i class="fa fa-pencil"></i> Éditer</a>
            <a href="<?php echo e(url('admin/marketplace-listings/'.$listing->id.'/photos')); ?>" class="btn btn-warning btn-sm" title="Gérer les photos (<?php echo e(count((array)$listing->images)); ?>)">
                <i class="fa fa-camera"></i> Photos
                <?php if(count((array)$listing->images) > 0): ?>
                    <span class="badge" style="background:#fff;color:#333;"><?php echo e(count((array)$listing->images)); ?></span>
                <?php endif; ?>
            </a>
            <button class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr ?')"><i class="fa fa-trash"></i> Supprimer</button>
        </form>
        
        <?php if($is_pending): ?>
        <hr style="margin:5px 0;">
        <form action="<?php echo e(route('admin.marketplace-listings.approve', $listing->id)); ?>" method="POST" style="display:inline-block;">
            <?php echo e(csrf_field()); ?>

            <button class="btn btn-success btn-sm"><i class="fa fa-check"></i> Valider</button>
        </form>
        <form action="<?php echo e(route('admin.marketplace-listings.reject', $listing->id)); ?>" method="POST" style="display:inline-block;">
            <?php echo e(csrf_field()); ?>

            <button class="btn btn-warning btn-sm" onclick="return confirm('Voulez-vous rejeter cette annonce ?')"><i class="fa fa-times"></i> Rejeter</button>
        </form>
        <?php endif; ?>
    </td>
</tr>
<?php /**PATH /app/resources/views/admin/marketplace/listings/_listing_row.blade.php ENDPATH**/ ?>