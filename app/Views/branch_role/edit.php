<section class="panel">
    <div class="tabs-custom">
        <ul class="nav nav-tabs">
            <li>
                <a href="<?php echo base_url('branch_role'); ?>"><i class="far fa-list-ul"></i> <?php echo translate('role') . " " . translate('list'); ?></a>
            </li>
            <li class="active">
                <a href="#create" data-toggle="tab"><i class="far fa-edit"></i> <?php echo translate('edit') . " " . translate('role'); ?></a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="create">
                <?php echo form_open($this->uri->uri_string(), array('class' => 'form-horizontal')); ?>
                <input type="hidden" name="id" value="<?php echo $role['id']; ?>">
                <div class="form-group <?php if (form_error('role')) echo 'has-error'; ?>">
                    <label class="col-md-3 control-label"><?php echo translate('role') . " " . translate('name'); ?> <span class="required">*</span></label>
                    <div class="col-md-6 mb-sm">
                        <input type="text" class="form-control" name="role" value="<?php echo set_value('role', $role['name']); ?>">
                        <span class="error"><?php echo form_error('role'); ?></span>
                    </div>
                </div>
                
                <?php if (is_superadmin_loggedin()): ?>
                    <div class="form-group <?php if (form_error('branch_id')) echo 'has-error'; ?>">
                        <label class="col-md-3 control-label"><?php echo translate('branch'); ?> <span class="required">*</span></label>
                        <div class="col-md-6 mb-sm">
                            <?php
                                $arrayBranch = $this->appLib->getSelectList('branch');
                                echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id', $role['branch_id']), "class='form-control' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
                            ?>
                            <span class="error"><?php echo form_error('branch_id'); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <footer class="panel-footer mt-lg">
                    <div class="row">
                        <div class="col-md-2 col-md-offset-3">
                            <button type="submit" name="save" value="1" class="btn btn-default btn-block"><i class="far fa-edit"></i> <?php echo translate('update'); ?></button>
                        </div>
                    </div>  
                </footer>
                <?= form_close(); ?>
            </div>
        </div>
    </div>
</section>
