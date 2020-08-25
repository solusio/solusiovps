<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\Database\Models;

use WHMCS\Database\Capsule as DB;

/**
 * @package WHMCS\Module\Server\SolusIoVps\Database\Models
 */
class SolusSshKey
{
    const TABLE = 'solusiovps_ssh_keys';
    const CUSTOM_FIELD_SSH_KEY = 'SSH Key';

    /**
     * @param string $sshKey
     * @return int
     */
    public static function getIdByKey(string $sshKey): int
    {
        $keyHash = self::getKeyHash($sshKey);
        $row = DB::table(self::TABLE)->where(['key_hash' => $keyHash])->first();

        if ($row === null) {
            return 0;
        }

        return (int) $row->solus_key_id;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public static function create(array $data)
    {
        return DB::table(self::TABLE)->insert($data);
    }

    public static function getKeyHash(string $sshKey): string
    {
        return sha1($sshKey);
    }
}
