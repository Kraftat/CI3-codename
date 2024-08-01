<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title><?php echo $getSettings->seo_title; ?></title>
    <meta name="keywords" content="<?php echo $getSettings->seo_keyword; ?>" />
    <meta name="description" content="<?php echo $getSettings->seo_description; ?>" />
    <meta name="author" content="<?= $global_config['institute_name']; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo base_url('assets/images/favicon.png'); ?>" />
    <link rel="canonical" href="<?php echo base_url(); ?>" /> <!-- Helps prevent duplicate content issues -->

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?php echo base_url(); ?>" />
    <meta property="og:title" content="<?php echo $getSettings->seo_title; ?>" />
    <meta property="og:description" content="<?php echo $getSettings->seo_description; ?>" />
    <meta property="og:image" content="https://cleve.io/uploads/app_image/landing-mobile.webp" /> <!-- Update image path as needed -->

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:url" content="<?php echo base_url(); ?>" />
    <meta name="twitter:title" content="<?php echo $getSettings->seo_title; ?>" />
    <meta name="twitter:description" content="<?php echo $getSettings->seo_description; ?>" />
    <meta name="twitter:image" content="https://cleve.io/uploads/app_image/landing-mobile.webp" /> <!-- Update image path as needed -->
    <meta name="twitter:creator" content="@TwitterHandle" /> <!-- Your Twitter handle, if available -->

    <!-- Additional tags for extra SEO points and accessibility -->
    <meta name="robots" content="index, follow" />
    <meta name="googlebot" content="index, follow" />
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#ffffff"> <!-- Browser color (Chrome, Firefox OS and Opera) -->
    <meta name="application-name" content="<?= $global_config['institute_name']; ?>"> <!-- Name of web app (only should be used if the website is used as an app) -->

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
    <!-- Include CSS and JS files for intl-tel-input -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/css/intlTelInput.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/js/intlTelInput.min.js"></script>
    <link
      rel="stylesheet"
      data-purpose="Layout StyleSheet"
      title="Web Awesome"
      href="/css/app-wa-d53d10572a0e0d37cb8d614a3f177a0c.css?vsn=d"
    >

      <link
        rel="stylesheet"
        href="https://site-assets.fontawesome.com/releases/v6.5.2/css/all.css"
      >

      <link
        rel="stylesheet"
        href="https://site-assets.fontawesome.com/releases/v6.5.2/css/sharp-thin.css"
      >

      <link
        rel="stylesheet"
        href="https://site-assets.fontawesome.com/releases/v6.5.2/css/sharp-solid.css"
      >

      <link
        rel="stylesheet"
        href="https://site-assets.fontawesome.com/releases/v6.5.2/css/sharp-regular.css"
      >

      <link
        rel="stylesheet"
        href="https://site-assets.fontawesome.com/releases/v6.5.2/css/sharp-light.css"
      >

    <!-- Google Analytics --> 
    <?php echo $getSettings->google_analytics; ?>
    

    <?php if ($getSettings->pwa_enable == 1) {  ?>
    <!-- Web Application Manifest -->
    <link rel="manifest" href="./manifest.json">
    <!-- Chrome for Android theme color -->
    <meta name="theme-color" content="#6e8fd4">
    
    <!-- Add to homescreen for Chrome on Android -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="Cleve School Management System">
    <link rel="icon" sizes="512x512" href="<?php echo base_url('uploads/appIcons/icon-512x512.png')?>">
    
    <!-- Add to homescreen for Safari on iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Cleve School Management System">
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
<body>

<style>
    #cookieConsentContainer {
        position: fixed;
        bottom: 40px;
        left: 50%;
        transform: translateX(-50%);
        width: 30rem; /* Default width for larger screens */
        max-width: 100%; /* Max width for mobile screens */
        background: white;
        padding: 10px;
        text-align: center;
        border: 1px solid rgb(204, 204, 204);
        box-shadow: 0 1rem 3rem rgba(35, 38, 45, 0.15) !important;
        border-radius: 20px;
    }

    @media (max-width: 768px) {
        #cookieConsentContainer {
            width: calc(100% - 2rem); /* Adjust width for smaller screens */
        }
    }
</style>

<div class="cookieConsentContainer" id="cookieConsentContainer">
    <p>We use cookies to improve your experience on our site. By continuing to use our site, you accept our use of cookies in accordance with our <a href="/privacy-policy">Privacy Policy</a>.</p>
    <button class ="btn" onclick="acceptCookies();" style="margin: 10px; border: 1px solid rgb(204, 204, 204);
">🍪 Accept All Cookies</button>
    <button class ="btn" onclick="rejectCookies();">Reject Non-Essential Cookies</button>
</div>

    <!-- Preloader -->
    <!-- <div class="preloader">
        <div class="preloader-inner">
            <div class="preloader-icon">
                <span></span>
                <span></span>
            </div>
        </div>
    </div> -->
    <!-- /End Preloader -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">

<style>
    @font-face {
    font-family: 'Nunito';
    src: url('nunito.eot'); /* IE9 Compat Modes */
    src: url('nunito.eot?#iefix') format('embedded-opentype'), /* IE6-IE8 */
         url('nunito.woff2') format('woff2'), /* Super Modern Browsers */
         url('nunito.woff') format('woff'), /* Pretty Modern Browsers */
         url('nunito.ttf') format('truetype'), /* Safari, Android, iOS */
         url('nunito.svg#svgNunito') format('svg'); /* Legacy iOS */
}

    body {
    font-family: "Nunito", sans-serif;
    font-weight: normal;
    font-style: normal;
    color: var(--thm-text);
    overflow-x: hidden;
    font-size: 15px;
}
.hero-area .hero-content h1 {
    font-size: 38px;
    font-weight: 800;
    line-height: 50px;
    color: #fff;
    text-shadow: 0px 0px 0px #00000017;
    text-transform: capitalize;
}
    .header .button .btn {
    background-color: var(--thm-primary);
    border: 0px solid #fff;
    color: #fff;
    padding: 5px 20px;
}
.header.sticky .button .btn {
    background-color: var(--thm-primary);
    color: #fff;
    border-color: transparent;
}
.navbar-nav .nav-item:hover a {
    color: var(--thm-primary);
}
.navbar-area:not(.sticky) {
    border-bottom: 0px solid #b3b3b3;
}
.navbar-nav .nav-item a {
    font-size: 15px;
    color: #000;
}
@media (max-width: 767px) {
    .header .mobile-menu-btn .toggler-icon {
        background-color: #000;
    }
}
.header .navbar .navbar-nav .nav-item a.active {
    color: var(--thm-primary);
}
.hero-area .hero-content h1 {
    color: #000;
}
.hero-area .hero-content .button .btn {
    background-color: var(--thm-primary);
    color: #fff;
    margin-right: 12px;
    display: inline;

}
.hero-area .hero-content p {
    margin-top: 30px;
    font-size: 20px;
    font-weight: 400;
    color: #000;

}

.section-title {
        margin-bottom: 70px;
}

.section-title h3 {
    display: inline-block;
    border: 0px solid var(--thm-primary);
    border-radius: 30px;
    padding: 5px 25px;
}

.section-title h3 {
    font-size: 14px;
    font-weight: 600;
    display: inline-block;
    margin-bottom: 8px;
    color: var(--thm-primary);
    background: #ecd3ff;
    text-transform: none;
}
.features .single-feature {
    text-align: left;
    padding: 10px 20px;
    background-color: #fff;
    border-radius: 0;
    position: relative;
    border-radius: 15px;
    margin-top: 30px;
    border: 1px solid #eee;
    -webkit-transition: all 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55) 0s;
    transition: all 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55) 0s;
}
.features .single-feature i {
    height: 60px;
    width: 60px;
    line-height: 60px;
    text-align: center;
    display: inline-block;
    background-color: #eef1f4ba;
    color: #7800d759;
    font-size: 30px;
    border-radius: 20px;
    -webkit-box-shadow: 0px 4px 6px #0000002a;
    box-shadow: 0px 4px 6px #00000012;
    margin-bottom: 30px;
}
.footer-copyright {
    background-color: #ffffff;
    color: var(--thm-copyright-text-color);
    padding: 15px 0 12px 0;
    border-top: 0px solid #404040;
}

.add-list-button {
    display: inline-block;
    margin-left: 0;
}

.navbar-collapse {
    position: static; /* or relative, depending on layout needs */
}

.sub-menu-bar {
    position: static;
    top: 100%; /* Ensures it drops down below the element */
    left: 0;
    z-index: 1000; /* Ensures it appears above other content */
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
                            <img src="<?= $applicationModel->getBranchImage(get_loggedin_branch_id(), 'logo-small') ?>" alt="Logo">                            </a>
                            <div class="collapse navbar-collapse sub-menu-bar" id="navbarSupportedContent">
                                <ul id="nav" class="navbar-nav ms-auto">
                                    <li class="nav-item">
                                        <a href="#home" class="page-scroll active" aria-label="Toggle navigation"><?php echo translate('home'); ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#features" class="page-scroll" aria-label="Toggle navigation"><?php echo translate('features'); ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#pricing" class="page-scroll" aria-label="Toggle navigation"><?php echo translate('pricing'); ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#faq" class="page-scroll" aria-label="Toggle navigation"><?php echo translate('faq'); ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#contact" class="page-scroll" aria-label="Toggle navigation"><?php echo translate('contact'); ?></a>
                                    </li>
                                </ul>
                            </div>
                            <!-- navbar collapse -->
                            <div class="header-btn">
                            <div class="button add-list-button">
                                <?php if (!is_loggedin()) { ?>
                                <a href="<?php echo base_url('authentication/index') ?>" class="btn"><?php echo translate('login'); ?></a>
                                <?php } else { ?>
                                    <a href="<?php echo base_url('dashboard/index') ?>" class="btn"><?php echo translate('dashboard'); ?></a>
                                <?php } ?>
                            </div>
                            <button class="navbar-toggler mobile-menu-btn" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="toggler-icon"></span>
                                <span class="toggler-icon"></span>
                                <span class="toggler-icon"></span>
                            </button>
                            </div>
                        </nav>
                        <!-- End Navbar -->
                        <?php $session = \Config\Services::session(); ?>
                    <?php if ($session->getFlashdata('website_expired_msg')) { ?>
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

<!-- Start Slider Area -->
<section id="home" class="hero-area">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-5 col-md-12 col-12">
                <div class="hero-content">
                    <h1 class="wow fadeInLeft" data-wow-delay=".4s"><?php echo $getSettings->slider_title; ?></h1>
                    <p class="wow fadeInLeft" data-wow-delay=".6s"><?php echo $getSettings->slider_description; ?></p>
                    <div class="button add-list-button wow fadeInLeft" data-wow-delay=".8s">
                    <a href="#" class="btn" data-bs-toggle="modal" data-bs-target="#registrationModal">
                    Sign up
                    </a>
                    </div>
                </div>
                    <div class="button wow fadeInLeft" data-wow-delay=".8s">
                    <?php if (!empty($getSettings->button_text_1)) { 
                        echo '<a href="' .  $getSettings->button_url_1 . '" class="btn">' . $getSettings->button_text_1 . '</a>';
                    }
                    ?></div>

                </div>
            </div>
            <div class="hero-image wow fadeInUp" data-wow-delay="1s">
                        <img src="<?php echo base_url('uploads/app_image/landing-saas-int.webp'); ?>" alt="Landing Image" class="img-fluid" style="margin-top: 50px;border-radius: 20px;
                        ">
                    </div>
            <div class="col-lg-7 col-md-12 col-12">
                <div class="hero-image wow fadeInRight" data-wow-delay=".4s">
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Slider Area -->
<style>
@keyframes scroll {
  0% {
    transform: translateX(0);
  }
  100% {
    transform: translateX(calc(-250px * 8)); /* Adjust the multiplier based on the number of duplicated slides */
  }
}
.loop--slider {
  height: 120px; /* Increased height to ensure visibility */
  margin: auto;
  overflow: hidden;
  position: relative;
  width: 100%; /* Full width */
}
.loop--slider::before, .loop--slider::after {
  background: linear-gradient(to right, white 0%, rgba(255, 255, 255, 0) 100%);
  content: "";
  height: 120px;
  position: absolute;
  width: 100px;
  z-index: 2;
}
.loop--slider::after {
  right: 0;
  top: 0;
  transform: rotateZ(180deg);
}
.loop--slider::before {
  left: 0;
  top: 0;
}
.loop--slide--track {
  animation: scroll 60s linear infinite; /* Slower scroll */
  display: flex;
  width: calc(250px * 8); /* Ensure this matches the total width of your logos and their duplication */
}
.loop--slide {
  width: 250px; /* Width of each slide */
  display: flex;
  align-items: center;
  justify-content: center; /* Centering images inside the slides */
  padding: 0 15px; /* Adding space between logos */
}
.loop--slide img {
  max-height: 100px; /* Restricting height */
  width: auto; /* Adjust width automatically */
  max-width: 100%; /* Ensure image does not exceed its container */
}

.loop--slider {
    height: 120px;
    overflow: hidden;
    position: relative;
    width: 100%;
}
.loop--slide--track {
    display: flex;
    white-space: nowrap;
}
.loop--slide {
    flex: 0 0 auto;
    width: 250px; /* Fixed width for each slide */
}
</style>


<div class="container" style="margin-bottom: 100px;">
    <div class="row">
        <div class="col-12">
            <div class="header" style="margin:5px 0;">
                <h3 class="wow fadeInUp" data-wow-delay=".4s">Trusted Partners</h3>
               
                </div>
                <div class="loop--slider">
                    <div class="loop--slide--track">

		<div class="loop--slide">
			<img src="https://www.cdnlogo.com/logos/m/6/microsoft.svg" height="80" width="250" alt="Partner Logo 1" />
		</div>
		<div class="loop--slide">
			<img src="https://www.cdnlogo.com/logos/m/66/mcgraw-hill-education-wordmark.svg" height="80" width="250" alt="Partner Logo 2" />
		</div>
		<div class="loop--slide">
			<img src="https://upload.wikimedia.org/wikipedia/commons/f/fc/Natgeologo.svg" height="80" alt="Partner Logo 3" />
		</div>
        <div class="loop--slide">
			<img src="https://www.cdnlogo.com/logos/g/45/google-cloud.svg" height="80" width="250" alt="Partner Logo 4" />
		</div>
		<div class="loop--slide">
			<img src="https://www.cdnlogo.com/logos/m/82/microsoft-365.svg" height="80" width="250" alt="Partner Logo 5" />
		</div>
		<div class="loop--slide">
			<img src="https://images.expertmarket.co.uk/wp-content/uploads/sites/2/2022/11/zoom-logo-e1684927750744.png" height="100" width="250" alt="Partner Logo 6" />
		</div>
		<div class="loop--slide">
			<img src="https://www.cdnlogo.com/logos/w/21/whatsapp.svg" height="80" width="250" alt="Partner Logo 7" />
		</div>
        <div class="loop--slide">
			<img src="https://edu.google.com/assets/icons/pages/main/workspace-for-education/classroom/classroom-banner-2.svg" height="80" width="250" alt="Partner Logo 8" />
		</div>
        <div class="loop--slide">
			<img src="https://www.cdnlogo.com/logos/m/6/microsoft.svg" height="80" width="250" alt="Partner Logo 1" />
		</div>
		<div class="loop--slide">
			<img src="https://www.cdnlogo.com/logos/m/66/mcgraw-hill-education-wordmark.svg" height="80" width="250" alt="Partner Logo 2" />
		</div>
		<div class="loop--slide">
			<img src="https://upload.wikimedia.org/wikipedia/commons/f/fc/Natgeologo.svg" height="80" alt="Partner Logo 3" />
		</div>
        <div class="loop--slide">
			<img src="https://www.cdnlogo.com/logos/g/45/google-cloud.svg" height="80" width="250" alt="Partner Logo 4" />
		</div>
		<div class="loop--slide">
			<img src="https://www.cdnlogo.com/logos/m/82/microsoft-365.svg" height="80" width="250" alt="Partner Logo 5" />
		</div>
		<div class="loop--slide">
			<img src="https://images.expertmarket.co.uk/wp-content/uploads/sites/2/2022/11/zoom-logo-e1684927750744.png" height="80" width="250" alt="Partner Logo 6" />
		</div>
		<div class="loop--slide">
			<img src="https://www.cdnlogo.com/logos/w/21/whatsapp.svg" height="80" width="250" alt="Partner Logo 7" />
		</div>
        <div class="loop--slide">
			<img src="https://edu.google.com/assets/icons/pages/main/workspace-for-education/classroom/classroom-banner-2.svg" height="80" width="250" alt="Partner Logo 8" />
		</div>
	</div>
</div>
            </div>
        </div>
    </div>





<!-- Start Outcomes Section -->
<style>
.outcomes {
    padding: 60px 0;
}

.single-outcome {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    transition: box-shadow 0.3s ease, transform 0.3s ease;
    min-height: 200px; /* Ensuring same size for all cards */
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    margin: 10px 0;
}

.single-outcome:hover {
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    transform: translateY(-10px);
}

.single-outcome h3 {
    font-size: 1.5rem;
    margin-bottom: 15px;
}

.single-outcome p {
    font-size: 1rem;
    margin-bottom: 20px;
}


</style>
<!-- Start Outcomes Section -->
<section id="outcomes" class="outcomes section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title">
                    <h2 class="wow fadeInUp" data-wow-delay=".4s">Manage your school operations in the cloud with secure access for everyone.</h2>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Schools Card -->
            <div class="col-lg-3 col-md-6 col-12">
                <div class="single-outcome wow fadeInUp" data-wow-delay=".2s" style="background-color: #fffaeb;">
                    <h3>Schools</h3>
                    <p>Enhance operations, improve efficiency, and lower overheads with the most powerful school platform at your service.</p>
                </div>
            </div>
            <!-- Teachers Card -->
            <div class="col-lg-3 col-md-6 col-12">
                <div class="single-outcome wow fadeInUp" data-wow-delay=".4s" style="background-color: #fff3f7;">
                    <h3>Teachers</h3>
                    <p>Foster a rich learning environment with top-tier educational content and digital tools that streamline every classroom activity.</p>
                </div>
            </div>
            <!-- Students Card -->
            <div class="col-lg-3 col-md-6 col-12">
                <div class="single-outcome wow fadeInUp" data-wow-delay=".6s" style="background-color: #eefdff;">
                    <h3>Students</h3>
                    <p>Stay engaged with seamless access to classroom, unlimited practice questions, and more, ensuring you never miss a lesson.</p>
                </div>
            </div>
            <!-- Parents Card -->
            <div class="col-lg-3 col-md-6 col-12">
                <div class="single-outcome wow fadeInUp" data-wow-delay=".8s" style="background-color: #fff3f7;">
                    <h3>Parents</h3>
                    <p>Enable parents to track their children's progress with full transparency and stay informed on all updates effortlessly.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Outcomes Section -->


<!-- Section hero -->
<style>
        .masonry-container {
            display: grid;
            grid-template-columns: repeat(6, 1fr); /* Creates a 6-column grid */
            grid-gap: 10px;
            padding: 20px;
            max-width: 1320px;
            margin: auto;
        }
        .masonry-item {
            background-color: #f0f0f0;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            align-items: center; /* Center the image vertically */
            justify-content: center; /* Center the image horizontally */
        }
        .masonry-item.large { /* Full width */
            grid-column: span 6;
        }
        .masonry-item.medium { /* Half width, for 2 images per row */
            grid-column: span 3;
            height: 300px; /* Fixed height */
        }
        .masonry-item.small { /* One third, for 3 images per row */
            grid-column: span 2;
            height: 300px; /* Fixed height to match the medium items */
        }
        .masonry-item img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }
        .header-statistics-container {
            display: flex;
            justify-content: space-between; /* Ensures the header and statistics are on opposite ends */
            align-items: center;
            width: 100%;
            margin: 10px;
            max-width: -webkit-fill-available; 
            border-radius: 20px;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid gainsboro;

        }
        
        .statistics {
            display: flex;
            text-align: center;
        }
        .stat {
            margin-left: 100px; /* Spaces out the statistics */
        }
        .stat strong {
            font-size: 34px;
            display: block;
            text-align: justify;
            color: var(--thm-primary);
        }
        .stat span {
            font-size: 16px;
        }
        .hand-drawn-line {
            margin-top: -100px; /* Space between the header and the image */
            margin-left: 20px;
        }
        
        @media (max-width: 768px) {
            .hand-drawn-line {
                max-width: 50%;
            }
        }

        @media (max-width: 768px) {
        .header-statistics-container {
        }

        .statistics {
            flex-direction: column; /* Stack statistics vertically on smaller screens */
            align-items: center; /* Center align the stats */
        }

        .stat {
            margin-right: 0; /* Remove right margin on smaller screens */
            margin-bottom: 10px; /* Adds spacing between stacked stats */
        }

        .hand-drawn-line {
            max-width: 50%; /* Optionally reduce the size of the line image */
        }
    }
    </style>

<div class="masonry-container">
        <!-- Row with 1 large image -->
        <div class="masonry-item large">
            <img src="uploads/app_image/landing-global-users.webp" alt="Global Users">
        </div>
        <!-- Row with 3 small images -->
        <div class="masonry-item small">
            <img src="uploads/app_image/landing-user-interaction.webp" alt="User Interaction">
        </div>
        <div class="masonry-item small">
            <img src="uploads/app_image/landing-team-meeting.webp" alt="Team Meeting">
        </div>
        <div class="masonry-item small">
            <img src="uploads/app_image/landing-casual.webp" alt="Casual Workspace">
        </div>
        <!-- Row with 2 medium images -->
        <!-- <div class="masonry-item medium">
            <img src="uploads/app_image/landing-call.webp" alt="Conference Call">
        </div>
        <div class="masonry-item medium">
            <img src="uploads/app_image/landing-discussion.webp" alt="Office Discussion">
        </div> -->
    </div>
    <!-- Statistics section -->
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="header-statistics-container">
                    <h3 class="wow fadeInUp" data-wow-delay=".4s">We serve users globally</h3>
                    <div class="statistics">
                        <div class="stat wow fadeInUp" data-wow-delay=".6s"><strong>50+</strong> Countries</div>
                        <div class="stat wow fadeInUp" data-wow-delay=".8s"><strong>14+</strong> Languages Supported</div>
                    </div>
                </div>
                <img src="uploads/app_image/landing-underline.png" alt="Decorative Line" class="wow fadeInUp hand-drawn-line" data-wow-delay=".8s">
            </div>
        </div>
    </div>
    </div>





    <!-- Start Features Area -->
    <section id="features" class="features section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-title">
                        <h3 class="wow zoomIn" data-wow-delay=".2s"><?php echo translate('features'); ?></h3>
                        <h2 class="wow fadeInUp" data-wow-delay=".4s"><?php echo $getSettings->feature_title; ?></h2>
                        <p class="wow fadeInUp" data-wow-delay=".6s"><?php echo $getSettings->feature_description; ?></p>
                    </div>
                </div>
            </div>
            <div class="row">
                <?php
                $delaycount = .2;
                $count = 0;
                foreach ($featureslist as $key => $feature) {
                    ?>
                <div class="col-lg-4 col-md-6 col-12">
                    <!-- Start Single Feature -->
                    <div class="single-feature wow fadeInUp" data-wow-delay="<?php echo $delaycount ?>s">
                        <i class="<?php echo $feature->icon; ?>"></i>
                        <h3><?php echo $feature->title; ?></h3>
                        <p><?php echo $feature->description; ?></p>
                    </div>
                    <!-- End Single Feature -->
                </div>
                <?php
                    if ($count < 2) {
                        $count++;
                        $delaycount += .2;
                    } else {
                        $count = 0;
                        $delaycount = .2;
                    } 
                } ?>
                </div>
            </div>
        </div>
    </section>
    <!-- End Features Area -->

    <style>
            .section-heading {
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            margin: 0; /* Remove margin to push to edge */
            color: var(--thm-primary); /* Ensure this variable is defined in your CSS or replace it with a specific color */
            background: #ecd3ff;
            text-transform: none;
            padding: 5px; /* Optional padding for better visual */
            display: inline-block;
            border-radius: 30px;
            padding: 5px 25px;
            margin-bottom: 8px;
        }
        .image-content img {
            width: 100%; /* Ensure the image takes up the full width of its container */
            height: auto; /* Maintain aspect ratio */
            object-fit: cover; /* Cover ensures the div is fully covered by the image */
            border-radius: 20px; /* Optional: add border-radius if needed */
            margin: 50px 0;
        }
    </style>
    
<!-- Start Managers Area -->
<section id="managers" class="section">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-12">
                <!-- Text Content -->
                <div class="text-content">
                    <h3 class="section-heading"><?php echo translate('managers'); ?></h3>
                    <h4 style="padding:10px 0;">Keep Everyone In Your School Connected Through Comprehensive Communication Channels.</h4>
                    <p style="padding:10px 0;">Our platform empowers seamless, multi-channel communication across SMS, WhatsApp, Email, and internal systems, ensuring that every staff member is informed with the latest updates promptly and securely.</p>
                    <!-- Button Trigger for Modal -->
                    <div class="button wow fadeInLeft" style="margin-top:10px ;" data-wow-delay=".8s">
                        <a href="#" class="btn" style="padding:var(--bs-btn-padding-y) var(--bs-btn-padding-x);" data-bs-toggle="modal" data-bs-target="#registrationModal">
                            Learn More
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-12">
                <!-- Image Content -->
                <div class="image-content">
                    <img src="uploads/app_image/landing-manager.webp" alt="Communication">
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Managers Area -->

<!-- Start Finance Area -->
<section id="finance" class="section">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-12">
                <!-- Image Content -->
                <div class="image-content">
                    <img src="uploads/app_image/landing-finance.webp" alt="Automated Payment">
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-12">
                <!-- Text Content -->
                <div class="text-content">
                    <h3 class="section-heading"><?php echo translate('accounting'); ?></h3>
                    <h4 style="padding:10px 0;">Streamline Financial Operations with Automated Billing and Invoicing.</h4>
                    <p style="padding:10px 0;">Effortlessly streamline school financial processes, ensuring timely tuition collection, fee management, and budget tracking for optimal financial oversight.</p>
                    <!-- Button Trigger for Modal -->
                    <div class="button fadeInLeft" style="margin-top:10px;" data-wow-delay=".8s">
                        <a href="#" class="btn" style="padding:var(--bs-btn-padding-y) var(--bs-btn-padding-x);" data-bs-toggle="modal" data-bs-target="#registrationModal">
                            Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Finance Area -->

<!-- Start Parents Area -->
<section id="parents" class="section">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-12">
                <!-- Text Content -->
                <div class="text-content">
                    <h3 class="section-heading"><?php echo translate('parents'); ?></h3>
                    <h4 style="padding:10px 0;">Enhance Parental Involvement In Their Child's Education With Our Intuitive Online Portal.</h4>
                    <p style="padding:10px 0;">With our online portal, parents can play an active role in their child's education, fostering a stronger home-school connection and supporting their child's academic success.</p>
                    <!-- Button Trigger for Modal -->
                    <div class="button wow fadeInLeft" style="margin-top:10px;" data-wow-delay=".8s">
                        <a href="#" class="btn" style="padding:var(--bs-btn-padding-y) var(--bs-btn-padding-x);" data-bs-toggle="modal" data-bs-target="#registrationModal">
                            Learn More
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-12">
                <!-- Image Content -->
                <div class="image-content">
                    <img src="uploads/app_image/landing-parents.webp" alt="Parental Involvement">
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Parents Area -->


<!-- Start Payment Methods Section -->
<section id="payment-methods" class="payment-methods section" style="margin-top: 20px;margin-bottom:0px;padding-bottom: 0px;">
    <div class="container" style="border: 1px solid gainsboro; border-radius: 35px; padding: 20px;">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-12 col-12">
                <div class="payment-text wow fadeInLeft" data-wow-delay=".4s">
                    <h3 class="section-heading" style="margin: 0 20px;">User-Friendly Dashboard</h3>
                    <h4 style="margin: 20px;">Access your dashboard anytime, anywhere, ensuring seamless usability across all devices for effective management on the go.</h4>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 col-12">
                <div class="payment-image wow fadeInRight" data-wow-delay=".4s">
                    <img src="<?php echo base_url('uploads/app_image/landing-mobile.webp'); ?>" alt="Payment Methods" style="max-width: 400px; width: 100%; height: auto; border-radius: 20px; float: right;">
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Payment Methods Section -->

<!-- Start Payment Methods Section -->
<section id="payment-methods" class="payment-methods section" style="margin-top: 0px;margin-bottom:0px;padding-bottom: 0px;">
    <div class="container" style="border: 1px solid gainsboro; border-radius: 35px; padding: 20px;">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-12 col-12">
                <div class="payment-text wow fadeInLeft" data-wow-delay=".4s">
                    <h3 class="section-heading" style="margin: 0 20px;">Payment Integration</h3>
                    <h4 style="margin: 20px;">Facilitate seamless transactions with automated scheduling and accept over 80 online and offline payment methods instantly.</h4>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 col-12">
                <div class="payment-image wow fadeInRight" data-wow-delay=".4s">
                    <img src="<?php echo base_url('uploads/app_image/landing-integrate.webp'); ?>" alt="Payment Methods" style="max-width: 400px; width: 100%; height: auto; border-radius: 20px; float: right;">
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Payment Methods Section -->

<!-- Start Payment Methods Section -->
<section id="payment-methods" class="payment-methods section">
    <div class="container" style="border: 1px solid gainsboro; border-radius: 35px; padding: 20px;">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-12 col-12">
                <div class="payment-text wow fadeInLeft" data-wow-delay=".4s">
                    <h3 class="section-heading" style="margin: 0 20px;">Analytics & More</h3>
                    <h4 style="margin: 20px;">Experience real-time insights with our integrated dashboard, combining advanced analytics and instant reports for streamlined performance monitoring.</h4>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 col-12">
                <div class="payment-image wow fadeInRight" data-wow-delay=".4s">
                    <img src="<?php echo base_url('uploads/app_image/landing-dashboard.webp'); ?>" alt="Payment Methods" style="max-width: 400px; width: 100%; height: auto; border-radius: 20px; float: right;">
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Payment Methods Section -->

<style>
    .payment-methods.section {
        position: relative;
        overflow: hidden;
    }

    .payment-methods .container {
        transition: transform 0.5s ease-in-out, opacity 0.5s ease-in-out;
        opacity: 0.5;
        transform: scale(0.95);
    }

    .payment-methods .container.in-view {
        opacity: 1;
        transform: scale(1);
    }

    .section-title h2 {
        margin-bottom: 20px;
    }

    @media (min-width: 992px) {
        .payment-methods.section {
            margin-bottom: 20px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sections = document.querySelectorAll('.payment-methods .container');

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                } else {
                    entry.target.classList.remove('in-view');
                }
            });
        }, { threshold: 0.5 });

        sections.forEach(section => {
            observer.observe(section);
        });
    });
</script>

<!-- Start New Row -->
<!-- <div class="row">
                <div class="col-12">
                    <div class="section-title">
                        <h3 class="wow zoomIn" data-wow-delay=".2s"><?php echo translate('Communication'); ?></h3>
                        <h2 class="wow fadeInUp" data-wow-delay=".4s"> Integrated Email & Messaging</h2>
                        <p class="wow fadeInUp" data-wow-delay=".6s"> Keep your school community engaged and informed with our secure messaging system, ensuring everyone stays updated effortlessly.</p>
                    </div>
                </div>
            </div>

<section id="new-section" class="new-section section" style=" padding-top: 50px;">
    <div class="container">
        <div class="row align-items-center" id="equal-height-container">
            <div class="col-lg-6 col-md-12 col-12">
                <div class="content wow fadeInLeft equal-height" data-wow-delay=".4s" style="border: 1px solid gainsboro; border-radius: 35px; padding: 20px; margin-bottom: 20px;">
                    <img src="<?php echo base_url('uploads/app_image/landing-image1.webp'); ?>" alt="First Image" class="img-fluid" style="border-radius: 20px; max-height:400px;">
                    <h2 style="margin:20px;">Seamless Communication</h2>
                    <p style="margin:20px;">Cleve offers a seamless communication feature, allowing you to send important updates and reminders effortlessly.</p>
                    <div class="button add-list-button wow fadeInLeft" data-wow-delay=".8s" style="margin:20px;">
                        <a href="#" class="btn" data-bs-toggle="modal" data-bs-target="#registrationModal">
                            Get started
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 col-12">
                <div class="content wow fadeInRight equal-height" data-wow-delay=".4s" style="border: 1px solid gainsboro; border-radius: 35px; padding: 20px; margin-bottom: 20px;">
                    <img src="<?php echo base_url('uploads/app_image/landing-image2.webp'); ?>" alt="Second Image" class="img-fluid" style="border-radius: 20px; max-height:400px;">
                    <h2 style="margin:20px;">Internal Messaging</h2>
                    <p style="margin:20px;">Utilize our secure and robust internal messaging feature to stay connected and informed. Easily send and receive messages within the school community.</p>
                    <div class="button add-list-button wow fadeInRight" data-wow-delay=".8s" style="margin:20px;">
                        <a href="#" class="btn" data-bs-toggle="modal" data-bs-target="#registrationModal">
                            Get started
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> -->
<!-- End New Section -->

<style>
    .equal-height {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .img-fluid {
        max-height: 300px; /* Adjust based on your design */
        width: 100%;
        object-fit: cover;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var equalHeightContainers = document.querySelectorAll('.equal-height');
        var maxHeight = 0;

        equalHeightContainers.forEach(function(container) {
            var containerHeight = container.offsetHeight;
            if (containerHeight > maxHeight) {
                maxHeight = containerHeight;
            }
        });

        equalHeightContainers.forEach(function(container) {
            container.style.height = maxHeight + 'px';
        });
    });
</script>


<!-- Start Pricing Table Area -->
<section id="pricing" class="pricing-table section">
    <div class="container">
        <div class="section-title text-center">
            <h3 class="wow zoomIn" data-wow-delay=".2s">Pricing</h3>
            <h2 class="wow fadeInUp" data-wow-delay=".4s">Choose the Plan That Suits Your School</h2>
            <p class="wow fadeInUp" data-wow-delay=".6s">Our flexible pricing plans are designed to meet the needs of all types of educational institutions. <br>Select a plan and get started today.</p>
        </div>
        <div class="row justify-content-center">
    <div class="col-md-6 col-xl-4 col-xxl-3 wow fadeInUp mt-3" data-wow-delay=".2s">
        <div class="card mb-4 box-shadow same-height">
            <div class="card-header">
                <h3 class="my-0 font-weight-normal">Basic Plan</h3>
                <h6>A starter plan for digitizing your school.</h6>

            </div>
            <div class="card-body d-flex flex-column">
                <ul class="list-unstyled mt-3 mb-4">
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Admin Dashboard</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> 40+ Analytics & Reports</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> 20+ Payment Gateways</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Mobile App</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Admission Management</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Student Information System</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Employee Management</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Teacher / Parent Portal</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Student Dashboard</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Student Accounting</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Event Management</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> ID Card Generator</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> WhatsApp Integration</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Teacher-Student Chat</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Internal Messaging</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Attendance Management</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Library Management</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Multi-language Support</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Customizable Tables</li>
                </ul>
                <div class="mt-auto">
                    <button type="button" class="btn btn-lg btn-block btn-primary stretched-link" data-bs-toggle="modal" data-bs-target="#registrationModal">Get Started</button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4 col-xxl-3 wow fadeInUp mt-3" data-wow-delay=".4s">
        <div class="card mb-4 box-shadow pxp-is-featured same-height">
            <div class="card-header">
                <h3 class="my-0 font-weight-normal">Premium Plan</h3>
                <h6>Includes everything in the Basic Plan, plus:</h6>

            </div>
            <div class="card-body d-flex flex-column">
                <ul class="list-unstyled mt-3 mb-4">
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Alumni Management</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Attachment Book</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Bulk SMS and Email Campaigns</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Card Management</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Certificate Generation</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Custom Domain</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Homework Management</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Housing Management</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Human Resource Management</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Inventory Management</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Multi-Class Support</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Office Accounting</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> QR Code Attendance</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Reception Management</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Student Transport Management</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Bulk Export and Import</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Website Builder</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> 24/7 Support</li>

                </ul>
                <div class="mt-auto">
                    <button type="button" class="btn btn-lg btn-block btn-primary stretched-link" data-bs-toggle="modal" data-bs-target="#registrationModal">Get Started</button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4 col-xxl-3 wow fadeInUp mt-3" data-wow-delay=".6s">
        <div class="card mb-4 box-shadow same-height">
            <div class="card-header">
                <h3 class="my-0 font-weight-normal">Advanced Plan</h3>
                <h6>Includes everything in the Premium Plan, plus:</h6>

            </div>
            <div class="card-body d-flex flex-column">
                <ul class="list-unstyled mt-3 mb-4">
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Live Class Integration</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Online Classes Management</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Online Exam Management</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Enhanced Online Features</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Comprehensive Reporting</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Analytics for Online Learning</li>
                    <li><span class="badge badge-success"><i class="lni lni-checkmark"></i></span> Virtual Classrooms</li>
                </ul>
                <div class="mt-auto">
                    <button type="button" class="btn btn-lg btn-block btn-primary stretched-link" data-bs-toggle="modal" data-bs-target="#registrationModal">Get Started</button>
                </div>
            </div>
        </div>
    </div>
</div>

    </div>
</div>
<!--/ End Pricing Table Area -->


<style>
    .card {
        border: 1px solid gainsboro;
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .card-header {
        border-bottom: none;
        border-radius: 10px 10px 0 0;
        background: none;
    }
    .card-body {
        padding: 20px;
        flex-grow: 1;
    }
    .pricing-card-title {
        font-size: 1.5rem;
        margin-bottom: 15px;
    }
    .btn-primary {
        background-color: var(--thm-primary);
        border: none;
    }
    .btn-primary:hover {
        background-color: var(--thm-primary-dark);
    }
    .pxp-is-featured {
        border: 2px solid var(--thm-primary);
    }
    .badge-success {
        background-color: rgba(40, 167, 69, 0.1);
        border-radius: 50%;
        color: #28a745;
        padding: 0.5em;
        display: inline-block;
        width: 2em;
        height: 2em;
        line-height: 1.5em;
        text-align: center;
    }
    .same-height {
        height: 100%;
    }
    .stretched-link {
        width: 100%;
    }
    .mt-auto {
        margin-top: auto;
    }
</style>
<!--/ End Pricing Table Area -->



    <!-- Start Faq Area -->
    <section class="faq section" id="faq">
        <div class=container>
            <div class=row>
                <div class=col-12>
                    <div class=section-title>
                        <h3 class="wow zoomIn" data-wow-delay=.2s><?php echo translate('FAQ'); ?></h3>
                        <h2 class="wow fadeInUp" data-wow-delay=.4s><?php echo $getSettings->faq_title; ?></h2>
                        <p class="wow fadeInUp" data-wow-delay=.6s><?php echo $getSettings->faq_description; ?></p>
                    </div>
                </div>
            </div>
            <div class=row>
                <div class=col-12>
                    <div class=accordion id=accordionExample>
                        <?php
                        $count = 1;
                        foreach ($faqs as $key => $faq) {
                            ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?php echo $key ?>">
                                <button class="accordion-button collapsed" type=button data-bs-toggle=collapse data-bs-target="#faq<?php echo $key ?>" aria-expanded="<?php echo $key == 0 ? 'true' : ''; ?>" aria-controls=collapseOne>
                                    <span class=title><span class="serial"><?php echo $count++; ?></span><?php echo $faq->title; ?></span><i class="lni lni-plus"></i>
                                </button>
                            </h2>
                            <div id="faq<?php echo $key ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $key ?>" data-bs-parent="#accordionExample">
                                <div class="accordion-body"><?php echo $faq->description; ?></div>
                            </div>
                        </div>
                    <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End faq Area -->
    

    <!-- Start Contact Area -->
    <!-- <section class="section call-action" id="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-5 col-md-12 col-12">
                    <div class="contact-form">
                        <?php echo form_open('saas_website/send_email', array('class' => 'contact-frm')); ?>
                        <h3 class="mb-5"><?php echo $getSettings->contact_title; ?></h3>
                        <?php if($session->getFlashdata('msg_success')): ?>
                        <div class="alert alert-success">
                            <i class="icon-text-ml far fa-check-circle"></i> <?php echo $session->getFlashdata('msg_success'); ?>
                        </div>
                        <?php endif; ?>
                        <?php if($session->getFlashdata('msg_error')): ?>
                        <div class="alert alert-danger">
                            <?php echo $session->getFlashdata('msg_error'); ?>
                        </div>
                        <?php endif; ?>
                        <div class="form-group wow fadeInUp" data-wow-delay=".2s">
                            <input type="text" class="form-control" name="name" autocomplete="off" placeholder="Name *" value="">
                            <span class="error"></span>
                        </div>
                        <div class="form-group wow fadeInUp" data-wow-delay=".2s">
                            <input type="text" class="form-control" name="email" autocomplete="off" placeholder="Email *" value="">
                            <span class="error"></span>
                        </div>
                        <div class="form-group wow fadeInUp" data-wow-delay=".4s">
                            <input type="text" class="form-control" name="mobile" autocomplete="off" placeholder="Mobile *" value="">
                            <span class="error"></span>
                        </div>
                        <div class="form-group wow fadeInUp" data-wow-delay=".4s">
                            <input type="text" class="form-control" name="subject" autocomplete="off"  placeholder="Subject  *" value="">
                            <span class="error"></span>
                        </div>
                        <div class="form-group wow fadeInUp" data-wow-delay=".6s">
                            <textarea type="text" rows="5" class="form-control alert_settings" placeholder="Type Message *" name="message"></textarea>
                            <span class="error"></span>
                        </div>
                        <div class="button wow fadeInUp" data-wow-delay=".8s">
                            <button class="btn btn-alt" type="submit" data-loading-text="<i class='far fa-spinner fa-spin'></i> Processing"><i class="far fa-envelope"></i> <?php echo $getSettings->contact_button; ?></button>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="col-lg-7 col-md-12 col-12">
                    <h2 class="contact-title wow fadeInUp" data-wow-delay=".2s"><?php echo $getSettings->contact_description; ?></h2>
                    <div class="contact-item-wrapper">
                        <div class="row">
                            <div class="col-12 col-md-6 col-xl-12">
                                <div class="contact-item wow fadeInUp" data-wow-delay=".4s">
                                    <div class="contact-icon">
                                        <i class="lni lni-phone"></i>
                                    </div>
                                    <div class="contact-content">
                                        <h4><?php echo translate('phone'); ?></h4>
                                        <p><?= $global_config['mobileno'] ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-xl-12">
                                <div class="contact-item wow fadeInUp" data-wow-delay=".6s">
                                    <div class="contact-icon">
                                        <i class="lni lni-envelope"></i>
                                    </div>
                                    <div class="contact-content">
                                        <h4><?php echo translate('email'); ?></h4>
                                        <p><?= $global_config['institute_email'] ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-xl-12">
                                <div class="contact-item wow fadeInUp" data-wow-delay=".8s">
                                    <div class="contact-icon">
                                        <i class="lni lni-map-marker"></i>
                                    </div>
                                    <div class="contact-content">
                                        <h4><?php echo translate('address'); ?></h4>
                                        <p><?= $global_config['address'] ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section> -->
    <!-- End Call To Action Area -->

    <!-- Start Footer Area -->
    <footer class="footer">
        <!-- Start Footer Top -->
        <div class="footer-top">
            <div class="container mb-5">
                <div class="row">
                    <div class="col-lg-4 col-md-4 col-12">
                        <!-- Single Widget -->
                        <div class="single-footer f-about">
                            <div class="logo">
                                <a href="<?php echo base_url() ?>">
                                <img src="<?= $applicationModel->getBranchImage(get_loggedin_branch_id(), 'logo-small') ?>" alt="#">
                                </a>
                            </div>
                            <p><?php echo $getSettings->footer_about; ?></p>
                        </div>
                        <!-- End Single Widget -->
                        <!-- Single Widget -->
                        <div class="single-footer f-about">
                        <style>
                            .compliance-logo {
                            margin: 10px 5px; /* Top and bottom margin with a little horizontal spacing */
                            width: 80px; /* Adjust the width as needed */
                            height: auto; /* Maintain aspect ratio */
                            max-height: 80px;
                            display: inline-block; /* Align logos inline with space around */
                        }
                        </style>
                        <div class="compliance">
                            <img src="<?php echo base_url('uploads/app_image/gdpr-bage.png'); ?>" class="compliance-logo" alt="GDPR Compliance Badge">
                            <img src="<?php echo base_url('uploads/app_image/soc-compliance.png'); ?>" class="compliance-logo" alt="SOC Compliance">
                            <img src="<?php echo base_url('uploads/app_image/pci-compliance.webp'); ?>" class="compliance-logo" alt="ISO 27001 Certification">
                            <img src="<?php echo base_url('uploads/app_image/iso-27001.png'); ?>" class="compliance-logo" alt="ISO 27001 Certification">

                        </div>
                        <img src="<?php echo base_url('uploads/app_image/landing-aus2024.png'); ?>" alt="Top EdTech Australia 2024">


                        </div>
                         <!-- End Single Widget -->

                    </div>
                    <div class="col-lg-8 col-md-8 col-12">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 col-12">
                                <!-- Single Widget -->
                                <div class="single-footer f-link">
                                    <h3>Quick Link</h3>
                                    <ul>
                                        <li><a href="#home" class="page-scroll active" aria-label="Toggle navigation"><i class="far fa-angle-right"></i> <?php echo translate('home'); ?></a></li>
                                        <li><a href="#features" class="page-scroll" aria-label="Toggle navigation"><i class="far fa-angle-right"></i> <?php echo translate('features'); ?></a></li>
                                        <li><a href="#pricing" class="page-scroll" aria-label="Toggle navigation"><i class="far fa-angle-right"></i> <?php echo translate('pricing'); ?></a></li>
                                        <li><a href="#faq" class="page-scroll" aria-label="Toggle navigation"><i class="far fa-angle-right"></i> <?php echo translate('FAQ'); ?></a></li>
                                        <li><a href="#contact" class="page-scroll" aria-label="Toggle navigation"><i class="far fa-angle-right"></i> <?php echo translate('contact'); ?></a></li>
                                        <?php if ($getSettings->terms_status == 1) { ?>
                                        <a href="javascript:void(0)" id="termsFooter" style="color:black;"><i class="far fa-angle-right"></i> <?php echo translate('terms_&_conditions'); ?></a>
                                        <?php } ?>
                                    </ul>
                                </div>
                                <!-- End Single Widget -->
                            </div>
                            <div class="col-lg-4 col-md-6 col-12">
                                <!-- Single Widget -->
                                <div class="single-footer f-link address">
                                    <h3>Address</h3>
                                    <ul>
                                        <!-- <li class="clearfix"><i class="lni lni-map-marker"></i> <div style="margin-left: 47px;"><?= $global_config['address'] ?></div></li>
                                        <li class="clearfix"><i class="lni lni-phone"></i> <?= $global_config['mobileno'] ?></li> -->
                                        <li class="clearfix"><i class="lni lni-envelope"></i> <?= $global_config['institute_email'] ?></li>
                                    </ul>
                                </div>
                                <!-- End Single Widget -->
                            </div>
                            <div class="col-lg-4 col-md-6 col-12">
                                <!-- Single Widget -->
                                <div class="single-footer f-link">
                                    <h3>Social Link</h3>
                                    <ul class="social">
                                        <li><a href="<?= $global_config['facebook_url'] ?>"><i class="lni lni-facebook-filled"></i></a></li>
                                        <li><a href="<?= $global_config['twitter_url'] ?>"><i class="lni lni-twitter-original"></i></a></li>
                                        <li><a href="<?= $global_config['instagram_url'] ?>"><i class="lni lni-instagram"></i></a></li>
                                        <li><a href="<?= $global_config['linkedin_url'] ?>"><i class="lni lni-linkedin-original"></i></a></li>
                                        <li><a href="<?= $global_config['youtube_url'] ?>"><i class="lni lni-youtube"></i></a></li>
                                        <li><a href="<?= $global_config['google_plus_url'] ?>"><i class="lni lni-google"></i></a></li>
                                    </ul>
                                </div>
                                <!-- End Single Widget -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-copyright">
                <div class="container d-flex justify-content-between align-items-center">
                    <div class="copyright-text">
                        <div class="footer-copyright__content">
                            <span><?= $global_config['footer_text']; ?></span>
                        </div>
                    </div>
                    <div class="payment-logo">
                        <img src="<?php echo base_url('assets/frontend/images/saas/' . $getSettings->payment_logo); ?>" alt="">
                    </div>
                </div>
            </div>

        </div>
        <!--/ End Footer Top -->
    </footer>
    <!--/ End Footer Area -->

    <!-- ========================= scroll-top ========================= -->
    <a href="#" class="scroll-top">
        <i class="lni lni-chevron-up"></i>
    </a>

    <!-- ========================= JS here ========================= -->
    <script src="<?php echo base_url('assets/frontend/js/bootstrap.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/frontend/js/wow.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/frontend/js/tiny-slider.js'); ?>"></script>
    <script src="<?php echo base_url('assets/frontend/js/glightbox.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/frontend/js/saas_main.js'); ?>"></script>
    <script src="<?php echo base_url('assets/vendor/select2/js/select2.full.min.js');?>"></script>
    <script src="<?php echo base_url('assets/vendor/sweetalert/sweetalert.min.js');?>"></script>
</body>
</html>

<!-- Modal -->
<div class="modal fade" id="regModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">School Subscription</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php echo form_open_multipart('saas_website/register', array('class' => 'school-reg frm-submit-data')); ?>
                <input type="hidden" name="package_id" value="" id="packageID">
                <section class="card pg-fw mb-4 mt-2">
                    <div class="card-body">
                        <h5 class="chart-title mb-xs">Plan Summary</h5>
                        <div class="mt-2">
                            <ul class="sp-summary" id="summary">
                            </ul>
                        </div>
                    </div>
                </section>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="school_name">School Name *</label>
                            <input id="school_name" name="school_name" type="text" class="form-control" autocomplete="off">
                            <span class="error"></span>
                        </div>
                        <div class="form-group">
                            <label for="country">Country *</label>
                                <select id="country" name="country" class="form-control" required>
                            </select>
                            <span class="error"></span>
                        </div>
                        <div class="form-group">
                            <label for="school_address">School Address *</label>
                            <input id="school_address" name="school_address" type="text" class="form-control" autocomplete="off">
                            <span class="error"></span>
                        </div>
                        <div class="form-group">
                            <label for="photo">School Logo</label>
                            <input class="form-control" type="file" accept="image/*" id="photo" name="logo_file">
                            <span class="error"></span>
                        </div>
                        <div class="form-group">
                            <label for="school_info">Message</label>
                            <textarea name="message" id="message" rows="5" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="admin_name">Admin Name *</label>
                            <input id="admin_name" name="admin_name" type="text" class="form-control" autocomplete="off">
                            <span class="error"></span>
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender *</label>
                            <select class="form-select" id="gender" name="gender" data-minimum-results-for-search='Infinity'>
                                <option value="">Select a gender</option>
                                <option value="1">Male</option>
                                <option value="2">Female</option>
                            </select>
                            <span class="error"></span>
                        </div>
                        <div class="form-group">
                            <label for="admin_phone">Contact Number *</label>
                            <input id="admin_phone" name="admin_phone" type="tel" class="form-control" aria-expanded="false" autocomplete="off" required>
                            <input type="hidden" id="adminPhoneCountryCode" name="phone_country_code" value="">
                            <span class="error"></span>
                        </div>

                        <div class="form-group">
                            <label for="admin_email">Contact Email *</label>
                            <input name="admin_email" type="text" class="form-control" autocomplete="off">
                            <span class="error"></span>
                        </div>
                        <div class="form-group">
                            <label for="admin_password">Admin Login Username *</label>
                            <input name="admin_username" type="text" class="form-control" autocomplete="off">
                            <span class="error"></span>
                        </div>
                        <div class="form-group">
                            <label for="admin_password">Admin Login Password *</label>
                            <input name="admin_password" type="password" class="form-control" autocomplete="off">
                            <span class="error"></span>
                        </div>
                        <div class="form-group">
                            <label for="admin_password">Retype Password *</label>
                            <input name="retype_admin_password" type="password" class="form-control" autocomplete="off">
                            <span class="error"></span>
                        </div>
                    <?php if ($getSettings->terms_status == 1) { ?>
                        <div class="form-group">
                            <div class="checkbox-replace">
                                <label class="i-checks"><input type="checkbox" name="terms_cb"><i></i> <?php echo $getSettings->agree_checkbox_text ?></label>
                            </div>
                            <span class="error"></span>
                        </div>
                    <?php } ?>

                    <?php if ($getSettings->captcha_status == 1): ?>
                        <div class="form-group">
                            <?php echo $recaptcha['widget']; echo $recaptcha['script']; ?>
                            <span class="error"></span>
                        </div>
                    <?php endif; ?>
                    <div class="pp-plans-bottom">
                            <div class="pp-plans-cta button">
                                <button class="btn mb-4" data-id="1" type="submit" data-loading-text="<i class='far fa-spinner fa-spin'></i> Processing"> Register & Payment</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>
</div>

<?php if ($getSettings->terms_status == 1) { ?>
<div class="modal fade" id="termsModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="exampleModalLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" style="max-width: 80%;">
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
<?php
$alertclass = "";
if($session->getFlashdata('alert-message-success')){
    $alertclass = "success";
} else if ($session->getFlashdata('alert-message-error')){
    $alertclass = "error";
} else if ($session->getFlashdata('alert-message-info')){
    $alertclass = "info";
}
if($alertclass != ''):
    $alert_message = $session->getFlashdata('alert-message-'. $alertclass);
?>
    <script type="text/javascript">
        swal({
            toast: true,
            position: 'top-end',
            type: '<?php echo $alertclass?>',
            title: '<?php echo $alert_message?>',
            confirmButtonClass: 'btn btn-1',
            buttonsStyling: false,
            timer: 8000
        })
    </script>
<?php endif; ?>
<script type="text/javascript">
    $("#gender").select2({
        width: "100%"
    });

    $("form.frm-submit-data").each(function(i, el) {
        var $this = $(el);
        $this.on('submit', function(e) {
            e.preventDefault();
            var btn = $this.find('[type="submit"]');
            $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: new FormData(this),
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function() {
                    btn.button('loading');
                },
                success: function(data) {
                    console.log(data);
                    $('.error').html("");
                    if (data.status == "fail") {
                        $.each(data.error, function(index, value) {
                            $this.find("[name='" + index + "']").parents('.form-group').find('.error').html(value);
                        });
                    } else {
                        if (data.url) {
                            window.location.href = data.url;
                        } else if (data.status == "access_denied") {
                            window.location.href = base_url + "dashboard";
                        } else if (data.status == "error") {
                            $('#regModal').modal('hide');
                            swal({
                                title: data.title,
                                text: data.message,
                                buttonsStyling: false,
                                showCloseButton: true,
                                focusConfirm: false,
                                confirmButtonClass: "btn swal2-btn-default",
                                type: "error"
                            });
                        } else {
                            location.reload(true);
                        }
                    }
                },
                complete: function() {
                    btn.button('reset');
                },
                error: function() {
                    btn.button('reset');
                }
            });
        });
    });
</script>

<!-- Modal 2 registration form -->

<?php if($session->getFlashdata('alert-message-success')): ?>
    <div class="alert alert-success" role="alert">
        <?= $session->getFlashdata('alert-message-success'); ?>
    </div>
<?php elseif($session->getFlashdata('alert-message-error')): ?>
    <div class="alert alert-danger" role="alert">
        <?= $session->getFlashdata('alert-message-error'); ?>
    </div>
<?php elseif($session->getFlashdata('alert-message-info')): ?>
    <div class="alert alert-info" role="alert">
        <?= $session->getFlashdata('alert-message-info'); ?>
    </div>
<?php endif; ?>

<!-- Registration Modal -->
<div class="modal fade" id="registrationModal" tabindex="-1" role="dialog" aria-labelledby="registrationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="registrationModalLabel"> </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-4 d-none d-md-block">
            <img src="<?= base_url('uploads/app_image/landing-reg.webp'); ?>" class="img-fluid" alt="Registration" style="border-radius: 10px; width: 100%; height: 100%; object-fit: cover; max-height: fit-content;">
          </div>
          <div class="col-md-8">
          <h4 class="mb-3">Digitize your school instantly with Cleve School Management System's platform.</h4>
            <form id="registrationForm" method="post" action="<?= base_url('saas_website/submit_registration_form'); ?>" enctype="multipart/form-data">
            <?php
$security = \Config\Services::security();
?>
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
            <div class="row mb-3">
                <div class="col-md-6">
                  <label for="yourName" class="form-label">Your Name *</label>
                  <input type="text" class="form-control" id="yourName" name="admin_name" required>
                </div>
                <div class="col-md-6">
                  <label for="role" class="form-label">Your Role in the School *</label>
                  <input type="text" class="form-control" id="role" name="role_in_school" required>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-12">
                  <label class="form-label">Select Organisation Type *</label>
                  <div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="organisation_type" id="independentSchool" value="independent" checked>
                    <label class="form-check-label" for="independentSchool">
                      Independent School
                    </label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="organisation_type" id="groupOfSchools" value="group">
                    <label class="form-check-label" for="groupOfSchools">
                      Group of Schools
                    </label>
                  </div>
                </div>
              </div>
              </div>
              <div class="row mb-3">
                <!-- School Name Column -->
                <div class="col-md-12" id="schoolNameDiv">
                    <label for="schoolName" class="form-label">Name of your School *</label>
                    <input type="text" class="form-control" id="schoolName" name="school_name" required>
                    </div>
                    <!-- Number of Branches Column (Initially Hidden) -->
                    <div class="col-md-12" id="branchesDiv" style="display: none;">
                    <label for="numberOfBranches" class="form-label">Number of Branches *</label>
                    <input type="number" class="form-control" id="numberOfBranches" name="number_of_branches">
                    </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="numberOfStudents" class="form-label">Estimated Number of Students *</label>
                  <input type="number" class="form-control" id="numberOfStudents" name="estimated_students" required>
                </div>
                <div class="col-md-6">
                  <label for="email" class="form-label">Your Email *</label>
                  <input type="email" class="form-control" id="email" name="admin_email" required>
                </div>
              </div>
              <div class="row mb-3">
              <div class="form-group">
    <label for="phoneNumber">Phone Number *</label>
    <input id="phoneNumber" name="admin_phone" type="tel" class="form-control" aria-expanded="false" autocomplete="off" required>
    <input type="hidden" id="phoneCountryCode" name="phone_country_code" value="">
</div>
              </div>
              <div class="modal-footer">
              <span id="responseMessage" class="me-auto"></span>
                <button type="submit" class="btn btn-primary">Submit</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



<script>
document.addEventListener('DOMContentLoaded', function () {
    function openModalOnScroll() {
        var scrollPosition = window.scrollY + window.innerHeight;
        var pageHeight = document.documentElement.scrollHeight;

        // Calculate scroll percentage
        var scrollPercent = (scrollPosition / pageHeight) * 100;

        if (scrollPercent >= 50) {
            var registrationModal = new bootstrap.Modal(document.getElementById('registrationModal'));
            registrationModal.show();
            
            // Remove the event listener after showing the modal once
            window.removeEventListener('scroll', openModalOnScroll);
        }
    }

    // Add the scroll event listener
    window.addEventListener('scroll', openModalOnScroll);
});
</script>


<script>
document.addEventListener("DOMContentLoaded", function() {
    function initializePhoneInput(inputId, countryCodeInputId) {
        var input = document.querySelector(inputId);
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
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/js/utils.js", // just for formatting/placeholders etc
            dropdownContainer: document.body // append dropdown to the body
        });

        // Automatically set the phone country code based on the selected country
        input.addEventListener('countrychange', function() {
            var countryCode = iti.getSelectedCountryData().dialCode;
            document.querySelector(countryCodeInputId).value = '+' + countryCode;
        });

        // Adjust the dropdown container to match the input width and position
        input.addEventListener('open:countrydropdown', function() {
            var inputRect = input.getBoundingClientRect();
            var container = document.querySelector('.iti.iti--container');
            container.style.width = inputRect.width + 'px';
            container.style.left = inputRect.left + 'px';
            container.style.top = (inputRect.top + window.scrollY + inputRect.height) + 'px'; // Adjust for scroll position
            input.setAttribute('aria-expanded', 'true'); // Add aria-expanded
        });

        // Remove aria-expanded attribute when dropdown is closed
        input.addEventListener('close:countrydropdown', function() {
            input.setAttribute('aria-expanded', 'false'); // Remove aria-expanded
        });
    }

    // Initialize phone inputs
    initializePhoneInput("#phoneNumber", "#phoneCountryCode"); // for phoneNumber
    initializePhoneInput("#admin_phone", "#adminPhoneCountryCode"); // for admin_phone
});

</script>


<script>
$(document).ready(function() {
  // Hide the #branchesDiv and set default value initially
  $('#numberOfBranches').val(1);
  $('#branchesDiv').hide();

  $('input[type="radio"][name="organisation_type"]').change(function() {
    if (this.value == 'group') {
      // Show branchesDiv and adjust column sizes
      $('#branchesDiv').show().removeClass('col-md-12').addClass('col-md-6');
      $('#schoolNameDiv').removeClass('col-md-12').addClass('col-md-6');
    } else {
      // Hide branchesDiv and reset column sizes
      $('#branchesDiv').hide().removeClass('col-md-6').addClass('col-md-12');
      $('#schoolNameDiv').removeClass('col-md-6').addClass('col-md-12');
      $('#numberOfBranches').val(1); // Reset value
    }
  });

  $('#registrationForm').submit(function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    $.ajax({
      url: '<?= base_url("saas_website/submit_registration_form"); ?>',
      type: 'POST',
      data: formData,
      success: function(response) {
        var data = JSON.parse(response);
        var responseMessage = document.getElementById('responseMessage');
        responseMessage.innerText = data.message;
        if (data.status) {
          responseMessage.classList.remove('text-danger');
          responseMessage.classList.add('text-success');
          setTimeout(function() {
            $('#registrationModal').modal('hide');
            responseMessage.innerText = ''; // Clear the message after hiding the modal
          }, 3000); // Close modal after 3 seconds
        } else {
          responseMessage.classList.remove('text-success');
          responseMessage.classList.add('text-danger');
        }
      },
      error: function(xhr, status, error) {
        var responseMessage = document.getElementById('responseMessage');
        responseMessage.innerText = 'Failed to submit registration. Please try again.';
        responseMessage.classList.add('text-danger');
      }
    });
  });
});
</script>


<style>
.btn-primary {
    background-color: var(--thm-primary) !important;
    border-color: var(--thm-primary) !important;
}

/* Adjust the dropdown to be the same width as the input field */
.iti.iti--container {
    width: auto !important; /* Adjust width dynamically based on input */
    position: absolute !important;
    z-index: 9999 !important; /* Ensure it appears above other elements */
}

.iti {
    position: relative;
    display: block;
}

.iti__country-list {
    position: absolute;
    z-index: 2;
    list-style: none;
    text-align: left;
    padding: 0;
    margin: 0 0 0 -1px;
    box-shadow: 1px 1px 4px rgba(0, 0, 0, .2);
    background-color: #fff;
    border: 1px solid #ccc;
    white-space: nowrap;
    max-width: 280px;
    max-height: 280px;
    overflow-y: scroll;
    -webkit-overflow-scrolling: touch;
}

.iti-mobile .iti__country-list {
    max-height: 280px!important;
    width: 100%;
}
</style>


<!-- Cookie Concent --> 
<style>
  #cookieConsentContainer {
    z-index: 1000; /* Make sure it is on top */
    color: #000;
    font-size: 14px;
  }
</style>

<script>
// Function to set a cookie
function setCookie(name, value, days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}

// Function to get a cookie
function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

// Function to display the cookie consent banner
function checkCookieConsent() {
    var consentGiven = getCookie("user-consent");
    if (!consentGiven) {
        document.getElementById('cookieConsentContainer').style.display = "block";
    }
}

// Function to handle "Accept" action
function acceptCookies() {
    setCookie("user-consent", "accepted", 365); // Set the consent cookie for 1 year
    document.getElementById('cookieConsentContainer').style.display = "none";
    // Optionally load non-essential scripts here
}

// Function to handle "Reject" action
function rejectCookies() {
    setCookie("user-consent", "rejected", 365); // Set the consent cookie for 1 year
    document.getElementById('cookieConsentContainer').style.display = "none";
    // Handle rejection, potentially unload non-essential scripts
}

window.onload = function() {
    checkCookieConsent();
};
</script>
