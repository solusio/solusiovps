<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\Database\Models;

use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Product;
use WHMCS\Database\Capsule as DB;

/**
 * @package WHMCS\Module\Server\SolusIoVps\Database\Models
 */
class Server
{
    const TABLE = 'tblservers';

    /**
     * @param int $serverId
     * @return array
     */
    public static function getParams(int $serverId): array
    {
        $row = DB::table(self::TABLE)->where(['id' => $serverId])->first();

        return [
            'serverhttpprefix' => ($row->secure === 'on') ? 'https' : 'http',
            'serverhostname' => $row->hostname,
            'serverpassword' => decrypt($row->password),
        ];
    }

    public static function getModuleServers(): array
    {
        $result = DB::table(self::TABLE)->whereType(Product::MODULE_NAME)->pluck('id');

        return collect($result)->all();
    }
}
