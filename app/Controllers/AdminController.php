<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\SaasModel;
use CodeIgniter\Controller;

class AdminController extends BaseController
{
    public $saasModel;

    public function __construct()
    {
        helper('url'); // Load URL helper to use `base_url()`

        $this->saasModel = new SaasModel();
        if (!is_loggedin()) {
            session()->set('redirect_url', current_url());
            return redirect()->to(base_url('authentication'));
        }

        if (!$this->saasModel->checkSubscriptionValidity()) {
            return redirect()->to(base_url('dashboard'));
        }
    }

    // Other controller methods go here
}
