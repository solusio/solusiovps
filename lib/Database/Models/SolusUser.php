<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\Database\Models;

use WHMCS\Database\Capsule as DB;

/**
 * @package WHMCS\Module\Server\SolusIoVps\Database\Models
 */
class SolusUser
{
    const TABLE = 'solusiovps_users';

    /**
     * @param int $whmcsUserId
     * @return int
     */
    public static function getSolusUserId(int $whmcsUserId): int
    {
        $row = DB::table(self::TABLE)->where(['whmcs_user_id' => $whmcsUserId])->first();

        if ($row === null) {
            return 0;
        }

        return (int) $row->solus_user_id;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public static function create(array $data)
    {
        return DB::table(self::TABLE)->insert($data);
    }
}
