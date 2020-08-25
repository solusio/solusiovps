<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\Database\Models;

use WHMCS\Database\Capsule as DB;

/**
 * @package WHMCS\Module\Server\SolusIoVps\Database\Models
 */
class Hosting
{
    const TABLE = 'tblhosting';

    /**
     * @param int $id
     * @return mixed
     */
    public static function getByServiceId(int $id)
    {
        return DB::table(self::TABLE)->where(['id' => $id])->first();
    }
}
