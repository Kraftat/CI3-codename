<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\SaasModel;
use CodeIgniter\Controller;

class UserController extends BaseController
{
    public $saasModel;

    public function __construct()
    {
        helper('url'); // Load URL helper to use `base_url()`

        if (!is_student_loggedin() && !is_parent_loggedin()) {
            session()->set('redirect_url', current_url());
            return redirect()->to(base_url('authentication'));
        }

        $this->saasModel = new SaasModel();
        if (!$this->saasModel->checkSubscriptionValidity()) {
            return redirect()->to(base_url('dashboard'));
        }
    }

    // Other controller methods go here
}
