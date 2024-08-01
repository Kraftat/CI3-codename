<?php

$controllers_directory = 'app/Controllers';
$log_file = 'ci3_to_ci4_migration_log.txt';

// Patterns to find and their replacements
$replacements = [
    '/\$this->load->helper\((.*?)\);/' => 'helper(\1);',
    '/\$this->load->library\(\'session\'\);/' => '$this->session = \Config\Services::session();',
    '/\$this->db->where\((.*?)\)->get\((.*?)\)->row_array\(\);/' => '$this->db->table(\2)->where(\1)->get()->getRowArray();',
    '/\$this->db->where\((.*?)\)->get\((.*?)\)->row\(\)->(.*?);/' => '$this->db->table(\2)->where(\1)->get()->getRow()->\3;',
    '/\$this->form_validation->set_rules\((.*?)\);/' => '$this->validation->setRule(\1);',
    '/\$this->load->view\((.*?)\);/' => 'echo view(\1);',
    '/force_download\((.*?)\);/' => 'return $this->response->download(\1);',
    '/\$this->form_validation->set_message\((.*?)\);/' => '$this->validation->setRule(\1);'
];

function update_file($file_path, $replacements) {
    $content = file_get_contents($file_path);
    $original_content = $content;
    $changes = [];

    // Apply replacements
    foreach ($replacements as $pattern => $replacement) {
        $new_content = preg_replace($pattern, $replacement, $content, -1, $count);
        if ($count > 0) {
            $content = $new_content;
            $changes[] = [
                'pattern' => $pattern,
                'replacement' => $replacement,
                'count' => $count
            ];
        }
    }

    // Add required use statements for models based on @var annotations
    if (preg_match_all('/@var\s+App\\\Models\\\(.*?)(?:\s|$)/m', $content, $matches)) {
        $required_models = array_unique($matches[1]);
        $existing_uses = [];
        if (preg_match_all('/use\s+App\\\Models\\\(.*?);/m', $content, $use_matches)) {
            $existing_uses = $use_matches[1];
        }
        $new_uses = array_diff($required_models, $existing_uses);
        if (!empty($new_uses)) {
            $use_statements = "";
            foreach ($new_uses as $model) {
                $use_statements .= "use App\\Models\\$model;\n";
            }
            $content = preg_replace('/<\?php\s+/', "<?php\n$use_statements", $content, 1);
            $changes[] = [
                'pattern' => '/<\?php\s+/',
                'replacement' => "<?php\n$use_statements",
                'count' => count($new_uses)
            ];
        }
    }

    if ($content !== $original_content) {
        file_put_contents($file_path, $content);
    }

    return $changes;
}

function log_changes($file_path, $changes, $log_file) {
    $log_content = "Changes in $file_path:\n";
    foreach ($changes as $change) {
        $log_content .= "Pattern: {$change['pattern']}\n";
        $log_content .= "Replacement: {$change['replacement']}\n";
        $log_content .= "Count: {$change['count']}\n";
    }
    $log_content .= "\n";

    file_put_contents($log_file, $log_content, FILE_APPEND);
}

function process_directory($directory, $replacements, $log_file) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $file_path = $file->getRealPath();
            $changes = update_file($file_path, $replacements);
            if (!empty($changes)) {
                log_changes($file_path, $changes, $log_file);
            }
        }
    }
}

// Run the script
process_directory($controllers_directory, $replacements, $log_file);

echo "Migration complete. Check the log file for details.\n";
?>
