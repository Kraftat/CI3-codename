<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#list" data-toggle="tab"><i class="far fa-list-ul"></i> <?php echo translate('features') . " " . translate('list'); ?></a>
			</li>
	<?php if (get_permission('frontend_features', 'is_add')) { ?>
			<li>
				<a href="#create" data-toggle="tab"><i class="far fa-edit"></i> <?php echo translate('add') . " " . translate('features'); ?></a>
			</li>
	<?php } ?>
		</ul>
		<div class="tab-content">
			<div id="list" class="tab-pane active">
				<table class="table table-bordered table-hover table-condensed table_default" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th><?php echo translate('sl'); ?></th>
<?php if (is_superadmin_loggedin()): ?>
							<th><?=translate('branch')?></th>
<?php endif; ?>
							<th><?php echo translate('icon'); ?></th>
							<th><?php echo translate('title'); ?></th>
							<th><?php echo translate('button_text'); ?></th>
							<th><?php echo translate('button_url'); ?></th>
							<th><?php echo translate('description'); ?></th>
							<th><?php echo translate('action'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count = 1;
						if (!empty($featureslist)) {
							foreach ($featureslist as $row): 
								$elements = json_decode($row['elements'], true);
								?>
						<tr>
							<td><?php echo $count++; ?></td>
<?php if (is_superadmin_loggedin()): ?>
							<td><?php echo $row['branch_name'];?></td>
<?php endif; ?>
							<td class="widget-row-in"><i class="<?php echo $elements['icon']; ?>"></i></td>
							<td><?php echo strip_tags($row['title']); ?></td>
							<td><?php echo $elements['button_text']; ?></td>
							<td><?php echo $elements['button_url']; ?></td>
							<td><?php echo strip_tags($row['description']); ?></td>
							<td class="min-w-xs">
								<?php if (get_permission('frontend_features', 'is_edit')): ?>
									<a href="<?php echo base_url('frontend/features/edit/' . $row['id']); ?>" class="btn btn-default btn-circle icon" data-toggle="tooltip"
									data-original-title="<?php echo translate('edit'); ?>"> 
										<i class="far fa-pen-nib"></i>
									</a>
								<?php endif; if (get_permission('frontend_features', 'is_delete')): ?>
									<?php echo btn_delete('frontend/features/delete/' . $row['id']); ?>
								<?php endif; ?>
							</td>
						</tr>
						<?php endforeach; } ?>
					</tbody>
				</table>
			</div>
	<?php if (get_permission('frontend_features', 'is_add')) { ?>
			<div class="tab-pane" id="create">
				<?php echo form_open_multipart($this->uri->uri_string(), array('class' => 'form-horizontal form-bordered frm-submit')); ?>
					<?php if (is_superadmin_loggedin()): ?>
					<div class="form-group">
						<label class="col-md-3 control-label"><?=translate('branch')?> <span class="required">*</span></label>
						<div class="col-md-6">
							<?php
								$arrayBranch = $this->appLib->getSelectList('branch');
								echo form_dropdown("branch_id", $arrayBranch, "", "class='form-control' data-plugin-selectTwo
								data-width='100%' data-minimum-results-for-search='Infinity'");
							?>
							<span class="error"></span>
						</div>
					</div>
					<?php endif; ?>
					<div class="form-group">
						<label class="col-md-3 control-label"><?php echo translate('title'); ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="title" value="<?php echo set_value('title'); ?>" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label  class="col-md-3 control-label"><?php echo translate('button_text'); ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="button_text" value="<?php echo set_value('button_text'); ?>" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label  class="col-md-3 control-label"><?php echo translate('button_url'); ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="button_url" value="<?php echo set_value('button_url'); ?>" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label  class="col-md-3 control-label"><?php echo translate('icon'); ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="icon" value="<?php echo set_value('icon'); ?>" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?php echo translate('description'); ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<textarea class="form-control" id="description" name="description" placeholder="" rows="3" ><?php echo set_value('description'); ?></textarea>
							<span class="error"></span>
						</div>
					</div>
					<footer class="panel-footer mt-lg">
						<div class="row">
							<div class="col-md-2 col-md-offset-3">
								<button type="submit" class="btn btn-default btn-block" data-loading-text="<i class='far fa-spinner fa-spin'></i> Processing">
									<i class="far fa-plus-circle"></i> <?php echo translate('save'); ?>
								</button>
							</div>
						</div>	
					</footer>
				<?= form_close(); ?>
			</div>
	<?php } ?>
		</div>
	</div>
</section>