<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\HomeModel;
use App\Models\SaasModel;

class FrontendController extends BaseController
{
    /**
     * @var \App\Models\HomeModel
     */
    public $HomeModel;

    /**
     * @var \App\Models\SaasModel
     */
    public $SaasModel;

    protected $homeModel;

    protected $saasModel;

    public function __construct()
    {
        $this->HomeModel = new \App\Models\HomeModel();
        $this->SaasModel = new \App\Models\SaasModel();
        $db = \Config\Database::connect(); // Connect to the database

        $branchID = $this->HomeModel->getDefaultBranch();
        $builder = $db->table('front_cms_setting'); // Initialize the query builder for the table
        $cmsSetting = $builder->getWhere(['branch_id' => $branchID])->getRowArray();

        if ($cmsSetting === null || !$cmsSetting['cms_active']) {
            return redirect()->to(site_url('authentication'))->send();
        }

        if (!$this->SaasModel->checkSubscriptionValidity($branchID)) {
            session()->setFlashdata('website_expired_msg', '1');
            return redirect()->to(base_url())->send();
        }

        $this->data['cms_setting'] = $cmsSetting;
    }
}
