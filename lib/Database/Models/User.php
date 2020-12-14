<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\Database\Models;

use WHMCS\Database\Capsule as DB;

/**
 * @package WHMCS\Module\Server\SolusIoVps\Database\Models
 */
class User
{
    const TABLE = 'tblusers';

    /**
     * @param int $id
     * @return object|null
     */
    public static function getById(int $id)
    {
        return DB::table(self::TABLE)->where(['id' => $id])->first();
    }
}
