
<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title><?php echo $getSettings->seo_title; ?></title>
    <meta name="keyword" content="<?php echo $getSettings->seo_keyword; ?>" />
    <meta name="description" content="<?php echo $getSettings->seo_description; ?>" />
    <meta name="author" content="<?= $global_config['institute_name'] ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo base_url('assets/images/favicon.png');?>" />

    <!-- CSS here -->
    <link rel="stylesheet" href="<?php echo base_url('assets/frontend/css/bootstrap.min.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url('assets/frontend/css/LineIcons.2.0.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/font-awesome/css/all.min.css');?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/frontend/css/animate.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url('assets/frontend/css/tiny-slider.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url('assets/frontend/css/glightbox.min.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/select2/css/select2.min.css');?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/sweetalert/sweetalert-custom.css');?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/frontend/css/saas_main.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url('assets/frontend/css/style.css'); ?>">

    <!-- Include CSS and JS files for intl-tel-input -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/css/intlTelInput.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/js/intlTelInput.min.js"></script>


    <!-- Google Analytics --> 
    <?php echo $getSettings->google_analytics; ?>

    <?php if ($getSettings->pwa_enable == 1) {  ?>
    <!-- Web Application Manifest -->
    <link rel="manifest" href="./manifest.json">
    <!-- Chrome for Android theme color -->
    <meta name="theme-color" content="#6e8fd4">
    
    <!-- Add to homescreen for Chrome on Android -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="Ramom School">
    <link rel="icon" sizes="512x512" href="<?php echo base_url('uploads/appIcons/icon-512x512.png')?>">
    
    <!-- Add to homescreen for Safari on iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Ramom School">
    <link rel="apple-touch-icon" href="<?php echo base_url('uploads/appIcons/icon-512x512.png')?>">

    <script type="text/javascript">
        // Initialize the service worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/serviceworker.js', {
                scope: '/'
            }).then(function (registration) {
                // Registration was successful
                // console.log('Service Worker registered successfully: ', registration.scope);
            }, function (err) {
                // registration failed :(
                console.log('Service Worker registration failed: ', err);
            });
        }
    </script>
    <?php } ?>

    <!-- Theme Color Options -->
    <script type="text/javascript">
        document.documentElement.style.setProperty('--thm-primary', '<?php echo $getSettings->primary_color ?>');
        document.documentElement.style.setProperty('--thm-header-text', '<?php echo $getSettings->heading_text_color ?>');
        document.documentElement.style.setProperty('--thm-text', '<?php echo $getSettings->text_color ?>');
        document.documentElement.style.setProperty('--thm-menu-bg', '<?php echo $getSettings->menu_bg_color ?>');
        document.documentElement.style.setProperty('--thm-menu-color', '<?php echo $getSettings->menu_text_color ?>');
        document.documentElement.style.setProperty('--thm-footer-bg', '<?php echo $getSettings->footer_bg_color ?>');
        document.documentElement.style.setProperty('--thm-footer-text', '<?php echo $getSettings->footer_text_color ?>');
    </script>

    <script src="<?php echo base_url('assets/vendor/jquery/jquery.min.js');?>"></script>
    <script type="text/javascript">
        var base_url = '<?php echo base_url(); ?>';
        var csrfData = <?php echo json_encode(csrf_jquery_token()); ?>;
        $(function($) {
            $.ajaxSetup({
                cache: false,
                data: csrfData
            });
        });
    </script>
</head>
<style>
        body {
            font-family: "Nunito", sans-serif;
            font-weight: normal;
            font-style: normal;
            color: var(--thm-text);
            overflow-x: hidden;
            font-size: 15px;
        }
        .navbar-area:not(.sticky) {
            border-bottom: 0px solid #b3b3b3;
        }
        .iti {
            position: relative;
            display: block;
        }
</style>

    <!-- Start Header Area -->
    <header class="header navbar-area">
        <div class="container-md">
            <div class="row align-items-center">
                <div class="col-lg-12">
                    <div class="nav-inner">
                        <!-- Start Navbar -->
                        <nav class="navbar navbar-expand-lg">
                            <a class="navbar-brand" href="<?php echo base_url() ?>">
                                <img src="<?=$applicationModel->getBranchImage(get_loggedin_branch_id(), 'logo-small')?>" alt="Logo">
                            </a>
                           
                        
                        </nav>
                        <!-- End Navbar -->
                    <?php if (session()->getFlashdata('website_expired_msg')) { ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-2">
                        <i class="far fa-exclamation-triangle"></i> <?php echo $getSettings->expired_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php } ?>
                    </div>
                </div>
            </div>
            <!-- row -->
        </div>
        <!-- container -->
    </header>
    <!-- End Header Area -->

    <div class="container" style="margin-top: 8rem!important">
        <h1>School Subscription</h1>
        <?php echo form_open_multipart('saas_website/register', array('class' => 'school-reg frm-submit-data')); ?>
        <input type="hidden" name="package_id" value="<?php echo $registration['package_id']; ?>" id="packageID">
        <input type="hidden" name="registration_id" value="<?php echo ($registration['registration_id']) ? $registration['registration_id'] : ''; ?>" id="registrationID">
        <section class="card pg-fw mb-4 mt-2">
            <div class="card-body">
                <h5 class="chart-title mb-xs">Plan Summary</h5>
                <div class="mt-2">
                <ul class="sp-summary" id="summary">
                <li>Plan Name <span><?php echo $package->name; ?></span></li>
    <li>Start Date <span><?php echo $created_at; ?></span></li>
    <li>Subscription Period <span>
        <?php
            $period_value = $package->period_value;
            $period_type = $package->period_type;
            $period_text = '';

            switch ($period_type) {
                case 1:
                    $period_text = 'Lifetime';
                    break;
                case 2:
                    $period_text = $period_value . ' Day' . ($period_value > 1 ? 's' : '');
                    break;
                case 3:
                    $period_text = $period_value . ' Month' . ($period_value > 1 ? 's' : '');
                    break;
                case 4:
                    $period_text = $period_value . ' Year' . ($period_value > 1 ? 's' : '');
                    break;
            }
            echo $period_text;
        ?>
    </span></li>
    <li class="total-costs">Total Cost <span><?php echo ($package->free_trial == 1) ? 'Free' : $global_config['currency_symbol'] . number_format(($package->price - $package->discount), 2, '.', ''); ?></span></li>
</ul>
                </ul>
                </div>
            </div>
        </section>
        <div class="row">
            <div class="col-lg-6">
             <!-- School Name -->
             <div class="form-group">
                <label for="school_name"><?php echo translate('school_name'); ?> </label>
                <input id="school_name" name="school_name" type="text" class="form-control" autocomplete="off" value="<?php echo $registration['school_name']; ?>" required>
                <span class="error"></span>
            </div>
            <!-- Country -->
                        <?php
            $countries = get_country(); // Get the list of countries
            ?>
            <div class="form-group">
                <label for="country"><?php echo translate('country'); ?> </label>
                <select id="country" name="country" class="form-control" required>
                    <?php foreach ($countries as $code => $name): ?>
                        <option value="<?php echo $name; ?>"><?php echo $name; ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="error"></span>
            </div>

            <!-- School Address -->
            <div class="form-group">
                <label for="school_address"><?php echo translate('school_address'); ?> </label>
                <input id="school_address" name="school_address" type="text" class="form-control" autocomplete="off" required>
                <span class="error"></span>
            </div>
            <!-- School Logo -->
            <div class="form-group">
                <label for="photo"><?php echo translate('school_logo'); ?></label>
                <input class="form-control" type="file" accept="image/*" id="photo" name="logo_file">
                <span class="error"></span>
            </div>
            <!-- Message -->
            <div class="form-group">
                <label for="school_info"><?php echo translate('message_( optional_request )'); ?></label>
                <textarea name="message" id="message" rows="5" class="form-control"></textarea>
            </div>
        </div>
        <div class="col-lg-6">
            <!-- Admin Name -->
            <div class="form-group">
                <label for="admin_name"><?php echo translate('admin_name'); ?> </label>
                <input id="admin_name" name="admin_name" type="text" class="form-control" autocomplete="off" value="<?php echo $registration['name']; ?>" required>
                <span class="error"></span>
            </div>
            <!-- Gender -->
            <div class="form-group">
                <label for="gender"><?php echo translate('gender'); ?> </label>
                <select class="form-select" id="gender" name="gender" data-minimum-results-for-search='Infinity' required>
                    <option value="">Select a gender</option>
                    <option value="1">Male</option>
                    <option value="2">Female</option>
                </select>
                <span class="error"></span>
            </div>
            <!-- Contact Number -->
            <div class="form-group">
                <label for="admin_phone"><?php echo translate('contact_number'); ?> </label>
                <input id="admin_phone" name="admin_phone" type="tel" class="form-control" autocomplete="off" value="<?php echo $registration['phone_number']; ?>" required>
                <input type="hidden" id="phoneCountryCode" name="phone_country_code" value="">
                <span class="error"></span>
            </div>
            <!-- Contact Email -->
            <div class="form-group">
                <label for="admin_email"><?php echo translate('contact_email'); ?> </label>
                <input name="admin_email" type="email" class="form-control" autocomplete="off" value="<?php echo $registration['email']; ?>" required>
                <span class="error"></span>
            </div>
            <!-- Admin Username -->
            <div class="form-group">
                <label for="admin_username"><?php echo translate('admin_username'); ?> </label>
                <input id="admin_username" name="admin_username" type="text" class="form-control" autocomplete="off" required>
                <span class="error"></span>
            </div>
            <!-- Admin Password -->
            <div class="form-group">
                <label for="admin_password"><?php echo translate('admin_password'); ?> </label>
                <input id="admin_password" name="admin_password" type="password" class="form-control" pattern="(?=.*\d).{6,}" title="Must contain at least one number and be at least 6 characters long" autocomplete="off" required>
                <span class="error" id="password_error"></span>
            </div>
            <!-- Retype Password -->
            <div class="form-group">
                <label for="retype_admin_password"><?php echo translate('retype_password'); ?> </label>
                <input id="retype_admin_password" name="retype_admin_password" type="password" class="form-control" autocomplete="off" required>
                <span class="error" id="retype_password_error"></span>
            </div>
            <!-- Terms and Conditions -->
            <?php if ($getSettings->terms_status == 1) { ?>
                    <div class="form-group">
                        <div class="checkbox-replace">
                            <label class="i-checks"><input type="checkbox" name="terms_cb"><i></i> <?php echo $getSettings->agree_checkbox_text ?></label>
                        </div>
                        <span class="error"></span>
                    </div>
                <?php } ?>
            <!-- Captcha -->
            <?php if ($getSettings->captcha_status == 1): ?>
                <div class="form-group">
                    <?php echo $recaptcha['widget']; echo $recaptcha['script']; ?>
                    <span class="error"></span>
                </div>
            <?php endif; ?>
            <!-- Submit Button -->
            <div class="pp-plans-bottom">
                <div class="pp-plans-cta button">
                    <button class="btn mb-4" type="submit" data-loading-text="<i class='far fa-spinner fa-spin'></i> Processing"><?php echo translate('register_and_pay'); ?></button>
                </div>
            </div>
        </div>
    </div>
    <?= form_close(); ?>
</div>

    <!-- modal terms & conditions -->

    <?php if ($getSettings->terms_status == 1) { ?>
<div class="modal fade" id="termsModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="exampleModalLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" style="max-width: 80%; height: fit-content;">
        <div class="modal-content" style="height: 100%;">
            <div class="modal-header p-2">
                <h5 class="modal-title" id="exampleModalLabel">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="color: #000;"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php } ?>


    <script src="<?php echo base_url('assets/frontend/js/bootstrap.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/frontend/js/saas_main.js'); ?>"></script>
    
    <script>
        $(document).ready(function() {
    $('#getPlanSummary').click(function() {
        var package_id = $('#package_id').val();
        var registration_id = $('#registration_id').val();
        var captchaResponse = grecaptcha.getResponse();

        $.post('saas_website/getPlanSummary', {
            package_id: package_id,
            registration_id: registration_id,
            'g-recaptcha-response': captchaResponse
        }, function(response) {
            var data = JSON.parse(response);
            if (data.status == 'success') {
                $('#summary').html(data.html);
            } else {
                alert(data.message);
            }
        });
    });
});

        // Phone input initialization
        var input = document.querySelector("#admin_phone");
        var iti = window.intlTelInput(input, {
            initialCountry: "auto",
            geoIpLookup: function(callback) {
                fetch('https://ipinfo.io/json')
                    .then(response => response.json())
                    .then(data => {
                        var countryCode = (data && data.country) ? data.country : "us";
                        callback(countryCode);
                    })
                    .catch(() => callback("us"));
            },
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/js/utils.js"
        });

        // Automatically set the phone country code based on the selected country
        input.addEventListener('countrychange', function() {
            var countryCode = iti.getSelectedCountryData().dialCode;
            document.querySelector('#phoneCountryCode').value = '+' + countryCode;
        });

        // Adjust the dropdown container to match the input width and position
        input.addEventListener('open:countrydropdown', function() {
            var inputRect = input.getBoundingClientRect();
            var container = document.querySelector('.iti.iti--container');
            container.style.width = inputRect.width + 'px';
            container.style.left = inputRect.left + 'px';
            container.style.top = inputRect.bottom + 'px';
        });
    </script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('.frm-submit-data');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Clear previous error messages
        document.querySelectorAll('.error').forEach(function(el) {
            el.textContent = '';
        });

        var btn = form.querySelector('[type="submit"]');
        btn.disabled = true;

        // Validate inputs before AJAX
        var errors = validateForm();

        // Only proceed with AJAX if there are no errors
        if (Object.keys(errors).length === 0) {
            $.ajax({
                url: form.action,
                type: "POST",
                data: new FormData(form),
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                success: function(data) {
                    if (data.status === "fail") {
                        // Display each error next to the corresponding input field
                        for (const [key, message] of Object.entries(data.error)) {
                            const inputElement = form.querySelector("[name='" + key + "']");
                            const errorElement = inputElement.nextElementSibling;
                            errorElement.textContent = message;
                        }
                    } else if (data.status === "access_denied") {
                        window.location.href = base_url + "dashboard";
                    } else if (data.status === "error") {
                        swal({
                            title: "Error",
                            text: data.message,
                            type: "error",
                            buttonsStyling: false,
                            confirmButtonClass: "btn swal2-btn-default",
                            showCloseButton: true
                        });
                    } else if (data.url) {
                        window.location.href = data.url;
                    } else {
                        location.reload();
                    }
                },
                complete: function() {
                    btn.disabled = false;
                },
                error: function() {
                    btn.disabled = false;
                }
            });
        } else {
            // Display client-side validation errors
            for (const [key, message] of Object.entries(errors)) {
                const inputElement = form.querySelector("[name='" + key + "']");
                const errorElement = inputElement.nextElementSibling;
                errorElement.textContent = message;
            }
            btn.disabled = false;
        }
    });
});

function validateForm() {
    var errors = {};
    var usernameField = document.getElementById('admin_username');
    var emailField = document.getElementById('admin_email');
    var passwordField = document.getElementById('admin_password');
    var retypePasswordField = document.getElementById('retype_admin_password');

    // Password validation
    var passwordPattern = /^(?=.*\d).{6,}$/;
    if (!passwordPattern.test(passwordField.value)) {
        errors['admin_password'] = 'Password must be at least 6 characters long and contain at least one number.';
    }
    if (passwordField.value !== retypePasswordField.value) {
        errors['retype_admin_password'] = 'Passwords do not match.';
    }
    if (passwordField.value === usernameField.value) {
        errors['admin_password'] = 'Password must not match the username.';
    }

    return errors;
}


</script>



