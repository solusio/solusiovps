<?php

// Copyright 2020. Plesk International GmbH.

include_once(__DIR__ . DIRECTORY_SEPARATOR . '../../../vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

use Carbon\Carbon;
use \GuzzleHttp\Exception\RequestException;
use WHMCS\Module\Server\SolusIoVps\Exceptions\SolusException;
use WHMCS\Module\Server\SolusIoVps\Helpers\Arr;
use WHMCS\Module\Server\SolusIoVps\Logger\Logger;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Helpers\Strings;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Requests\ServerCreateRequestBuilder;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Requests\UserRequestBuilder;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ApplicationResource;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\LimitGroupResource;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\LocationResource;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\OsImageResource;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\PlanResource;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ProjectResource;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\RoleResource;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\ServerResource;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\UserResource;
use WHMCS\Module\Server\SolusIoVps\Database\Migrations\Servers;
use WHMCS\Module\Server\SolusIoVps\Database\Migrations\SshKeys;
use WHMCS\Module\Server\SolusIoVps\Database\Models\Hosting;
use WHMCS\Module\Server\SolusIoVps\Database\Models\ProductConfigOption;
use WHMCS\Module\Server\SolusIoVps\Database\Models\Server;
use WHMCS\Module\Server\SolusIoVps\Database\Models\SolusServer;
use WHMCS\Module\Server\SolusIoVps\Database\Models\SolusSshKey;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Connector;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Config;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Crypt;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Language;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\SshKey;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\User;
use WHMCS\Service\Status;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

// Run the migrations
if (!defined('SKIP_MIGRATIONS')) {
    Servers::run();
    SshKeys::run();
}

// Load translations
Language::load();

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related abilities and
 * settings.
 */
function solusiovps_MetaData(): array
{
    return [
        'DisplayName' => 'SolusIO VPS',
        'APIVersion' => '1.1',
        'RequiresServer' => true,
        'ServiceSingleSignOnLabel' => false,
        'AdminSingleSignOnLabel' => false,
        'ListAccountsUniqueIdentifierDisplayName' => 'Domain',
        'ListAccountsUniqueIdentifierField' => 'domain',
        'ListAccountsProductField' => 'configoption1',
    ];
}

/**
 * @return array
 */
function solusiovps_ConfigOptions(): array
{
    return [
        'plan' => [ // configoption1
            'FriendlyName' => Language::trans('solusiovps_config_option_plan'),
            'Type' => 'text',
            'Size' => '25',
            'Loader' => 'solusiovps_PlanLoader',
            'SimpleMode' => true,
        ],
        'location' => [ // configoption2
            'FriendlyName' => Language::trans('solusiovps_config_option_default_location'),
            'Type' => 'text',
            'Size' => '25',
            'Loader' => 'solusiovps_LocationLoader',
            'SimpleMode' => true,
        ],
        'os_image' => [ // configoption3
            'FriendlyName' => Language::trans('solusiovps_config_option_default_operating_system'),
            'Type' => 'text',
            'Size' => '25',
            'Loader' => 'solusiovps_OsImageLoader',
            'SimpleMode' => true,
        ],
        'application' => [ // configoption4
            'FriendlyName' => Language::trans('solusiovps_config_option_application'),
            'Type' => 'text',
            'Size' => '25',
            'Loader' => 'solusiovps_ApplicationLoader',
            'SimpleMode' => true,
        ],
        'user_data' => [ // configoption5
            'FriendlyName' => Language::trans('solusiovps_config_option_user_data'),
            'Type' => 'textarea',
            'Rows' => 5,
            'Cols' => 25,
            'SimpleMode' => true,
        ],
        'backup_enabled' => [ // configoption6
            'FriendlyName' => Language::trans('solusiovps_config_option_backup_enabled'),
            'Type' => 'yesno',
            'SimpleMode' => true,
        ],
        'role' => [ // configoption7
            'FriendlyName' => Language::trans('solusiovps_config_option_default_role'),
            'Type' => 'text',
            'Size' => '25',
            'Loader' => 'solusiovps_RoleLoader',
            'SimpleMode' => true,
        ],
        'limit_group' => [ // configoption8
            'FriendlyName' => Language::trans('solusiovps_config_option_default_limit_group'),
            'Type' => 'text',
            'Size' => '25',
            'Loader' => 'solusiovps_LimitGroupLoader',
            'SimpleMode' => true,
        ],
    ];
}


/**
 * @throws Exception
 */
function solusiovps_PlanLoader(array $params): array
{
    try {
        $planResource = new PlanResource(Connector::create($params));
        $result = [];

        foreach ($planResource->list() as $item) {
            $result[Arr::get($item, 'id')] = Arr::get($item, 'name');
        }

        return $result;
    } catch (Exception $e) {
        Logger::log([], $e->getMessage());

        throw $e;
    }
}

/**
 * @throws Exception
 */
function solusiovps_OsImageLoader(array $params): array
{
    try {
        $osImageResource = new OsImageResource(Connector::create($params));

        $result = [
            0 => Language::trans('solusiovps_config_option_none'),
        ];

        foreach ($osImageResource->list() as $item) {
            foreach ($item['versions'] as $version) {
                $result[Arr::get($version, 'id')] = Arr::get($item, 'icon.name', Arr::get($item, 'name')) . ' ' . Arr::get($version, 'version');
            }
        }

        return $result;
    } catch (Exception $e) {
        Logger::log([], $e->getMessage());

        throw $e;
    }
}

/**
 * @throws Exception
 */
function solusiovps_LocationLoader(array $params): array
{
    try {
        $locationResource = new LocationResource(Connector::create($params));
        $result = [];

        foreach ($locationResource->list() as $item) {
            $result[Arr::get($item, 'id')] = Arr::get($item, 'name');
        }

        return $result;
    } catch (Exception $e) {
        Logger::log([], $e->getMessage());

        throw $e;
    }
}

/**
 * @throws Exception
 */
function solusiovps_ApplicationLoader(array $params): array
{
    try {
        $applicationResource = new ApplicationResource(Connector::create($params));

        $result = [
            0 => Language::trans('solusiovps_config_option_none'),
        ];

        foreach ($applicationResource->list() as $item) {
            $result[Arr::get($item, 'id')] = Arr::get($item, 'name');
        }

        return $result;
    } catch (Exception $e) {
        Logger::log([], $e->getMessage());

        throw $e;
    }
}

/**
 * @throws Exception
 */
function solusiovps_RoleLoader(array $params): array
{
    try {
        $roleResource = new RoleResource(Connector::create($params));

        $result = [
            0 => Language::trans('solusiovps_config_option_none'),
        ];

        foreach ($roleResource->list() as $item) {
            $result[Arr::get($item, 'id')] = Arr::get($item, 'name');
        }

        return $result;
    } catch (Exception $e) {
        Logger::log([], $e->getMessage());

        throw $e;
    }
}

/**
 * @throws Exception
 */
function solusiovps_LimitGroupLoader(array $params): array
{
    try {
        $limitGroupResource = new LimitGroupResource(Connector::create($params));

        $result = [
            0 => Language::trans('solusiovps_config_option_none'),
        ];

        foreach ($limitGroupResource->list() as $item) {
            $result[Arr::get($item, 'id')] = Arr::get($item, 'name');
        }

        return $result;
    } catch (Exception $e) {
        Logger::log([], $e->getMessage());

        throw $e;
    }
}

/**
 * @param array $params
 * @return string
 * @throws SolusException
 */
function solusiovps_CreateAccount(array $params): string
{
    if ($params['status'] !== Hosting::STATUS_PENDING) {
        return Language::trans('solusiovps_error_server_already_created');
    }

    try {
        $connector = Connector::create($params);
        $serviceId = (int)$params['serviceid'];
        $params['password'] = Strings::generatePassword();

        $userResource = new UserResource($connector);

        $solusUserId = User::syncWithSolusUser(
            $userResource,
            UserRequestBuilder::fromWHMCSCreateAccountParams($params),
        );

        $serverData = ServerCreateRequestBuilder::fromWHMCSCreateAccountParams($params);
        $sshKey = Strings::convertToSshKey($params['customfields'][SolusSshKey::CUSTOM_FIELD_SSH_KEY] ?? '');

        if (!empty($sshKey)) {
            $sshKeyId = SshKey::create($params, $sshKey, $solusUserId);
            $serverData->withSshKeys([ $sshKeyId ]);
        }

        $userApiToken = $userResource->createToken($solusUserId);
        $serverResource = new ServerResource(Connector::create($params, $userApiToken));

        $response = $serverResource->create($serverData->get());
        $data = Arr::get($response, 'data', []);

        Hosting::updateByServiceId(
            $serviceId,
            ['password' => Crypt::encrypt($params['password'])]
        );
        Hosting::syncWithSolusServer($serviceId, $data, !empty($params['domain']));
        SolusServer::create([
            'service_id' => $serviceId,
            'server_id' => (int)Arr::get($response, 'data.id'),
            'payload' => json_encode($data),
        ]);

        return 'success';
    } catch (RequestException $e) {
        Logger::log($params, $e->getResponse()->getBody()->getContents());
    } catch (Exception $e) {
        Logger::log($params, $e->getMessage());

        return $e->getMessage();
    }

    throw new SolusException('Failed to place new order, something went wrong');
}

/**
 * @param array $params
 * @return string
 */
function solusiovps_TerminateAccount(array $params): string
{
    try {
        $serverResource = new ServerResource(Connector::create($params));

        if ($server = SolusServer::getByServiceId((int)Arr::get($params, 'serviceid'))) {
            $serverResource->delete($server->server_id);

            SolusServer::deleteByServerId($server->server_id);

            return 'success';
        }

        return Language::trans('solusiovps_error_server_not_found');
    } catch (Exception $e) {
        Logger::log($params, $e->getMessage());

        return $e->getMessage();
    }
}

/**
 * @param array $params
 * @return string
 */
function solusiovps_SuspendAccount(array $params): string
{
    try {
        $serverResource = new ServerResource(Connector::create($params));

        if ($server = SolusServer::getByServiceId((int)Arr::get($params, 'serviceid'))) {
            $serverResource->suspend($server->server_id);

            return 'success';
        }

        return Language::trans('solusiovps_error_server_not_found');
    } catch (Exception $e) {
        Logger::log($params, $e->getMessage());

        return $e->getMessage();
    }
}

/**
 * @param array $params
 * @return string
 */
function solusiovps_UnsuspendAccount(array $params): string
{
    try {
        $serverResource = new ServerResource(Connector::create($params));

        if ($server = SolusServer::getByServiceId((int)Arr::get($params, 'serviceid'))) {
            $serverResource->resume($server->server_id);

            return 'success';
        }

        return Language::trans('solusiovps_error_server_not_found');
    } catch (Exception $e) {
        Logger::log($params, $e->getMessage());

        return $e->getMessage();
    }
}

/**
 * @param array $params
 * @return array
 */
function solusiovps_ClientArea(array $params): array
{
    try {
        solusiovps_syncAccount($params);
        $serverResource = new ServerResource(Connector::create($params));
        $server = SolusServer::getByServiceId((int)Arr::get($params, 'serviceid'));

        if ($server === null) {
            throw new Exception(Language::trans('solusiovps_error_server_not_found'));
        }

        $serverResponse = $serverResource->get($server->server_id);
        $productId = (int)$params['pid'];
        $defaultOsId = (int)Arr::get($params, 'configoption3');

        return [
            'tabOverviewReplacementTemplate' => 'templates/overview.tpl',
            'templateVariables' => [
                'data' => [
                    'ip' => Arr::get($serverResponse, 'data.ip_addresses.ipv4.0.ip'),
                    'status' => Arr::get($serverResponse, 'data.status'),
                    'operating_systems' => json_encode(ProductConfigOption::getProductOptions($productId, ProductConfigOption::OPERATING_SYSTEM)),
                    'default_os_id' => $defaultOsId,
                    'domain' => $params['domain'],
                    'boot_mode' => Arr::get($serverResponse, 'data.boot_mode'),
                ],
            ],
        ];
    } catch (Exception $exception) {
        Logger::log($params, $exception->getMessage());

        $title = Language::trans('solusiovps_exception_page_default_title');
        $message = Language::trans('solusiovps_exception_page_default_message');

        if ($params['status'] === 'Pending') {
            $title = Language::trans('solusiovps_exception_page_pending_title');
            $message = Language::trans('solusiovps_exception_page_pending_message');
        } elseif ($params['status'] === 'Cancelled') {
            $title = Language::trans('solusiovps_exception_page_cancelled_title');
            $message = Language::trans('solusiovps_exception_page_cancelled_message');
        }

        return [
            'tabOverviewReplacementTemplate' => 'error.tpl',
            'templateVariables' => [
                'title' => $title,
                'message' => $message,
            ],
        ];
    }
}

/**
 * @param array $params
 * @return array
 */
function solusiovps_TestConnection(array $params)
{
    try {
        $projectResource = new ProjectResource(Connector::create($params));

        $projectResource->list();

        return ['success' => true, 'error' => ''];
    } catch (Exception $e) {
        Logger::log([], $e->getMessage());

        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function solusiovps_AdminCustomButtonArray(array $params): array
{
    $vncUrl = Config::getSystemUrl() . 'modules/servers/solusiovps/pages/vnc.php?serviceId=' . $params['serviceid'];

    return [
        Language::trans('solusiovps_button_restart') => 'restart',
        Language::trans('solusiovps_button_vnc') => [
            'href' => "javascript:window.open('{$vncUrl}', '', 'menubar=no,location=no,resizable=yes,scrollbars=yes,status=no,width=800,height=450');",
        ],
        Language::trans('solusiovps_button_sync') => 'syncAccount',
    ];
}

function solusiovps_restart(array $params)
{
    try {
        $serviceId = (int)$params['serviceid'];
        $hosting = Hosting::getByServiceId($serviceId);
        $server = SolusServer::getByServiceId($serviceId);
        $serverId = (int)$hosting->server;
        $serverParams = Server::getParams($serverId);
        $serverResource = new ServerResource(Connector::create($serverParams));

        $serverResource->restart($server->server_id);

        return 'success';
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

function solusiovps_ListAccounts(array $params)
{
    try {
        $accounts = [];

        $serverParams = Server::getParams((int)$params['serverid']);
        $serverResource = new ServerResource(Connector::create($serverParams));

        $servers = $serverResource->list();

        foreach ($servers as $server) {
            $accounts[] = [
                'email' => $server['user']['email'],
                'username' => $server['user']['email'],
                'domain' => $server['name'],
                'uniqueIdentifier' => $server['name'],
                'product' => $server['plan']['name'],
                'primaryip' => $server['ip_addresses']['ipv4'][0]['ip'],
                'created' => Carbon::parse($server['created_at'])->format('Y-m-d H:i:s'),
                'status' => !$server['is_suspended'] ? Status::ACTIVE : Status::SUSPENDED,
            ];
        }

        return [
            'success' => true,
            'accounts' => $accounts,
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }
}

function solusiovps_syncAccount(array $params)
{
    try {
        if (!empty(SolusServer::getByServiceId($params['serviceid']))) {
            return [
                'success' => "Account is already synced",
            ];
        }

        $connector = Connector::create(Server::getParams((int)$params['serverid']));
        $userResource = new UserResource($connector);
        $solusUser = $userResource->getUserByEmail($params['clientsdetails']['email']);
        if (!$solusUser) {
            throw new Exception(Language::trans('solusiovps_error_user_not_found'));
        }

        $serverResource = new ServerResource($connector);
        $allServersOfUser = $serverResource->getAllByUser($solusUser['id']);
        foreach ($allServersOfUser as $server) {
            if ($server['name'] == $params['domain']) {
                SolusServer::create([
                    'service_id' => $params['serviceid'],
                    'server_id' => (int)Arr::get($server, 'id', []),
                    'payload' => json_encode($server),
                ]);
                return [
                    'success' => "Account has been synced correctly"
                ];
            }
        }
        return [
            'Success' => "Unable to find the service in SolusIO"
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }
}
