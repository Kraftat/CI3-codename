<?php

namespace App\Libraries;

use CodeIgniter\CodeIgniter;

class Ciqrcode
{
    public $cacheable = true;
    public $cachedir = 'application/cache/';
    public $errorlog = 'application/logs/';
    public $quality = true;
    public $size = 1024;

    public function __construct(array $config = [])
    {
        // Call original library
        include __DIR__ . "/qrcode/qrconst.php";
        include __DIR__ . "/qrcode/qrtools.php";
        include __DIR__ . "/qrcode/qrspec.php";
        include __DIR__ . "/qrcode/qrimage.php";
        include __DIR__ . "/qrcode/qrinput.php";
        include __DIR__ . "/qrcode/qrbitstream.php";
        include __DIR__ . "/qrcode/qrsplit.php";
        include __DIR__ . "/qrcode/qrrscode.php";
        include __DIR__ . "/qrcode/qrmask.php";
        include __DIR__ . "/qrcode/qrencode.php";

        $this->initialize($config);
    }

    public function initialize(array $config = []): void
    {
        $this->cacheable = $config['cacheable'] ?? $this->cacheable;
        $this->cachedir = $config['cachedir'] ?? WRITEPATH . $this->cachedir;
        $this->errorlog = $config['errorlog'] ?? WRITEPATH . $this->errorlog;
        $this->quality = $config['quality'] ?? $this->quality;
        $this->size = $config['size'] ?? $this->size;

        // Use cache - more disk reads but less CPU power, masks and format templates are stored there
        if (!defined('QR_CACHEABLE')) {
            define('QR_CACHEABLE', $this->cacheable);
        }

        // Used when QR_CACHEABLE === true
        if (!defined('QR_CACHE_DIR')) {
            define('QR_CACHE_DIR', $this->cachedir);
        }

        // Default error logs dir
        if (!defined('QR_LOG_DIR')) {
            define('QR_LOG_DIR', $this->errorlog);
        }

        // If true, estimates best mask (spec. default, but extremely slow; set to false to significant performance boost but (probably) worst quality code
        if ($this->quality) {
            if (!defined('QR_FIND_BEST_MASK')) {
                define('QR_FIND_BEST_MASK', true);
            }
        } else {
            if (!defined('QR_FIND_BEST_MASK')) {
                define('QR_FIND_BEST_MASK', false);
            }

            if (!defined('QR_DEFAULT_MASK')) {
                define('QR_DEFAULT_MASK', $this->quality);
            }
        }

        // If false, checks all masks available, otherwise value tells count of masks need to be checked, mask id are got randomly
        if (!defined('QR_FIND_FROM_RANDOM')) {
            define('QR_FIND_FROM_RANDOM', false);
        }

        // Maximum allowed png image width (in pixels), tune to make sure GD and PHP can handle such big images
        if (!defined('QR_PNG_MAXIMUM_SIZE')) {
            define('QR_PNG_MAXIMUM_SIZE', $this->size);
        }
    }

    public function generate(array $params = []): ?string
    {
        if (isset($params['black']) && is_array($params['black']) && count($params['black']) == 3 && array_filter($params['black'], 'is_int') === $params['black']) {
            QRimage::$black = $params['black'];
        }

        if (isset($params['white']) && is_array($params['white']) && count($params['white']) == 3 && array_filter($params['white'], 'is_int') === $params['white']) {
            QRimage::$white = $params['white'];
        }

        $params['data'] = $params['data'] ?? 'QR Code Library';
        if (isset($params['savename'])) {
            $level = 'L';
            if (isset($params['level']) && in_array($params['level'], ['L', 'M', 'Q', 'H'])) {
                $level = $params['level'];
            }

            $size = 4;
            if (isset($params['size'])) {
                $size = min(max((int)$params['size'], 1), 10);
            }

            QRcode::png($params['data'], $params['savename'], $level, $size, 2);
            return $params['savename'];
        }

        $level = 'L';
        if (isset($params['level']) && in_array($params['level'], ['L', 'M', 'Q', 'H'])) {
            $level = $params['level'];
        }

        $size = 4;
        if (isset($params['size'])) {
            $size = min(max((int)$params['size'], 1), 10);
        }

        QRcode::png($params['data'], null, $level, $size, 2);

        return null;
    }
}
