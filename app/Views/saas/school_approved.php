<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?=translate('select_ground')?></h4>
			</header>
			<?php echo form_open_multipart('saas/schoolApprovedSave', array('class' => 'form-horizontal form-bordered frm-submit-data')); ?>
			<div class="panel-body">
				<input type="hidden" name="saas_register_id" value="<?php echo $data->id; ?>">
				<input type="hidden" name="branch_name" value="<?php echo $data->school_name; ?>">
	
				<div class="form-group mt-md">
					<label class="col-md-3 control-label"><?=translate('message')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<div class="alert alert-info mb-none"><?php echo empty($data->message) ? "N/A" : $data->message; ?></div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('school_name')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<input type="text" class="form-control" name="school_name" value="<?=set_value('school_name', $data->school_name)?>" />
						<span class="error"><?=form_error('school_name') ?></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('admin_name')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<input type="text" class="form-control" name="admin_name" value="<?=set_value('admin_name', $data->admin_name)?>" />
						<span class="error"><?=form_error('admin_name') ?></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('admin_login_username')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<input type="text" class="form-control" name="admin_name" value="<?=set_value('admin_name', $data->username)?>" />
						<span class="error"><?=form_error('admin_name') ?></span>
					</div>
				</div>
				<div class="form-group">
    <label class="col-md-3 control-label"><?=translate('admin_login_password')?> <span class="required">*</span></label>
    <div class="col-md-6">
        <input id="admin_password" type="password" class="form-control" value="" readonly />
        <span class="error"><?=form_error('admin_name') ?></span>
    </div>
</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('email')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<input type="text" class="form-control" name="email" value="<?=set_value('email', $data->email)?>"  />
						<span class="error"><?=form_error('email') ?></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('mobile_no')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<input type="text" class="form-control" name="mobileno" value="<?=set_value('mobileno', $data->contact_number)?>" />
						<span class="error"><?=form_error('mobileno') ?></span>
					</div>
				</div>
                <?php
$currencies = get_currencies();
?>

<div class="form-group">
    <label class="col-md-3 control-label">
        <?=translate('currency');?>
    </label>
    <div class="col-md-6">
        <select class="form-control" name="currency" id="currency">
            <?php foreach ($currencies as $currency): ?>
                <option value="<?= $currency['code']; ?>" data-symbol="<?= $currency['symbol']; ?>" <?= set_select('currency', $currency['code'], $currency['code'] == $global_config['currency']); ?>>
                    <?= $currency['code']; ?> (<?= $currency['name']; ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
<div class="form-group">
    <label class="col-md-3 control-label">
        <?=translate('currency_symbol');?>
    </label>
    <div class="col-md-6">
        <input type="text" class="form-control" name="currency_symbol" id="currency_symbol" value="<?=set_value('currency_symbol', $global_config['currency_symbol'])?>" readonly />
    </div>
</div>

<script>
document.getElementById('currency').addEventListener('change', function() {
    var selectedOption = this.options[this.selectedIndex];
    var symbol = selectedOption.getAttribute('data-symbol');
    document.getElementById('currency_symbol').value = symbol;
});

// Set the initial currency symbol on page load
window.onload = function() {
    var selectedOption = document.getElementById('currency').options[document.getElementById('currency').selectedIndex];
    var symbol = selectedOption.getAttribute('data-symbol');
    document.getElementById('currency_symbol').value = symbol;
};
</script>

                <div class="form-group">
    <label class="col-md-3 control-label"><?= translate('country') ?> <span class="required">*</span></label>
    <div class="col-md-6">
        <select class="form-control" id="country" name="country" required>
            <?php
            $countries = get_country();

            // Retrieve the country from your data source
            $selected_country = set_value('country', isset($data->country) ? $data->country : '');

            foreach ($countries as $code => $name) {
                // Check if the current option should be selected
                $selected = ($name == $selected_country) ? 'selected' : '';
                echo "<option value='{$name}' {$selected}>{$name}</option>";
            }
            ?>
        </select>
        <span class="error"><?= form_error('country') ?></span>
    </div>
</div>

				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('city')?></label>
					<div class="col-md-6">
						<input type="text" class="form-control" name="city" value="">
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('state')?></label>
					<div class="col-md-6">
						<input type="text" class="form-control" name="state" value="">
					</div>
				</div>
				<div class="form-group">
					<label  class="col-md-3 control-label"><?=translate('address')?></label>
					<div class="col-md-6">
						<textarea type="text" rows="3" class="form-control" name="address" ><?=set_value('address', $data->address)?></textarea>
					</div>
				</div>
				<div class="form-group">
					<label  class="col-md-3 control-label"><?=translate('plan')?> <span class="required">*</span></label>
					<div class="col-md-6">
						<?php
							$saasPackage = $this->saas_model->getSaasPackage();
							echo form_dropdown("saas_package_id", $saasPackage, set_value('saas_package_id', $data->package_id), "class='form-control' data-width='100%' disabled data-plugin-selectTwo  data-minimum-results-for-search='Infinity'");
						?>
						<span class="error"><?=form_error('saas_package_id'); ?></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('start_date')?></label>
					<div class="col-md-6">
						<div class="input-group">
							<span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
							<input type="text" class="form-control" name="start_date" readonly value="<?=_d(date("Y-m-d"))?>" autocomplete="off" readonly />
						</div>
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('expiry_date')?></label>
					<div class="col-md-6">
						<div class="input-group">
							<span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
							<input type="text" class="form-control" name="expire_date" readonly autocomplete="off" value="<?=$this->saas_model->getPlanExpiryDate($data->package_id)?>" />
						</div>
						<span class="error"></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?=translate('text_logo');?></label>
					<div class="col-md-3 mb-md">
						<input type="hidden" name="text_logo" value="<?=empty($data->logo) ? base_url('uploads/app_image/logo-small.png') : base_url('uploads/saas_school_logo/' . $data->logo); ?>">
						<input type="file" name="text_logo" class="dropify" data-allowed-file-extensions="png" data-default-file="<?=empty($data->logo) ? base_url('uploads/app_image/logo-small.png') : base_url('uploads/saas_school_logo/' . $data->logo); ?>" />
					</div>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-offset-3 col-md-2">
						<button type="submit" class="btn btn-default btn-block" name="submit" value="save" data-loading-text="<i class='far fa-spinner fa-spin'></i> Processing">
							<i class="far fa-plus-circle"></i> <?=translate('approved')?>
						</button>
					</div>
				</div>
			</footer>
			<?php echo form_close();?>
		</section>
	</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var passwordField = document.getElementById('admin_password');
        var passwordValue = "<?= $data->password ?>"; // PHP variable containing the password
        var maskedPassword = '*'.repeat(passwordValue.length);
        passwordField.value = maskedPassword;
    });
</script>

