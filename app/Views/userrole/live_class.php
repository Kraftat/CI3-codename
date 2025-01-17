<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="far fa-headset"></i> <?=translate('live_class') . " " . translate('list')?></h4>
			</header>
			<div class="panel-body">
			<div class="tab-pane box active mb-md" id="list">
				<table class="table table-bordered table-hover mb-none table-condensed table-export">
					<thead>
						<tr>
							<th><?=translate('sl')?></th>
							<th><?=translate('title')?></th>
							<th><?=translate('meeting_id')?></th>
							<th><?=translate('date')?></th>
							<th><?=translate('start_time')?></th>
							<th><?=translate('end_time')?></th>
							<th><?=translate('created_by')?></th>
							<th><?=translate('status')?></th>
							<th><?=translate('created_at')?></th>
							<th><?=translate('action')?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count = 1;
						$getStudent = $applicationModel->getStudentDetails(get_loggedin_user_id());
						$builder->select('live_class.*,staff.name as staffname');
						$this->db->from('live_class');
						$builder->join('staff', 'staff.id = live_class.created_by', 'left');
						$this->db->where('live_class.class_id', $getStudent['class_id']);
						$this->db->where('live_class.branch_id', $getStudent['branch_id']);
						$this->db->order_by('live_class.id', 'ASC');
						$liveClass = $builder->get()->result_array();
						foreach ($liveClass as $row):
							$array = json_decode($row['section_id'], true);
							if (in_array($getStudent['section_id'], $array)) {
						?>
						<tr>
							<td><?php echo $count++; ?></td>
							<td><?php echo $row['title']; ?></td>
							<td><?php echo empty($row['meeting_id']) ? "-" : $row['meeting_id']; ?></td>
							<td><?php echo _d($row['date']);?></td>
                            <td><?php echo date("h:i A", strtotime($row['start_time'])); ?></td>
                            <td><?php echo date("h:i A", strtotime($row['end_time'])); ?></td>
							<td><?php echo $row['staffname']; ?></td>
							<td>
								<?php  
								$status = '<i class="far fa-clock"></i> ' . translate('waiting');
								$labelmode = 'label-info-custom';
								if (strtotime($row['date']) == strtotime(date("Y-m-d")) && strtotime($row['start_time']) <= time() && time() >= strtotime(date("h:i"))) {
									$status = '<i class="far fa-video"></i> ' . translate('live');
									$labelmode = 'label-success-custom';
								}
								if (strtotime($row['date']) < strtotime(date("Y-m-d")) || strtotime($row['end_time']) <= time()) {
									$status = '<i class="far fa-check-square"></i> ' . translate('expired');
									$labelmode = 'label-danger-custom';
								}
								echo "<span class='label " . $labelmode . " '>" . $status . "</span>";
								?>
							</td>
							<td><?php echo _d($row['created_at']);?></td>
							<td class="min-w-c">
								<button class="btn btn-circle btn-default" onclick="getJoinModal('<?=$row['meeting_id'] . "|" . $row['id'] ?>', this);" data-loading-text="<i class='far fa-spinner fa-spin'></i> Processing">
									<i class="far fa-network-wired"></i> <?php echo translate('join_class'); ?> 
								</button>
							</td>
						</tr>
						<?php } endforeach; ?>
					</tbody>
				</table>
			</div>
		</section>
	</div>
</div>

<div class="zoom-anim-dialog modal-block modal-block-lg mfp-hide" id="modal">
	<section class="panel">
		<header class="panel-heading">
			<h4 class="panel-title"><i class="far fa-bars"></i> <?php echo translate('live_class'); ?></h4>
		</header>
		<div class="panel-body">
			<div id='quick_view'></div>
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

<script type="text/javascript">
	function getJoinModal(id, elem) {
		var btn = $(elem);
	    $.ajax({
	        url: base_url + 'userrole/joinModal',
	        type: 'POST',
	        data: {'meeting_id': id},
	        dataType: "html",
            beforeSend: function () {
                btn.button('loading');
            },
	        success: function (data) {
	            $('#quick_view').html(data);
	            mfp_modal('#modal');
	        },
            error: function (xhr) {
                btn.button('reset');
            },
            complete: function () {
               btn.button('reset');
            } 
	    });
	}
</script>