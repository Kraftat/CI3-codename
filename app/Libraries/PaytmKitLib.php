<?php

namespace App\Libraries;

use Exception;

class PaytmKitLib
{
    private static string $iv = "@@@@&&&&####$$$$";

    public static function encrypt(string $input, string $key): string|false
    {
        $key = html_entity_decode($key);
        return openssl_encrypt($input, "AES-128-CBC", $key, 0, self::$iv);
    }

    public static function decrypt(string $encrypted, string $key): string|false
    {
        $key = html_entity_decode($key);
        return openssl_decrypt($encrypted, "AES-128-CBC", $key, 0, self::$iv);
    }

    public static function generateSignature(array|string $params, string $key): string|false
    {
        if (!is_array($params) && !is_string($params)) {
            throw new Exception("string or array expected, " . gettype($params) . " given");
        }

        if (is_array($params)) {
            $params = self::getStringByParams($params);
        }

        return self::generateSignatureByString($params, $key);
    }

    public static function verifySignature(array|string $params, string $key, string $checksum): bool
    {
        if (!is_array($params) && !is_string($params)) {
            throw new Exception("string or array expected, " . gettype($params) . " given");
        }

        if (isset($params['CHECKSUMHASH'])) {
            unset($params['CHECKSUMHASH']);
        }

        if (is_array($params)) {
            $params = self::getStringByParams($params);
        }

        return self::verifySignatureByString($params, $key, $checksum);
    }

    private static function generateSignatureByString(string $params, string $key): string|false
    {
        $salt = self::generateRandomString(4);
        return self::calculateChecksum($params, $key, $salt);
    }

    private static function verifySignatureByString(string $params, string $key, string $checksum): bool
    {
        $paytm_hash = self::decrypt($checksum, $key);
        $salt = substr((string) $paytm_hash, -4);
        return $paytm_hash == self::calculateHash($params, $salt);
    }

    private static function generateRandomString(int $length): string
    {
        $random = "";
        srand((double) microtime() * 1000000);

        $data = "9876543210ZYXWVUTSRQPONMLKJIHGFEDCBAabcdefghijklmnopqrstuvwxyz!@#$&_";

        for ($i = 0; $i < $length; $i++) {
            $random .= substr($data, (rand() % (strlen($data))), 1);
        }

        return $random;
    }

    private static function getStringByParams(array $params): string
    {
        ksort($params);
        $params = array_map(function ($value) {
            return ($value !== null && strtolower($value) !== "null") ? $value : "";
        }, $params);
        return implode("|", $params);
    }

    private static function calculateHash(string $params, string $salt): string
    {
        $finalString = $params . "|" . $salt;
        $hash = hash("sha256", $finalString);
        return $hash . $salt;
    }

    private static function calculateChecksum(string $params, string $key, string $salt): string|false
    {
        $hashString = self::calculateHash($params, $salt);
        return self::encrypt($hashString, $key);
    }

    private static function pkcs5Pad(string $text, int $blocksize): string
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    private static function pkcs5Unpad(string $text): false|string
    {
        $pad = ord($text[strlen($text) - 1]);
        if ($pad > strlen($text)) {
            return false;
        }

        return substr($text, 0, -1 * $pad);
    }
}
