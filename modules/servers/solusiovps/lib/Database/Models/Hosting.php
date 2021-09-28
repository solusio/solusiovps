<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\Database\Models;

use LogicException;
use WHMCS\Database\Capsule as DB;
use WHMCS\Module\Server\SolusIoVps\Helpers\Arr;

/**
 * @package WHMCS\Module\Server\SolusIoVps\Database\Models
 */
class Hosting
{
    const TABLE = 'tblhosting';

    const STATUS_PENDING = 'Pending';

    /**
     * @param int $id
     * @return mixed
     */
    public static function getByServiceId(int $id)
    {
        return DB::table(self::TABLE)->where(['id' => $id])->first();
    }

    /**
     * @param int $id
     * @param array $data
     * @return void
     */
    public static function updateByServiceId(int $id, array $data): void
    {
        DB::table(self::TABLE)->where('id', $id)->update($data);
    }

    public static function syncWithSolusServer(int $serviceId, array $data, bool $updateDomain): void
    {
        $assignedIps = array_map(static function (array $item) {
            return $item['ip'];
        }, Arr::get($data, 'ip_addresses.ipv4', []));

        if ($ipV6PrimaryIp = Arr::get($data, 'ip_addresses.ipv6.primary_ip')) {
            $assignedIps[] = $ipV6PrimaryIp;
        }

        if (!$assignedIps) {
            throw new LogicException('Virtual server should have at least one ip address');
        }

        $updateData = [
            'dedicatedip' => $assignedIps[0],
            'assignedips' => implode(',', $assignedIps),
        ];
        if ($updateDomain) {
            $updateData['domain'] = $data['name'];
        }

        self::updateByServiceId($serviceId, $updateData);
    }
}
