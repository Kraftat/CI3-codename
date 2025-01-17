<?php
$widget = (is_superadmin_loggedin() ? 2 : 3);
$branch = $this->db->where('id',$branch_id)->get('branch')->row_array();
?>
<div class="row">
	<div class="col-md-12">
		<section class="panel">
			<?php echo form_open('exam/tabulation_sheet', array('class' => 'validate')); ?>
			<header class="panel-heading">
				<h4 class="panel-title"><?=translate('select_ground')?></h4>
			</header>
			<div class="panel-body">
				<div class="row mb-sm">
				<?php if (is_superadmin_loggedin() ): ?>
					<div class="col-md-3 mb-sm">
						<div class="form-group">
							<label class="control-label"><?=translate('branch')?> <span class="required">*</span></label>
							<?php
								$arrayBranch = $this->appLib->getSelectList('branch');
								echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id' required
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
							?>
						</div>
					</div>
				<?php endif; ?>
					<div class="col-md-<?=$widget?> mb-sm">
						<div class="form-group">
							<label class="control-label"><?=translate('academic_year')?> <span class="required">*</span></label>
							<?php
								$arrayYear = array("" => translate('select'));
								$years = $builder->get('schoolyear')->result();
								foreach ($years as $year){
									$arrayYear[$year->id] = $year->school_year;
								}
								echo form_dropdown("session_id", $arrayYear, set_value('session_id', get_session_id()), "class='form-control' required
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
						</div>
					</div>
					<div class="col-md-<?=$widget?> mb-sm">
						<div class="form-group">
							<label class="control-label"><?=translate('exam')?> <span class="required">*</span></label>
							<?php
								
								if(!empty($branch_id)){
									$arrayExam = array("" => translate('select'));
									$exams = $builder->getWhere('exam', array('branch_id' => $branch_id,'session_id' => get_session_id()))->result();
									foreach ($exams as $exam){
										$arrayExam[$exam->id] = $applicationModel->exam_name_by_id($exam->id);
									}
								} else {
									$arrayExam = array("" => translate('select_branch_first'));
								}
								echo form_dropdown("exam_id", $arrayExam, set_value('exam_id'), "class='form-control' id='exam_id' required
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
						</div>
					</div>

					<div class="col-md-3 mb-sm">
						<div class="form-group">
							<label class="control-label"><?=translate('class')?> <span class="required">*</span></label>
							<?php
								$arrayClass = $this->appLib->getClass($branch_id);
								echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)'
								required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
						</div>
					</div>

					<div class="col-md-<?=$widget?>">
						<div class="form-group">
							<label class="control-label"><?=translate('section')?> <span class="required">*</span></label>
							<?php
								$arraySection = $this->appLib->getSections(set_value('class_id'), true);
								echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id' required
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
						</div>
					</div>
				</div>
			</div>
			<div class="panel-footer">
				<div class="row">
					<div class="col-md-offset-10 col-md-2">
						<button type="submit" name="submit" value="search" class="btn btn-default btn-block"><i class="far fa-filter"></i> <?=translate('filter')?></button>
					</div>
				</div>
			</div>
			<?php echo form_close();?>
		</section>

		<?php if (isset($get_subjects)) { ?>
			<section class="panel appear-animation" data-appear-animation="<?= $global_config['animations'];?>" data-appear-animation-delay="100">
				<header class="panel-heading">
					<h4 class="panel-title">
						<i class="far fa-users"></i> <?=translate('tabulation_sheet')?>
					</h4>
				</header>
				<div class="panel-body">
					<div class="table-responsive mt-sm mb-md">
						<div id="printResult">
							<!-- hidden school information prints -->
							<div class="visible-print">
								<center>
									<h4 class="text-dark text-weight-bold"><?=$branch['name']?></h4>
									<h5 class="text-dark"><?=$branch['address']?></h5>
									<h5 class="text-dark text-weight-bold"><?=$applicationModel->exam_name_by_id(set_value('exam_id'))?> - Tabulation Sheet</h5>
									<h5 class="text-dark">
										<?php 
										echo translate('class') . ' : ' . get_type_name_by_id('class', set_value('class_id'));
										echo ' ( ' . translate('section') . ' : ' . get_type_name_by_id('section', set_value('section_id')) . ' )';
										?>
									</h5>
									<hr>
								</center>
							</div>
							<table class="table table-bordered table-hover table-condensed mb-none">
								<thead class="text-dark">
									<tr>
										<td><?=translate('sl')?></td>
										<td><?=translate('students')?></td>
										<td><?=translate('roll')?></td>
                                        <?php
                                            foreach($get_subjects as $subject){
                                            	$fullMark = array_sum(array_column(json_decode($subject['mark_distribution'], true), 'full_mark'));
                                                echo '<td>' . $subject['subject_name'] . " (" . $fullMark . ')</td>';
                                            }
                                        ?>
										<td><?=translate('total_marks')?></td>
										<td>GPA</td>
										<td><?=translate('result')?></td>
									</tr>
								</thead>
								<tbody>
									<?php
									$count = 1;
									$enrolls = $builder->getWhere('enroll', array(
										'class_id' 		=> set_value('class_id'),
										'section_id' 	=> set_value('section_id'),
										'session_id' 	=> set_value('session_id'),
										'branch_id' 	=> $branch_id,
									))->result_array();
									if(count($enrolls)) {
										foreach($enrolls as $enroll):
											$stu = $builder->select('CONCAT(first_name, " ", last_name) as fullname')
											->where('id', $enroll['student_id'])
											->get('student')->row_array();
											?>
									<tr>
										<td><?php echo $count++; ?></td>
										<td><?php echo $stu['fullname']; ?></td>
										<td><?php echo $enroll['roll']; ?></td>
										<?php
										$totalMarks 		= 0;
										$totalFullmarks 	= 0;
										$totalGradePoint 	= 0;
										$grand_result 		= 0;
										$unset_subject 		= 0;
										foreach ($get_subjects as $subject):
											$result_status = 1;
											?>
										<td>
										<?php
											$this->db->where(array(
												'class_id' 	 => set_value('class_id'),
												'exam_id'	 => set_value('exam_id'),
												'subject_id' => $subject['subject_id'],
												'student_id' => $enroll['student_id'],
												'session_id' => set_value('session_id')
											));
											$getMark = $builder->get('mark')->row_array();
											if (!empty($getMark)) {
												if ($getMark['absent'] != 'on') {
													$totalObtained = 0;
													$totalFullMark = 0;
													$fullMarkDistribution = json_decode($subject['mark_distribution'], true);
													$obtainedMark = json_decode($getMark['mark'], true);
													foreach ($fullMarkDistribution as $i => $val) {
														$obtained_mark = floatval($obtainedMark[$i]);
														$totalObtained += $obtained_mark;
														$totalFullMark += $val['full_mark'];
														$passMark = floatval($val['pass_mark']);
														if ($obtained_mark < $passMark) {
															$result_status = 0;
														}
													}
													echo ($totalObtained . "/" . $totalFullMark);
													if ($totalObtained != 0 && !empty($totalObtained)) {
														$grade = $this->exam_model->get_grade($totalObtained, $branch_id);
														$totalGradePoint += $grade['grade_point'];
													}
													$totalMarks += $totalObtained;
												} else {
													echo translate('absent');
												}
												$totalFullmarks += $totalFullMark;
											} else {
												echo "N/A";
												$unset_subject++;
											}
										?>
										</td>
										<?php endforeach; ?>
										<td><?php echo ($totalMarks . '/' . $totalFullmarks); ?></td>
										<td>
											<?php
											$totalSubjects = count($get_subjects);
											if(!empty($totalSubjects)) {
												echo number_format(($totalGradePoint / $totalSubjects), 2,'.','');
											}
											?>
										</td>
										<td>
										<?php
											if ($unset_subject == 0) {
												if (empty($result_status)) {
													echo '<span class="label label-primary">PASS</span>';
												} else {
													echo '<span class="label label-danger">FAIL</span>';
												}
											}
										?>
										</td>
									</tr>
									<?php
									endforeach;
									}else{
										$colspan = ($get_subjects->num_rows() + 5);
										echo '<tr><td colspan="' . $colspan . '"><h5 class="text-danger text-center">' . translate('no_information_available') . '</td></tr>';
									}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<footer class="panel-footer">
					<div class="row">
						<div class="col-md-offset-10 col-md-2">
							<button onclick="fn_printElem('printResult')" class="btn btn-default btn-sm btn-block">
								<i class="far fa-print"></i> <?=translate('print')?>
							</button>
						</div>
					</div>
				</footer>
			</section>
	<?php } ?>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		$('#branch_id').on("change", function() {
			var branchID = $(this).val();
			getClassByBranch(branchID);
			getExamByBranch(branchID);
		});
	});
</script>
