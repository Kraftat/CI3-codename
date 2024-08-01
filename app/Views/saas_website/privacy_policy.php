<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Privacy Policy - Cleve.io</title>
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
</style>
</head>
<body>
    <header>
        <h1>Privacy Policy</h1>
    </header>
    <main>
        <section>
            <h2>Introduction</h2>
            <p>At Cleve.io, we are committed to protecting the privacy of all stakeholders in the educational ecosystem, including students, parents, and educational staff. This Privacy Policy outlines our practices concerning the data we collect from the schools that subscribe to our SaaS school management system.</p>
        </section>

        <section>
            <h2>Data Collection</h2>
            <p>We collect various types of information, including:</p>
            <ul>
                <li><strong>Personal Data:</strong> This may include names, contact details, and identification details of students, parents, and school staff provided by our subscribing schools.</li>
                <li><strong>Educational Data:</strong> Such as student enrollment details, attendance records, grades, and other educational achievement data.</li>
                <li><strong>Usage Data:</strong> Information on how the services are accessed and used, such as clickstream data and user interaction with our services.</li>
            </ul>
        </section>

        <section>
            <h2>Use of Data</h2>
            <p>The data collected is used to:</p>
            <ul>
                <li>Provide and maintain our service to subscribing schools.</li>
                <li>Communicate important notices and updates about the system.</li>
                <li>Enhance system functionality and user experience based on usage analytics.</li>
                <li>Ensure security and integrity of our services.</li>
            </ul>
        </section>

        <section>
            <h2>Legal Basis for Processing</h2>
            <p>Processing of personal data is based on:</p>
            <ul>
                <li><strong>Consent:</strong> Explicit consent provided by the users when they subscribe and enter data into the system.</li>
                <li><strong>Contractual Necessity:</strong> Processing is necessary for the performance of a contract to which the data subject is party.</li>
                <li><strong>Compliance with Legal Obligations:</strong> Processing is necessary for compliance with a legal obligation to which we are subject.</li>
                <li><strong>Legitimate Interests:</strong> Processing is necessary for the purposes of the legitimate interests pursued by us or by a third party.</li>
            </ul>
        </section>

        <section>
            <h2>Data Sharing and Transfers</h2>
            <p>Your data may be shared under the following circumstances:</p>
            <ul>
                <li>With service providers and partners who assist us in the operation of our services and who are bound by confidentiality agreements.</li>
                <li>With educational authorities when required by law or necessary for educational oversight.</li>
                <li>With third parties in connection with a merger, sale of company assets, or acquisition.</li>
            </ul>
        </section>

        <section>
            <h2>Your Rights</h2>
            <p>Under GDPR, you have the right to:</p>
            <ul>
                <li>Access, update or delete the information we have on you.</li>
                <li>Rectify any information if that information is inaccurate or incomplete.</li>
                <li>Object to our processing of your Personal Data.</li>
                <li>Request the transfer of your data to another organization (Data Portability).</li>
                <li>Withdraw consent at any time where we rely on your consent to process your personal data.</li>
            </ul>
        </section>

        <section>
            <h2>Cookies and Tracking Technologies</h2>
            <p>We use cookies to track the activity on our service and hold certain information. Cookies are files with a small amount of data which may include an anonymous unique identifier. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent.</p>
        </section>

        <section>
            <h2>Data Security</h2>
            <p>We take the security of your data seriously and implement appropriate technical and organizational measures to protect it against unauthorized or unlawful processing and against accidental loss, destruction, or damage.</p>
        </section>

        <section>
            <h2>Changes to This Privacy Policy</h2>
            <p>We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page.</p>
        </section>

        <section>
            <h2>Contact Us</h2>
            <p>If you have any questions about this Privacy Policy, please contact us at info@cleve.io.</p>
        </section>
    </main>
    <footer>
        <p>© 2024 Cleve.io. All rights reserved.</p>
    </footer>
</body>
</html>
