<?php

// Copyright 2020. Plesk International GmbH.

use WHMCS\Module\Server\SolusIoVps\Logger\Logger;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ServerResource;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\UserResource;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\ClientArea;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Config;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Product;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Servers;
use WHMCS\User\Client;

function createAddHook() {
    return function (array $params) {
        Product::upgrade($params['upgradeid']);
    };
}

function createPreDeleteClientHook() {
    return function (array $params) {
        try {
            $config = Config::loadModuleConfig();

            if (empty($config['delete_solus_user'])) {
                return;
            }

            $whmcsUser = Client::findOrFail($params['userid']);
            $serverParams = Servers::getValidParams();

            if (empty($serverParams)) {
                throw new Exception('No valid WHMCS server found');
            }

            $userResource = new UserResource(Connector::create($serverParams));
            $solusUser = $userResource->getUserByEmail($whmcsUser['email']);

            if (empty($solusUser)) {
                return;
            }

            $serverResource = new ServerResource(Connector::create($serverParams));
            $servers = $serverResource->getAllByUser($solusUser['id']);

            if (!empty($servers)) {
                return;
            }

            $userResource->deleteUser($solusUser['id']);
        } catch (Exception $e) {
            Logger::log([], $e->getMessage());
        }
    };
}

function createClientDetailsValidationHook()
{
    return function (array $params) {
        try {
            $userId = null;

            if (empty($params['email'])) {
                return [];
            }

            // If updating user via admin area
            if (isset($params['userid'])) {
                $userId = $params['userid'];
            }

            // If updating user via client area
            if (array_key_exists('save', $params)) {
                $userId = (new ClientArea())->getUserID();
            }

            if ($userId) {
                $whmcsUser = Client::findOrFail($userId);

                if ($whmcsUser->email === $params['email']) {
                    return [];
                }
            }

            $serverParams = Servers::getValidParams();

            if (empty($serverParams)) {
                return [];
            }

            $userResource = new UserResource(Connector::create($serverParams));
            $solusUser = $userResource->getUserByEmail($params['email']);

            if (!empty($solusUser)) {
                return 'email is already taken';
            }
        } catch (Exception $e) {
            Logger::log([], $e->getMessage());
        }

        return [];
    };
}

function createClientEditHook()
{
    return function (array $params) {
        try {
            if (empty($params['email'])) {
                return;
            }

            $serverParams = Servers::getValidParams();

            if (empty($serverParams)) {
                return;
            }

            $userResource = new UserResource(Connector::create($serverParams));
            $solusUser = $userResource->getUserByEmail($params['olddata']['email']);

            if (empty($solusUser)) {
                return;
            }

            $userResource->patchUser($solusUser['id'], ['email' => $params['email']]);
        } catch (Exception $e) {
            Logger::log([], $e->getMessage());
        }
    };
}

add_hook('AfterProductUpgrade', 1, createAddHook());
add_hook('PreDeleteClient', 1, createPreDeleteClientHook());
add_hook('ClientDetailsValidation', 1, createClientDetailsValidationHook());
add_hook('ClientEdit', 1, createClientEditHook());
