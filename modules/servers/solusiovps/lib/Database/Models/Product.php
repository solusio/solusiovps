<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\Database\Models;

use WHMCS\Database\Capsule as DB;

class Product
{
    const TABLE = 'tblproducts';

    /**
     * @param int $id
     * @return mixed
     */
    public static function getById(int $id)
    {
        return DB::table(self::TABLE)->where(['id' => $id])->first();
    }
}
