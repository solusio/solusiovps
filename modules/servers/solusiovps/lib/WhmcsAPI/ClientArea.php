<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\WhmcsAPI;

use WHMCS\ClientArea as CA;

/**
 * @package WHMCS\Module\Server\SolusIoVps\WhmcsAPI
 */
class ClientArea extends CA
{
    public function hasAccessToHosting(object $hosting): bool
    {
        $adminId = isset($_SESSION['adminid']) ? (int) $_SESSION['adminid'] : 0;

        if ($adminId > 0) {
            return true;
        }

        if (!$this->isLoggedIn()) {
            return false;
        }

        return ($this->getUserID() === (int) $hosting->userid);
    }
}
