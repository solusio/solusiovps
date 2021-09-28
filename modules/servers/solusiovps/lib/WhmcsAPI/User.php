<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\WhmcsAPI;

use WHMCS\Module\Server\SolusIoVps\SolusAPI\Requests\UserRequestBuilder;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\UserResource;

class User
{
    public static function syncWithSolusUser(UserResource $userResource, UserRequestBuilder $request): int
    {
        $solusUser = $userResource->getUserByEmail($request->getEmail());

        $whmcsUserId = $request->getWhmcsUserId();

        if (empty($solusUser)) {
            return $userResource->create($request->getCreateRequest());
        } else {
            $solusUserId = $solusUser['id'];

            if ((int)$solusUser['billing_user_id'] !== $whmcsUserId) {
                $userResource->updateUser($solusUserId, $request->getUpdateRequest());
            }

            return $solusUserId;
        }
    }
}
