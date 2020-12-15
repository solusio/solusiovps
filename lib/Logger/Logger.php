<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\Logger;

/**
 * @package WHMCS\Module\Server\SolusIoVps\DI
 */
final class Logger
{
    const MODULE_NAME = 'solusiovps';

    /**
     * @param array $data
     * @param string $message
     */
    public static function log(array $data, string $message)
    {
        logModuleCall(self::MODULE_NAME, __FUNCTION__, $data, $message);
    }
}
