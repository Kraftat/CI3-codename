<?php

namespace App\Models;

use CodeIgniter\Model;
class InventoryModel extends MYModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        parent::__construct();
    }
    public function save_product($data)
    {
        $insert_product = array('name' => $data['product_name'], 'code' => $data['product_code'], 'category_id' => $data['product_category'], 'purchase_unit_id' => $data['purchase_unit'], 'sales_unit_id' => $data['sales_unit'], 'unit_ratio' => $data['unit_ratio'], 'purchase_price' => $data['purchase_price'], 'sales_price' => $data['sales_price'], 'remarks' => $data['remarks'], 'branch_id' => $this->applicationModel->get_branch_id());
        if (isset($data['product_id']) && !empty($data['product_id'])) {
            $builder->where('id', $data['product_id']);
            $builder->update('product', $insert_product);
        } else {
            $builder->insert('product', $insert_product);
        }
    }
    public function save_supplier($data)
    {
        $insertSupplier = array('name' => $data['supplier_name'], 'email' => $data['email_address'], 'mobileno' => $data['contact_number'], 'company_name' => $data['company_name'], 'product_list' => $data['product_list'], 'address' => $data['address'], 'branch_id' => $this->applicationModel->get_branch_id());
        if (isset($data['supplier_id']) && !empty($data['supplier_id'])) {
            $builder->where('id', $data['supplier_id']);
            $builder->update('product_supplier', $insertSupplier);
        } else {
            $builder->insert('product_supplier', $insertSupplier);
        }
    }
    public function get_product_list()
    {
        $builder->select('product.*,product_category.name as category_name,p_unit.name as p_unit_name,s_unit.name as s_unit_name');
        $builder->from('product');
        $builder->join('product_category', 'product_category.id = product.category_id', 'left');
        $builder->join('product_unit as p_unit', 'p_unit.id = product.purchase_unit_id', 'left');
        $builder->join('product_unit as s_unit', 's_unit.id = product.sales_unit_id', 'left');
        if (!is_superadmin_loggedin()) {
            $this->db->table('product.branch_id', get_loggedin_branch_id())->where();
        }
        $builder->order_by('product.id', 'ASC');
        return $builder->get()->result_array();
    }
    public function get_purchase_list()
    {
        $sql = "SELECT purchase_bill.*,product_supplier.name as supplier_name,staff.name as biller_name FROM purchase_bill LEFT JOIN product_supplier ON product_supplier.id = purchase_bill.supplier_id LEFT JOIN staff ON staff.id = purchase_bill.prepared_by";
        if (!is_superadmin_loggedin()) {
            $sql .= " WHERE product_supplier.branch_id = " . $db->escape(get_loggedin_branch_id());
        }
        $sql .= " ORDER BY purchase_bill.id ASC";
        $query = $db->query($sql);
        return $query->getResultArray();
    }
    public function get_invoice($id)
    {
        $builder->select('purchase_bill.*,product_supplier.name as supplier_name,product_supplier.address as supplier_address,product_supplier.company_name as supplier_company_name,product_supplier.mobileno as supplier_mobileno,staff.name as biller_name');
        $builder->from('purchase_bill');
        $builder->join('product_supplier', 'product_supplier.id = purchase_bill.supplier_id', 'left');
        $builder->join('staff', 'staff.id = purchase_bill.prepared_by', 'left');
        $builder->where('purchase_bill.id', $id);
        if (!is_superadmin_loggedin()) {
            $this->db->table('purchase_bill.branch_id', get_loggedin_branch_id())->where();
        }
        return $builder->get()->row_array();
    }
    public function save_purchase($data)
    {
        $arrayInvoice = array('supplier_id' => $data['supplier_id'], 'bill_no' => $data['bill_no'], 'store_id' => $data['store_id'], 'remarks' => $data['remarks'], 'total' => $data['grand_total'], 'discount' => $data['total_discount'], 'due' => $data['net_grand_total'], 'paid' => 0, 'payment_status' => 1, 'purchase_status' => $data['purchase_status'], 'date' => date('Y-m-d', strtotime($data['date'])), 'prepared_by' => get_loggedin_user_id(), 'modifier_id' => get_loggedin_user_id(), 'branch_id' => $this->applicationModel->get_branch_id());
        $builder->insert('purchase_bill', $arrayInvoice);
        $purchase_bill_id = $builder->insert_id();
        $arrayData = array();
        $purchases = $data['purchases'];
        foreach ($purchases as $key => $value) {
            $arrayproduct = array('purchase_bill_id' => $purchase_bill_id, 'product_id' => $value['product'], 'unit_price' => $value['unit_price'], 'discount' => $value['discount'], 'quantity' => $value['quantity'], 'sub_total' => $value['sub_total']);
            $arrayData[] = $arrayproduct;
            //update product available stock
            if ($data['purchase_status'] == 2) {
                $unit_ratio = $db->table('product')->get('product')->row()->unit_ratio;
                $stockQuantity = $value['quantity'] * $unit_ratio;
                $this->stock_upgrade($stockQuantity, $value['product']);
            }
        }
        $builder->insert_batch('purchase_bill_details', $arrayData);
    }
    // add partly of the purchase payment
    public function save_payment($data)
    {
        $payment_status = 1;
        $attach_orig_name = "";
        $attach_file_name = "";
        $purchase_bill_id = $data['purchase_bill_id'];
        $payment_amount = $data['payment_amount'];
        $paid_date = $data['paid_date'];
        // uploading file using codeigniter upload library
        if (isset($_FILES['attach_document']['name']) && !empty($_FILES['attach_document']['name'])) {
            $config['upload_path'] = './uploads/attachments/inventory_payment/';
            $config['allowed_types'] = '*';
            $config['encrypt_name'] = true;
            $this->upload->initialize($config);
            if ($this->upload->do_upload("attach_document")) {
                $attach_orig_name = $this->upload->data('orig_name');
                $attach_file_name = $this->upload->data('file_name');
            }
        }
        $array_history = array('purchase_bill_id' => $purchase_bill_id, 'payment_by' => get_loggedin_user_id(), 'amount' => $payment_amount, 'pay_via' => $this->request->getPost('pay_via'), 'remarks' => $this->request->getPost('remarks'), 'attach_orig_name' => $attach_orig_name, 'attach_file_name' => $attach_file_name, 'coll_type' => 1, 'paid_on' => date("Y-m-d", strtotime($paid_date)));
        $builder->insert('purchase_payment_history', $array_history);
        if ($data['getbill']['due'] <= $payment_amount) {
            $payment_status = 3;
        } else {
            $payment_status = 2;
        }
        $sql = "UPDATE `purchase_bill` SET `payment_status` = " . $payment_status . ", `paid` = `paid` + " . $payment_amount . ", `due` = `due` - " . $payment_amount . " WHERE `id` = " . $db->escape($purchase_bill_id);
        $db->query($sql);
    }
    public function get_stock_product_wisereport($branch_id, $category_id = '')
    {
        $builder->select('product.*,product_store.name as store_name,product_supplier.name as supplier_name,product_category.name as category_name, (SELECT sum(quantity) from product_issues_details JOIN product_issues ON product_issues.id = product_issues_details.issues_id where product.id=product_issues_details.product_id AND product_issues.status = 0) as total_issued, (SELECT sum(quantity) from sales_bill_details where product.id=sales_bill_details.product_id) as total_sales, IFNULL(SUM(purchase_bill_details.quantity),0) as in_stock');
        $builder->from('purchase_bill');
        $builder->join('purchase_bill_details', 'purchase_bill_details.purchase_bill_id = purchase_bill.id', 'inner');
        $builder->join('product', 'product.id = purchase_bill_details.product_id', 'inner');
        $builder->join('product_category', 'product_category.id = product.category_id', 'left');
        $builder->join('product_store', 'purchase_bill.store_id = product_store.id', 'left');
        $builder->join('product_supplier', 'purchase_bill.supplier_id = product_supplier.id', 'left');
        $builder->order_by('purchase_bill.id', 'ASC');
        $builder->where('purchase_bill.branch_id', $branch_id);
        if ($category_id != 'all') {
            $builder->where('product.category_id', $category_id);
        }
        $builder->group_by('purchase_bill_details.product_id');
        return $builder->get()->result_array();
    }
    public function get_purchase_report($branch_id, $supplier_id = '', $payment_status = '', $start = '', $end = '')
    {
        $builder->select('purchase_bill.*,product_store.name as store_name,IFNULL(SUM(purchase_bill.total - purchase_bill.discount),0) as net_payable,product_supplier.name as supplier_name');
        $builder->from('purchase_bill');
        $builder->join('product_supplier', 'product_supplier.id = purchase_bill.supplier_id', 'left');
        $builder->join('product_store', 'purchase_bill.store_id = product_store.id', 'left');
        if ($supplier_id != 'all') {
            $builder->where('purchase_bill.supplier_id', $supplier_id);
        }
        if ($payment_status != 'all') {
            $builder->where('purchase_bill.payment_status', $payment_status);
        }
        $builder->where('purchase_bill.date >=', $start);
        $builder->where('purchase_bill.date <=', $end);
        $builder->where('purchase_bill.branch_id', $branch_id);
        $builder->group_by('purchase_bill.id');
        $builder->order_by('purchase_bill.id', 'ASC');
        return $builder->get()->result_array();
    }
    public function get_sales_report($branch_id, $payment_status = '', $start = '', $end = '')
    {
        $builder->select('sales_bill.*,roles.name as role_name,IFNULL(SUM(sales_bill.total - sales_bill.discount),0) as net_payable');
        $builder->from('sales_bill');
        $builder->join('roles', 'roles.id = sales_bill.role_id', 'left');
        if ($payment_status != 'all') {
            $builder->where('purchase_bill.payment_status', $payment_status);
        }
        $builder->where('sales_bill.date >=', $start);
        $builder->where('sales_bill.date <=', $end);
        $builder->where('sales_bill.branch_id', $branch_id);
        $builder->group_by('sales_bill.id');
        $builder->order_by('sales_bill.id', 'ASC');
        return $builder->get()->result_array();
    }
    public function getIssuesreport($branchID = '', $start = '', $end = '')
    {
        $builder->select('product_issues.*,product.name as product_name,roles.name as role_name,product_issues_details.quantity,product_category.name as category_name');
        $builder->from('product_issues_details');
        $builder->join('product_issues', 'product_issues.id = product_issues_details.issues_id', 'inner');
        $builder->join('product', 'product.id = product_issues_details.product_id', 'left');
        $builder->join('product_category', 'product_category.id = product.category_id', 'left');
        $builder->join('roles', 'roles.id = product_issues.role_id', 'left');
        $builder->where('product_issues.date_of_issue >=', $start);
        $builder->where('product_issues.date_of_issue <=', $end);
        $builder->where('product_issues.branch_id', $branchID);
        $builder->order_by('product_issues.id', 'ASC');
        return $builder->get()->result_array();
    }
    public function save_store($data)
    {
        $insertStore = array('name' => $data['store_name'], 'code' => $data['store_code'], 'mobileno' => $data['mobileno'], 'address' => $data['address'], 'description' => $data['description'], 'branch_id' => $this->applicationModel->get_branch_id());
        if (isset($data['store_id']) && !empty($data['store_id'])) {
            $builder->where('id', $data['store_id']);
            $builder->update('product_store', $insertStore);
        } else {
            $builder->insert('product_store', $insertStore);
        }
    }
    public function getProductByBranch($branch_id = '')
    {
        if (!empty($branch_id)) {
            $builder->where('branch_id', $branch_id);
            $result = $builder->get('product')->result_array();
            return $result;
        }
        return "";
    }
    public function save_sales($data)
    {
        $paid = 0;
        $paymentStatus = 1;
        $dueAmount = $data['net_amount'];
        if (!empty($data['payment_amount'])) {
            $paymentStatus = 2;
            $paid = $data['payment_amount'];
            $dueAmount = $data['net_amount'] - $paid;
            if ($data['net_amount'] == $paid) {
                $paymentStatus = 3;
            }
        }
        $arrayInvoice = array('bill_no' => $data['bill_no'], 'role_id' => $data['role_id'], 'user_id' => $data['sale_to'], 'remarks' => $data['payment_remarks'], 'total' => $data['grand_total'], 'discount' => $data['total_discount'], 'due' => $dueAmount, 'paid' => $paid, 'payment_status' => $paymentStatus, 'date' => date('Y-m-d', strtotime($data['date'])), 'prepared_by' => get_loggedin_user_id(), 'modifier_id' => get_loggedin_user_id(), 'branch_id' => $this->applicationModel->get_branch_id());
        $builder->insert('sales_bill', $arrayInvoice);
        $sales_bill_id = $builder->insert_id();
        $arrayData = array();
        $sales = $data['sales'];
        foreach ($sales as $key => $value) {
            $arrayproduct = array('sales_bill_id' => $sales_bill_id, 'product_id' => $value['product'], 'unit_price' => $value['unit_price'], 'discount' => $value['discount'], 'quantity' => $value['quantity'], 'sub_total' => $value['sub_total']);
            $arrayData[] = $arrayproduct;
            //update product available stock
            $this->stock_upgrade($value['quantity'], $value['product'], false);
        }
        $builder->insert_batch('sales_bill_details', $arrayData);
        if (!empty($data['payment_amount'])) {
            $arrayInvoice = array('sales_bill_id' => $sales_bill_id, 'amount' => $data['payment_amount'], 'pay_via' => $data['pay_via'], 'payment_by' => get_loggedin_user_id(), 'remarks' => $data['payment_remarks'], 'coll_type' => 1, 'attach_orig_name' => '', 'attach_file_name' => '', 'paid_on' => date("Y-m-d"));
            $builder->insert('sales_payment_history', $arrayInvoice);
        }
    }
    public function save_issue($data)
    {
        $arrayInvoice = array('role_id' => $data['role_id'], 'user_id' => $data['sale_to'], 'remarks' => $data['remarks'], 'date_of_issue' => date('Y-m-d', strtotime($data['date_of_issue'])), 'due_date' => date('Y-m-d', strtotime($data['due_date'])), 'prepared_by' => get_loggedin_user_id(), 'branch_id' => $this->applicationModel->get_branch_id());
        $builder->insert('product_issues', $arrayInvoice);
        $issues_id = $builder->insert_id();
        $arrayData = array();
        $sales = $data['sales'];
        foreach ($sales as $key => $value) {
            $arrayproduct = array('issues_id' => $issues_id, 'product_id' => $value['product'], 'quantity' => $value['quantity']);
            $arrayData[] = $arrayproduct;
            //update product available stock
            $this->stock_upgrade($value['quantity'], $value['product'], false);
        }
        $builder->insert_batch('product_issues_details', $arrayData);
    }
    public function getSalesList()
    {
        $builder->select('sales_bill.*,roles.name as role_name');
        $builder->from('sales_bill');
        $builder->join('roles', 'roles.id = sales_bill.role_id', 'left');
        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id', get_loggedin_branch_id())->where();
        }
        $builder->order_by('sales_bill.id', 'asc');
        $result = $builder->get()->result_array();
        return $result;
    }
    public function getSalesInvoice($id)
    {
        $builder->select('sales_bill.*,staff.name as biller_name,roles.name as role_name');
        $builder->from('sales_bill');
        $builder->join('roles', 'roles.id = sales_bill.role_id', 'left');
        $builder->join('staff', 'staff.id = sales_bill.prepared_by', 'left');
        $builder->where('sales_bill.id', $id);
        if (!is_superadmin_loggedin()) {
            $this->db->table('sales_bill.branch_id', get_loggedin_branch_id())->where();
        }
        return $builder->get()->row_array();
    }
    // add partly of the sales payment
    public function save_sales_payment($data)
    {
        $payment_status = 1;
        $attach_orig_name = "";
        $attach_file_name = "";
        $sales_bill_id = $data['sales_bill_id'];
        $payment_amount = $data['payment_amount'];
        $paid_date = $data['paid_date'];
        // uploading file using codeigniter upload library
        if (isset($_FILES['attach_document']['name']) && !empty($_FILES['attach_document']['name'])) {
            $config['upload_path'] = './uploads/attachments/inventory_payment/';
            $config['allowed_types'] = '*';
            $config['encrypt_name'] = true;
            $this->upload->initialize($config);
            if ($this->upload->do_upload("attach_document")) {
                $attach_orig_name = $this->upload->data('orig_name');
                $attach_file_name = $this->upload->data('file_name');
            }
        }
        $array_history = array('sales_bill_id' => $sales_bill_id, 'payment_by' => get_loggedin_user_id(), 'amount' => $payment_amount, 'pay_via' => $this->request->getPost('pay_via'), 'remarks' => $this->request->getPost('remarks'), 'attach_orig_name' => $attach_orig_name, 'attach_file_name' => $attach_file_name, 'coll_type' => 1, 'paid_on' => date("Y-m-d", strtotime($paid_date)));
        $builder->insert('sales_payment_history', $array_history);
        if ($data['getbill']['due'] <= $payment_amount) {
            $payment_status = 3;
        } else {
            $payment_status = 2;
        }
        $sql = "UPDATE `sales_bill` SET `payment_status` = " . $payment_status . ", `paid` = `paid` + " . $payment_amount . ", `due` = `due` - " . $payment_amount . " WHERE `id` = " . $db->escape($sales_bill_id);
        $db->query($sql);
    }
    public function stock_upgrade($quantity, $productID, $add = true)
    {
        if ($add == true) {
            $sql = "UPDATE `product` SET `available_stock` = `available_stock` + " . $quantity . " WHERE `id` = " . $db->escape($productID);
        } else {
            $sql = "UPDATE `product` SET `available_stock` = `available_stock` - " . $quantity . " WHERE `id` = " . $db->escape($productID);
        }
        $db->query($sql);
    }
    public function getIssueList()
    {
        $builder->select('product_issues.*,roles.name as role_name');
        $builder->from('product_issues');
        $builder->join('roles', 'roles.id = product_issues.role_id', 'left');
        if (!is_superadmin_loggedin()) {
            $this->db->table('branch_id', get_loggedin_branch_id())->where();
        }
        $builder->order_by('product_issues.id', 'asc');
        $result = $builder->get()->result_array();
        return $result;
    }
}



