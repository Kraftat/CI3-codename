<?php $widget = (is_superadmin_loggedin() ? 4 : 6); ?>
<section class="panel">
	<?= form_open(current_url()); ?>
    <header class="panel-heading">
		<h4 class="panel-title"><?=translate('select_ground')?></h4>
	</header>
	<div class="panel-body">
		<div class="row mb-sm">
			<?php if (is_superadmin_loggedin() ): ?>
				<div class="col-md-4 mb-sm">
					<div class="form-group">
						<label class="control-label"><?=translate('branch')?> <span class="required">*</span></label>
						<?php
							$arrayBranch = $this->appLib->getSelectList('branch');
							echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control'
							data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
						?>
					</div>
					<span class="error"><?=form_error('branch_id')?></span>
				</div>
			<?php endif; ?>
			<div class="col-md-<?php echo $widget; ?> mb-sm">
				<div class="form-group">
					<label class="control-label"><?=translate('role')?> <span class="required">*</span></label>
					<?php
						$role_list = $this->appLib->getRoles();
						echo form_dropdown("staff_role", $role_list, set_value('staff_role'), "class='form-control'
						data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
					?>
				</div>
			</div>
			<div class="col-md-<?php echo $widget; ?> mb-sm">
				<div class="form-group">
					<label class="control-label"><?=translate('month')?> <span class="required">*</span></label>
					<div class="input-group">
						<input type="text" class="form-control" name="timestamp" value="<?=set_value('timestamp', date('Y-F'))?>" data-plugin-datepicker required
						data-plugin-options='{ "format": "yyyy-MM", "minViewMode": "months", "orientation": "bottom"}' />
						<span class="input-group-addon"><i class="icon-event icons"></i></span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<footer class="panel-footer">
		<div class="row">
			<div class="col-md-offset-10 col-md-2">
				<button type="submit" name="search" value="1" class="btn btn btn-default btn-block">
					<i class="far fa-filter"></i> <?=translate('filter')?>
				</button>
			</div>
		</div>
	</footer>
	<?= form_close(); ?>
</section>

<?php if (isset($stafflist)): ?>
	<section class="panel appear-animation mt-sm" data-appear-animation="<?=$global_config['animations'] ?>" data-appear-animation-delay="100">
		<header class="panel-heading">
			<h4 class="panel-title"><i class="far fa-users"></i> <?=translate('attendance_report')?></h4>
		</header>
		<div class="panel-body">
			<style type="text/css">
				table.dataTable.table-condensed > thead > tr > th {
				  padding-right: 3px !important;
				}
			</style>
			<div class="row mt-sm">
				<div class="col-md-offset-8 col-md-4">
					<table class="table table-condensed table-bordered text-dark text-center">
						<tbody>
							<tr>
								<td><strong>Weekends :</strong> W<span class="visible-print">W</span></td>
								<td><strong>Present :</strong> <i class="far fa-check-circle hidden-print text-success"></i><span class="visible-print">P</span></td>
								<td><strong>Absent : </strong> <i class="far fa-times-circle hidden-print text-danger"></i><span class="visible-print">A</span></td>
								<td><strong>Holiday : </strong> <i class="far fa-hospital-symbol hidden-print text-info"></i><span class="visible-print">H</span></td>
								<td><strong>Late : </strong> <i class="far fa-clock hidden-print text-tertiary"></i><span class="visible-print">L</span></td>
								<td><strong>Half Day : </strong> <i class="far fa-star-half-alt text-tertiary"></i><span class="visible-print">HD</span></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<div class="export_title">Employees Attendance Sheet on <?=date("F Y", strtotime($year.'-'.$month))?></div>
					<table class="table table-bordered table-hover table-condensed mb-none text-dark table-export">
						<thead>
							<tr>
								<th><?=translate('employee')?></th>
<?php

$weekends = $this->attendance_model->getWeekendDaysSession($branch_id);
$getHolidays = $this->attendance_model->getHolidays($branch_id);
$getHolidays = explode('","', $getHolidays);

for($i = 1; $i <= $days; $i++){
$date = date('Y-m-d', strtotime($year . '-' . $month . '-' . $i));
?>
								<th <?php if(in_array($date, $weekends)) { echo "style='background-color: #f99'"; } ?> class="text-center no-sort"><?php echo date('D', strtotime($date)); ?> <br> <?php echo date('d', strtotime($date)); ?></th>
<?php } ?>
								<th class="text-center" style="padding-right: 15px !important;">(%)</th>
								<th class="text-center" style="padding-right: 15px !important;">W</th>
								<th class="text-center text-success" style="padding-right: 15px !important;">P</th>
								<th class="text-center text-danger" style="padding-right: 15px !important;">A</th>
								<th class="text-center text-tertiary" style="padding-right: 15px !important;">L</th>
								<th class="text-center text-tertiary isExport">HD</th>
							</tr>
						</thead>
						<tbody>
							<?php									 
							foreach ($stafflist as $row):
							$staffID = $row['id'];
							?>
							<tr>
								<td><?php echo $row['name']; ?></td>
								<?php
									$total_present = 0;
									$total_absent = 0;
									$total_late = 0;
									$total_weekends = 0;
									$total_half_day = 0;
									for ($i = 1; $i <= $days; $i++) {
										$date = date('Y-m-d', strtotime($year . '-' . $month . '-' . $i));
										$getAttendance = $builder->getWhere('staff_attendance', array('staff_id' => $staffID,'date' => $date))->row_array();
										echo '<td class="center">';
										if (!empty($getAttendance)) {
											$status = $getAttendance['status'];
											echo '<span data-toggle="popover" data-placement="top" data-trigger="hover" data-content="' . $getAttendance['remark'] . '">';
											if ($status == 'P'){
												$total_present++;
												echo '<i class="far fa-check-circle hidden-print text-success"></i><span class="visible-print">P</span>';
											}
											if($status == 'A'){
												$total_absent++;
												echo '<i class="far fa-times-circle hidden-print text-danger"></i><span class="visible-print">A</span>';
											}
											if($status == 'H')
												echo '<i class="far fa-hospital-symbol hidden-print text-info"></i><span class="visible-print">H</span>';
											if($status == 'L'){
												$total_late++;
												echo '<i class="far fa-clock hidden-print text-tertiary"></i><span class="visible-print">L</span>';
											}
											if($status == 'HD'){
												$total_half_day++;
												echo '<i class="far fa-star-half-alt hidden-print text-tertiary"></i><span class="visible-print">HD</span>';
											}
											echo '</span>';
										} else {
											if(in_array($date, $getHolidays)) {
												echo '<i class="far fa-hospital-symbol text-tertiary"></i><span class="visible-print">H</span>';
											} else {
												if(in_array($date, $weekends)) {
													$total_weekends++;
													echo '<span class="text-success">W</span>';
												}
											}
										}
										echo '</td>';
									}
								?>
								<td class="center"><?php 
								$total_working_days = ($total_present + $total_absent + $total_late + $total_half_day);
								if ($total_working_days == 0) {
									echo "-";
								} else {
									$total_present = ($total_present + $total_late + $total_half_day);
									$percentage = ($total_present / $total_working_days) * 100;
									echo round($percentage);
								}
								?></td>
								<td class="center"><?=$total_weekends?></td>
								<td class="center"><?=$total_present?></td>
								<td class="center"><?=$total_absent?></td>
								<td class="center"><?=$total_late?></td>
								<td class="center"><?=$total_half_day?></td>
								<?php endforeach; ?>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</section>
<?php endif; ?>