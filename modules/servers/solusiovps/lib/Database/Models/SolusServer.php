<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\Database\Models;

use WHMCS\Database\Capsule as DB;

/**
 * @package WHMCS\Module\Server\SolusIoVps\Database\Models
 */
class SolusServer
{
    const TABLE = 'solusiovps_servers';

    /**
     * @param array $data
     * @return mixed
     */
    public static function create(array $data)
    {
        return DB::table(self::TABLE)->insert($data);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public static function getByServiceId(int $id)
    {
        return DB::table(self::TABLE)->where(['service_id' => $id])->first();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public static function deleteByServerId(int $id)
    {
        return DB::table(self::TABLE)->where(['server_id' => $id])->delete();
    }
}
