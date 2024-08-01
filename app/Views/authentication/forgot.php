<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $global_config['institute_name'] ?>">
    <meta name="author" content="<?= $global_config['institute_name'] ?>">
    <title><?= translate('password_restoration'); ?></title>
    <link rel="shortcut icon" href="<?= base_url('assets/images/favicon.png'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/font-awesome/css/all.min.css'); ?>">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.2/css/all.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.2/css/sharp-thin.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.2/css/sharp-solid.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.2/css/sharp-regular.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.2/css/sharp-light.css">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .input-group .form-control:last-child, .input-group-addon:last-child, .input-group-btn:first-child>.btn-group:not(:first-child)>.btn, .input-group-btn:first-child>.btn:not(:first-child), .input-group-btn:last-child>.btn, .input-group-btn:last-child>.btn-group>.btn, .input-group-btn:last-child>.dropdown-toggle {
            border-radius: 4px;
        }
        .input-group {
            position: relative;
            display: grid;
            border-collapse: separate;
        }
        .auth-main {
            background: #fff;
            padding: 30px;
            border-radius: 20px;
            width: 100%;
            max-width: 400px;
        }
        .auth-main .header img {
            margin: 0 auto 20px;
        }
        .auth-main h2 {
            text-align: left;
            margin-bottom: 20px;
            color: #333;
        }
        .auth-main .form-group {
            margin-bottom: 15px;
        }
        .auth-main .form-control {
            border-radius: 50px;
            width: 100%;
        }
        .auth-main .btn {
            border: none;
            margin-top: 10rem;
            background-color: #000;
            color: #fff;
        }
        .auth-main .btn:hover {
            background-color: #5e00a6;
        }
        .auth-main .sign-footer {
            text-align: left;
            margin-top: 40px;
            color: #777;
        }
        .auth-main .social-links a {
            color: #333;
            margin: 0 10px;
        }
        .error-messages {
            text-align: left; 
            margin-bottom: 20px; 
            color: #ff0000; 
            font-size: 14px;
        }
        .error {
            color: #a94442;
        }
    </style>
    <script src="<?= base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>
</head>
<body>
    <div class="auth-main">
        <div class="header">
            <img src="<?= $applicationModel->getBranchImage($branch_id, 'logo') ?>" height="60" alt="School">
            <h2><?= $global_config['institute_name'] ?></h2>
        </div>
        <?php if (session()->getFlashdata('reset_res')): ?>
            <?php if (session()->getFlashdata('reset_res') == 'true'): ?>
                <div class="alert-msg success">Password reset email sent successfully. Check email</div>
            <?php elseif (session()->getFlashdata('reset_res') == 'false'): ?>
                <div class="alert-msg danger">You entered the wrong email address</div>
            <?php endif; ?>
        <?php endif; ?>
        <div class="forgot-header">
            <h4><i class="fa-regular fa-fingerprint"></i> <?= translate('password_restoration'); ?></h4>
            <p><?= translate('Enter your email and you will receive reset instructions'); ?></p>
        </div>
        <?= form_open(current_url()); ?>
            <div class="form-group <?= isset($validation) && $validation->hasError('username') ? 'has-error' : '' ?>">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><h4>Email</h4></span>
                    </div>
                    <input type="text" class="form-control" name="username" value="<?= set_value('username') ?>" autocomplete="off" placeholder="<?= translate('Enter your email'); ?>" />
                </div>
                <?php if (isset($validation) && $validation->hasError('username')): ?>
                    <span class="error"><?= $validation->getError('username'); ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <button type="submit" id="btn_submit" class="btn btn-block">
                    <?= translate('Reset password'); ?>
                </button>
            </div>
            <div class="text">
                <a style="font-weight: 600; color: black;" href="<?= base_url("{$authenticationModel->getSegment(1)}authentication"); ?>"><i class="fa-sharp fa-regular fa-circle-arrow-left"></i> <?= translate('back_to_login'); ?></a>
            </div>
        <?= form_close(); ?>
        <div class="sign-footer">
            <p><?= $global_config['footer_text'] ?></p>
        </div>
    </div>

    <script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/sweetalert/sweetalert.min.js'); ?>"></script>
</body>
</html>
