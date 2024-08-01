<?php

// Return translation
function translate($word = '')
{
    $session = service('session');
    $db = db_connect();
    $set_lang = $session->has('set_lang') ? $session->get('set_lang') : get_global_setting('translation');

    if ($set_lang == '') {
        $set_lang = 'english';
    }

    $query = $db->query("SELECT `english`, `$set_lang` FROM `languages` WHERE `word` = ?", [$word]);
    $result = $query->getRow();

    if ($result) {
        return $result->$set_lang != '' ? $result->$set_lang : $result->english;
    } else {
        $data = [
            'word' => $word,
            'english' => ucwords(str_replace('_', ' ', $word)),
        ];
        $db->table('languages')->insert($data);
        return ucwords(str_replace('_', ' ', $word));
    }
}

function moduleIsEnabled($prefix)
{
    $session = service('session');
    $db = db_connect();
    $role_id = $session->get('loggedin_role_id');
    $branchID = $session->get('loggedin_branch');

    if ($role_id == 1) {
        return 1;
    }

    $sql = "SELECT IF(`oaf`.`isEnabled` IS NULL, 1, `oaf`.`isEnabled`) AS `status` FROM `permission_modules` 
            LEFT JOIN `modules_manage` AS `oaf` ON `oaf`.`modules_id` = `permission_modules`.`id` 
            AND `oaf`.`branch_id` = ? 
            WHERE `permission_modules`.`prefix` = ?";
    $query = $db->query($sql, [$branchID, $prefix]);
    $result = $query->getRow();

    return empty($result) ? 1 : $result->status;
}

function checkSaasLimit($prefix)
{
    $session = service('session');
    $db = db_connect();
    $role_id = $session->get('loggedin_role_id');
    $branchID = $session->get('loggedin_branch');

    if ($role_id == 1) {
        return 1;
    }

    $sql = "SELECT `sb`.`expire_date`, `sb`.`school_id`, `student_limit`, `staff_limit`, `teacher_limit`, `parents_limit` 
            FROM `branch` AS `b` 
            LEFT JOIN `saas_subscriptions` AS `sb` ON `sb`.`school_id` = `b`.`id` 
            LEFT JOIN `saas_package` AS `sp` ON `sp`.`id` = `sb`.`package_id` 
            WHERE `sb`.`school_id` = ?";
    $row = $db->query($sql, [$branchID])->getRow();

    if (empty($row)) {
        return 1;
    }

    switch ($prefix) {
        case 'student':
            // Note: The where clause needs to be added as part of the query builder chain.
            $total_student = $db->table('enroll')
                                ->where('branch_id', $branchID)
                                ->groupBy('student_id')
                                ->countAllResults();
            return $total_student > $row->student_limit ? 0 : 1;
        case 'staff':
        case 'teacher':
            $db->select('IFNULL(COUNT(staff.id), 0) AS snumber')
               ->from('staff')
               ->join('login_credential', 'login_credential.user_id = staff.id', 'inner')
               ->where('staff.branch_id', $branchID);

            if ($prefix == 'teacher') {
                $db->where('login_credential.role', 3);
            } else {
                $db->whereNotIn('login_credential.role', [1, 3, 6, 7]);
            }

            $total_staff = $db->get()->getRow()->snumber;
            $limit = $prefix == 'teacher' ? $row->teacher_limit : $row->staff_limit;
            return $total_staff > $limit ? 0 : 1;
        case 'parent':
            $total_parents = $db->table('parent')
                                ->where('branch_id', $branchID)
                                ->countAllResults();
            return $total_parents > $row->parents_limit ? 0 : 1;
    }
    return null;
}

function isEnabledSubscription($schoolID = '')
{
    $db = db_connect();
    return $db->table('saas_subscriptions')->where('school_id', $schoolID)->countAllResults() > 0;
}

function get_permission($permission, $can = '')
{
    $session = service('session');
    $role_id = $session->get('loggedin_role_id');

    if ($role_id == 1) {
        return true;
    }

    $permissions = get_staff_permissions($role_id);
    foreach ($permissions as $permObject) {
        if ($permObject->permission_prefix == $permission && $permObject->$can == '1') {
            return true;
        }
    }
    return false;
}

function get_staff_permissions($id)
{
    $db = db_connect();
    $sql = "SELECT `staff_privileges`.*, `permission`.`id` AS `permission_id`, `permission`.`prefix` AS `permission_prefix` 
            FROM `staff_privileges` 
            JOIN `permission` ON `permission`.`id`=`staff_privileges`.`permission_id` 
            WHERE `staff_privileges`.`role_id` = ?";
    return $db->query($sql, [$id])->getResult();
}

function get_session_id()
{
    $session = service('session');
    return $session->has('set_session_id') ? $session->get('set_session_id') : get_global_setting('session_id');
}

function is_secure($url)
{
    return ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 'https://' . $url : 'http://' . $url;
}

function get_global_setting($name = '')
{
    $db = db_connect();
    $result = $db->table('global_settings')->select($name)->where('id', 1)->get()->getRow();
    return $result ? $result->$name : null;
}

function is_superadmin_loggedin()
{
    
    $session = service('session');
    return $session->get('loggedin_role_id') == 1;
}

function is_admin_loggedin()
{
    $session = service('session');
    return $session->get('loggedin_role_id') == 2;
}

function is_teacher_loggedin()
{
    $session = service('session');
    return $session->get('loggedin_role_id') == 3;
}

function is_accountant_loggedin()
{
    $session = service('session');
    return $session->get('loggedin_role_id') == 4;
}

function is_librarian_loggedin()
{
    $session = service('session');
    return $session->get('loggedin_role_id') == 5;
}

function is_parent_loggedin()
{
    $session = service('session');
    return $session->get('loggedin_role_id') == 6;
}

function is_student_loggedin()
{
    $session = service('session');
    return $session->get('loggedin_role_id') == 7;
}

function get_loggedin_id()
{
    $session = service('session');
    return $session->get('loggedin_id');
}

function get_loggedin_user_id()
{
    $session = service('session');
    return $session->get('loggedin_userid');
}

function is_loggedin()
{
    $session = service('session');
    return $session->has('loggedin');
}

function loggedin_role_name()
{
    $session = service('session');
    $db = db_connect();
    $roleID = $session->get('loggedin_role_id');
    return $db->table('roles')->select('name')->where('id', $roleID)->get()->getRow()->name;
}

function loggedin_role_id()
{
    $session = service('session');
    return $session->get('loggedin_role_id');
}

function get_loggedin_user_type()
{
    $session = service('session');
    return $session->get('loggedin_type');
}

function get_loggedin_branch_id()
{
    $session = service('session');
    return $session->get('loggedin_branch');
}

function get_activeChildren_id()
{
    $session = service('session');
    return $session->get('myChildren_id');
}

function get_type_name_by_id($table, $type_id = '', $field = 'name')
{
    $db = db_connect();
    return $db->table($table)->select($field)->where('id', $type_id)->get()->getRowArray()[$field];
}

function set_alert($type, $message)
{
    $session = service('session');
    $session->setFlashdata('alert-message-' . $type, $message);
}

function app_generate_hash()
{
    return md5(random_int(0, mt_getrandmax()) . microtime() . time() . uniqid());
}

function generate_encryption_key()
{
    $encrypter = Services::encrypter();
    return bin2hex((string) $encrypter->createKey(16));
}

function get_image_url($role = '', $file_name = '')
{
    if ($file_name == 'defualt.png' || empty($file_name)) {
        return base_url('uploads/app_image/defualt.png');
    } else {
        $path = 'uploads/images/' . $role . '/' . $file_name;
        return file_exists($path) ? base_url($path) : base_url('uploads/app_image/defualt.png');
    }
}

function _d($date)
{
    if ($date == '' || is_null($date) || $date == '0000-00-00') {
        return '';
    }
    $formats = 'Y-m-d';
    $get_format = get_global_setting('date_format');
    if ($get_format != '') {
        $formats = $get_format;
    }
    return date($formats, strtotime((string) $date));
}

function btn_delete($uri)
{
    return "<button class='btn btn-danger icon btn-circle' onclick=confirm_modal('" . base_url($uri) . "') ><i class='far fa-trash-alt'></i></button>";
}

function csrf_jquery_token() {
    $security = service('security');
    return [
        'csrf_token_name' => csrf_token(),
        'csrf_hash' => csrf_hash(),
    ];
}



function check_hash_restrictions($table, $id, $hash)
{
    if (!$table || !$id || !$hash) {
        throw new \CodeIgniter\Exceptions\PageNotFoundException();
    }

    $db = db_connect();
    $query = $db->table($table)->select('hash')->where('id', $id)->get();
    $get_hash = $query->getRow()->hash ?? '';

    if (empty($hash) || ($get_hash != $hash)) {
        throw new \CodeIgniter\Exceptions\PageNotFoundException();
    }
}

function get_nicetime($date)
{
    $get_format = get_global_setting('date_format');
    if (empty($date)) {
        return "Unknown";
    }
    $ptime = strtotime((string) $date);
    $ctime = time();

    $timeDiff = floor(abs($ctime - $ptime) / 60);

    if ($timeDiff < 2) {
        $timeDiff = "Just now";
    } elseif ($timeDiff > 2 && $timeDiff < 60) {
        $timeDiff = floor(abs($timeDiff)) . " minutes ago";
    } elseif ($timeDiff > 60 && $timeDiff < 120) {
        $timeDiff = floor(abs($timeDiff / 60)) . " hour ago";
    } elseif ($timeDiff < 1440) {
        $timeDiff = floor(abs($timeDiff / 60)) . " hours ago";
    } elseif ($timeDiff > 1440 && $timeDiff < 2880) {
        $timeDiff = floor(abs($timeDiff / 1440)) . " day ago";
    } else {
        $timeDiff = date($get_format, $ptime);
    }
    return $timeDiff;
}

function bytesToSize($path, $filesize = '')
{
    $bytes = is_numeric($filesize) ? $filesize : sprintf('%u', filesize($path));
    if ($bytes > 0) {
        $unit = intval(log($bytes, 1024));
        $units = ['B', 'KB', 'MB', 'GB'];
        return isset($units[$unit]) ? sprintf('%d %s', $bytes / 1024 ** $unit, $units[$unit]) : $bytes;
    }
    return $bytes;
}

function array_to_object($array)
{
    return !is_array($array) && !is_object($array) ? new stdClass() : json_decode(json_encode((object)$array));
}

function access_denied()
{
    set_alert('error', translate('access_denied'));
    return redirect()->to(site_url('dashboard'));
}

function ajax_access_denied(): never
{
    set_alert('error', translate('access_denied'));
    echo json_encode(['status' => 'access_denied']);
    exit();
}

function slugify($text)
{
    $text = preg_replace('~[^\pL\d]+~u', '_', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^\-\w]+~', '', $text);
    $text = trim((string) $text, '_');
    $text = preg_replace('~-+~', '_', $text);
    return strtolower((string) $text);
}

function web_menu_list($publish = '', $default = '', $branchID = '')
{
    $db = db_connect();
    if (empty($branchID)) {
        $branchID = model('HomeModel')->getDefaultBranch();
    }

    $builder = $db->table('front_cms_menu')
                  ->select('*, IF(front_cms_menu_visible.name IS NULL, front_cms_menu.title, front_cms_menu_visible.name) AS title, front_cms_menu_visible.invisible')
                  ->join('front_cms_menu_visible', 'front_cms_menu_visible.menu_id = front_cms_menu.id AND front_cms_menu_visible.branch_id = ' . $branchID, 'left')
                  ->where('front_cms_menu.branch_id', [0, $branchID])
                  ->orderBy('front_cms_menu.ordering', 'asc');

    if ($publish != '') {
        $builder->where('front_cms_menu.publish', $publish);
    }

    if ($default != '') {
        $builder->where('front_cms_menu.system', $default);
    }

    return $builder->get()->getResultArray();
}

function get_request_url()
{
    $url = $_SERVER['QUERY_STRING'];
    return empty($url) ? '' : '?' . $url;
}

function delete_dir($dirPath)
{
    if (!is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (!str_ends_with((string) $dirPath, '/')) {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            delete_dir($file);
        } else {
            unlink($file);
        }
    }
    return rmdir($dirPath);
}

function currencyFormat($amount = 0)
{
    $ci = Services::session();
    $global_config = $ci->get('global_config');
    $currency = $global_config['currency'];
    $currency_symbol = $global_config['currency_symbol'];
    $currency_formats = $global_config['currency_formats'];
    $symbol_position = $global_config['symbol_position'];

    $amount = empty($amount) ? 0 : $amount;
    $value = $amount;
    switch ($currency_formats) {
        case 1:
            $value = number_format($amount, 2, '.', '');
            break;
        case 2:
            $value = moneyFormatIndia($amount);
            break;
        case 3:
            $value = number_format($amount, 3, '.', ',');
            break;
        case 4:
            $value = number_format($amount, 2, ',', '.');
            break;
        case 5:
            $value = number_format($amount, 2, '.', ',');
            break;
        case 6:
            $value = number_format($amount, 2, ',', ' ');
            break;
        case 7:
            $value = number_format($amount, 2, '.', ' ');
            break;
        case 8:
            $value = $amount;
            break;
    }
    return match ($symbol_position) {
        1 => $currency_symbol . $value,
        2 => $value . $currency_symbol,
        3 => $currency_symbol . " " . $value,
        4 => $value . " " . $currency_symbol,
        5 => $currency . " " . $value,
        6 => $value . " " . $currency,
        default => $value,
    };
}

function moneyFormatIndia($num)
{
    $explrestunits = "";
    $num = preg_replace('/,+/', '', $num);
    $words = explode(".", $num);
    $des = "00";
    if (count($words) <= 2) {
        $num = $words[0];
        if (count($words) >= 2) {
            $des = $words[1];
        }
        $des = strlen($des) < 2 ? "$des" : substr($des, 0, 2);
    }
    if (strlen($num) > 3) {
        $lastthree = substr($num, strlen($num) - 3, strlen($num));
        $restunits = substr($num, 0, strlen($num) - 3);
        $restunits = (strlen($restunits) % 2 == 1) ? "0" . $restunits : $restunits;
        $expunit = str_split($restunits, 2);
        $counter = count($expunit);
        for ($i = 0; $counter > $i; $i++) {
            if ($i == 0) {
                $explrestunits .= (int)$expunit[$i] . ",";
            } else {
                $explrestunits .= $expunit[$i] . ",";
            }
        }
        $thecash = $explrestunits . $lastthree;
    } else {
        $thecash = $num;
    }
    return "$thecash.$des";
}

function getEnrollToStudentID($enroll_id = '')
{
    $db = db_connect();
    return $db->table('enroll')->select('student_id')->where('id', $enroll_id)->get()->getRow()->student_id;
}


//Fahad Added helpers

function get_currencies() {
    return [
        // North America
        ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$'],
        ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$'],
        ['code' => 'MXN', 'name' => 'Mexican Peso', 'symbol' => '$'],

        // Europe
        ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€'],
        ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£'],
        ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF'],
        ['code' => 'SEK', 'name' => 'Swedish Krona', 'symbol' => 'kr'],
        ['code' => 'NOK', 'name' => 'Norwegian Krone', 'symbol' => 'kr'],
        ['code' => 'CZK', 'name' => 'Czech Koruna', 'symbol' => 'Kč'],
        ['code' => 'HUF', 'name' => 'Hungarian Forint', 'symbol' => 'Ft'],
        ['code' => 'PLN', 'name' => 'Polish Zloty', 'symbol' => 'zł'],

        // Asia
        ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥'],
        ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥'],
        ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹'],
        ['code' => 'SGD', 'name' => 'Singapore Dollar', 'symbol' => 'S$'],
        ['code' => 'HKD', 'name' => 'Hong Kong Dollar', 'symbol' => 'HK$'],
        ['code' => 'KRW', 'name' => 'South Korean Won', 'symbol' => '₩'],
        ['code' => 'IDR', 'name' => 'Indonesian Rupiah', 'symbol' => 'Rp'],
        ['code' => 'MYR', 'name' => 'Malaysian Ringgit', 'symbol' => 'RM'],
        ['code' => 'THB', 'name' => 'Thai Baht', 'symbol' => '฿'],
        ['code' => 'VND', 'name' => 'Vietnamese Dong', 'symbol' => '₫'],
        ['code' => 'TWD', 'name' => 'New Taiwan Dollar', 'symbol' => 'NT$'],
        ['code' => 'PHP', 'name' => 'Philippine Peso', 'symbol' => '₱'],

        // Middle East
        ['code' => 'AED', 'name' => 'United Arab Emirates Dirham', 'symbol' => 'AED'],
        ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => 'SAR'],
        ['code' => 'KWD', 'name' => 'Kuwaiti Dinar', 'symbol' => 'KD'],
        ['code' => 'QAR', 'name' => 'Qatari Riyal', 'symbol' => 'QAR'],
        ['code' => 'OMR', 'name' => 'Omani Rial', 'symbol' => 'OMR'],
        ['code' => 'BHD', 'name' => 'Bahraini Dinar', 'symbol' => 'BD'],

        // South America
        ['code' => 'BRL', 'name' => 'Brazilian Real', 'symbol' => 'R$'],
        ['code' => 'ARS', 'name' => 'Argentine Peso', 'symbol' => '$'],
        ['code' => 'CLP', 'name' => 'Chilean Peso', 'symbol' => '$'],
        ['code' => 'COP', 'name' => 'Colombian Peso', 'symbol' => '$'],
        ['code' => 'PEN', 'name' => 'Peruvian Sol', 'symbol' => 'S/'],
        ['code' => 'UYU', 'name' => 'Uruguayan Peso', 'symbol' => '$U'],

        // Africa
        ['code' => 'ZAR', 'name' => 'South African Rand', 'symbol' => 'R'],
        ['code' => 'EGP', 'name' => 'Egyptian Pound', 'symbol' => 'E£'],
        ['code' => 'NGN', 'name' => 'Nigerian Naira', 'symbol' => '₦'],
        ['code' => 'KES', 'name' => 'Kenyan Shilling', 'symbol' => 'KSh'],
        ['code' => 'GHS', 'name' => 'Ghanaian Cedi', 'symbol' => 'GH₵'],
        ['code' => 'DZD', 'name' => 'Algerian Dinar', 'symbol' => 'DA'],
        ['code' => 'MAD', 'name' => 'Moroccan Dirham', 'symbol' => 'MAD'],

        // Oceania
        ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$'],
        ['code' => 'NZD', 'name' => 'New Zealand Dollar', 'symbol' => 'NZ$'],
    ];

}

if (!function_exists('get_country')) {
    function get_country() {
        return [
            "AF" => "Afghanistan",
            "AL" => "Albania",
            "DZ" => "Algeria",
            "AS" => "American Samoa",
            "AD" => "Andorra",
            "AO" => "Angola",
            "AI" => "Anguilla",
            "AQ" => "Antarctica",
            "AG" => "Antigua and Barbuda",
            "AR" => "Argentina",
            "AM" => "Armenia",
            "AW" => "Aruba",
            "AU" => "Australia",
            "AT" => "Austria",
            "AZ" => "Azerbaijan",
            "BS" => "Bahamas",
            "BH" => "Bahrain",
            "BD" => "Bangladesh",
            "BB" => "Barbados",
            "BY" => "Belarus",
            "BE" => "Belgium",
            "BZ" => "Belize",
            "BJ" => "Benin",
            "BM" => "Bermuda",
            "BT" => "Bhutan",
            "BO" => "Bolivia",
            "BA" => "Bosnia and Herzegovina",
            "BW" => "Botswana",
            "BR" => "Brazil",
            "BN" => "Brunei Darussalam",
            "BG" => "Bulgaria",
            "BF" => "Burkina Faso",
            "BI" => "Burundi",
            "KH" => "Cambodia",
            "CM" => "Cameroon",
            "CA" => "Canada",
            "CV" => "Cape Verde",
            "KY" => "Cayman Islands",
            "CF" => "Central African Republic",
            "TD" => "Chad",
            "CL" => "Chile",
            "CN" => "China",
            "CO" => "Colombia",
            "KM" => "Comoros",
            "CG" => "Congo",
            "CD" => "Congo, the Democratic Republic of the",
            "CR" => "Costa Rica",
            "CI" => "Côte d'Ivoire",
            "HR" => "Croatia",
            "CU" => "Cuba",
            "CY" => "Cyprus",
            "CZ" => "Czech Republic",
            "DK" => "Denmark",
            "DJ" => "Djibouti",
            "DM" => "Dominica",
            "DO" => "Dominican Republic",
            "EC" => "Ecuador",
            "EG" => "Egypt",
            "SV" => "El Salvador",
            "GQ" => "Equatorial Guinea",
            "ER" => "Eritrea",
            "EE" => "Estonia",
            "ET" => "Ethiopia",
            "FJ" => "Fiji",
            "FI" => "Finland",
            "FR" => "France",
            "GA" => "Gabon",
            "GM" => "Gambia",
            "GE" => "Georgia",
            "DE" => "Germany",
            "GH" => "Ghana",
            "GR" => "Greece",
            "GL" => "Greenland",
            "GD" => "Grenada",
            "GU" => "Guam",
            "GT" => "Guatemala",
            "GN" => "Guinea",
            "GW" => "Guinea-Bissau",
            "GY" => "Guyana",
            "HT" => "Haiti",
            "HN" => "Honduras",
            "HK" => "Hong Kong",
            "HU" => "Hungary",
            "IS" => "Iceland",
            "IN" => "India",
            "ID" => "Indonesia",
            "IR" => "Iran, Islamic Republic of",
            "IQ" => "Iraq",
            "IE" => "Ireland",
            "IT" => "Italy",
            "JM" => "Jamaica",
            "JP" => "Japan",
            "JO" => "Jordan",
            "KZ" => "Kazakhstan",
            "KE" => "Kenya",
            "KI" => "Kiribati",
            "KP" => "Korea, Democratic People's Republic of",
            "KR" => "Korea, Republic of",
            "KW" => "Kuwait",
            "KG" => "Kyrgyzstan",
            "LA" => "Lao People's Democratic Republic",
            "LV" => "Latvia",
            "LB" => "Lebanon",
            "LS" => "Lesotho",
            "LR" => "Liberia",
            "LY" => "Libya",
            "LI" => "Liechtenstein",
            "LT" => "Lithuania",
            "LU" => "Luxembourg",
            "MO" => "Macao",
            "MK" => "Macedonia, the Former Yugoslav Republic of",
            "MG" => "Madagascar",
            "MW" => "Malawi",
            "MY" => "Malaysia",
            "MV" => "Maldives",
            "ML" => "Mali",
            "MT" => "Malta",
            "MH" => "Marshall Islands",
            "MR" => "Mauritania",
            "MU" => "Mauritius",
            "MX" => "Mexico",
            "FM" => "Micronesia, Federated States of",
            "MD" => "Moldova, Republic of",
            "MC" => "Monaco",
            "MN" => "Mongolia",
            "ME" => "Montenegro",
            "MA" => "Morocco",
            "MZ" => "Mozambique",
            "MM" => "Myanmar",
            "NA" => "Namibia",
            "NR" => "Nauru",
            "NP" => "Nepal",
            "NL" => "Netherlands",
            "NZ" => "New Zealand",
            "NI" => "Nicaragua",
            "NE" => "Niger",
            "NG" => "Nigeria",
            "NU" => "Niue",
            "NF" => "Norfolk Island",
            "MP" => "Northern Mariana Islands",
            "NO" => "Norway",
            "OM" => "Oman",
            "PK" => "Pakistan",
            "PW" => "Palau",
            "PA" => "Panama",
            "PG" => "Papua New Guinea",
            "PY" => "Paraguay",
            "PE" => "Peru",
            "PH" => "Philippines",
            "PL" => "Poland",
            "PT" => "Portugal",
            "PR" => "Puerto Rico",
            "QA" => "Qatar",
            "RO" => "Romania",
            "RU" => "Russian Federation",
            "RW" => "Rwanda",
            "KN" => "Saint Kitts and Nevis",
            "LC" => "Saint Lucia",
            "VC" => "Saint Vincent and the Grenadines",
            "WS" => "Samoa",
            "SM" => "San Marino",
            "ST" => "Sao Tome and Principe",
            "SA" => "Saudi Arabia",
            "SN" => "Senegal",
            "RS" => "Serbia",
            "SC" => "Seychelles",
            "SL" => "Sierra Leone",
            "SG" => "Singapore",
            "SX" => "Sint Maarten (Dutch part)",
            "SK" => "Slovakia",
            "SI" => "Slovenia",
            "SB" => "Solomon Islands",
            "SO" => "Somalia",
            "ZA" => "South Africa",
            "SS" => "South Sudan",
            "ES" => "Spain",
            "LK" => "Sri Lanka",
            "SD" => "Sudan",
            "SR" => "Suriname",
            "SZ" => "Swaziland",
            "SE" => "Sweden",
            "CH" => "Switzerland",
            "SY" => "Syrian Arab Republic",
            "TW" => "Taiwan, Province of China",
            "TJ" => "Tajikistan",
            "TZ" => "Tanzania, United Republic of",
            "TH" => "Thailand",
            "TL" => "Timor-Leste",
            "TG" => "Togo",
            "TK" => "Tokelau",
            "TO" => "Tonga",
            "TT" => "Trinidad and Tobago",
            "TN" => "Tunisia",
            "TR" => "Turkey",
            "TM" => "Turkmenistan",
            "TV" => "Tuvalu",
            "UG" => "Uganda",
            "UA" => "Ukraine",
            "AE" => "United Arab Emirates",
            "GB" => "United Kingdom",
            "US" => "United States",
            "UY" => "Uruguay",
            "UZ" => "Uzbekistan",
            "VU" => "Vanuatu",
            "VE" => "Venezuela, Bolivarian Republic of",
            "VN" => "Viet Nam",
            "EH" => "Western Sahara",
            "YE" => "Yemen",
            "ZM" => "Zambia",
            "ZW" => "Zimbabwe"
        ];
    }
}