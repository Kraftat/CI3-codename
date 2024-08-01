<?php if (is_superadmin_loggedin()): ?>
    <section class="panel">
        <header class="panel-heading">
            <h4 class="panel-title"><?= translate('select_ground') ?></h4>
        </header>
        <?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
        <div class="panel-body">
            <div class="row mb-sm">
                <div class="col-md-offset-3 col-md-6">
                    <div class="form-group">
                        <label class="control-label"><?= translate('branch') ?> <span class="required">*</span></label>
                        <?php
                            $arrayBranch = $this->appLib->getSelectList('branch');
                            echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id', $this->session->userdata('selected_branch_id')), "class='form-control'
                            data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-offset-10 col-md-2">
                    <button type="submit" name="search" value="1" class="btn btn-default btn-block"> <i class="far fa-filter"></i> <?= translate('filter') ?></button>
                </div>
            </div>
        </footer>
        <?= form_close(); ?>
    </section>
<?php endif; ?>

<section class="panel">
    <div class="tabs-custom">
        <ul class="nav nav-tabs">
            <li class="<?php echo (!isset($validation_error) ? 'active' : ''); ?>">
                <a href="#list" data-toggle="tab"><i class="far fa-list-ul"></i> <?php echo translate('role') . " " . translate('list'); ?></a>
            </li>
            <li class="<?php echo (isset($validation_error) ? 'active' : ''); ?>">
                <a href="#create" data-toggle="tab"><i class="far fa-edit"></i> <?php echo translate('create') . " " . translate('role'); ?></a>
            </li>
        </ul>
        <div class="tab-content">
            <div id="list" class="tab-pane <?php echo (!isset($validation_error) ? 'active' : ''); ?>">
                <div class="mb-md">
                    <table class="table table-bordered table-hover table-condensed table_default">
                        <thead>
                            <tr>
                                <th><?php echo translate('sl'); ?></th>
                                <th><?php echo translate('role') . " " . translate('name'); ?></th>
                                <?php if (is_superadmin_loggedin()): ?>
                                    <th><?php echo translate('branch_id'); ?></th>
                                <?php endif; ?>
                                <th><?php echo translate('action'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($roles)) { 
                                $count = 1; 
                                foreach ($roles as $row): ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <td><?php echo $row['name']; ?></td>
                                    <?php if (is_superadmin_loggedin()): ?>
                                        <td><?php echo $row['branch_id']; ?></td>
                                    <?php endif; ?>
                                    <td class="min-w-xs">
                                        <?php if (!$row['is_system'] && ($row['branch_id'] == get_loggedin_branch_id() || is_superadmin_loggedin())) { ?>
                                            <a class="btn btn-default btn-circle icon" data-toggle="tooltip" data-original-title="<?php echo translate('edit'); ?>" href="<?php echo base_url('branch_role/edit/' . $row['id']); ?>"><i class="far fa-pen-nib"></i></a>
                                            <a class="btn btn-default btn-circle" href="<?php echo base_url('branch_role/permission/' . $row['id']); ?>"><i class="fab fa-buromobelexperte"></i> <?php echo translate('permission'); ?></a>
                                            <?php echo btn_delete('branch_role/delete/' . $row['id']); ?>
                                        <?php } else { ?>
                                            <?php if (!is_superadmin_loggedin()) { ?>
                                                <span> </span>
                                            <?php } ?>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php endforeach; } else { ?>
                                <tr>
                                    <td colspan="4" class="text-center"><?php echo translate('no_records_found'); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane <?php echo (isset($validation_error) ? 'active' : ''); ?>" id="create">
                <?php echo form_open($this->uri->uri_string(), array('class' => 'form-horizontal')); ?>
                    <div class="form-group <?php if (form_error('role')) echo 'has-error'; ?>">
                        <label class="col-md-3 control-label"><?php echo translate('role') . " " . translate('name'); ?> <span class="required">*</span></label>
                        <div class="col-md-6 mb-sm">
                            <input type="text" class="form-control" name="role" value="<?php echo set_value('role'); ?>">
                            <span class="error"><?php echo form_error('role'); ?></span>
                        </div>
                    </div>
                    
                    <footer class="panel-footer mt-lg">
                        <div class="row">
                            <div class="col-md-2 col-md-offset-3">
                                <button type="submit" name="save" value="1" class="btn btn-default btn-block"><i class="far fa-plus-circle"></i> <?php echo translate('save'); ?></button>
                            </div>
                        </div>  
                    </footer>
                <?= form_close(); ?>
            </div>
        </div>
    </div>
</section>
