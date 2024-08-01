<div class="row appear-animation" data-appear-animation="<?=$global_config['animations'] ?>">
<div class="col-md-12 mb-lg">
    <div class="profile-head card">
        <div class="card-body">
            <div class="row justify-content-center">
                <!-- Profile Picture -->
                <div class="col-12 col-md-4 col-xl-3 mb-3 mb-md-0 d-flex justify-content-center">
                    <img class="rounded" src="<?=get_image_url('parent', $parent['photo']); ?>" class="img-fluid rounded-circle mx-auto d-block" alt="Profile Picture" style="width: 150px;height: 150px;">
                </div>

                <!-- Profile Details -->
                <div class="col-12 col-md-8 col-xl-9">
                    <h5 class="card-title text-center" style="padding: 0 35px;"><?=html_escape($parent['name'])?></h5>
                    <p class="card-text text-center" style="padding: 0 35px;"><small class="text-muted"><?=ucfirst('parent')?></small></p>
                    
                    <div class="info-list">
                        <div class="row d-flex justify-content-center" style="margin:20px;">
                            <div class="col-6 col-md-6 col-lg-6 col-xl-6">
                                <strong style="color: #999;"><?=translate('relation')?></strong>
                                <p style="padding:5px 0; font-size: 14px;"><?=html_escape($parent['relation'])?></p>
                            </div>
                            <div class="col-6 col-md-6 col-lg-6 col-xl-6">
                                <strong style="color: #999;"><?=translate('occupation')?></strong>
                                <p style="padding:5px 0; font-size: 14px;"><?=html_escape(empty($parent['occupation']) ? 'N/A' : $parent['occupation']);?></p>
                            </div>
                            <div class="col-6 col-md-6 col-lg-6 col-xl-6">
                                <strong style="color: #999;"><?=translate('income')?></strong>
                                <p style="padding:5px 0; font-size: 14px;"><?=html_escape(empty($parent['income']) ? 'N/A' : $parent['income']);?></p>
                            </div>
                            <div class="col-6 col-md-6 col-lg-6 col-xl-6">
                                <strong style="color: #999;"><?=translate('mobile_no')?></strong>
                                <p style="padding:5px 0; font-size: 14px;"><?=html_escape(empty($parent['mobileno']) ? 'N/A' : $parent['mobileno']);?></p>
                            </div>
                            <div class="col-6 col-md-6 col-lg-6 col-xl-6">
                                <strong style="color: #999;"><?=translate('email')?></strong>
                                <p style="padding:5px 0; font-size: 14px;"><?=html_escape(!empty($parent['email']) ? $parent['email'] : 'N/A')?></p>
                            </div>
                            <div class="col-6 col-md-6 col-lg-6 col-xl-6">
                                <strong style="color: #999;"><?=translate('address')?></strong>
                                <p style="padding:5px 0; font-size: 14px;"><?=html_escape(!empty($parent['address']) ? $parent['address'] : 'N/A'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-head {
        text-align: center;
    }

    .profile-head .info-list .col-6 {
        text-align: left;
    }

    @media (max-width: 767.98px) {
        .profile-head .info-list .col-6 {
            width: 45%;
            display: inline-block;
            vertical-align: top;
        }
		.card-title, .card-text {
            text-align: center!important;
            padding: 0 35px;
        }
    }
</style>

	<div class="col-md-12">
		<div class="panel-group" id="accordion">
		<div class="panel panel-accordion">
			<div class="panel-heading">
				<h4 class="panel-title">
                    <div class="pull-right mt-hs">
						<button class="btn btn-default btn-circle" id="authentication_btn">
							<i class="far fa-unlock-alt"></i> <?=translate('authentication')?>
						</button>
                    </div> 
					<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#profile">
						<i class="far fa-user-edit"></i> <?=translate('details')?>
					</a>
				</h4>
			</div>
			<div id="profile" class="accordion-body collapse <?=(session()->getFlashdata('profile_tab') ? 'in' : ''); ?>">
				<?php echo form_open_multipart($this->uri->uri_string()); ?>
				<input type="hidden" name="parent_id" value="<?php echo $parent['id']; ?>" id="parent_id">
				<div class="panel-body">
<?php if (is_superadmin_loggedin()) { ?>
					<!-- academic details-->
					<div class="headers-line mt-md">
						<i class="far fa-school"></i> <?=translate('academic_details')?>
					</div>
					<div class="row">
						<div class="col-md-12 mb-lg">
							<div class="form-group">
								<label class="control-label"><?=translate('branch')?> <span class="required">*</span></label>
								<?php
									$arrayBranch = $this->appLib->getSelectList('branch');
									echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id', $parent['branch_id']), "class='form-control' id='branch_id'
									data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
								?>
								<span class="error"><?php echo form_error('branch_id'); ?></span>
							</div>
						</div>
					</div>
<?php } ?>
					<!-- parents details -->
					<div class="headers-line">
						<i class="far fa-user-check"></i> <?=translate('parents_details')?>
					</div>
					<div class="row">
						<div class="col-md-6 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('name')?> <span class="required">*</span></label>
								<div class="input-group">
									<span class="input-group-addon"><i class="far fa-user"></i></span>
									<input class="form-control" name="name" type="text" value="<?=set_value('name', $parent['name'])?>" autocomplete="off" />
								</div>
								<span class="error"><?php echo form_error('name'); ?></span>
							</div>
						</div>
						<div class="col-md-6 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('relation')?> <span class="required">*</span></label>
								<input type="text" class="form-control" name="relation" value="<?=set_value('relation', $parent['relation'])?>" autocomplete="off" />
								<span class="error"><?php echo form_error('relation'); ?></span>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('father_name')?></label>
								<input class="form-control" name="father_name" type="text" value="<?=set_value('father_name', $parent['father_name'])?>" autocomplete="off" />
							</div>
						</div>
						<div class="col-md-6 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('mother_name')?></label>
								<input type="text" class="form-control" name="mother_name" value="<?=set_value('mother_name', $parent['mother_name'])?>" autocomplete="off" />
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-4 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('occupation')?> <span class="required">*</span></label>
								<input type="text" class="form-control" name="occupation" value="<?=set_value('occupation', $parent['occupation'])?>" autocomplete="off" />
								<span class="error"><?php echo form_error('occupation'); ?></span>
							</div>
						</div>
						<div class="col-md-4 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('income')?></label>
								<input type="text" class="form-control" name="income" value="<?=set_value('income', $parent['income'])?>" autocomplete="off" />
								<span class="error"><?php echo form_error('income'); ?></span>
							</div>
						</div>
						<div class="col-md-4 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('education')?></label>
								<input type="text" class="form-control" name="education" value="<?=set_value('education', $parent['education'])?>" />
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-3 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('city')?></label>
								<input type="text" class="form-control" name="city" value="<?=set_value('city', $parent['city'])?>" />
							</div>
						</div>
						<div class="col-md-3 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('state')?></label>
								<input type="text" class="form-control" name="state" value="<?=set_value('state', $parent['state'])?>" />
							</div>
						</div>
						<div class="col-md-3 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('mobile_no')?> <span class="required">*</span></label>
								<div class="input-group">
									<span class="input-group-addon"><i class="far fa-phone-volume"></i></span>
									<input class="form-control" name="mobileno" type="text" value="<?=set_value('mobileno', $parent['mobileno'])?>" autocomplete="off" />
								</div>
								<span class="error"><?php echo form_error('mobileno'); ?></span>
							</div>
						</div>
						<div class="col-md-3 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('email')?></label>
								<div class="input-group">
									<span class="input-group-addon"><i class="far fa-envelope-open"></i></span>
									<input type="email" class="form-control" name="email" id="email" value="<?=set_value('email', $parent['email'])?>" autocomplete="off" />
								</div>
								<span class="error"><?php echo form_error('email'); ?></span>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('address')?></label>
								<textarea name="address" rows="2" class="form-control" aria-required="true"><?=set_value('address', $parent['address'])?></textarea>
							</div>
						</div>
					</div>

					<!--custom fields details-->
					<div class="row" id="customFields">
						<?php echo render_custom_Fields('parents', $parent['branch_id'], $parent['id']); ?>
					</div>

					<div class="row mb-md">
						<div class="col-md-12 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('profile_picture')?> <span class="required">*</span></label>
								<input type="file" name="user_photo" class="dropify" data-default-file="<?=get_image_url('parent', $parent['photo'])?>" />
							</div>
							<span class="error"><?php echo form_error('user_photo'); ?></span>
						</div>
						<input type="hidden" name="old_user_photo" value="<?=$parent['photo']?>">
					</div>

					<!-- login details -->
					<div class="headers-line">
						<i class="far fa-user-lock"></i> <?=translate('login_details')?>
					</div>

					<div class="row mb-lg">
						<div class="col-md-12 mb-sm">
							<div class="form-group">
								<label class="control-label"><?=translate('username')?> <span class="required">*</span></label>
								<div class="input-group">
									<span class="input-group-addon"><i class="far fa-user"></i></span>
									<input type="text" class="form-control" name="username" value="<?=set_value('username', $parent['username'])?>" autocomplete="off" />
								</div>
								<span class="error"><?php echo form_error('username'); ?></span>
							</div>
						</div>
					</div>

					<!-- social links -->
					<div class="headers-line">
						<i class="far fa-globe"></i> <?=translate('social_links')?>
					</div>

					<div class="row mb-md">
						<div class="col-md-4 mb-xs">
							<div class="form-group">
								<label class="control-label">Facebook</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fab fa-facebook-f"></i></span>
									<input type="text" class="form-control" name="facebook" placeholder="eg: https://www.facebook.com/username" value="<?=set_value('facebook', $parent['facebook_url'])?>" />
								</div>
								<span class="error"><?php echo form_error('facebook'); ?></span>
							</div>
						</div>
						<div class="col-md-4 mb-xs">
							<div class="form-group">
								<label class="control-label">Twitter</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fab fa-twitter"></i></span>
									<input type="text" class="form-control" name="twitter" placeholder="eg: https://www.twitter.com/username" value="<?=set_value('twitter', $parent['twitter_url'])?>" />
								</div>
								<span class="error"><?php echo form_error('twitter'); ?></span>
							</div>
						</div>
						<div class="col-md-4 mb-xs">
							<div class="form-group">
								<label class="control-label">Linkedin</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fab fa-linkedin-in"></i></span>
									<input type="text" class="form-control" name="linkedin" placeholder="eg: https://www.linkedin.com/username" value="<?=set_value('linkedin', $parent['linkedin_url'])?>" />
								</div>
								<span class="error"><?php echo form_error('linkedin'); ?></span>
							</div>
						</div>
					</div>
				</div>
				<div class="panel-footer">
					<div class="row">
						<div class="col-md-offset-9 col-md-3">
							<button type="submit" name="update" value="1" class="btn btn-default btn-block"><?=translate('update')?></button>
						</div>
					</div>
				</div>
				</form>
			</div>
		</div>
		<div class="panel panel-accordion">
			<div class="panel-heading">
				<h4 class="panel-title">
					<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#childs">
						<i class="far fa-user-graduate"></i> <?=translate('childs')?>
					</a>
				</h4>
			</div>
			<div id="childs" class="accordion-body collapse">
				<div class="panel-body">
					<div class="row">
						<?php 
						$childsresult = $this->parents_model->childsResult($parent['id']);
						if (count($childsresult)) {
							foreach($childsresult as $student) :
						?>
						<div class="col-md-12 col-lg-6 col-xl-4">
							<section class="panel mt-sm mb-sm">
								<div class="panel-body">
									<div class="widget-summary">
										<div class="widget-summary-col widget-summary-col-icon">
											<div class="summary-icon">
												<img class="rounded" src="<?=get_image_url('student', $student['photo'])?>"/>
											</div>
										</div>
										<div class="widget-summary-col">
											<div class="summary">
												<h4 class="title"><?=$student['fullname']?></h4>
												<div class="info">
													<span>
														<?php 
														echo translate('class') . ' : ' . $student['class_name'] . ' (' . $student['section_name']  . ')'; 
														?>
													</span>
												</div>
											</div>
											<div class="summary-footer">
												<a class="text-muted mail-subj" href="<?php echo base_url('student/profile/' . $student['id']);?>" target="_blank"><?=translate('profile')?></a>
											</div>
										</div>
									</div>
								</div>
							</section>
						</div>
						<?php 
							endforeach;
						}else{
							echo '<div class="col-md-12"><div class="alert alert-subl mt-md text-center text-danger">' . translate('no_information_available') . ' !</div></div>';
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
</div>

<!-- login authentication and account inactive modal -->
<div id="authentication_modal" class="zoom-anim-dialog modal-block modal-block-primary mfp-hide">
	<section class="panel">
		<header class="panel-heading">
			<h4 class="panel-title">
				<i class="far fa-unlock-alt"></i> <?=translate('authentication')?>
			</h4>
		</header>
		<?php echo form_open('parents/change_password', array('class' => 'frm-submit')); ?>
        <div class="panel-body">
        	<input type="hidden" name="parent_id" value="<?=$parent['id']?>">
            <div class="form-group">
	            <label for="password" class="control-label"><?=translate('password')?> <span class="required">*</span></label>
	            <div class="input-group">
	                <input type="password" class="form-control password" name="password" autocomplete="off" />
	                <span class="input-group-addon">
	                    <a href="javascript:void(0);" id="showPassword" ><i class="far fa-eye"></i></a>
	                </span>
	            </div>
	            <span class="error"></span>
                <div class="checkbox-replace mt-lg">
                    <label class="i-checks">
                        <input type="checkbox" name="authentication" id="cb_authentication">
                        <i></i> <?=translate('login_authentication_deactivate')?>
                    </label>
                </div>
            </div>
        </div>
        <footer class="panel-footer">
            <div class="text-right">
                <button type="submit" class="btn btn-default mr-xs" data-loading-text="<i class='far fa-spinner fa-spin'></i> Processing"><?=translate('update')?></button>
                <button class="btn btn-default modal-dismiss"><?=translate('close')?></button>
            </div>
        </footer>
        <?= form_close(); ?>
	</section>
</div>

<script type="text/javascript">
	var authenStatus = "<?=$parent['active']?>";
</script>
