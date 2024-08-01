<style type="text/css">
/* Package Subscription */
.package ul.nav-tabs {
    text-align: center;
}

.package .nav-tabs li a {
    font-size: 1.4rem;
}

.panel.single-pricing-pack {
    transition: all 0.2s ease 0s;
    border-radius: 1rem !important;
    border: 2px solid #ced0d2;
    margin-top: 30px;
    position: relative;
    overflow: hidden;
    -webkit-transition: all 0.5s ease 0s;
    -moz-transition: all 0.5s ease 0s;
    transition: all 0.5s ease 0s;
}

.package-name {
    position: relative;
    background: transparent;
    padding: 35px 15px 20px;
}

.package-name h5 {
    margin-bottom: 0;
    font-size: 27px;
}

.package-name h5 span {
	height: 30px;
	line-height: 30px;
	position: absolute;
	top: 20px;
	right: -7px;
	background: #7800d7;
	text-align: center;
	color: #ffffff;
	font-size: 12px;
	padding: 0 15px;
	font-weight: 500;
}

.pricing-header {
    position: relative;
    background: transparent;
    padding: 10px 15px 20px;
    border: none;
}

.single-pricing-pack .panel-body {
    color: rgb(132, 146, 166);
    flex: 1 1 auto;
    padding: 40px 55px;
}

.pricing-feature-list li {
    font-size: 16px;
    line-height: 24px;
    color: #7c8088;
    padding: 6px 0;
    text-align: left;
}

.pricing-header .price {
    font-size: 30px;
    font-weight: 700;
    color: #404040;
}

html.dark .pricing-header .price,
html.dark .pricing-header .price span {
    color: #fff;
}

.pricing-header::after {
    content: "";
    display: block;
    width: 50%;
    position: absolute;
    bottom: 0;
    left: 65%;
    margin-left: -40%;
    height: 1px;
    background: radial-gradient(at center center, rgb(112, 0, 184) 0px, rgba(255, 255, 255, 0) 75%);
}

.pricing-header .price span {
    font-size: 20px;
    font-weight: 300;
    color: #404040;
}

.pricing-feature-list li span {
    font-weight: 500;
    color: #404040;
    font-size: 16px;
}

html.dark .pricing-feature-list li span {
    color: #fff;
}

.pricing-feature-list li i {
    color: #0fba0b;
    font-size: 16px;
}

.pricing-feature-list li i.fa-times-circle {
    color: #ff4049 !important;
}

.single-pricing-pack:hover {
    box-shadow: 3px 5px 15px rgba(0, 0, 0, 0.2);
    border-color: #7800d7;
}

.pricing-header .discount {
    font-size: 20px;
    font-weight: 400;
    text-decoration: line-through;
    display: inline;
}
</style>
<?php 
$getType = $getType ?? 'all';
unset($getPeriodType['']);
?>
<section class="panel">
    <div class="tabs-custom package">
        <ul class="nav nav-tabs justify-content-center">
            <li class="nav-item <?php if ('all' == $getType) echo 'active'; ?>">
                <a class="nav-link" href="<?php echo base_url('subscription/list'); ?>">
                    <i class="far fa-dot-circle"></i> <?php echo translate('all') . " " . translate('plan') ?>
                </a>
            </li>
            <?php foreach ($getPeriodType as $key => $value) { 
                if ($key == 1 || $key == 2) continue; // Hide types 1 and 2
            ?>
                <li class="nav-item <?php if ($key == $getType) echo 'active'; ?>">
                    <a class="nav-link" href="<?php echo base_url('subscription/list?type='. $key); ?>">
                        <i class="far fa-dot-circle"></i> <?php echo $value ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
        <div class="tab-content">
            <div class="tab-pane box active">
                <div class="row">
                <?php if (!empty($packages)) { 
                    foreach ($packages as $package) {
                        $periodType = $package['period_type'];
                    ?>
                    <div class="col-lg-4 col-md-4 col-sm-6">
                        <div class="panel text-center single-pricing-pack popular-price">
                            <div class="package-name">
                                <h5><?php echo $package['name'] ?> <?php echo ($package['recommended'] == 1 ) ? '<span class="badge color-1 color-1-bg">Popular</span>' : ""; ?></h5>
                            </div>
                            <div class="panel-header pricing-header">
                                <div class="price text-center">
                                <?php if ($package['discount'] == 0) { ?>
                                    <?php echo $currency_symbol . " " .  $package['price'] ?><span>/ 
                                    <?php echo ($periodType == 1 ? $getPeriodType[$periodType] : ($package['period_value'] . " " . $getPeriodType[$periodType])); ?>
                                    </span>
                                <?php } else { ?>
                                    <div class="discount"><?php echo $currency_symbol . " " . number_format($package['price'], 1, '.', '') ?></div> 
                                    <?php echo $currency_symbol . " " . number_format(($package['price'] - $package['discount']), 1, '.', ''); ?><span> / <?php echo ($periodType == 1 ? $getPeriodType[$periodType] : ($package['period_value'] . " " . $getPeriodType[$periodType])) ?></span>
                                <?php } ?>
                                </div>
                            </div>
                            <div class="panel-body">
                                <ul class="list-unstyled pricing-feature-list">
                                    <li><?=translate('student') . " " . translate('limit')?> : <b><?=$package['student_limit']?></b></li>
                                    <li><?=translate('staff') . " " . translate('limit')?> : <b><?=$package['staff_limit']?></b></li>
                                    <li><?=translate('teacher') . " " . translate('limit')?> : <b><?=$package['teacher_limit']?></b></li>
                                    <li><?=translate('parents') . " " . translate('limit')?> : <b><?=$package['parents_limit']?></b></li>
                                    <?php 
                                    if (empty($package['permission']) || $package['permission'] == 'null' ) {
                                        $permissions = [];
                                    } else {
                                        $permissions = json_decode($package['permission'], true);
                                    }

                                    foreach ($modules as $module) {
                                        if (in_array($module->id, $permissions)) {
                                    ?>
                                        <li><i class="far fa-check-circle"></i> <?php echo $module->name ?></li>
                                    <?php } ?>
                                    <?php } ?>
                                </ul>
                                <a href="<?php echo base_url('subscription/renew?id=' . $package['id']); ?>" class="btn btn btn-default btn-block mt-lg"><?php echo translate('purchase_now'); ?></a>
                            </div>
                        </div>
                    </div>
                <?php } } else {
                    echo '<div class="text-center mb-lg mt-xs text-danger">' . translate('no_information_available') . '</div>';
                } ?>
                </div>
            </div>
        </div>
    </div>
</section>
