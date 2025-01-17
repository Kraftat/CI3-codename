<div class="row">
<?php if (get_permission('disable_reason', 'is_add')): ?>
	<div class="col-md-5">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="far fa-edit"></i> <?php echo translate('add') . " " . translate('deactivate_reason'); ?></h4>
			</header>
            <?php  echo form_open($this->uri->uri_string()); ?>
				<div class="panel-body">
				<?php if (is_superadmin_loggedin()): ?>
					<div class="form-group">
						<label class="control-label"><?=translate('branch')?> <span class="required">*</span></label>
						<?php
							$arrayBranch = $this->appLib->getSelectList('branch');
							echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id'
							data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
						?>
						<span class="error"><?=form_error('branch_id')?></span>
					</div>
				<?php endif; ?>
					<div class="form-group mb-md">
						<label class="control-label"><?php echo translate('reason'); ?> <span class="required">*</span></label>
						<input type="text" class="form-control" name="name" value="<?php echo set_value('name'); ?>" />
						<span class="error"><?php echo form_error('name'); ?></span>
					</div>
				</div>
				<div class="panel-footer">
					<div class="row">
						<div class="col-md-12">
							<button class="btn btn-default pull-right" type="submit" name="disable_reason" value="1"><i class="far fa-plus-circle"></i> <?php echo translate('save'); ?></button>
						</div>	
					</div>
				</div>
			<?= form_close(); ?>
		</section>
	</div>
<?php endif; ?>
<?php if (get_permission('disable_reason', 'is_view')): ?>
	<div class="col-md-<?php if (get_permission('disable_reason', 'is_add')){ echo "7"; } else { echo "12"; } ?>">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="far fa-list-ul"></i> <?php echo translate('reason') . " " . translate('list'); ?></h4>
			</header>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table table-bordered table-hover table-condensed mb-none">
						<thead>
							<tr>
								<th><?=translate('branch')?></th>
								<th><?php echo translate('reason'); ?></th>
								<th><?php echo translate('action'); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php
						if (!empty($categorylist)){
							foreach ($categorylist as $row): 
								?>
							<tr>
								<td><?php echo $row['branch_name'];?></td>
								<td><?php echo $row['name']; ?></td>
								<td class="min-w-xs">
								<?php if (get_permission('disable_reason', 'is_edit')): ?>
									<!-- update link -->
									<a class="btn btn-default btn-circle icon" href="javascript:void(0);" onclick="getDisable_reason('<?php echo $row['id']; ?>')">
										<i class="far fa-pen-nib"></i>
									</a>
								<?php endif; if (get_permission('disable_reason', 'is_delete')): ?>
									<!-- delete link -->
									<?php echo btn_delete('student/disable_reason_delete/' . $row['id']); ?>
								<?php endif; ?>
								</td>
							</tr>
						<?php
							endforeach;
						}else{
							echo '<tr><td colspan="4"><h5 class="text-danger text-center">' . translate('no_information_available') . '</td></tr>';
						}
						?>
						</tbody>
					</table>
				</div>
			</div>
		</section>
	</div>
</div>
<?php endif; ?>
<?php if (get_permission('disable_reason', 'is_edit')): ?>
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<section class="panel">
		<header class="panel-heading">
			<h4 class="panel-title">
				<i class="far fa-edit"></i> <?php echo translate('edit') . " " . translate('deactivate_reason'); ?>
			</h4>
		</header>
		<?php echo form_open('student/disable_reason_edit', array('class' => 'frm-submit')); ?>
			<div class="panel-body">
				<input type="hidden" name="reason_id" id="ereason_id" value="">
				<?php if (is_superadmin_loggedin()): ?>
					<div class="form-group">
						<label class="control-label"><?=translate('branch')?> <span class="required">*</span></label>
						<?php
							$arrayBranch = $this->appLib->getSelectList('branch');
							echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='ebranch_id'
							data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
						?>
						<span class="error"></span>
					</div>
				<?php endif; ?>
				<div class="form-group mb-md">
					<label class="control-label"><?php echo translate('reason'); ?> <span class="required">*</span></label>
					<input type="text" class="form-control" value="" name="name" id="ecategory_name" />
					<span class="error"></span>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-12 text-right">
						<button type="submit" class="btn btn-default" data-loading-text="<i class='far fa-spinner fa-spin'></i> Processing">
							<i class="far fa-plus-circle"></i> <?php echo translate('update'); ?>
						</button>
						<button class="btn btn-default modal-dismiss"><?php echo translate('cancel'); ?></button>
					</div>
				</div>
			</footer>
		<?= form_close(); ?>
	</section>
</div>
<?php endif; ?>