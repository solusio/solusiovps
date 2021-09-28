<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\WhmcsAPI;

use Exception;
use WHMCS\Module\Server\SolusIoVps\Database\Models\SolusSshKey;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\SshKeyResource;

class SshKey
{
    /**
     * @throws Exception
     */
    public static function create(array $serverParams, string $sshKey, int $solusUserId): int
    {
        $sshKeyId = SolusSshKey::getIdByKey($sshKey);

        if ($sshKeyId === 0) {
            $sshKeyResource = new SshKeyResource(Connector::create($serverParams));
            $sshKeyHash = SolusSshKey::getKeyHash($sshKey);
            $sshKeyId = $sshKeyResource->create($sshKeyHash, $sshKey, $solusUserId);

            SolusSshKey::create([
                'solus_key_id' => $sshKeyId,
                'key_hash' => $sshKeyHash,
            ]);
        }

        return $sshKeyId;
    }
}
