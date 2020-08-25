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
}
