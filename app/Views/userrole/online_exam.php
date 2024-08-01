<section class="panel">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="far fa-list-ul"></i> <?=translate('online_exam') ." ". translate('list')?></h4>
	</header>
	<div class="panel-body">
		<table class="table table-bordered table-hover mb-none table-condensed exam-list" width="100%">
			<thead>
				<tr>
					<th class="no-sort"><?=translate('sl')?></th>
					<th><?=translate('title')?></th>
					<th><?=translate('class')?> (<?=translate('section')?>)</th>
					<th class="no-sort"><?=translate('subject')?></th>
					<th><?=translate('questions_qty')?></th>
					<th><?=translate('start_time')?></th>
					<th><?=translate('end_time')?></th>
					<th><?=translate('duration')?></th>
					<th class="no-sort"><?=translate('exam') . " " . translate('fees')?></th>
					<th class="no-sort"><?=translate('exam_status')?></th>
					<th><?=translate('action')?></th>
				</tr>
			</thead>
		</table>
	</div>
</section>

<div class="zoom-anim-dialog modal-block modal-block-lg mfp-hide payroll-t-modal" id="modal">
	<section class="panel">
		<header class="panel-heading">
			<h4 class="panel-title"><i class="far fa-users-between-lines"></i> <?php echo translate('exam_result'); ?></h4>
		</header>
		<div class="panel-body">
			<div id="quick_view"></div>
		</div>
		<footer class="panel-footer">
			<div class="row">
				<div class="col-md-12 text-right">
					<button class="btn btn-default modal-dismiss"><?php echo translate('close'); ?></button>
				</div>
			</div>
		</footer>
	</section>
</div>

<div class="zoom-anim-dialog modal-block mfp-hide" id="payModal">
	<section class="panel">
		<?php echo form_open('onlineexam_payment/checkout', array('class' => ' frm-submit' )); ?>
		<header class="panel-heading">
			<h4 class="panel-title"><i class="far fa-credit-card"></i> <?php echo translate('online_exam') . " " . translate('payment'); ?></h4>
		</header>
		<div class="panel-body">
			<div id="payForm"></div>
		</div>
		<footer class="panel-footer mt-md">
			<div class="row">
				<div class="col-md-12 text-right">
					<button class="btn btn-default modal-dismiss"><?php echo translate('cancel'); ?></button>
					<button type="submit" class="btn btn-default" data-loading-text="<i class='far fa-spinner fa-spin'></i> Processing"><?php echo translate('pay_now') ?></button>
				</div>
			</div>
		</footer>
		<?php echo form_close();?>
	</section>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		// initiate Datatable
		initDatatable('.exam-list', 'userrole/getExamListDT', {}, 25);


		$(document).ready(function () {
    $(document).on('change', '#pay_via', function() {
        var method = $(this).val();

        // Hide all payment method specific fields initially
        $('.payu, .sslcommerz, .toyyibpay, .tap').hide(400);

        // Show relevant fields based on the selected payment method
        switch (method) {
            case 'payumoney':
                $('.payu').show(400);
                break;
            case 'sslcommerz':
                $('.sslcommerz').show(400);
                break;
            case 'toyyibpay':
            case 'payhere': // Assuming you want the same fields for payhere and toyyibpay
                $('.toyyibpay').show(400);
                break;
            case 'tap':
                $('.tap').show(400);
                break;
            default:
                // No fields to show
                break;
        }
    });
});
	});


function paymentModal(id) {
    $.ajax({
        url: base_url + 'userrole/getExamPaymentForm',
        type: 'POST',
        data: {'examID': id},
        dataType: "json",
        success: function (res) {
        	if (res.status == 1) {
	            $('#payForm').html(res.data);
				$('#payVia').themePluginSelect2({});
	            mfp_modal('#payModal');
        	} else {
        		alertMsg(res.message, "error", "<?php echo translate('error') ?>", "");
        	}
        }
    });
}

</script>