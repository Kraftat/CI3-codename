<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= isset($global_config['institute_name']) ? $global_config['institute_name'] : 'Default Description' ?>">
    <meta name="author" content="<?= isset($global_config['institute_name']) ? $global_config['institute_name'] : 'Default Author' ?>">
    <title><?= translate('login'); ?></title>
    <link rel="shortcut icon" href="<?= base_url('assets/images/favicon.png'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/font-awesome/css/all.min.css'); ?>">
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
    <script>
        $(document).ready(function(){
            console.log("Document ready. Form submission should be handled.");
        });
    </script>
</head>
<body>
    <div class="auth-main">
        <div class="header">
            <img src="<?= $applicationModel->getBranchImage($branch_id, 'logo') ?>" height="60" alt="School">
            <h2><?= isset($global_config['institute_name']) ? $global_config['institute_name'] : 'Default Institute' ?></h2>
            <h3>Welcome Back!</h3>
        </div>

        <form method="post" action="<?= base_url('authentication') ?>">
            <?= csrf_field(); ?>
            <div class="form-group <?= isset($validation) && $validation->hasError('email') ? 'has-error' : '' ?>">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><h4>Username</h4></span>
                    </div>
                    <input type="text" class="form-control" name="email" value="<?= set_value('email'); ?>" placeholder="<?= translate('Enter your Username'); ?>" />
                </div>
                <?php if (isset($validation) && $validation->hasError('email')): ?>
                    <span class="error"><?= $validation->getError('email'); ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group <?= isset($validation) && $validation->hasError('password') ? 'has-error' : '' ?>">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><h4>Password</h4></i></span>
                    </div>
                    <input type="password" class="form-control" name="password" placeholder="<?= translate('Enter your password'); ?>" />
                </div>
                <?php if (isset($validation) && $validation->hasError('password')): ?>
                    <span class="error"><?= $validation->getError('password'); ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group d-flex justify-content-between align-items-center">
                <div class="checkbox">
                    <label><input type="checkbox" name="remember"> <?= translate('remember my password'); ?></label>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" id="btn_submit" class="btn btn-block">
                    <i class=""></i> <?= translate('login'); ?>
                </button>
            </div>
            <div class="error-messages">
                <?php if (session()->getFlashdata('alert-message-error')): ?>
                    <span class="error"><?= session()->getFlashdata('alert-message-error'); ?></span>
                <?php endif; ?>
            </div>
            <a style="font-weight: 600; color: black;" href="<?= base_url("{$authenticationModel->getSegment(1)}forgot"); ?>"><?= translate('Forgot your password?'); ?></a>
        </form>

        <div class="sign-footer">
            <p><?= isset($global_config['footer_text']) ? $global_config['footer_text'] : '' ?></p>
        </div>
    </div>

    <script src="<?= base_url('assets/vendor/sweetalert/sweetalert.min.js'); ?>"></script>

    <?php
    $alertclass = "";
    if (session()->getFlashdata('alert-message-success')) {
        $alertclass = "success";
    } else if (session()->getFlashdata('alert-message-error')) {
        $alertclass = "error";
    } else if (session()->getFlashdata('alert-message-info')) {
        $alertclass = "info";
    }
    if ($alertclass != ''):
        $alert_message = session()->getFlashdata('alert-message-' . $alertclass);
    ?>
    <script>
        swal("<?= ucfirst($alertclass) ?>", "<?= $alert_message ?>", "<?= $alertclass ?>");
    </script>
    <?php endif; ?>
</body>
</html>
