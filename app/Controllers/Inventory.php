<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models;
/**
 * @package : Ramom school management system
 * @version : 6.5
 * @developed by : RamomCoder
 * @support : ramomcoder@yahoo.com
 * @author url : http://codecanyon.net/user/RamomCoder
 * @filename : Inventory.php
 * @copyright : Reserved RamomCoder Team
 */
class Inventory extends AdminController

{
    public $appLib;

    protected $db;

    /**
     * @var App\Models\InventoryModel
     */
    public $inventory;

    public $validation;

    public $input;

    public $inventoryModel;

    public $applicationModel;

    public $load;

    public $session;

    public function __construct()
    {

        parent::__construct();

        $this->appLib = service('appLib'); 
$this->inventory = new \App\Models\InventoryModel();
        if (!moduleIsEnabled('inventory')) {
            access_denied();
        }
    }

    public function index()
    {
        $this->product();
    }

    /* product form validation rules */
    protected function product_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['product_name' => ["label" => translate('product') . " " . translate('name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['product_code' => ["label" => translate('product') . " " . translate('code'), "rules" => 'trim|required']]);
        $this->validation->setRules(['product_category' => ["label" => translate('product') . " " . translate('category'), "rules" => 'trim|required']]);
        $this->validation->setRules(['purchase_unit' => ["label" => translate('purchase_unit'), "rules" => 'trim|required|numeric']]);
        $this->validation->setRules(['sales_unit' => ["label" => translate('sales_unit'), "rules" => 'trim|required|numeric']]);
        $this->validation->setRules(['unit_ratio' => ["label" => translate('unit_ratio'), "rules" => 'trim|required|numeric']]);
        $this->validation->setRules(['purchase_price' => ["label" => translate('purchase_price'), "rules" => 'trim|required|numeric']]);
        $this->validation->setRules(['sales_price' => ["label" => translate('sales_price'), "rules" => 'trim|required|numeric']]);
    }

    // add new product
    public function product()
    {
        // check access permission
        if (!get_permission('product', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            if (!get_permission('product', 'is_add')) {
                ajax_access_denied();
            }

            $this->product_validation();
            if ($this->validation->run() == true) {
                // save product information in the database
                $post = $this->request->getPost();
                $this->inventoryModel->save_product($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['productlist'] = $this->inventoryModel->get_product_list();
        $this->data['unitlist'] = $this->appLib->getSelectByBranch('product_unit', $branchID);
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/product';
        $this->data['main_menu'] = 'inventory';
        echo view('layout/index', $this->data);
    }

    // update existing product
    public function product_edit($id)
    {
        // check access permission
        if (!get_permission('product', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->product_validation();
            if ($this->validation->run() == true) {
                $post = $this->request->getPost();
                $this->inventoryModel->save_product($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $array = ['status' => 'success', 'url' => base_url('inventory/product')];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['product'] = $this->appLib->getTable('product', ['t.id' => $id], true);
        $this->data['categorylist'] = $this->appLib->getSelectByBranch('product_category', $this->data['product']['branch_id']);
        $this->data['unitlist'] = $this->appLib->getSelectByBranch('product_unit', $this->data['product']['branch_id']);
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/product_edit';
        $this->data['main_menu'] = 'inventory';
        echo view('layout/index', $this->data);
    }

    // delete product from database
    public function product_delete($id)
    {
        // check access permission
        if (!get_permission('product', 'is_delete')) {
            access_denied();
        }

        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('id')->where();
        $this->db->table('product')->delete();
    }

    // add category from database
    public function category()
    {
        if (isset($_POST['category'])) {
            if (!get_permission('product_category', 'is_add')) {
                access_denied();
            }

            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'trim|required']]);
            }

            $this->validation->setRules(['category_name' => ["label" => 'Category Name', "rules" => 'trim|required|callback_unique_category']]);
            if ($this->validation->run() !== false) {
                $arrayCategory = ['name' => $this->request->getPost('category_name'), 'branch_id' => $this->applicationModel->get_branch_id()];
                $this->db->table('product_category')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                return redirect()->to(base_url('inventory/category'));
            }
        }

        $this->data['categorylist'] = $this->appLib->getTable('product_category');
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/category';
        $this->data['main_menu'] = 'inventory';
        echo view('layout/index', $this->data);
        return null;
    }

    public function category_edit()
    {
        // check access permission
        if (!get_permission('product_category', 'is_edit')) {
            access_denied();
        }

        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'trim|required']]);
        }

        $this->validation->setRules(['category_name' => ["label" => 'Category Name', "rules" => 'trim|required|callback_unique_category']]);
        if ($this->validation->run() !== false) {
            $arrayCategory = ['name' => $this->request->getPost('category_name'), 'branch_id' => $this->applicationModel->get_branch_id()];
            $categoryId = $this->request->getPost('category_id');
            $this->db->table('id')->where();
            $this->db->table('product_category')->update();
            set_alert('success', translate('information_has_been_updated_successfully'));
        }

        return redirect()->to(base_url('inventory/category'));
    }

    // delete category from database
    public function category_delete($id)
    {
        // check access permission
        if (!get_permission('product_category', 'is_delete')) {
            access_denied();
        }

        $this->db->table('id')->where();
        $this->db->table('product_category')->delete();
    }

    // duplicate category name check in db
    public function unique_category($name)
    {
        $this->applicationModel->get_branch_id();
        $categoryId = $this->request->getPost('category_id');
        if (!empty($categoryId)) {
            $this->db->where_not_in('id', $categoryId);
        }

        $this->db->table('name')->where();
        $this->db->table('branch_id')->where();
        $query = $builder->get('product_category');
        if ($query->num_rows() > 0) {
            if (!empty($categoryId)) {
                set_alert('error', "The Category name are already used");
            } else {
                $this->validation->setRule("unique_category", "The %s name are already used.");
            }

            return false;
        }

        return true;
    }

    // add new supplier member
    public function supplier()
    {
        // check access permission
        if (!get_permission('product_supplier', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            if (!get_permission('product_supplier', 'is_add')) {
                ajax_access_denied();
            }

            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['supplier_name' => ["label" => translate('supplier_name'), "rules" => 'trim|required']]);
            $this->validation->setRules(['contact_number' => ["label" => translate('contact_number'), "rules" => 'trim|required|numeric']]);
            if ($this->validation->run() == true) {
                $post = $this->request->getPost();
                $this->inventoryModel->save_supplier($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['supplierlist'] = $this->appLib->getTable('product_supplier');
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/supplier';
        $this->data['main_menu'] = 'inventory';
        echo view('layout/index', $this->data);
    }

    // update existing supplier member
    public function supplier_edit($id)
    {
        // check access permission
        if (!get_permission('product_supplier', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
            }

            $this->validation->setRules(['supplier_name' => ["label" => translate('supplier_name'), "rules" => 'trim|required']]);
            $this->validation->setRules(['contact_number' => ["label" => translate('contact_number'), "rules" => 'trim|required']]);
            if ($this->validation->run() == true) {
                $post = $this->request->getPost();
                $this->inventoryModel->save_supplier($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $array = ['status' => 'success', 'url' => base_url('inventory/supplier')];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['supplier'] = $this->appLib->getTable('product_supplier', ['t.id' => $id], true);
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/supplier_edit';
        $this->data['main_menu'] = 'inventory';
        echo view('layout/index', $this->data);
    }

    // delete existing supplier member
    public function supplier_delete($id)
    {
        // check access permission
        if (!get_permission('product_supplier', 'is_delete')) {
            access_denied();
        }

        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('id')->where();
        $this->db->table('product_supplier')->delete();
    }

    public function unit()
    {
        if (isset($_POST['unit'])) {
            if (!get_permission('product_unit', 'is_add')) {
                access_denied();
            }

            if (is_superadmin_loggedin()) {
                $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'trim|required']]);
            }

            $this->validation->setRules(['unit_name' => ["label" => 'Unit Name', "rules" => 'trim|required|callback_unique_unit']]);
            if ($this->validation->run() !== false) {
                $arrayUnit = ['name' => $this->request->getPost('unit_name'), 'branch_id' => $this->applicationModel->get_branch_id()];
                $this->db->table('product_unit')->insert();
                set_alert('success', translate('information_has_been_saved_successfully'));
                return redirect()->to(base_url('inventory/unit'));
            }
        }

        $this->data['unitlist'] = $this->inventoryModel->get('product_unit', '', false, true);
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/unit';
        $this->data['main_menu'] = 'inventory';
        echo view('layout/index', $this->data);
        return null;
    }

    public function unit_edit()
    {
        if (!get_permission('product_unit', 'is_edit')) {
            access_denied();
        }

        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'trim|required']]);
        }

        $this->validation->setRules(['unit_name' => ["label" => 'Unit Name', "rules" => 'trim|required|callback_unique_unit']]);
        if ($this->validation->run() !== false) {
            $unitId = $this->request->getPost('unit_id');
            $arrayUnit = ['name' => $this->request->getPost('unit_name'), 'branch_id' => $this->applicationModel->get_branch_id()];
            $this->db->table('id')->where();
            $this->db->table('product_unit')->update();
            set_alert('success', translate('information_has_been_updated_successfully'));
        }

        return redirect()->to(base_url('inventory/unit'));
    }

    public function unit_delete($id)
    {
        if (!get_permission('product_unit', 'is_delete')) {
            access_denied();
        }

        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('id')->where();
        $this->db->table('product_unit')->delete();
    }

    public function unitDetails()
    {
        if (get_permission('product_unit', 'is_edit')) {
            $id = $this->request->getPost('id');
            $this->db->table('id')->where();
            $query = $builder->get('product_unit');
            $result = $query->row_array();
            echo json_encode($result);
        }
    }

    public function unique_unit($name)
    {
        $branchID = $this->applicationModel->get_branch_id();
        $unitId = $this->request->getPost('unit_id');
        if (!empty($unitId)) {
            $this->db->where_not_in('id', $unitId);
        }

        $this->db->table(['name' => $name, 'branch_id' => $branchID])->where();
        $uniformRow = $builder->get('student_category')->num_rows();
        if ($uniformRow == 0) {
            return true;
        }

        $this->validation->setRule("unique_unit", translate('already_taken'));
        return false;
    }

    // add new product purchase bill
    public function purchase()
    {
        if (!get_permission('product_purchase', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['purchaselist'] = $this->inventoryModel->get_purchase_list();
        $this->data['productlist'] = $this->inventoryModel->getProductByBranch($branchID);
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/purchase';
        $this->data['main_menu'] = 'inventory';
        echo view('layout/index', $this->data);
    }

    public function purchaseItems()
    {
        $branchID = $this->applicationModel->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['productlist'] = $this->inventoryModel->getProductByBranch($branchID);
        echo view('inventory/purchaseItems', $this->data, true);
    }

    public function getPurchasePrice()
    {
        $this->request->getPost('id');
        $price = $db->table('product')->get('product')->row_array();
        $unit = $db->table('product_unit')->get('product_unit')->row();
        echo json_encode(['price' => $price['price'], 'unit' => $unit->name]);
    }

    /* purchase form validation rules */
    protected function purchase_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['supplier_id' => ["label" => 'Supplier', "rules" => 'trim|required']]);
        $this->validation->setRules(['store_id' => ["label" => 'Store', "rules" => 'trim|required']]);
        $this->validation->setRules(['bill_no' => ["label" => 'Bill No', "rules" => 'trim|required']]);
        $this->validation->setRules(['purchase_status' => ["label" => 'Purchase Status', "rules" => 'trim|required']]);
        $this->validation->setRules(['date' => ["label" => 'Date', "rules" => 'trim|required']]);

        $items = $this->request->getPost('purchases');
        if (!empty($items)) {
            foreach ($items as $key => $value) {
                $this->validation->setRules(['purchases[' . $key . '][product]' => ["label" => 'Product', "rules" => 'trim|required']]);
                $this->validation->setRules(['purchases[' . $key . '][quantity]' => ["label" => 'Quantity', "rules" => 'trim|required']]);
            }
        }
    }

    public function purchase_save()
    {
        if (!get_permission('product_purchase', 'is_add')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->purchase_validation();
            if ($this->validation->run() == false) {
                $msg = ['supplierID' => form_error('supplier_id'), 'storeID' => form_error('store_id'), 'bill_no' => form_error('bill_no'), 'purchase_status' => form_error('purchase_status'), 'date' => form_error('date'), 'delivery_time' => form_error('delivery_time'), 'payment_amount' => form_error('payment_amount')];
                if (is_superadmin_loggedin()) {
                    $msg['branch_id'] = form_error('branch_id');
                }

                $items = $this->request->getPost('purchases');
                if (!empty($items)) {
                    foreach ($items as $key => $value) {
                        $msg['product' . $key] = form_error('purchases[' . $key . '][product]');
                        $msg['quantity' . $key] = form_error('purchases[' . $key . '][quantity]');
                    }
                }

                $array = ['status' => 'fail', 'url' => '', 'error' => $msg];
            } else {
                $data = $this->request->getPost();
                $this->inventoryModel->save_purchase($data);
                $url = base_url('inventory/purchase');
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            }

            echo json_encode($array);
        }
    }

    public function purchaseMakeReceived($id = '')
    {
        if (!get_permission('product_purchase', 'is_eit')) {
            access_denied();
        }

        if (!empty($id)) {
            $r = $db->table('purchase_bill')->get('purchase_bill')->row()->cid;
            if ($r > 0) {
                $billDetails = $db->table('purchase_bill_details')->get('purchase_bill_details')->getResult();
                foreach ($billDetails as $value) {
                    $unitRatio = $db->table('product')->get('product')->row()->unit_ratio;
                    $sql = "UPDATE `product` SET `available_stock` = `available_stock` + " . $value->quantity * $unitRatio . " WHERE `id` = " . $db->escape($value->product_id);
                    $db->query($sql);
                }

                $this->db->table('id')->where();
                $this->db->table('purchase_bill')->update();
            }
        }
    }

    public function purchase_edit_save()
    {
        if (!get_permission('product_purchase', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            // validate inputs
            $this->validation->setRules(['supplier_id' => ["label" => 'Supplier', "rules" => 'trim|required']]);
            $this->validation->setRules(['store_id' => ["label" => 'Store', "rules" => 'trim|required']]);
            $this->validation->setRules(['bill_no' => ["label" => 'Bill No', "rules" => 'trim|required']]);
            $this->validation->setRules(['purchase_status' => ["label" => 'Purchase Status', "rules" => 'trim|required']]);
            $this->validation->setRules(['date' => ["label" => 'Date', "rules" => 'trim|required']]);
            $items = $this->request->getPost('purchases');
            foreach ($items as $key => $value) {
                $this->validation->setRules(['purchases[' . $key . '][product]' => ["label" => 'Product', "rules" => 'trim|required']]);
                $this->validation->setRules(['purchases[' . $key . '][quantity]' => ["label" => 'Quantity', "rules" => 'trim|required']]);
            }

            if ($this->validation->run() == false) {
                $msg = ['supplierID' => form_error('supplier_id'), 'storeID' => form_error('store_id'), 'bill_no' => form_error('bill_no'), 'purchase_status' => form_error('purchase_status'), 'date' => form_error('date'), 'delivery_time' => form_error('delivery_time'), 'payment_amount' => form_error('payment_amount')];
                foreach ($items as $key => $value) {
                    $msg['product' . $key] = form_error('purchases[' . $key . '][product]');
                    $msg['quantity' . $key] = form_error('purchases[' . $key . '][quantity]');
                }

                $array = ['status' => 'fail', 'url' => '', 'error' => $msg];
            } else {
                $purchaseBillId = $this->request->getPost('purchase_bill_id');
                $supplierId = $this->request->getPost('supplier_id');
                $storeId = $this->request->getPost('store_id');
                $billNo = $this->request->getPost('bill_no');
                $purchaseStatus = $this->request->getPost('purchase_status');
                $grandTotal = $this->request->getPost('grand_total');
                $discount = $this->request->getPost('total_discount');
                $purchasePaid = $this->request->getPost('purchase_paid');
                $netTotal = $this->request->getPost('net_grand_total');
                $date = $this->request->getPost('date');
                $remarks = $this->request->getPost('remarks');
                $paymentStatus = $netTotal <= $purchasePaid ? 3 : 2;
                $arrayInvoice = ['supplier_id' => $supplierId, 'store_id' => $storeId, 'bill_no' => $billNo, 'remarks' => $remarks, 'total' => $grandTotal, 'discount' => $discount, 'due' => $netTotal - $purchasePaid, 'purchase_status' => $purchaseStatus, 'payment_status' => $paymentStatus, 'date' => date('Y-m-d', strtotime((string) $date)), 'modifier_id' => get_loggedin_user_id()];
                $this->db->table('id')->where();
                $this->db->table('purchase_bill')->update();
                $purchases = $this->request->getPost('purchases');
                foreach ($purchases as $value) {
                    $arrayProduct = ['purchase_bill_id' => $purchaseBillId, 'product_id' => $value['product'], 'unit_price' => $value['unit_price'], 'discount' => $value['discount'], 'quantity' => $value['quantity'], 'sub_total' => $value['sub_total']];
                    if (isset($value['old_product_id'])) {
                        if ($value['old_product_id'] == $value['product']) {
                            $unitRatio = $db->table('product')->get('product')->row()->unit_ratio;
                            if (isset($value['old_quantity'])) {
                                if ($value['quantity'] >= $value['old_quantity']) {
                                    $stock = floatval($value['quantity'] * $unitRatio - $value['old_quantity'] * $unitRatio);
                                    $this->inventoryModel->stock_upgrade($stock, $value['product']);
                                } else {
                                    $stock = floatval($value['old_quantity'] * $unitRatio - $value['quantity'] * $unitRatio);
                                    $this->inventoryModel->stock_upgrade($stock, $value['product'], false);
                                }
                            }
                        } else {
                            $unitRatio = $db->table('product')->get('product')->row()->unit_ratio;
                            $newunitRatio = $db->table('product')->get('product')->row()->unit_ratio;
                            $this->inventoryModel->stock_upgrade($value['old_quantity'] * $unitRatio, $value['old_product_id'], false);
                            $this->inventoryModel->stock_upgrade($value['quantity'] * $newunitRatio, $value['product']);
                        }
                    }

                    if (isset($value['old_bill_details_id'])) {
                        $this->db->table('id')->where();
                        $this->db->table('purchase_bill_details')->update();
                    } else {
                        $unitRatio = $db->table('product')->get('product')->row()->unit_ratio;
                        $this->inventoryModel->stock_upgrade($value['quantity'] * $unitRatio, $value['product']);
                        $this->db->table('purchase_bill_details')->insert();
                    }
                }

                $url = base_url('inventory/purchase');
                set_alert('success', translate('information_has_been_updated_successfully'));
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            }

            echo json_encode($array);
        }
    }

    // update existing product purchase bill
    public function purchase_edit($id)
    {
        if (!get_permission('product_purchase', 'is_edit')) {
            access_denied();
        }

        $this->data['purchaselist'] = $this->appLib->getTable('purchase_bill', ['t.id' => $id], true);
        $branchID = $this->data['purchaselist']['branch_id'];
        $this->data['branch_id'] = $branchID;
        $this->data['productlist'] = $this->inventoryModel->getProductByBranch($branchID);
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/purchase_edit';
        $this->data['main_menu'] = 'inventory';
        echo view('layout/index', $this->data);
    }

    // delete product purchase bill from database
    public function purchase_delete($id)
    {
        if (!get_permission('product_purchase', 'is_delete')) {
            access_denied();
        }

        $getStock = $builder->getWhere('purchase_bill_details', ['purchase_bill_id' => $id])->result();
        foreach ($getStock as $value) {
            $unitRatio = $db->table('product')->get('product')->row()->unit_ratio;
            $this->inventoryModel->stock_upgrade($value->quantity * $unitRatio, $value->product_id, false);
        }

        $this->db->table('id')->where();
        $this->db->table('purchase_bill')->delete();
        $this->db->table('purchase_bill_id')->where();
        $this->db->table('purchase_bill_details')->delete();
        //delete purchase payment history from database
        $this->db->table('purchase_bill_id')->where();
        $this->db->table('purchase_payment_history')->delete();
    }

    public function purchase_bill($id = '')
    {
        if (!get_permission('purchase_payment', 'is_add')) {
            access_denied();
        }

        $this->data['billdata'] = $this->inventoryModel->get_invoice($id);
        if (empty($this->data['billdata'])) {
            access_denied();
        }

        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        $this->data['payvia_list'] = $this->appLib->getSelectList('payment_types');
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/purchase_bill';
        $this->data['main_menu'] = 'inventory';
        echo view('layout/index', $this->data);
    }

    // purchase partially payment add
    public function add_payment()
    {
        if (!get_permission('purchase_payment', 'is_add')) {
            access_denied();
        }

        if ($this->request->getPost()) {
            $data = $this->request->getPost();
            $data['getbill'] = $db->table('purchase_bill')->get('purchase_bill')->row_array();
            $this->validation->setRules(['paid_date' => ["label" => 'Paid Date', "rules" => 'trim|required']]);
            $this->validation->setRules(['payment_amount' => ["label" => 'Payment Amount', "rules" => 'trim|required|numeric|greater_than[1]|callback_payment_validation']]);
            $this->validation->setRules(['pay_via' => ["label" => 'Pay Via', "rules" => 'trim|required']]);
            $this->validation->setRules(['attach_document' => ["label" => translate('attach_document'), "rules" => 'callback_fileHandleUpload[attach_document]']]);
            if ($this->validation->run() !== false) {
                $this->inventoryModel->save_payment($data);
                set_alert('success', translate('payment_successfull'));
                if (get_permission('purchase_payment', 'is_view')) {
                    session()->set_flashdata('active_tab', 2);
                }

                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    // payment amount validation
    public function payment_validation($amount)
    {
        $this->request->getPost('purchase_bill_id');
        $dueAmount = $db->table('purchase_bill')->get('purchase_bill')->row()->due;
        if ($amount <= $dueAmount) {
            return true;
        }

        $this->validation->setRule("payment_validation", "Payment Amount Is More Than The Due Amount.");
        return false;
    }

    /* store form validation rules */
    protected function store_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['store_name' => ["label" => translate('name'), "rules" => 'trim|required']]);
        $this->validation->setRules(['store_code' => ["label" => translate('store_code'), "rules" => 'trim|required']]);
        $this->validation->setRules(['mobileno' => ["label" => translate('mobile_no'), "rules" => 'trim|required|numeric']]);
    }

    /* add new store member */
    public function store()
    {
        // check access permission
        if (!get_permission('product_store', 'is_view')) {
            access_denied();
        }

        if ($_POST !== []) {
            if (!get_permission('product_store', 'is_add')) {
                ajax_access_denied();
            }

            $this->store_validation();
            if ($this->validation->run() == true) {
                $post = $this->request->getPost();
                $this->inventoryModel->save_store($post);
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['storelist'] = $this->appLib->getTable('product_store');
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/store';
        $this->data['main_menu'] = 'inventory';
        echo view('layout/index', $this->data);
    }

    // update existing store member
    public function store_edit($id)
    {
        // check access permission
        if (!get_permission('product_store', 'is_edit')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->store_validation();
            if ($this->validation->run() == true) {
                $post = $this->request->getPost();
                $this->inventoryModel->save_store($post);
                set_alert('success', translate('information_has_been_updated_successfully'));
                $array = ['status' => 'success', 'url' => base_url('inventory/store')];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
            exit;
        }

        $this->data['store'] = $this->appLib->getTable('product_store', ['t.id' => $id], true);
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/store_edit';
        $this->data['main_menu'] = 'inventory';
        echo view('layout/index', $this->data);
    }

    // delete existing store
    public function store_delete($id)
    {
        // check access permission
        if (!get_permission('product_store', 'is_delete')) {
            access_denied();
        }

        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id')->where();
        }

        $this->db->table('id')->where();
        $this->db->table('product_store')->delete();
    }

    /* sales form validation rules */
    protected function sales_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['role_id' => ["label" => translate('role'), "rules" => 'trim|required']]);
        $this->validation->setRules(['sale_to' => ["label" => translate('sale_to'), "rules" => 'trim|required']]);
        $this->validation->setRules(['date' => ["label" => translate('date'), "rules" => 'trim|required']]);
        $this->validation->setRules(['bill_no' => ["label" => translate('bill_no'), "rules" => 'trim|required|numeric']]);
        $this->validation->setRules(['payment_amount' => ["label" => translate('payment_amount'), "rules" => 'trim|numeric|callback_sales_amount']]);

        $paymentAmount = $this->request->getPost('payment_amount');
        if (!empty($paymentAmount)) {
            $this->validation->setRules(['pay_via' => ["label" => translate('pay_via'), "rules" => 'trim|required']]);
        }

        $items = $this->request->getPost('sales');
        if (!empty($items)) {
            foreach ($items as $key => $value) {
                $this->validation->setRules(['sales[' . $key . '][category]' => ["label" => translate('category'), "rules" => 'trim|required']]);
                $this->validation->setRules(['sales[' . $key . '][product]' => ["label" => translate('product'), "rules" => 'trim|required']]);
                $this->validation->setRules(['sales[' . $key . '][quantity]' => ["label" => translate('quantity'), "rules" => 'trim|required']]);
            }
        }
    }

    public function sales()
    {
        if (!get_permission('product_sales', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['saleslist'] = $this->inventoryModel->getSalesList();
        $this->data['categorylist'] = $this->appLib->getSelectByBranch('product_category', $branchID);
        $this->data['payvia_list'] = $this->appLib->getSelectList('payment_types');
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/sales';
        $this->data['main_menu'] = 'inventory';
        echo view('layout/index', $this->data);
    }

    public function sales_save()
    {
        if (!get_permission('product_sales', 'is_add')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->sales_validation();
            if ($this->validation->run() == false) {
                $msg = ['bill_no' => form_error('bill_no'), 'payment_amount' => form_error('payment_amount'), 'pay_via' => form_error('pay_via'), 'roleID' => form_error('role_id'), 'receiverID' => form_error('sale_to'), 'date' => form_error('date')];
                if (is_superadmin_loggedin()) {
                    $msg['branchID'] = form_error('branch_id');
                }

                $items = $this->request->getPost('sales');
                if (!empty($items)) {
                    foreach ($items as $key => $value) {
                        $msg['category' . $key] = form_error('sales[' . $key . '][category]');
                        $msg['product' . $key] = form_error('sales[' . $key . '][product]');
                        $msg['quantity' . $key] = form_error('sales[' . $key . '][quantity]');
                    }
                }

                $array = ['status' => 'fail', 'url' => '', 'error' => $msg];
            } else {
                $data = $this->request->getPost();
                $this->inventoryModel->save_sales($data);
                $url = base_url('inventory/sales');
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            }

            echo json_encode($array);
        }
    }

    public function getSaleprice()
    {
        $this->request->getPost('id');
        $price = $db->table('product')->get('product')->row_array();
        $unit = $db->table('product_unit')->get('product_unit')->row();
        echo json_encode(['price' => $price['salesprice'], 'unit' => $unit->name, 'availablestock' => translate('available_stock_quantity') . " : " . $price['available_stock']]);
    }

    public function saleItems()
    {
        $branchID = $this->applicationModel->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['categorylist'] = $this->appLib->getSelectByBranch('product_category', $branchID);
        echo view('inventory/saleItems', $this->data, true);
    }

    public function getProductByCategory()
    {
        $this->request->getPost('category_id');
        $selectedId = $this->request->getPost('selected_id');
        $this->applicationModel->get_branch_id();
        $productlist = $db->table('product')->get('product')->result_array();
        $html = "<option value=''>" . translate('select') . "</option>";
        foreach ($productlist as $product) {
            $selected = $product['id'] == $selectedId ? 'selected' : '';
            $html .= "<option value='" . $product['id'] . "' " . $selected . ">" . $product['name'] . " (" . $product['code'] . ")</option>";
        }

        echo $html;
    }

    // check valid received amount
    public function sales_amount($amount)
    {
        if (!empty($amount)) {
            $netPayable = $this->request->getPost('net_payable_amount');
            if ($netPayable < $amount) {
                $this->validation->setRule('sales_amount', "Invalid Received Amount.");
                return false;
            }
        }

        return true;
    }

    public function sales_invoice($id = '')
    {
        if (!get_permission('product_sales', 'is_view')) {
            access_denied();
        }

        $this->data['billdata'] = $this->inventoryModel->getSalesInvoice($id);
        if (empty($this->data['billdata'])) {
            access_denied();
        }

        $this->data['headerelements'] = ['css' => ['vendor/dropify/css/dropify.min.css'], 'js' => ['vendor/dropify/js/dropify.min.js']];
        $this->data['payvia_list'] = $this->appLib->getSelectList('payment_types');
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/sales_invoice';
        $this->data['main_menu'] = 'inventory';
        echo view('layout/index', $this->data);
    }

    // sales partially payment add
    public function add_sales_payment()
    {
        if (!get_permission('sales_payment', 'is_add')) {
            access_denied();
        }

        if ($this->request->getPost()) {
            $data = $this->request->getPost();
            $data['getbill'] = $db->table('sales_bill')->get('sales_bill')->row_array();
            $this->validation->setRules(['paid_date' => ["label" => 'Paid Date', "rules" => 'trim|required']]);
            $this->validation->setRules(['payment_amount' => ["label" => 'Payment Amount', "rules" => 'trim|required|numeric|greater_than[1]|callback_sales_amount_validation']]);
            $this->validation->setRules(['pay_via' => ["label" => 'Pay Via', "rules" => 'trim|required']]);
            $this->validation->setRules(['attach_document' => ["label" => translate('attach_document'), "rules" => 'callback_fileHandleUpload[attach_document]']]);
            if ($this->validation->run() !== false) {
                $this->inventoryModel->save_sales_payment($data);
                set_alert('success', translate('payment_successfull'));
                if (get_permission('purchase_payment', 'is_view')) {
                    session()->set_flashdata('active_tab', 2);
                }

                $array = ['status' => 'success'];
            } else {
                $error = $this->validation->error_array();
                $array = ['status' => 'fail', 'error' => $error];
            }

            echo json_encode($array);
        }
    }

    // payment amount validation
    public function sales_amount_validation($amount)
    {
        $this->request->getPost('sales_bill_id');
        $dueAmount = $db->table('sales_bill')->get('sales_bill')->row()->due;
        if ($amount <= $dueAmount) {
            return true;
        }

        $this->validation->setRule("sales_amount_validation", "Payment Amount Is More Than The Due Amount.");
        return false;
    }

    // delete product sales bill from database
    public function sales_delete($id)
    {
        if (!get_permission('product_sales', 'is_delete')) {
            access_denied();
        }

        $getStock = $builder->getWhere('sales_bill_details', ['sales_bill_id' => $id])->result();
        foreach ($getStock as $value) {
            $this->inventoryModel->stock_upgrade($value->quantity, $value->product_id);
        }

        $this->db->table('id')->where();
        $this->db->table('sales_bill')->delete();
        $this->db->table('sales_bill_id')->where();
        $this->db->table('sales_bill_details')->delete();
        $this->db->table('sales_bill_id')->where();
        $this->db->table('sales_bill_details')->delete();
    }

    /* issue form validation rules */
    protected function issue_validation()
    {
        if (is_superadmin_loggedin()) {
            $this->validation->setRules(['branch_id' => ["label" => translate('branch'), "rules" => 'required']]);
        }

        $this->validation->setRules(['role_id' => ["label" => translate('role'), "rules" => 'trim|required']]);
        $this->validation->setRules(['sale_to' => ["label" => translate('sale_to'), "rules" => 'trim|required']]);
        $this->validation->setRules(['date_of_issue' => ["label" => translate('date_of_issue'), "rules" => 'trim|required']]);
        $this->validation->setRules(['due_date' => ["label" => translate('due_date'), "rules" => 'trim|required']]);

        $items = $this->request->getPost('sales');
        if (!empty($items)) {
            foreach ($items as $key => $value) {
                $this->validation->setRules(['sales[' . $key . '][category]' => ["label" => translate('category'), "rules" => 'trim|required']]);
                $this->validation->setRules(['sales[' . $key . '][product]' => ["label" => translate('product'), "rules" => 'trim|required']]);
                $this->validation->setRules(['sales[' . $key . '][quantity]' => ["label" => translate('quantity'), "rules" => 'trim|required']]);
            }
        }
    }

    public function issue()
    {
        if (!get_permission('product_issue', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['saleslist'] = $this->inventoryModel->getIssueList();
        $this->data['categorylist'] = $this->appLib->getSelectByBranch('product_category', $branchID);
        $this->data['title'] = translate('inventory');
        $this->data['sub_page'] = 'inventory/issue';
        $this->data['main_menu'] = 'inventory';
        echo view('layout/index', $this->data);
    }

    public function issue_save()
    {
        if (!get_permission('product_issue', 'is_add')) {
            access_denied();
        }

        if ($_POST !== []) {
            $this->issue_validation();
            if ($this->validation->run() == false) {
                $msg = ['date_of_issue' => form_error('date_of_issue'), 'due_date' => form_error('due_date'), 'roleID' => form_error('role_id'), 'receiverID' => form_error('sale_to')];
                if (is_superadmin_loggedin()) {
                    $msg['branchID'] = form_error('branch_id');
                }

                $items = $this->request->getPost('sales');
                if (!empty($items)) {
                    foreach ($items as $key => $value) {
                        $msg['category' . $key] = form_error('sales[' . $key . '][category]');
                        $msg['product' . $key] = form_error('sales[' . $key . '][product]');
                        $msg['quantity' . $key] = form_error('sales[' . $key . '][quantity]');
                    }
                }

                $array = ['status' => 'fail', 'url' => '', 'error' => $msg];
            } else {
                $data = $this->request->getPost();
                $this->inventoryModel->save_issue($data);
                $url = base_url('inventory/issue');
                set_alert('success', translate('information_has_been_saved_successfully'));
                $array = ['status' => 'success', 'url' => $url, 'error' => ''];
            }

            echo json_encode($array);
        }
    }

    public function issueItems()
    {
        $branchID = $this->applicationModel->get_branch_id();
        $this->data['branch_id'] = $branchID;
        $this->data['categorylist'] = $this->appLib->getSelectByBranch('product_category', $branchID);
        echo view('inventory/issueItems', $this->data, true);
    }

    // delete product issue from database
    public function issue_delete($id)
    {
        if (!get_permission('product_issue', 'is_delete')) {
            access_denied();
        }

        $getStock = $builder->getWhere('product_issues_details', ['issues_id' => $id])->result();
        foreach ($getStock as $value) {
            $this->inventoryModel->stock_upgrade($value->quantity, $value->product_id);
        }

        $this->db->table('id')->where();
        $this->db->table('product_issues')->delete();
        $this->db->table('issues_id')->where();
        $this->db->table('product_issues_details')->delete();
    }

    public function returnProduct()
    {
        if ($_POST !== []) {
            if (!get_permission('product_issue', 'is_add')) {
                ajax_access_denied();
            }

            $id = $this->request->getPost('issue_id');
            $getStock = $builder->getWhere('product_issues_details', ['issues_id' => $id])->result();
            foreach ($getStock as $value) {
                $this->inventoryModel->stock_upgrade($value->quantity, $value->product_id);
            }

            $this->db->table('id')->where();
            $this->db->table('product_issues')->update();
            set_alert('success', translate('information_has_been_saved_successfully'));
            $array = ['status' => 'success'];
            echo json_encode($array);
        }
    }

    public function getIssueDetails()
    {
        if (get_permission('product_issue', 'is_view')) {
            $this->data['salary_id'] = $this->request->getPost('id');
            echo view('inventory/issue_modalView', $this->data);
        }
    }

    // inventory reports
    public function stockreport()
    {
        if (!get_permission('inventory_report', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            $categoryId = $this->request->getPost('category_id');
            $this->data['results'] = $this->inventoryModel->get_stock_product_wisereport($branchID, $categoryId);
        }

        $this->data['title'] = translate('inventory');
        $this->data['categorylist'] = $this->appLib->getSelectByBranch('product_category', $branchID, true);
        $this->data['sub_page'] = 'inventory/stockreport';
        $this->data['main_menu'] = 'inventory_report';
        echo view('layout/index', $this->data);
    }

    public function purchase_report()
    {
        if (!get_permission('inventory_report', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            $supplierId = $this->request->getPost('supplier_id');
            $paymentStatus = $this->request->getPost('payment_status');
            $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['daterange'] = $daterange;
            $this->data['results'] = $this->inventoryModel->get_purchase_report($branchID, $supplierId, $paymentStatus, $start, $end);
        }

        $this->data['headerelements'] = ['css' => ['vendor/daterangepicker/daterangepicker.css'], 'js' => ['vendor/moment/moment.js', 'vendor/daterangepicker/daterangepicker.js']];
        $this->data['title'] = translate('inventory');
        $this->data['supplierlist'] = $this->appLib->getSelectByBranch('product_supplier', $branchID, true);
        $this->data['sub_page'] = 'inventory/purchase_report';
        $this->data['main_menu'] = 'inventory_report';
        echo view('layout/index', $this->data);
    }

    public function sales_report()
    {
        if (!get_permission('inventory_report', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            $supplierId = $this->request->getPost('supplier_id');
            $paymentStatus = $this->request->getPost('payment_status');
            $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['daterange'] = $daterange;
            $this->data['results'] = $this->inventoryModel->get_sales_report($branchID, $paymentStatus, $start, $end);
        }

        $this->data['title'] = translate('inventory');
        $this->data['supplierlist'] = $this->appLib->getSelectByBranch('product_supplier', $branchID, true);
        $this->data['sub_page'] = 'inventory/sales_report';
        $this->data['main_menu'] = 'inventory_report';
        $this->data['headerelements'] = ['css' => ['vendor/daterangepicker/daterangepicker.css'], 'js' => ['vendor/moment/moment.js', 'vendor/daterangepicker/daterangepicker.js']];
        echo view('layout/index', $this->data);
    }

    public function issues_report()
    {
        if (!get_permission('inventory_report', 'is_view')) {
            access_denied();
        }

        $branchID = $this->applicationModel->get_branch_id();
        if (isset($_POST['search'])) {
            $supplierId = $this->request->getPost('supplier_id');
            $paymentStatus = $this->request->getPost('payment_status');
            $daterange = explode(' - ', (string) $this->request->getPost('daterange'));
            $start = date("Y-m-d", strtotime($daterange[0]));
            $end = date("Y-m-d", strtotime($daterange[1]));
            $this->data['daterange'] = $daterange;
            $this->data['results'] = $this->inventoryModel->getIssuesreport($branchID, $start, $end);
        }

        $this->data['title'] = translate('inventory');
        $this->data['supplierlist'] = $this->appLib->getSelectByBranch('product_supplier', $branchID, true);
        $this->data['sub_page'] = 'inventory/issues_report';
        $this->data['main_menu'] = 'inventory_report';
        $this->data['headerelements'] = ['css' => ['vendor/daterangepicker/daterangepicker.css'], 'js' => ['vendor/moment/moment.js', 'vendor/daterangepicker/daterangepicker.js']];
        echo view('layout/index', $this->data);
    }

    public function getDataByBranch()
    {
        $html = "";
        $table = $this->request->getPost('table');
        $branchId = $this->applicationModel->get_branch_id();
        if (!empty($branchId)) {
            $result = $db->table($table)->get($table)->result_array();
            if (count($result) > 0) {
                $html .= "<option value=''>" . translate('select') . "</option>";
                $html .= "<option value='all'>" . translate('all_select') . "</option>";
                foreach ($result as $row) {
                    $html .= '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                }
            } else {
                $html .= '<option value="">' . translate('no_information_available') . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('select_branch_first') . '</option>';
        }

        echo $html;
    }

    public function getProductUnitDetails()
    {
        if (get_permission('product_unit', 'is_edit')) {
            $id = $this->request->getPost('id');
            $this->db->table('id')->where();
            $query = $builder->get('product_unit');
            $result = $query->row_array();
            echo json_encode($result);
        }
    }
}
