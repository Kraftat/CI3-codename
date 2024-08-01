<?php
$div = 0;
if (get_permission('employee_count_widget', 'is_view')) {
	$div++;	
}
if (get_permission('student_count_widget', 'is_view')) {
	$div++;	
}
if (get_permission('parent_count_widget', 'is_view')) {
	$div++;	
}
if (get_permission('teacher_count_widget', 'is_view')) {
	$div++;	
}
if ($div == 0) {
	$widget1 = 0;
}else{
	$widget1 = 12 / $div;
}

$div2 = 0;
if (get_permission('admission_count_widget', 'is_view')) {
	$div2++;	
}
if (get_permission('voucher_count_widget', 'is_view')) {
	$div2++;	
}
if (get_permission('transport_count_widget', 'is_view') && moduleIsEnabled('transport')) {
	$div2++;	
}
if (get_permission('hostel_count_widget', 'is_view') && moduleIsEnabled('hostel')) {
	$div2++;	
}
if ($div2 == 0) {
	$widget2 = 0;
}else{
	$widget2 = 12 / $div2;
}

$div3 = 12;
if (get_permission('student_birthday_widget', 'is_view') || get_permission('staff_birthday_widget', 'is_view')) {
	$div3 = 9;	
}
?>
<?php if ($sqlMode == true) { ?>
    <div class="alert alert-danger">
        <i class="far fa-exclamation-triangle"></i> This School management system may not work properly because "ONLY_FULL_GROUP_BY" is enabled, <strong>Strongly recommended</strong> - consult with your hosting provider to disable "ONLY_FULL_GROUP_BY" in sql_mode configuration.
    </div>
<?php } ?>

<?php 
if (!is_superadmin_loggedin()) {
	if (!empty($this->saas_model->getSubscriptionsExpiredNotification())) { ?>
    <div class="alert alert-danger">
        <?php echo $this->saas_model->getSubscriptionsExpiredNotification(); ?>
    </div>
<?php } } ?>

<style>
    .widget-card {
        border-radius: 10px;
        border: 1px solid #D1D1D1;
        background-color: #fff;
        padding: 10px;
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
    }

    .widget-header {
        font-size: 1.2em;
        font-weight: bold;
        margin-bottom: 10px;
        color: #000; /* Header text color */
        display: flex;
        align-items: center;
    }

    .widget-header .icon-circle {
        width: 35px;
        height: 35px;
        border-radius: 40%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        margin-left: 10px;
    }

    .widget-header .icon-circle i {
        font-size: 1em;
        color: inherit; /* Icon color */
    }

    .widget-value {
        font-size: 2em;
        color: #000; /* Value text color */
        padding-top: 5px;
        padding-bottom: 5px;
        margin: 0px 10px;
        }

    p {
        margin: 0px 10px 0px;
    }

    .widget-footer {
        font-size: 0.9em;
        color: #7f8c8d; /* Footer text color */
        margin: 0 10px;
    }

    /* Specific pastel colors for each icon background */
    .admission-icon {
        background-color: #a8d5e2; /* Pastel blue */
        color: #6ca5b2; /* Darker blue */
    }

    .voucher-icon {
        background-color: #ffd8b1; /* Pastel orange */
        color: #b38a67; /* Darker orange */
    }

    .transport-icon {
        background-color: #c5e1a5; /* Pastel green */
        color: #8ea47b; /* Darker green */
    }

    .hostel-icon {
        background-color: #f8bbd0; /* Pastel pink */
        color: #c7879b; /* Darker pink */
    }

    .employee-icon {
        background-color: #d1c4e9; /* Pastel purple */
        color: #9f91b5; /* Darker purple */
    }

    .student-icon {
        background-color: #ffcc80; /* Pastel yellow */
        color: #b38f45; /* Darker yellow */
    }

    .parent-icon {
        background-color: #80cbc4; /* Pastel teal */
        color: #5e968f; /* Darker teal */
    }

    .teacher-icon {
        background-color: #ffab91; /* Pastel coral */
        color: #b37566; /* Darker coral */
    }

    .row {
        margin-right: 0px;
        margin-left: 0px;
    }
</style>

<!-- Modernized dashboard cards with smaller icons and background circles -->
<h1 style="margin-left: 30px;">
    <?php echo translate('Hello'); ?>, <?php echo $this->session->userdata('name'); ?>! 👋
</h1>
<h4 style="margin-bottom: 30px; margin-left: 30px;">
    <?php echo translate('Welcome back to your dashboard.'); ?>
</h4>

<?php if ($widget2 > 0) { ?>
    <div class="row widget-2">
        <div class="col-12">
            <div class="row">
                <?php if (get_permission('admission_count_widget', 'is_view')) { ?>
                    <div class="col-lg-<?php echo $widget2; ?> col-sm-6">
                        <div class="widget-card">
                            <div class="widget-header">
                                <div class="icon-circle admission-icon">
                                    <i class="far fa-address-card"></i>
                                </div>
                                <h4><?php echo translate('admission'); ?></h4>
                            </div>
                            <p class="widget-value"><?=$get_monthly_admission;?></p>
                            <div class="widget-footer">
                                <?php echo translate('interval_month'); ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                
                <?php if (get_permission('voucher_count_widget', 'is_view')) { ?>
                    <div class="col-lg-<?php echo $widget2; ?> col-sm-6">
                        <div class="widget-card">
                            <div class="widget-header">
                                <div class="icon-circle voucher-icon">
                                    <i class="far fa-money-check-alt"></i>
                                </div>
                                <h4><?php echo translate('voucher'); ?></h4>
                            </div>
                            <p class="widget-value"><?=$get_voucher?></p>
                            <div class="widget-footer">
                                <?php echo translate('total_number'); ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                
                <?php if (get_permission('transport_count_widget', 'is_view') && moduleIsEnabled('transport')) { ?>
                    <div class="col-lg-<?php echo $widget2; ?> col-sm-6">
                        <div class="widget-card">
                            <div class="widget-header">
                                <div class="icon-circle transport-icon">
                                    <i class="far fa-road"></i>
                                </div>
                                <h4><?php echo translate('transport'); ?></h4>
                            </div>
                            <p class="widget-value"><?=$get_transport_route?></p>
                            <div class="widget-footer">
                                <?php echo translate('total_route'); ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if (get_permission('hostel_count_widget', 'is_view') && moduleIsEnabled('hostel')) { ?>
                    <div class="col-lg-<?php echo $widget2; ?> col-sm-6">
                        <div class="widget-card">
                            <div class="widget-header">
                                <div class="icon-circle hostel-icon">
                                    <i class="far fa-warehouse"></i>
                                </div>
                                <h4><?php echo translate('hostel'); ?></h4>
                            </div>
                            <p class="widget-value"><?php
                                if (!empty($school_id))
                                    $this->db->where('branch_id', $school_id);
                                $hostel_room = $builder->select('id')->get('hostel_room')->num_rows();
                                echo $hostel_room;
                            ?></p>
                            <div class="widget-footer">
                                <?php echo translate('total_room'); ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
<?php } ?>

<?php if ($widget1 > 0) { ?>
    <div class="row widget-1">
        <div class="col-12">
            <div class="row">
                <?php if (get_permission('employee_count_widget', 'is_view')) { ?>
                    <div class="col-lg-<?php echo $widget1; ?> col-sm-6">
                        <div class="widget-card">
                            <div class="widget-header">
                                <div class="icon-circle employee-icon">
                                    <i class="far fa-users"></i>
                                </div>
                                <h4><?php echo translate('employees'); ?></h4>
                            </div>
                            <p class="widget-value"><?php
                                $staff = $this->dashboard_model->getstaffcounter('', $school_id);
                                echo $staff['snumber'];
                            ?></p>
                            <div class="widget-footer">
                                <?php echo translate('total_strength'); ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if (get_permission('student_count_widget', 'is_view')) { ?>
                    <div class="col-lg-<?php echo $widget1; ?> col-sm-6">
                        <div class="widget-card">
                            <div class="widget-header">
                                <div class="icon-circle student-icon">
                                    <i class="far fa-user-graduate"></i>
                                </div>
                                <h4><?php echo translate('students'); ?></h4>
                            </div>
                            <p class="widget-value"><?=$get_total_student?></p>
                            <div class="widget-footer">
                                <?php echo translate('total_strength'); ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if (get_permission('parent_count_widget', 'is_view')) { ?>
                    <div class="col-lg-<?php echo $widget1; ?> col-sm-6">
                        <div class="widget-card">
                            <div class="widget-header">
                                <div class="icon-circle parent-icon">
                                    <i class="far fa-user-tie"></i>
                                </div>
                                <h4><?php echo translate('parents'); ?></h4>
                            </div>
                            <p class="widget-value"><?php
                                if (!empty($school_id))
                                    $this->db->where('branch_id', $school_id);
                                echo $builder->select('id')->get('parent')->num_rows();
                            ?></p>
                            <div class="widget-footer">
                                <?php echo translate('total_strength'); ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if (get_permission('teacher_count_widget', 'is_view')) { ?>
                    <div class="col-lg-<?php echo $widget1; ?> col-sm-6">
                        <div class="widget-card">
                            <div class="widget-header">
                                <div class="icon-circle teacher-icon">
                                    <i class="far fa-chalkboard-teacher"></i>
                                </div>
                                <h4><?php echo translate('teachers'); ?></h4>
                            </div>
                            <p class="widget-value"><?php
                                $staff = $this->dashboard_model->getstaffcounter(3, $school_id);
                                echo $staff['snumber'];
                            ?></p>
                            <div class="widget-footer">
                                <?php echo translate('total_strength'); ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
<?php } ?>




<div class="dashboard-page">
	<div class="row">
<?php if (get_permission('monthly_income_vs_expense_chart', 'is_view')) { ?>
		<!-- monthly cash book transaction -->
		
		<h4 style="margin: 20px; margin-bottom: 30px;">
    <?php echo translate('Analytics Summary'); ?>
</h4>

		<div class="<?php echo get_permission('annual_student_fees_summary_chart', 'is_view') ? 'col-md-12 col-lg-4 col-xl-3' : 'col-md-12'; ?>">
			<section class="panel pg-fw">
				<div class="panel-body">
					<h4 class="chart-title mb-xs"><?=translate('income_vs_expense_of') . " " . translate(strtolower(date('F')))?></h4>
					<div id="cash_book_transaction"></div>
					<div class="round-overlap"><i class='fa-regular fa-file-invoice-dollar'></i></div>
					<div class="text-center">
						<ul class="list-inline">
							<li>
								<h6 class="text-muted"><i class="fa fa-circle text-green"></i> <?=translate('income')?></h6>
							</li>
							<li>
								<h6 class="text-muted"><i class="fa fa-circle text-danger"></i> <?=translate('expense')?></h6>
							</li>
						</ul>
					</div>
				</div>
			</section>
		</div>
<?php } ?>
<?php if (get_permission('annual_student_fees_summary_chart', 'is_view')) { ?>
		<!-- student fees summary graph -->
		<div class="<?php echo get_permission('monthly_income_vs_expense_chart', 'is_view') ? 'col-md-12 col-lg-8 col-xl-9' : 'col-md-12'; ?>">
			<section class="panel">
				<div class="panel-body">
					<h4 class="chart-title mb-md"><?=translate('annual_fee_summary')?></h4>
					<div class="pe-chart">
						<canvas id="fees_graph" style="height: 322px;"></canvas>
					</div>
				</div>
			</section>
		</div>
<?php } ?>
	</div>

	<!-- student quantity chart -->
	<div class="row">
<?php if (get_permission('student_quantity_pie_chart', 'is_view')) { ?>
		<div class="<?php echo get_permission('weekend_attendance_inspection_chart', 'is_view') ? 'col-md-12 col-lg-4 col-xl-3' : 'col-md-12'; ?>">
			<section class="panel pg-fw">
				<div class="panel-body">
					<h4 class="chart-title mb-xs"><?=translate('student_quantity')?></h4>
					<div id="student_strength"></div>
					<div class="round-overlap"><i class="far fa-school"></i></div>
				</div>
			</section>
		</div>
<?php } ?>
<?php if (get_permission('weekend_attendance_inspection_chart', 'is_view')) { ?>
		<div class="<?php echo get_permission('student_quantity_pie_chart', 'is_view') ? 'col-md-12 col-lg-8 col-xl-9' : 'col-md-12'; ?>">
			<section class="panel">
				<div class="panel-body">
					<h4 class="chart-title mb-md"><?=translate('weekend_attendance_inspection')?></h4>
					<div class="pg-fw">
						<canvas id="weekend_attendance" style="height: 340px;"></canvas>
					</div>
				</div>
			</section>
		</div>
<?php } ?>
	</div>

	<div class="row">
	    <!-- event calendar -->
		<div class="col-md-<?php echo $div3 ?>">
			<section class="panel">
				<div class="panel-body">
					<div id="event_calendar"></div>
				</div>
			</section>
		</div>
	<?php if ($div3 == 9) { ?>
		<div class="col-md-3">
			<style>
				.panel-with-background {
					background-image: url('<?php echo base_url('uploads/app_image/birthday.png'); ?>');
					background-size: cover; /* Cover the entire container */
					background-position: center; /* Center the image */
					}
			</style>
		<div class="row widget-row-in">
    <?php if (get_permission('student_birthday_widget', 'is_view')) { ?>
        <div class="col-xs-12">
            <a href="<?php echo base_url('birthday/student') ?>" data-toggle="tooltip" data-original-title="<?=translate('view') . " " . translate('list')?>">
                <div class="panel panel-with-background">
                    <div class="panel-body">
                        <div class="widget-col-in row">
                            <div class="col-md-6 col-sm-6 col-xs-6">
                                <h1>🥳</h1>
                                <h5 class="text-muted"><?=translate('student')?></h5>
                            </div>
                            <div class="col-md-6 col-sm-6 col-xs-6">
                                <h3 class="counter text-right mt-md text-primary">
                                    <?php
                                    $builder->select('student.id');
                                    $this->db->from('student');
                                    $builder->join('enroll', 'enroll.student_id = student.id', 'inner');
                                    $this->db->where("enroll.session_id", get_session_id());
                                    if (!empty($school_id))
                                        $this->db->where('branch_id', $school_id);
                                    $this->db->where("MONTH(student.birthday)", date('m'));
                                    $this->db->where("DAY(student.birthday)", date('d'));
                                    $this->db->group_by('student.id');
                                    $stuTodayBirthday = $builder->get()->result();
                                    echo(count($stuTodayBirthday));
                                    ?>
                                </h3>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="box-top-line line-color-primary">
                                    <span class="text-muted text"><?=translate('today_birthday')?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    <?php } if (get_permission('staff_birthday_widget', 'is_view')) { ?>
        <div class="col-xs-12">
            <a href="<?php echo base_url('birthday/staff') ?>" data-toggle="tooltip" data-original-title="<?=translate('view') . " " . translate('list')?>">
                <div class="panel panel-with-background">
                    <div class="panel-body">
                        <div class="widget-col-in row">
                            <div class="col-md-6 col-sm-6 col-xs-6">
                                <h1>🎂</h1>
                                <h5 class="text-muted"><?=translate('employee')?></h5>
                            </div>
                            <div class="col-md-6 col-sm-6 col-xs-6">
                                <h3 class="counter text-right mt-md text-primary">
                                    <?php
                                    $builder->select('id');
                                    if (!empty($school_id))
                                        $this->db->where('branch_id', $school_id);
                                    $this->db->where("MONTH(birthday)", date('m'));
                                    $this->db->where("DAY(birthday)", date('d'));
                                    $emyTodayBirthday = $builder->get('staff')->result();
                                    echo(count($emyTodayBirthday));
                                    ?>
                                </h3>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="box-top-line line-color-primary">
                                    <span class="text-muted text"><?=translate('today_birthday')?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    <?php } ?>
</div>

<!-- Add this CSS to your styles -->
<style>
.panel-with-background {
    background-color: #f9f9f9; /* Light background color */
    border: 1px solid #ddd; /* Light border color */
    margin-bottom: 15px; /* Space between panels */
    transition: background-color 0.3s; /* Smooth transition */
}

.panel-with-background:hover {
    background-color: #e9e9e9; /* Slightly darker background on hover */
}

.box-top-line {
    border-top: 2px solid #337ab7; /* Adjust color to match your theme */
    margin-top: 10px;
    padding-top: 10px;
}
</style>

			</div>
		</div>
	<?php } ?>
	</div>
</div>

<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<section class="panel">
		<header class="panel-heading">
			<div class="panel-btn">
				<button onclick="fn_printElem('printResult')" class="btn btn-default btn-circle icon" ><i class="far fa-print"></i></button>
			</div>
			<h4 class="panel-title"><i class="far fa-info-circle"></i> <?=translate('event_details')?></h4>
		</header>
		<div class="panel-body">
			<div id="printResult" class=" pt-sm pb-sm">
				<div class="table-responsive">						
					<table class="table table-bordered table-condensed text-dark tbr-top" id="ev_table">
						
					</table>
				</div>
			</div>
		</div>
		<footer class="panel-footer">
			<div class="row">
				<div class="col-md-12 text-right">
					<button class="btn btn-default modal-dismiss">
						<?=translate('close')?>
					</button>
				</div>
			</div>
		</footer>
	</section>
</div>

<script type="application/javascript">
(function($) {
	$('#event_calendar').fullCalendar({
		header: {
		left: 'prev,next,today',
		center: 'title',
			right: 'month,agendaWeek,agendaDay,listWeek'
		},
		firstDay: 1,
		height: 720,
		droppable: false,
		editable: true,
		timezone: 'UTC',
		lang: '<?php echo $language ?>',
		events: {
			url: "<?=base_url('event/get_events_list/'. $school_id)?>"
		},
		
		eventRender: function(event, element) {
			$(element).on("click", function() {
				viewEvent(event.id);
			});
			if(event.icon){          
				element.find(".fc-title").prepend("<i class='far fa-"+event.icon+"'></i> ");
			}
		}
	});

	// Annual Fee Summary JS
var total_fees = <?php echo json_encode($fees_summary["total_fee"]); ?>;
var total_paid = <?php echo json_encode($fees_summary["total_paid"]); ?>;
var total_due = <?php echo json_encode($fees_summary["total_due"]); ?>;
var feesGraph = {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: '<?php echo translate("total"); ?>',
            data: total_fees,
            backgroundColor: 'rgba(173, 216, 230, 0.8)', // Light Blue with higher opacity
            borderColor: 'rgb(173, 216, 230)', // Light Blue
            borderWidth: 1
        }, {
            label: '<?php echo translate("collected"); ?>',
            data: total_paid,
            backgroundColor: 'rgba(144, 238, 144, 0.8)', // Light Green with higher opacity
            borderColor: 'rgb(144, 238, 144)', // Light Green
            borderWidth: 1
        }, {
            label: '<?php echo translate("remaining"); ?>',
            data: total_due,
            backgroundColor: 'rgba(255, 182, 193, 0.8)', // Light Pink with higher opacity
            borderColor: 'rgb(255, 182, 193)', // Light Pink
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        circumference: Math.PI,
        tooltips: {
            mode: 'index',
            bodySpacing: 4
        },
        legend: {
            position: 'bottom',
            labels: {
                boxWidth: 12
            }
        },
        scales: {
            xAxes: [{
                scaleLabel: {
                    display: false
                }
            }],
            yAxes: [{
                stacked: true,
                scaleLabel: {
                    display: false
                }
            }]
        }
    }
};

var days = <?php echo json_encode($weekend_attendance["days"]); ?>;
var employees_att = <?php echo json_encode($weekend_attendance["employee_att"]); ?>;
var student_att = <?php echo json_encode($weekend_attendance["student_att"]); ?>;
var weekendAttendanceChart = {
    type: 'bar',
    data: {
        labels: days,
        datasets: [{
            label: '<?php echo translate("employee"); ?>',
            data: employees_att,
            backgroundColor: 'rgb(250, 235, 215)', // Light Almond with higher opacity
            borderColor: 'rgb(250, 235, 215)', // Light Almond
            borderWidth: 1,
            fill: false
        }, {
            label: '<?php echo translate("student"); ?>',
            data: student_att,
            backgroundColor: 'rgb(173, 216, 230)', // Light blue with higher opacity
            borderColor: 'rgb(173, 216, 230)', // Light blue
            borderWidth: 1,
            fill: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        circumference: Math.PI,
        tooltips: {
            mode: 'index',
            bodySpacing: 4
        },
        legend: {
            position: 'bottom',
            labels: {
                boxWidth: 12
            }
        },
        scales: {
            xAxes: [{
                scaleLabel: {
                    display: false
                }
            }],
            yAxes: [{
                scaleLabel: {
                    display: false
                }
            }]
        }
    }
};

<?php if (get_permission('annual_student_fees_summary_chart', 'is_view')) { ?>
    var ctx = document.getElementById('fees_graph').getContext('2d');
    window.myLine = new Chart(ctx, feesGraph);
<?php } ?>
<?php if (get_permission('weekend_attendance_inspection_chart', 'is_view')) { ?>
    var ctx2 = document.getElementById('weekend_attendance').getContext('2d');
    window.myLine = new Chart(ctx2, weekendAttendanceChart);
<?php } ?>

<?php if (get_permission('monthly_income_vs_expense_chart', 'is_view')) { ?>
    // Monthly income vs expense chart
    var cash_book_transaction = document.getElementById("cash_book_transaction");
    var cashbookchart = echarts.init(cash_book_transaction);
    cashbookchart.setOption({
        tooltip: {
            trigger: 'item',
            formatter: "{a} <br/>{b} : <?= $global_config['currency_symbol'];?> {c} ({d}%)"
        }, 
        legend: {
            show: false
        },
        color: [
            'rgb(255, 182, 193)', // Light Pink
            'rgb(144, 238, 144)'  // Light Green
        ],
        series: [{
            name: 'Transaction',
            type: 'pie',
            radius: ['75%', '90%'],
            itemStyle: {
                normal: {
                    label: {
                        show: false
                    },
                    labelLine: {
                        show: false
                    }
                },
                emphasis: {
                    label: {
                        show: false
                    }
                }
            },
            data: <?=json_encode($income_vs_expense)?>
        }]
    });
<?php } ?>

<?php if (get_permission('student_quantity_pie_chart', 'is_view')) { ?>
    // Student Strength Doughnut Chart
    var color = [
        'rgb(173, 216, 230)', // Light Blue
        'rgb(255, 182, 193)', // Light Pink
        'rgb(255, 239, 213)', // Light Yellow
        'rgb(144, 238, 144)', // Light Green
        'rgb(221, 160, 221)', // Light Purple
        'rgb(250, 235, 215)', // Light Almond
        'rgb(240, 230, 140)', // Light Khaki
        'rgb(224, 255, 255)', // Light Cyan
        'rgb(255, 228, 181)', // Light Moccasin
        'rgb(255, 222, 173)', // Light NavajoWhite
        'rgb(245, 222, 179)'  // Light Wheat
    ];
    var strength_data = <?php echo json_encode($student_by_class);?>;
    var student_strength = document.getElementById("student_strength");
    var studentchart = echarts.init(student_strength);
    studentchart.setOption( {
        tooltip: {
            trigger: 'item',
            formatter: "{a} <br/>{b} : {c} ({d}%)"
        }, 
        legend: {
            type: 'scroll',
            x: 'center',
            y: 'bottom',
            itemWidth: 14,
<?php if($theme_config["dark_skin"] == "true"): ?>
            inactiveColor: '#4b4b4b',
            textStyle: {
                color: '#6b6b6c'
            }
<?php endif; ?>
        },
        series: [{
            name: 'Strength',
            type: 'pie',
            color: color,
            radius: ['70%', '85%'],
            center: ['50%', '46%'],
            itemStyle: {
                normal: {
                    label: {
                        show: false
                    },
                    labelLine: {
                        show: false
                    }
                },
                emphasis: {
                    label: {
                        show: false
                    }
                }
            },
            data: strength_data
        }]
    });
<?php } ?>

	// charts resize
	$(".sidebar-toggle").on("click",function(event){
		echartsresize();
	});

	$(window).on("resize", echartsresize);

	function echartsresize() {
		setTimeout(function () {
			if ($("#student_strength").length) {
				studentchart.resize();
			}
			if ($("#cash_book_transaction").length) {
				cashbookchart.resize();
			}
		}, 350);
	}
})(jQuery);
</script>