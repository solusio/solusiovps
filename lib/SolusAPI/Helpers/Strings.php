<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\SolusAPI\Helpers;

/**
 * @package WHMCS\Module\Server\SolusIoVps\SolusAPI\Helpers
 */
class Strings
{
    /**
     * @param string $str
     * @return string
     */
    public static function convertToUserData(string $str): string
    {
        return str_replace(["\r\n", "\r"], "\n", $str);
    }

    /**
     * @param string $str
     * @return string
     */
    public static function convertToSshKey(string $str): string
    {
        $str = str_replace(["\r\n", "\r", "\n"], '', $str);

        return trim($str);
    }

    /**
     * Generate SolusIO-compatible password
     *
     * @return string
     */
    public static function generatePassword(): string
    {
        $patterns = [
            '1234567890',
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'abcdefghijklmnopqrstuvwxyz',
        ];

        $password = '';

        foreach ($patterns as $pattern) {
            $password .= substr(str_shuffle($pattern), 0, 5);
        }

        return str_shuffle($password);
    }
}
