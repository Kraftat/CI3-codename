<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <header class="panel-heading">
                <h4 class="panel-title"><?= translate('Registration Requests') ?></h4>
            </header>
            <?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
            <div class="panel-body">
                <div class="row mb-sm">
                    <div class="col-md-offset-3 col-md-6 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('date'); ?> <span class="required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="far fa-calendar-check"></i></span>
                                <input type="text" class="form-control daterange" name="daterange" value="<?= set_value('daterange', date("Y/m/d") . ' - ' . date("Y/m/d")); ?>" required />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-offset-3 col-md-6 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('status'); ?> <span class="required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="far fa-filter"></i></span>
                                <select class="form-control" name="status_filter">
                                    <option value=""><?= translate('all') ?></option>
                                    <option value="new"><?= translate('new') ?></option>
                                    <option value="pending"><?= translate('pending') ?></option>
                                    <option value="contacted"><?= translate('contacted') ?></option>
                                    <option value="converted"><?= translate('converted') ?></option>
                                    <option value="lost"><?= translate('lost') ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-offset-10 col-md-2">
                        <button type="submit" name="search" value="1" class="btn btn-default btn-block"><i class="far fa-filter"></i> <?= translate('filter') ?></button>
                    </div>
                </div>
            </footer>
            <?= form_close(); ?>
        </section>

        <?php if (isset($requests)): ?>
        <section class="panel">
            <header class="panel-heading">
                <h4 class="panel-title"><i class="far fa-list-ul"></i> <?= translate('registration_form_list') ?></h4>
            </header>
            <div class="panel-body mb-md">
                <table class="table table-bordered table-hover table-export">
                    <thead>
                    <tr>
                    <th><?= translate('sl') ?></th>
                    <th><?= translate('registration_id') ?></th>
                    <th><?= translate('name') ?></th>
                    <!-- <th><?= translate('organization_type') ?></th> -->
                    <th><?= translate('school_name') ?></th>
                    <th><?= translate('number_of_branches') ?></th>
                    <th><?= translate('number_of_students') ?></th>
                    <th><?= translate('email') ?></th>
                    <th><?= translate('phone_number') ?></th>
                    <th><?= translate('role') ?></th>
                    <th><?= translate('status') ?></th>
                    <th><?= translate('package_id') ?></th>
                    <!-- <th><?= translate('date_of_conversion') ?></th>
                    <th><?= translate('email_sent') ?></th> -->
                    <th><?= translate('comments') ?></th>
                    <th><?= translate('created_at') ?></th>
                    <th><?= translate('updated_at') ?></th>
                    <th><?= translate('actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $count = 1; foreach ($requests as $row): ?>
                <tr>
                    <td><?= $count++ ?></td>
                    <td><?= htmlspecialchars($row['registration_id'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($row['name'] ?? ''); ?></td>
                    <!-- <td><?= htmlspecialchars($row['organization_type'] ?? ''); ?></td> -->
                    <td><?= htmlspecialchars($row['school_name'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($row['number_of_branches'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($row['number_of_students'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($row['email'] ?? ''); ?></td>
                    <td><?= htmlspecialchars(($row['phone_country_code'] ?? '') . ' ' . ($row['phone_number'] ?? '')); ?></td>
                    <td><?= htmlspecialchars($row['role'] ?? ''); ?></td>
                    <td>
                    <?php
                    $status_class = '';
                    switch ($row['status']) {
                        case 'contacted':
                            $status_class = 'label label-warning-custom text-xs';
                            break;
                        case 'pending':
                            $status_class = 'label label-info-custom text-xs';
                            break;
                        case 'converted':
                            $status_class = 'label label-primary-custom text-xs';
                            break;
                        case 'lost':
                            $status_class = 'label label-danger-custom text-xs';
                            break;
                        default:
                            $status_class = 'label label-success-custom text-xs';
                            break;
                    }
                    ?>
                    <span class="<?= $status_class ?>"><?= translate($row['status'] ?? ''); ?></span>
                </td>
                    <td><?= htmlspecialchars($saas_packages[$row['package_id']] ?? ''); ?></td>
                    <!-- <td><?= htmlspecialchars($row['date_of_conversion'] ?? ''); ?></td> -->
                    <!-- <td><?= htmlspecialchars($row['email_sent'] ?? ''); ?></td> -->
                    <td><?= htmlspecialchars($row['comments'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($row['created_at'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($row['updated_at'] ?? ''); ?></td>
                    <td>
                        <button onclick="showUpdateModal('<?= $row['id'] ?>', '<?= $row['name'] ?>', '<?= $row['organization_type'] ?>', '<?= $row['school_name'] ?>', '<?= $row['number_of_branches'] ?>', '<?= $row['number_of_students'] ?>', '<?= $row['email'] ?>', '<?= $row['phone_number'] ?>', '<?= $row['status'] ?>', '<?= $row['comments'] ?>', '<?= $row['package_id'] ?>')" class="btn btn-default btn-circle icon" data-toggle="tooltip" data-original-title="<?= translate('update_status') ?>">
                            <i class="far fa-pencil-alt"></i>
                        </button>
                        <button onclick="deleteRegistration('<?= $row['id'] ?>')" class="btn btn-danger btn-circle icon" data-toggle="tooltip" data-original-title="<?= translate('delete') ?>">
                            <i class="far fa-trash-alt"></i>
                        </button>
                        <?php if (!empty($row['package_id']) && $row['status'] == 'pending'): ?>
                            <a href="<?= base_url('saas_website/registration_pending/' . $row['registration_id']) ?>" target="_blank" class="btn btn-success btn-circle icon" data-toggle="tooltip" data-original-title="<?= translate('view') ?>">
                                <i class="far fa-eye"></i>
                            </a>
                    <?php endif; ?>

                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endif; ?>
</div>
</div>

<div id="updateStatusModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo form_open(base_url('saas/updateRequestStatus')); ?>
                <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h5 class="modal-title" id="updateStatusModalLabel"><?= translate('update_status') ?></h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="request_id" id="update_request_id">
                    
                    <div class="form-group">
                        <label for="update_name"><?= translate('name') ?></label>
                        <input type="text" class="form-control" name="name" id="update_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="update_organization_type"><?= translate('organization_type') ?></label>
                        <select class="form-control" name="organization_type" id="update_organization_type" required>
                            <option value="independent"><?= translate('independent') ?></option>
                            <option value="group"><?= translate('group') ?></option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="update_school_name"><?= translate('school_name') ?></label>
                        <input type="text" class="form-control" name="school_name" id="update_school_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="update_number_of_branches"><?= translate('number_of_branches') ?></label>
                        <input type="number" class="form-control" name="number_of_branches" id="update_number_of_branches">
                    </div>
                    
                    <div class="form-group">
                        <label for="update_number_of_students"><?= translate('number_of_students') ?></label>
                        <input type="number" class="form-control" name="number_of_students" id="update_number_of_students" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="update_email"><?= translate('email') ?></label>
                        <input type="email" class="form-control" name="email" id="update_email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="update_phone_number"><?= translate('phone_number') ?></label>
                        <input type="text" class="form-control" name="phone_number" id="update_phone_number" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="update_status"><?= translate('status') ?></label>
                        <select class="form-control" name="status" id="update_status" required>
                            <option value="contacted"><?= translate('contacted') ?></option>
                            <option value="pending"><?= translate('pending') ?></option>
                            <option value="converted"><?= translate('converted') ?></option>
                            <option value="lost"><?= translate('lost') ?></option>
                        </select>
                    </div>
                    
                    <!-- Add Saas Package selection -->
                    <div class="form-group">
                        <label for="update_package_id"><?= translate('package') ?></label>
                        <select class="form-control" name="package_id" id="update_package_id" required>
                            <?php foreach ($saas_packages as $package_id => $package_name): ?>
                                <option value="<?= $package_id ?>"><?= $package_name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="update_comments"><?= translate('comments') ?></label>
                        <textarea class="form-control" name="comments" id="update_comments"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><?= translate('save_changes') ?></button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= translate('close') ?></button>
                </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>


<script>
    $.ajax({
    url: "<?= base_url('saas/updateRequestStatus') ?>",
    type: "POST",
    data: {
        csrf_test_name: '<?= $this->security->get_csrf_hash() ?>', // Replace 'csrf_test_name' with your actual CSRF token name
        request_id: $('#update_request_id').val(),
        name: $('#update_name').val(),
        organization_type: $('#update_organization_type').val(),
        school_name: $('#update_school_name').val(),
        number_of_branches: $('#update_number_of_branches').val(),
        number_of_students: $('#update_number_of_students').val(),
        email: $('#update_email').val(),
        phone_number: $('#update_phone_number').val(),
        status: $('#update_status').val(),
        package_id: $('#update_package_id').val(),
        comments: $('#update_comments').val()
    },
    success: function(response) {
        // Handle success
    },
    error: function(xhr, status, error) {
        // Handle error
    }
});


function showUpdateModal(requestId, name, organizationType, schoolName, numberOfBranches, numberOfStudents, email, phoneNumber, status, comments, packageId) {
    $('#update_request_id').val(requestId);
    $('#update_name').val(name);
    $('#update_organization_type').val(organizationType);
    $('#update_school_name').val(schoolName);
    $('#update_number_of_branches').val(numberOfBranches);
    $('#update_number_of_students').val(numberOfStudents);
    $('#update_email').val(email);
    $('#update_phone_number').val(phoneNumber);
    $('#update_status').val(status);
    $('#update_comments').val(comments);
    $('#update_package_id').val(packageId);
    $('#updateStatusModal').modal('show');
}

function deleteRegistration(requestId) {
    if (confirm('<?= translate('delete_confirmation') ?>')) {
        $.post("<?= base_url('saas/deleteRegistration') ?>", {
            csrf_test_name: '<?= $this->security->get_csrf_hash() ?>',
            id: requestId
        }, function(data) {
            location.reload();
        });
    }
}

</script>