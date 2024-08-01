<div class="row">
	<div class="col-md-12">
		<?php if (empty($query_language)):?>
			<section class="panel">
				<header class="panel-heading">
					<h4 class="panel-title">
						<i class="far fa-globe"></i> <?=translate('language_list');?>
						<?php if(get_permission('translations', 'is_add')){ ?>
						<div class="panel-btn">
							<a href="javascript:void(0);" class="add_lang btn btn-default btn-circle">
								<i class="far fa-plus-square"></i> <?=translate('add_language');?>
							</a>
						</div>
						<?php } ?>
					</h4>
				</header>
				<div class="panel-body">
	                <div class="table-responsive mt-md mb-md">
					<!-- Fahad updates -->

					<table class="table table-bordered table-hover table-condensed">
						<thead>
							<tr>
								<th>#</th>
								<th><?=translate('language')?></th>
								<th><?=translate('language_code')?></th>
								<th><?=translate('flag')?></th>
								<th width="85"><?=translate('stats')?></th>
								<th width="85">RTL</th>
								<th><?=translate('created_at')?></th>
								<th><?=translate('updated_at')?></th>
								<th><?=translate('action')?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$count = 1;
							$languages = $builder->get('language_list')->result();
							foreach($languages as $row) {
								?>
							<tr>
								<td><?php echo $count++;?></td>
								<td><?php echo ucwords($row->name);?></td>
								<td><?php echo $row->language_code;?></td>
								<td>
									<?php
									if (!empty($row->flag_url)) {
										// Prioritize CDN flag if available
										$flag_url = $row->flag_url;
									} else {
										// Use the uploaded flag if CDN flag is not available
										$flag_url = $applicationModel->getLangImage($row->id, false);
									}
									?>
									<img class="flag-img" src="<?=$flag_url?>" alt="Flag" />
								</td>
								<td>
									<input data-size="mini" data-lang="<?=$row->id?>" class="toggle-switch stats" data-width="70" data-on="<i class='far fa-check'></i> ON" data-off="<i class='far fa-times'></i> OFF" <?=($row->status == 1 ? 'checked' : '');?> data-style="bswitch" type="checkbox">
								</td>
								<td>
									<input data-size="mini" data-lang="<?=$row->id?>" class="toggle-switch rtl" data-width="70" data-on="<i class='far fa-check'></i> ON" data-off="<i class='far fa-times'></i> OFF" <?=($row->rtl == 1 ? 'checked' : '');?> data-style="bswitch" type="checkbox">
								</td>
								<td><?php echo _d($row->created_at);?></td>
								<td><?php echo _d($row->updated_at);?></td>
								<td>
									<?php if(get_permission('translations', 'is_view')){ ?>
									<!-- word update link -->
									<a href="<?php echo base_url('translations/update?lang=' . $row->lang_field);?>" class="btn btn-default btn-circle">
										<i class="glyphicon glyphicon-link"></i> <?=translate('edit_word');?>
									</a>

									<!-- language rename link -->
									<a class="btn btn-default btn-circle edit_modal" href="javascript:void(0);" data-id="<?=$row->id?>">
										<i class="far fa-pen-nib"></i> <?=translate('rename');?>
									</a>
									<?php } if(get_permission('translations', 'is_delete')){ ?>
									<!-- delete link -->
									<?php echo btn_delete('translations/submitted_data/delete/' . $row->id);?>
									<?php } ?>
								</td>
							</tr>
							<?php  }?>
						</tbody>
					</table>


					</div>
				</div>
			</section>
		<?php 
		else:
		$get_name = $builder->select('name')->where('lang_field',$select_language)->get('language_list')->row()->name;
		?>
			<!-- word update -->
			<section class="panel appear-animation" data-appear-animation="<?=$global_config['animations'] ?>" data-appear-animation-delay="100">
				<header class="panel-heading">
					<h4 class="panel-title"><i class="far fa-pen-nib"></i> <?=ucfirst($get_name) . ' - ' . translate('translation_update');?></h4>
				</header>
				<?php echo form_open('translations/update?lang=' . $select_language, array('class' => 'validate')); ?>
				<div class="panel-body">
					<table class="table table-bordered table-condensed mb-none table-export">
						<thead>
							<tr>
								<th>ID</th>
								<th><?=translate('word')?></th>
								<th><?=translate('translations')?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$count = 1;
							$words = $query_language->result();
								foreach($words as $row) {
							?>
							<tr>
								<td><?php echo $count++;?></td>
								<td><?php echo ucwords(str_replace('_', ' ',  $row->word));?></td>
								<td>
									<div style="width:  100%">
									<div class="input-group">
										<span class="input-group-addon">
											<span class="icon"><i class="far fa-comment-alt"></i></span>
										</span>
										<input  type="text" placeholder="Set Word Translation" name="word_<?=$row->word?>" value="<?=$row->$select_language?>" class="form-control" />
									</div>
									</div>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
				<footer class="panel-footer">
				<div class="row">
					<div class="col-md-offset-10 col-md-2">
						<button class="btn btn btn-default btn-block" name="submit" value="update"><i class="far fa-edit"></i> <?=translate('update')?></button>
					</div>
				</div>
				</footer>
				<?php echo form_close();?>
			</section>
		<?php endif;?>
		
	<?php if(get_permission('translations', 'is_add')){ ?>
		<!-- language add modal -->
		<div id="add_modal" class="zoom-anim-dialog modal-block modal-block-primary mfp-hide">
			<section class="panel">
				<?php
					echo form_open_multipart(base_url('translations/submitted_data/create'), array(
						'class' 	=> 'validate',
						'method' 	=> 'post'
					));
				?>
				<div class="panel-heading">
					<h4 class="panel-title">
						<i class="far fa-plus-square"></i> <?=translate('add_language')?>
					</h4>
				</div>

				<div class="panel-body">
					<div class="form-group mb-md">
						<label class="control-label"><?=translate('language')?> <span class="required">*</span></label>
						<input type="text" class="form-control" name="name" required  value="">
					</div>
					<div class="form-group mb-md">
						<label class="control-label"><?=translate('flag_icon')?></label>
						<input type="file" name="flag" data-height="90" class="dropify" data-allowed-file-extensions="jpg png bmp" />
					</div>
				</div>
				<footer class="panel-footer">
					<div class="text-right">
						<button type="submit" class="btn btn-default"><?=translate('save')?></button>
						<button class="btn btn-default modal-dismiss"><?=translate('cancel')?></button>
					</div>
				</footer>
				<?php echo form_close();?>
			</section>
		</div>
	<?php } ?>
		
		<!-- language edit modal - Fahad Added flag cdn-->
<!-- language edit modal -->
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
    <section class="panel">
        <?php
            echo form_open_multipart(base_url('translations/submitted_data/rename/' . $id), array(
                'id' => 'modalfrom',
                'class' => 'validate',
                'method' => 'post'
            ));
        ?>
        <header class="panel-heading">
            <h4 class="panel-title"><i class="far fa-edit"></i> <?=translate('rename')?></h4>
        </header>
        <div class="panel-body">
            <div class="form-group mb-md">
                <label class="control-label"><?=translate('name')?> <span class="required">*</span></label>
                <input type="text" class="form-control" name="rename" id="modal_name" required value="" />
            </div>
            <div class="form-group mb-md">
                <label class="control-label"><?=translate('language_code')?> <span class="required">*</span></label>
                <input type="text" class="form-control" name="language_code" id="language_code" required value="" />
            </div>
            <div class="form-group mb-md">
                <label class="control-label"><?=translate('flag_icon')?></label>
                <input type="file" name="flag" data-height="80" class="dropify" data-allowed-file-extensions="jpg png bmp" />
            </div>
            <!-- New flag selection -->
            <div class="form-group mb-md">
                <label class="control-label"><?=translate('select_country_flag')?></label>
				<select class="form-control" id="flag_select" name="flag_select">
				<option value="">Select a flag</option>
				<!-- English -->
				<option value="united-states_1f1fa-1f1f8" data-lang-code="en">English - United States</option>
				<option value="united-kingdom_1f1ec-1f1e7" data-lang-code="en">English - United Kingdom</option>
				<!-- Bengali -->
				<option value="bangladesh_1f1e7-1f1e9" data-lang-code="bn">Bengali - Bangladesh</option>
				<!-- Arabic -->
				<option value="saudi-arabia_1f1f8-1f1e6" data-lang-code="ar">Arabic - Saudi Arabia</option>
				<option value="united-arab-emirates_1f1e6-1f1ea" data-lang-code="ar">Arabic - United Arab Emirates</option>
				<!-- French -->
				<option value="france_1f1eb-1f1f7" data-lang-code="fr">French - France</option>
				<option value="canada_1f1e8-1f1e6" data-lang-code="fr">French - Canada</option>
				<!-- Hindi -->
				<option value="india_1f1ee-1f1f3" data-lang-code="hi">Hindi - India</option>
				<!-- Indonesian -->
				<option value="indonesia_1f1ee-1f1e9" data-lang-code="id">Indonesian - Indonesia</option>
				<!-- Italian -->
				<option value="italy_1f1ee-1f1f9" data-lang-code="it">Italian - Italy</option>
				<option value="switzerland_1f1e8-1f1ed" data-lang-code="it">Italian - Switzerland</option>
				<!-- Japanese -->
				<option value="japan_1f1ef-1f1f5" data-lang-code="ja">Japanese - Japan</option>
				<!-- Korean -->
				<option value="south-korea_1f1f0-1f1f7" data-lang-code="ko">Korean - South Korea</option>
				<!-- Dutch -->
				<option value="netherlands_1f1f3-1f1f1" data-lang-code="nl">Dutch - Netherlands</option>
				<!-- Portuguese -->
				<option value="brazil_1f1e7-1f1f7" data-lang-code="pt">Portuguese - Brazil</option>
				<option value="portugal_1f1f5-1f1f9" data-lang-code="pt">Portuguese - Portugal</option>
				<!-- Thai -->
				<option value="thailand_1f1f9-1f1ed" data-lang-code="th">Thai - Thailand</option>
				<!-- Turkish -->
				<option value="turkey_1f1f9-1f1f7" data-lang-code="tr">Turkish - Turkey</option>
				<!-- Urdu -->
				<option value="pakistan_1f1f5-1f1f0" data-lang-code="ur">Urdu - Pakistan</option>
				<!-- Chinese (Mandarin) -->
				<option value="china_1f1e8-1f1f3" data-lang-code="zh">Chinese - China</option>
				<option value="taiwan_1f1f9-1f1fc" data-lang-code="zh">Chinese - Taiwan</option>
				<!-- Afrikaans -->
				<option value="south-africa_1f1ff-1f1e6" data-lang-code="af">Afrikaans - South Africa</option>
				<!-- German -->
				<option value="germany_1f1e9-1f1ea" data-lang-code="de">German - Germany</option>
				<option value="austria_1f1e6-1f1f9" data-lang-code="de">German - Austria</option>
				<!-- Greek -->
				<option value="greece_1f1ec-1f1f7" data-lang-code="el">Greek - Greece</option>
				<!-- Spanish -->
				<option value="spain_1f1ea-1f1f8" data-lang-code="es">Spanish - Spain</option>
				<option value="mexico_1f1f2-1f1fd" data-lang-code="es">Spanish - Mexico</option>
				<!-- Nepali -->
				<option value="nepal_1f1f3-1f1f5" data-lang-code="ne">Nepali - Nepal</option>
				<!-- Russian -->
				<option value="russia_1f1f7-1f1fa" data-lang-code="ru">Russian - Russia</option>
				<!-- Danish -->
				<option value="denmark_1f1e9-1f1f0" data-lang-code="da">Danish - Denmark</option>
				<!-- Armenian -->
				<option value="armenia_1f1e6-1f1f2" data-lang-code="hy">Armenian - Armenia</option>
				<!-- Georgian -->
				<option value="georgia_1f1ec-1f1ea" data-lang-code="ka">Georgian - Georgia</option>
				<!-- Marathi -->
				<option value="india_1f1ee-1f1f3" data-lang-code="mr">Marathi - India</option>
				<!-- Malay -->
				<option value="malaysia_1f1f2-1f1fe" data-lang-code="ms">Malay - Malaysia</option>
				<!-- Punjabi -->
				<option value="india_1f1ee-1f1f3" data-lang-code="pa">Punjabi - India</option>
				<!-- Tamil -->
				<option value="india_1f1ee-1f1f3" data-lang-code="ta">Tamil - India</option>
				<!-- Telugu -->
				<option value="india_1f1ee-1f1f3" data-lang-code="te">Telugu - India</option>
				<!-- Swedish -->
				<option value="sweden_1f1f8-1f1ea" data-lang-code="sv">Swedish - Sweden</option>
				<!-- Filipino -->
				<option value="philippines_1f1f5-1f1ed" data-lang-code="fil">Filipino - Philippines</option>
				<!-- Finnish -->
				<option value="finland_1f1eb-1f1ee" data-lang-code="fi">Finnish - Finland</option>
				<!-- Greek -->
				<option value="greece_1f1ec-1f1f7" data-lang-code="el">Greek - Greece</option>
				<!-- Turkish -->
				<option value="turkey_1f1f9-1f1f7" data-lang-code="tr">Turkish - Turkey</option>
				<!-- Polish -->
				<option value="poland_1f1f5-1f1f1" data-lang-code="pl">Polish - Poland</option>
				<!-- Ukrainian -->
				<option value="ukraine_1f1fa-1f1e6" data-lang-code="uk">Ukrainian - Ukraine</option>
				<!-- Romanian -->
				<option value="romania_1f1f7-1f1f4" data-lang-code="ro">Romanian - Romania</option>
				<!-- Czech -->
				<option value="czech-republic_1f1e8-1f1ff" data-lang-code="cs">Czech - Czech Republic</option>
				<!-- Hungarian -->
				<option value="hungary_1f1ed-1f1fa" data-lang-code="hu">Hungarian - Hungary</option>
				<!-- Bulgarian -->
				<option value="bulgaria_1f1e7-1f1ec" data-lang-code="bg">Bulgarian - Bulgaria</option>
				<!-- Croatian -->
				<option value="croatia_1f1ed-1f1f7" data-lang-code="hr">Croatian - Croatia</option>
				<!-- Slovenian -->
				<option value="slovenia_1f1f8-1f1ee" data-lang-code="sl">Slovenian - Slovenia</option>
				<!-- Vietnamese -->
				<option value="vietnam_1f1fb-1f1ea" data-lang-code="vi">Vietnamese - Vietnam</option>
			</select>


            </div>
            <!-- Flag Preview -->
            <div class="form-group">
                <label for="flag_preview">Flag Preview</label>
                <img id="flag_preview" src="" alt="Flag Preview" style="display: none; max-width: 100px; margin-top: 10px;">
            </div>
            <!-- Hidden field for flag URL -->
            <input type="hidden" name="flag_url" id="flag_url" value="">
        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button type="submit" class="btn btn-default"><?=translate('update')?></button>
                    <button class="btn btn-default modal-dismiss"><?=translate('cancel')?></button>
                </div>
            </div>
        </footer>
        <?php echo form_close();?>
    </section>
</div>


		
	</div>
</div>
<script type="text/javascript">
$(document).ready(function () {
    $(document).on('click', '.edit_modal', function () {
        var id = $(this).data('id');
        $.ajax({
            url: "<?=base_url('translations/get_details')?>",
            type: 'POST',
            data: {id: id},
            dataType: 'json',
            success: function (res) {
                $('#modal_name').val(res.name);
                $('#language_code').val(res.language_code); // Set the language code
                $('#flag_select').val(res.flag_code); // Adjust if you have a flag code
                $('#modalfrom').attr('action', '<?php echo base_url("translations/submitted_data/rename/");?>' + res.id); 
                mfp_modal('#modal');
            }
        });
    });

    $(document).on('click', '.add_lang', function () {
        mfp_modal('#add_modal');
    });

    // Handle flag selection
    $('#flag_select').on('change', function () {
        var selectedFlag = $(this).val();
        var selectedLanguageCode = $('#flag_select option:selected').data('lang-code');
        if (selectedFlag) {
            // Construct the Emojipedia flag URL
            var flagUrl = `https://em-content.zobj.net/source/google/387/flag-${selectedFlag}.png`;
            $('#flag_preview').attr('src', flagUrl).show();
            $('#language_code').val(selectedLanguageCode);
            $('#flag_url').val(flagUrl);
            // Override file upload
            $('input[name="flag"]').val('');
        } else {
            $('#flag_preview').hide();
        }
    });

    // Toggle-switch handling
    $(document).on('change', '.toggle-switch.stats', function () {
        var state = $(this).prop('checked');
        var lang_id = $(this).data('lang');

        $.ajax({
            type: 'POST',
            url: "<?=base_url('translations/status')?>",
            data: {
                lang_id: lang_id,
                status: state
            },
            dataType: "html",
            success: function(data) {
                swal({
                    type: 'success',
                    title: "<?=translate('successfully')?>",
                    text: data,
                    showCloseButton: true,
                    focusConfirm: false,
                    buttonsStyling: false,
                    confirmButtonClass: 'btn btn-default swal2-btn-default',
                    footer: '*Note : You can undo this action at any time'
                });
            }
        });
    });

    $(document).on('change', '.toggle-switch.rtl', function () {
        var state = $(this).prop('checked');
        var lang_id = $(this).data('lang');
        $.ajax({
            type: 'POST',
            url: "<?=base_url('translations/isRTL')?>",
            data: {
                lang_id: lang_id,
                status: state
            },
            dataType: "html",
            success: function(data) {
                swal({
                    type: 'success',
                    title: "<?=translate('successfully')?>",
                    text: data,
                    showCloseButton: true,
                    focusConfirm: false,
                    buttonsStyling: false,
                    confirmButtonClass: 'btn btn-default swal2-btn-default',
                    footer: '*Note : You can undo this action at any time'
                }).then((result) => {
                    if (result.value) {
                        location.reload();
                    }
                });
            }
        });
    });
});


</script>