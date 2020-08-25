<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\ClientAPI;

/**
 * Trait Response
 * @package WHMCS\Module\Server\SolusIoVps\ClientAPI
 */
trait Response
{
    /**
     * @param array $data
     */
    public function success(array $data)
    {
        echo json_encode(['success' => true, 'data' => $data]);
        exit();
    }

    /**
     * @param string $error
     */
    public function error(string $error)
    {
        echo json_encode(['success' => false, 'error' => $error]);
        exit();
    }
}
