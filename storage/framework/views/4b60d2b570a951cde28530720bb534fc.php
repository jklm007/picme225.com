<?php $__env->startSection('title', 'Marketplace Listings'); ?>
<?php $__env->startSection('content'); ?>
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <h5 class="mb-1">Annonces Marketplace</h5>
            <a href="<?php echo e(route('admin.marketplace-listings.create')); ?>" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Publier une annonce</a>
            
            <ul class="nav nav-tabs mt-2" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#pending" role="tab">En attente de validation (<?php echo e($pendingListings->count()); ?>)</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#active" role="tab">Toutes les annonces (<?php echo e($activeListings->count()); ?>)</a>
                </li>
            </ul>

            <div class="mb-2 mt-2">
                <form id="bulk-action-form" method="POST" action="<?php echo e(route('admin.marketplace-listings.bulk-action')); ?>">
                    <?php echo e(csrf_field()); ?>

                    <input type="hidden" name="action" id="bulk-action-input" value="">
                    <div id="hidden-ids-container"></div>
                    
                    <div class="btn-group">
                        <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="btn-bulk-action" disabled>
                            Actions Groupées (<span id="selected-count">0</span>)
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item bulk-action-btn" href="#" data-action="approve"><i class="fa fa-check text-success"></i> Approuver la sélection</a>
                            <a class="dropdown-item bulk-action-btn" href="#" data-action="reject"><i class="fa fa-times text-warning"></i> Rejeter la sélection</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item bulk-action-btn" href="#" data-action="delete"><i class="fa fa-trash text-danger"></i> Supprimer la sélection</a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="tab-content mt-2">
                <!-- ONGLET EN ATTENTE -->
                <div class="tab-pane active" id="pending" role="tabpanel">
                    <table class="table table-striped table-bordered dataTable" id="table-pending">
                        <thead>
                            <tr>
                                <th style="width: 40px;"><input type="checkbox" class="select-all"></th>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Image</th>
                                <th>Titre</th>
                                <th>Catégorie</th>
                                <th>Prix</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $pendingListings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $listing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo $__env->make('admin.marketplace.listings._listing_row', ['listing' => $listing, 'is_pending' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <!-- ONGLET ACTIVES -->
                <div class="tab-pane" id="active" role="tabpanel">
                    <table class="table table-striped table-bordered dataTable" id="table-active">
                        <thead>
                            <tr>
                                <th style="width: 40px;"><input type="checkbox" class="select-all"></th>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Image</th>
                                <th>Titre</th>
                                <th>Catégorie</th>
                                <th>Prix</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $activeListings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $listing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo $__env->make('admin.marketplace.listings._listing_row', ['listing' => $listing, 'is_pending' => false], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
$(document).ready(function() {
    function updateSelectedCount() {
        var count = $('.row-checkbox:checked').length;
        $('#selected-count').text(count);
        if(count > 0) {
            $('#btn-bulk-action').prop('disabled', false);
        } else {
            $('#btn-bulk-action').prop('disabled', true);
        }
    }

    $('.select-all').change(function() {
        // Toggle only the checkboxes in the currently active tab
        var isChecked = $(this).prop('checked');
        var $table = $(this).closest('table');
        $table.find('.row-checkbox').prop('checked', isChecked);
        
        // Also uncheck the other select-all if we uncheck
        if (!isChecked) {
            $('.select-all').prop('checked', false);
        } else {
            // Uncheck checkboxes in other tables to avoid confusion
            $('table').not($table).find('.row-checkbox').prop('checked', false);
            $('table').not($table).find('.select-all').prop('checked', false);
        }
        
        updateSelectedCount();
    });

    $('.row-checkbox').change(function() {
        var $table = $(this).closest('table');
        var total = $table.find('.row-checkbox').length;
        var checked = $table.find('.row-checkbox:checked').length;
        
        if(total == checked) {
            $table.find('.select-all').prop('checked', true);
        } else {
            $table.find('.select-all').prop('checked', false);
        }
        
        updateSelectedCount();
    });

    $('.bulk-action-btn').click(function(e) {
        e.preventDefault();
        var action = $(this).data('action');
        var count = $('.row-checkbox:checked').length;
        
        if (count === 0) return;
        
        var confirmMsg = "Êtes-vous sûr de vouloir appliquer cette action ?";
        if (action === 'delete') confirmMsg = "Êtes-vous sûr de vouloir SUPPRIMER DÉFINITIVEMENT ces " + count + " annonces ?";
        
        if (confirm(confirmMsg)) {
            $('#bulk-action-input').val(action);
            $('#hidden-ids-container').empty();
            
            $('.row-checkbox:checked').each(function() {
                $('#hidden-ids-container').append('<input type="hidden" name="selected_ids[]" value="'+$(this).val()+'">');
            });
            
            $('#bulk-action-form').submit();
        }
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.base', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /app/resources/views/admin/marketplace/listings/index.blade.php ENDPATH**/ ?>