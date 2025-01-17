<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#list" data-toggle="tab"><i class="far fa-list-ul"></i> <?=translate('grade_list')?></a>
			</li>
<?php if (get_permission('exam_grade', 'is_add')): ?>
			<li>
				<a href="#create" data-toggle="tab"><i class="far fa-edit"></i> <?=translate('create_grade')?></a>
			</li>
<?php endif; ?>
		</ul>
		<div class="tab-content">
			<div id="list" class="tab-pane active">
				<table class="table table-bordered table-hover mb-none table-export">
					<thead>
						<tr>
							<th><?=translate('sl')?></th>
<?php if (is_superadmin_loggedin()): ?>
							<th><?=translate('branch')?></th>
<?php endif; ?>
							<th><?=translate('grade_name')?></th>
							<th><?=translate('grade_point')?></th>
							<th><?=translate('min_percentage')?></th>
							<th><?=translate('max_percentage')?></th>
							<th><?=translate('remarks')?></th>
							<th><?=translate('action')?></th>
						</tr>
					</thead>
					<tbody>
						<?php
							$count = 1;
                            $grades = $this->appLib->getTable('grade');
							foreach($grades as $grade):
						?>
						<tr>
							<td><?php echo $count++;?></td>
<?php if (is_superadmin_loggedin()): ?>
							<td><?php echo $grade['branch_name'];?></td>
<?php endif; ?>
							<td><?php echo $grade['name']; ?></td>
							<td><?php echo $grade['grade_point']; ?></td>
							<td><?php echo $grade['lower_mark']; ?>%</td>
							<td><?php echo $grade['upper_mark']; ?>%</td>
							<td><?php echo $grade['remark']; ?></td>
							<td>
							<?php if (get_permission('exam_grade', 'is_edit')): ?>
								<!--update link-->
								<a href="<?php echo base_url('exam/grade_edit/' . $grade['id']);?>" class="btn btn-default btn-circle icon">
									<i class="far fa-pen-nib"></i>
								</a>
							<?php endif; if (get_permission('exam_grade', 'is_delete')): ?>
								<!-- deletion link -->
								<?php echo btn_delete('exam/grade_delete/' . $grade['id']);?>
							<?php endif;?>
							</td>
						</tr>
						<?php endforeach;?>
					</tbody>
				</table>
			</div>
<?php if (get_permission('exam_grade', 'is_add')): ?>
			<div class="tab-pane" id="create">
				<?php echo form_open($this->uri->uri_string(), array('class' => 'form-horizontal form-bordered frm-submit'));?>
				<?php if (is_superadmin_loggedin()): ?>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('branch')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<?php
							$arrayBranch = $this->appLib->getSelectList('branch');
							echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control'
							data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
						?>
						<span class="error"></span>
					</div>
				</div>
				<?php endif; ?>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('name')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<input type="text" class="form-control" name="name" value="" />
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('grade_point')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<input type="text" class="form-control" name="grade_point" value="" />
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('min_percentage')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<input type="number" class="form-control" name="lower_mark" value="" />
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('max_percentage')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<input type="number" class="form-control" name="upper_mark" value="" />
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('remarks')?></label>
					<div class="col-md-6 mb-md">
						<input type="text" class="form-control" name="remark" value="" />
					</div>
				</div>
				<footer class="panel-footer">
					<div class="row">
						<div class="col-md-offset-3 col-md-2">
							<button type="submit" class="btn btn-default btn-block" data-loading-text="<i class='far fa-spinner fa-spin'></i> Processing">
								<i class="far fa-plus-circle"></i> <?=translate('save')?>
							</button>
						</div>
					</div>
				</footer>
				<?php echo form_close();?>
			</div>
<?php endif; ?>
		</div>
	</div>
</section>