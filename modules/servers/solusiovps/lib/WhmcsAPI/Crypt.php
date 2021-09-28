<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\WhmcsAPI;

/**
 * @package WHMCS\Module\Server\SolusIoVps\WhmcsAPI
 */
class Crypt
{
    public static function encrypt(string $password): string
    {
        $response = localAPI('EncryptPassword', ['password2' => $password]);

        return $response['password'];
    }
}
