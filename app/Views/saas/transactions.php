<?php $currency_symbol = $global_config['currency_symbol']; ?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?=translate('select_ground')?></h4>
			</header>
			<?php echo form_open($this->uri->uri_string(), array('class' => 'validate'));?>
			<div class="panel-body">
				<div class="row mb-sm">
					<div class="col-md-offset-3 col-md-6 mb-sm">
						<div class="form-group">
							<label class="control-label"><?php echo translate('date'); ?> <span class="required">*</span></label>
							<div class="input-group">
								<span class="input-group-addon"><i class="far fa-calendar-check"></i></span>
								<input type="text" class="form-control daterange" name="daterange" value="<?php echo set_value('daterange', date("Y/m/d") . ' - ' . date("Y/m/d")); ?>" required />
							</div>
						</div>
					</div>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-offset-10 col-md-2">
						<button type="submit" name="search" value="1" class="btn btn-default btn-block"> <i class="far fa-filter"></i> <?=translate('filter')?></button>
					</div>
				</div>
			</footer>
			<?php echo form_close();?>
		</section>
		<?php if (isset($getTransactions)):?>
		<section class="panel appear-animation" data-appear-animation="<?=$global_config['animations'] ?>" data-appear-animation-delay="100">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="far fa-money-bill-alt"></i> <?php echo translate('transactions');?></h4>
			</header>
			<div class="panel-body mb-md">
			<table class="table table-bordered table-condensed table-hover table-export">
    <thead>
        <tr>
            <th><?=translate('sl')?></th>
            <th><?=translate('school_name')?></th>
            <th><?=translate('plan') . " " . translate('name')?></th>
            <th><?=translate('payment') . " " . translate('type')?></th>
            <th><?=translate('payment') . " " . translate('method')?></th>
            <th><?=translate('amount')?></th>
            <th>Trx ID</th>
            <th><?=translate('date')?></th>
            <th><?=translate('action')?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $count = 1;
        $totalsByCurrency = [];
        foreach ($getTransactions as $row):
            $currency = $row->currency ? $row->currency : 'N/A';
            if (!isset($totalsByCurrency[$currency])) {
                $totalsByCurrency[$currency] = 0;
            }
            $totalsByCurrency[$currency] += $row->amount;
            ?>
            <tr>
                <td><?php echo $count++; ?></td>
                <td><?php echo $row->school_name; ?></td>
                <td><?php echo $row->plan_name; ?></td>
                <td><?php echo $row->renew ? translate('renew') : translate('subscribed'); ?></td>
                <td><?php echo $row->payvia; ?></td>
                <td><?php echo $currency . ' ' . number_format($row->amount, 2, '.', ''); ?></td>
                <td><?php echo $row->payment_id; ?></td>
                <td><?php echo _d($row->created_at); ?></td>
                <td>
                    <a href="<?=base_url('saas/school_details/'.$row->bid)?>" class="btn btn-default btn-circle icon" data-toggle="tooltip" data-original-title="<?php echo translate('details') ?>">
                        <i class="far fa-qrcode"></i>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <?php foreach ($totalsByCurrency as $currency => $total): ?>
        <tr>
            <th colspan="5">Total in <?php echo $currency; ?>:</th>
            <th><?php echo $currency . ' ' . number_format($total, 2, '.', ''); ?></th>
            <th colspan="3"></th>
        </tr>
        <?php endforeach; ?>
    </tfoot>
</table>
			</div>
		</section>
		<?php endif;?>
	</div>
</div>