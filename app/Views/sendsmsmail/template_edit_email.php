<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li>
				<a href="<?=base_url('sendsmsmail/template/' . $type)?>"><i class="far fa-list-ul"></i> <?php echo translate('template') . ' ' . translate('list'); ?></a>
			</li>
			<li class="active">
				<a href="#create" data-toggle="tab"><i class="far fa-edit"></i> <?php echo translate('edit') . ' ' . translate('template'); ?></a>
			</li>
		</ul>
		<div class="tab-content">
	
			<div id="create" class="tab-pane active">
				<?php echo form_open($this->uri->uri_string(), array('class' => 'form-horizontal form-bordered frm-submit')); ?>
				<input type="hidden" name="template_id" value="<?=$templete['id']?>" >
				<?php if (is_superadmin_loggedin()): ?>
					<div class="form-group">
						<label class="col-md-3 control-label"><?php echo translate('branch');?> <span class="required">*</span></label>
						<div class="col-md-6">
							<?php
								$arrayBranch = $this->appLib->getSelectList('branch');
								echo form_dropdown("branch_id", $arrayBranch, $templete['branch_id'], "class='form-control'
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
							?>
							<span class="error"></span>
						</div>
					</div>
				<?php endif; ?>
					<div class="form-group">
						<label class="col-md-3 control-label"><?php echo translate('name'); ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="template_name" value="<?=$templete['name']?>" />
							<span class="error"></span>
						</div>
					</div>

					<div class="form-group">
						<label class="col-md-3 control-label"><?php echo translate('message'); ?> <span class="required">*</span></label>
						<div class="col-md-6">
							<textarea name="message" id="message" class="summernote"><?=$templete['body']?></textarea>
							<span class="error"></span>
						</div>
					</div>
					<p class="col-md-offset-3 mt-md">
						<strong>Dynamic Tag : </strong>
						<a data-value=" {name} " class="btn btn-default btn-xs btn_tag ">{name}</a>
						<a data-value=" {email} " class="btn btn-default btn-xs btn_tag">{email}</a>
						<a data-value=" {mobile_no} " class="btn btn-default btn-xs btn_tag">{mobile_no}</a>
					</p>
					<footer class="panel-footer">
						<div class="row">
							<div class="col-md-offset-3 col-md-2">
								<button type="submit" data-loading-text="<i class='far fa-spinner fa-spin'></i> Processing" class="btn btn-default btn-block">
									<i class="far fa-plus-circle"></i> <?=translate('update')?>
								</button>
							</div>
						</div>
					</footer>
				<?= form_close(); ?>
			</div>
		</div>
	</div>
</section>

<script type="text/javascript">
	$(document).ready(function () {
		$('.btn_tag').on('click', function() {
			var txtToAdd = $(this).data("value");
			$('.summernote').summernote('editor.insertText', txtToAdd);
		});
	});
</script>