<?php

// Copyright 2020. Plesk International GmbH.

include_once(__DIR__ . DIRECTORY_SEPARATOR . '../../../vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

use Carbon\Carbon;
use GuzzleHttp\Exception\RequestException;
use WHMCS\Module\Server\SolusIoVps\Exceptions\SolusException;
use WHMCS\Module\Server\SolusIoVps\Helpers\Arr;
use WHMCS\Module\Server\SolusIoVps\Helpers\Unit;
use WHMCS\Module\Server\SolusIoVps\Logger\Logger;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Helpers\Strings;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Requests\ConfigOptionExtractor;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Requests\ServerCreateRequestBuilder;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Requests\ServerResizeRequestBuilder;
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
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\BackupResource;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\UsageResource;
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
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Product;
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
        $serverData->withUser($solusUserId);
        $sshKey = Strings::convertToSshKey($params['customfields'][SolusSshKey::CUSTOM_FIELD_SSH_KEY] ?? '');

        if (!empty($sshKey)) {
            $sshKeyId = SshKey::create($params, $sshKey, $solusUserId);
            $serverData->withSshKeys([ $sshKeyId ]);
        }

        $serverResource = new ServerResource(Connector::create($params));

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
    if (isset($_GET['a'])) {
        $functionName = 'solusiovps_' . $_GET['a'];
        if (function_exists($functionName)) {
            $functionName($params);
        } else {
            $result = (object)array(
                'success' => false,
                'msg' => $functionName . ' not found',
            );
            exit(json_encode($result));
        }
    }

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
        $defaultApplicationId = (int)Arr::get($params, 'configoption4');

        $applicationOptions = ProductConfigOption::getProductOptions($productId, ProductConfigOption::APPLICATION);

        $applicationResource = new ApplicationResource(Connector::create($params));
        $applications = [];
        foreach ($applicationResource->list() as $item) {
            $id = (int)Arr::get($item, 'id');
            if (isset($applicationOptions[$id]) || $id === $defaultApplicationId) {
                $schema = json_decode(Arr::get($item, 'json_schema'), true);
                foreach ($schema['required'] as $property) {
                    $schema['properties'][$property]['required'] = true;
                }
                $applications[$id] = [
                    'name' => Arr::get($item, 'name'),
                    'schema' => $schema,
                ];
            }
        }

        $totalTraffic = Unit::convert(
            Arr::get($serverResponse, 'data.usage.network.incoming.value') +
            Arr::get($serverResponse, 'data.usage.network.outgoing.value'),
            Arr::get($serverResponse, 'data.plan.limits.network_total_traffic.unit')
        );

        return [
            'tabOverviewReplacementTemplate' => 'templates/overview.tpl',
            'templateVariables' => [
                'data' => [
                    'ip' => Arr::get($serverResponse, 'data.ip_addresses.ipv4.0.ip'),
                    'status' => Arr::get($serverResponse, 'data.status'),
                    'operating_systems' => json_encode(
                        ProductConfigOption::getProductOptions($productId, ProductConfigOption::OPERATING_SYSTEM)
                    ),
                    'default_os_id' => $defaultOsId,
                    'applications' => json_encode($applications),
                    'default_application_id' => $defaultApplicationId,
                    'domain' => $params['domain'],
                    'boot_mode' => Arr::get($serverResponse, 'data.boot_mode'),
                    'traffic_current' => $totalTraffic,
                    'traffic_limit' => Arr::get($serverResponse, 'data.plan.limits.network_total_traffic.is_enabled')
                        ? Arr::get($serverResponse, 'data.plan.limits.network_total_traffic.limit')
                        : null,
                    'traffic_unit' => Arr::get($serverResponse, 'data.plan.limits.network_total_traffic.unit'),
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

function solusiovps_ChangePackage(array $params)
{
    try {
        if ($_REQUEST['type'] !== 'configoptions') {
            return 'success';
        }

        $server = SolusServer::getByServiceId((int)$params['serviceid']);
        if ($server === null) {
            return Language::trans('solusiovps_error_server_not_found');
        }
        $solusServerId = $server->server_id;
        $serverResource = new ServerResource(Connector::create($params));

        // Handle plan params
        $requestBuilder = ServerResizeRequestBuilder::fromWHMCSUpgradeDowngradeParams($params);
        $serverResource->resize($solusServerId, $requestBuilder->get());

        // Handle additional IPs
        $additionalIpCount = ConfigOptionExtractor::extractFromModuleParams($params, ProductConfigOption::EXTRA_IP_ADDRESS);

        if ($additionalIpCount !== null) {
            $additionalIpCount = (int)$additionalIpCount;
            $solusServer = $serverResource->get($solusServerId);
            $serverAdditionalIps = array_filter(Arr::get($solusServer, 'data.ip_addresses.ipv4'), function (array $ip) {
                return $ip['is_primary'] === false;
            });
            $serverAdditionalIpCount = count($serverAdditionalIps);

            if ($additionalIpCount > $serverAdditionalIpCount) {
                $needIpCount = $additionalIpCount - $serverAdditionalIpCount;

                $serverResource->createAdditionalIps($solusServerId, $needIpCount);
            } elseif ($additionalIpCount < $serverAdditionalIpCount) {
                // Remove IPs from the end
                $reversedServerAdditionalIps = array_reverse($serverAdditionalIps);
                $reversedServerAdditionalIpsForDelete = array_slice(
                    $reversedServerAdditionalIps,
                    0,
                    $serverAdditionalIpCount - $additionalIpCount
                );
                $ipIdsForDelete = array_map(static function (array $additionalIp) {
                    return $additionalIp['id'];
                }, $reversedServerAdditionalIpsForDelete);

                $serverResource->deleteAdditionalIps($solusServerId, $ipIdsForDelete);
            }
        }

        return 'success';
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

function solusiovps_ChangeHostName(array $params)
{
    $serviceId = (int) $params['serviceid'];
    $hostname = $_GET['hostname'];
    $hosting = Hosting::getByServiceId($serviceId);

    $server = SolusServer::getByServiceId($serviceId);
    $serverId = (int) $hosting->server;
    $serverParams = Server::getParams($serverId);
    $serverResource = new ServerResource(Connector::create($serverParams));
    try {
        $serverResource->changeHostname($server->server_id, $hostname);
        Product::updateDomain($serviceId, $hostname);

        exit(Language::trans('solusiovps_hostname_changed'));
    } catch (Exception $e) {
        exit(Language::trans('solusiovps_error_change_hostname') . "\n" . 'Error: ' . $e->getMessage());
    }
}

function solusiovps_ResetRootPass(array $params)
{

    $serviceId = (int) $params['serviceid'];

    $hosting = Hosting::getByServiceId($serviceId);

    $server = SolusServer::getByServiceId($serviceId);
    $serverId = (int) $hosting->server;
    $serverParams = Server::getParams($serverId);
    $payload = json_decode($server->payload, true);
    $solusUserId = (int) $payload['user']['id'];
    $userResource = new UserResource(Connector::create($serverParams));
    $userApiToken = $userResource->createToken($solusUserId);
    $serverResource = new ServerResource(Connector::create($serverParams, $userApiToken));

    $serverResource->resetPassword($server->server_id);
    exit(Language::trans('solusiovps_password_reset_success'));
}

function solusiovps_ChangeBootMode(array $params)
{
    $serviceId = (int) $params['serviceid'];
    $bootMode = $_GET['bootMode'];

    $hosting = Hosting::getByServiceId($serviceId);

    $server = SolusServer::getByServiceId($serviceId);
    $serverId = (int) $hosting->server;
    $serverParams = Server::getParams($serverId);
    $serverResource = new ServerResource(Connector::create($serverParams));
    $serverResource->changeBootMode($server->server_id, $bootMode);
}

function solusiovps_CreateBackup(array $params)
{
    $serviceId = (int) $params['serviceid'];

    $hosting = Hosting::getByServiceId($serviceId);

    $server = SolusServer::getByServiceId($serviceId);
    $serverId = (int) $hosting->server;
    $serverParams = Server::getParams($serverId);
    $backupResource = new BackupResource(Connector::create($serverParams));

    $backupResource->create($server->server_id);
}

function solusiovps_GetBackups(array $params)
{
    $serviceId = (int) $params['serviceid'];
    $hosting = Hosting::getByServiceId($serviceId);

    $server = SolusServer::getByServiceId($serviceId);
    $serverId = (int) $hosting->server;
    $serverParams = Server::getParams($serverId);
    $backupResource = new BackupResource(Connector::create($serverParams));
    $response = $backupResource->getAll($server->server_id);
    $backups = [];

    if (isset($response['data']) && is_array($response['data'])) {
        foreach ($response['data'] as $item) {
            $progress = (int) $item['backup_progress'];
            $status = $item['status'];

            if ($progress > 0) {
                $status .= " {$progress}%";
            }

            $time = new DateTimeImmutable($item['created_at']);

            $backups[] = [
                'id' => $item['id'],
                'status' => $status,
                'message' => $item['backup_fail_reason'] ?? '',
                'time' => $time->format('Y-m-d H:i'),
            ];
        }
    }
    exit(json_encode($backups));
}

function solusiovps_RestoreBackup(array $params)
{
    $serviceId = (int) $params['serviceid'];
    $backupId = (int) $_GET['backupId'];

    $hosting = Hosting::getByServiceId($serviceId);
    $serverId = (int) $hosting->server;
    $serverParams = Server::getParams($serverId);
    $backupResource = new BackupResource(Connector::create($serverParams));

    $backupResource->restore($backupId);
}


function solusiovps_Reinstall(array $params)
{
    $serviceId = (int) $params['serviceid'];
    $osId = (int) $_GET['osId'];
    $applicationId = (int) $_GET['applicationId'];
    $applicationData = $_GET['applicationData'] ?? [];

    $hosting = Hosting::getByServiceId($serviceId);
    $server = SolusServer::getByServiceId($serviceId);
    $serverId = (int) $hosting->server;
    $serverParams = Server::getParams($serverId);
    $serverResource = new ServerResource(Connector::create($serverParams));

    $serverResource->reinstall($server->server_id, $osId, $applicationId, $applicationData);
}

function solusiovps_Stop(array $params)
{
    $serviceId = (int) $params['serviceid'];

    $hosting = Hosting::getByServiceId($serviceId);

    $server = SolusServer::getByServiceId($serviceId);
    $serverId = (int)$hosting->server;
    $serverParams = Server::getParams($serverId);
    $serverResource = new ServerResource(Connector::create($serverParams));

    $serverResource->stop($server->server_id);
}

function solusiovps_Start(array $params)
{
    $serviceId = (int) $params['serviceid'];
    $hosting = Hosting::getByServiceId($serviceId);

    $server = SolusServer::getByServiceId($serviceId);
    $serverId = (int) $hosting->server;
    $serverParams = Server::getParams($serverId);
    $serverResource = new ServerResource(Connector::create($serverParams));

    $serverResource->start($server->server_id);
}

function solusiovps_Restart(array $params)
{
    try {
        $serviceId = (int) $params['serviceid'];
        $hosting = Hosting::getByServiceId($serviceId);

        $server = SolusServer::getByServiceId($serviceId);
        $serverId = (int) $hosting->server;
        $serverParams = Server::getParams($serverId);
        $serverResource = new ServerResource(Connector::create($serverParams));

        $serverResource->restart($server->server_id);

        return 'success';
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

function solusiovps_Usage(array $params)
{
    $serviceId = (int) $params['serviceid'];

    $hosting = Hosting::getByServiceId($serviceId);

    $server = SolusServer::getByServiceId($serviceId);
    $serverId = (int) $hosting->server;
    $serverParams = Server::getParams($serverId);
    $payload = json_decode($server->payload, true);
    $uuid = $payload['uuid'];

    $usageResource = new UsageResource(Connector::create($serverParams));
    $cpuUsage = $usageResource->cpu($uuid);
    $networkUsage = $usageResource->network($uuid);
    $diskUsage = $usageResource->disks($uuid);
    $memoryUssage = $usageResource->memory($uuid);

    $usage = [
        'cpu' => [],
        'network' => [],
        'disk' => [],
        'memory' => [],
    ];

    foreach ($cpuUsage['data']['items'] as $item) {
        $usage['cpu'][] = [
            'second' => date('H:i:s', strtotime($item['time'])),
            'load_average' => $item['load_average'],
        ];
    }

    foreach ($networkUsage['data']['items'] as $item) {
        $usage['network'][] = [
            'second' => date('H:i:s', strtotime($item['time'])),
            'read_kb' => $item['derivative']['read_kb'],
            'write_kb' => $item['derivative']['write_kb'],
        ];
    }

    foreach ($diskUsage['data']['items'] as $item) {
        $usage['disk'][] = [
            'second' => date('H:i:s', strtotime($item['time'])),
            'read_kb' => $item['derivative']['read_kb'],
            'write_kb' => $item['derivative']['write_kb'],
        ];
    }

    foreach ($memoryUssage['data']['items'] as $item) {
        $usage['memory'][] = [
            'second' => date('H:i:s', strtotime($item['time'])),
            'memory' => $item['memory'],
        ];
    }

    exit(json_encode($usage));
}

function solusiovps_VNC(array $params)
{
    $serviceId = (int) $params['serviceid'];

    $hosting = Hosting::getByServiceId($serviceId);
    $solusServer = SolusServer::getByServiceId($serviceId);
    $serverId = (int)$hosting->server;
    $serverParams = Server::getParams($serverId);
    $serverResource = new ServerResource(Connector::create($serverParams));
    $server = $serverResource->get($solusServer->server_id);
    $password = $server['data']['settings']['vnc_password'] ?? $server['data']['settings']['vnc']['password'];
    $response = $serverResource->vncUp($solusServer->server_id);
    $url = 'wss://' . $serverParams['serverhostname'] . '/vnc?url=' . $response['url'];

    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <title>VNC</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            body, html {
                height: 100%;
                min-height: 100%;
                padding: 0;
                margin: 0;
            }

            a:hover {
                text-decoration: underline;
            }

            #screen {
                height: calc(100% - 60px);
            }

            .top-bar {
                background: rgb(255, 255, 255);
                display: flex;
                align-items: center;
                padding: 20px;
                max-height: 20px;
            }

            .container {
                height: inherit;
                display: flex;
                flex-direction: column;
            }

            #ctrl-alt-del {
                font-weight: 600;
                display: flex;
                align-items: center;
                color: black;
                text-decoration: none;
            }
        </style>
        <script type="module" crossorigin="anonymous">
            import RFB from '../modules/servers/solusiovps/node_modules/@novnc/novnc/core/rfb.js';

            const url = <?= json_encode($url) ?>;
            const password = <?= json_encode($password) ?>;
            const options = {
                credentials: {
                    password: password
                }
            };

            const rfb = new RFB(
                document.getElementById('screen'),
                url,
                options
            );

            rfb.scaleViewport = true;
            rfb.resizeSession = true;
            rfb.focusOnClick = true;

            document.querySelector('#ctrl-alt-del').addEventListener('click', () =>  {
                rfb.sendCtrlAltDel();
            });
        </script>
    </head>
    <body>
    <div class="container">
        <div class="top-bar">
            <a href="#" id="ctrl-alt-del">
                Ctrl + Alt + Del
            </a>
        </div>
        <div id="screen"></div>
    </div>
    </body>
    </html>
    <?php
    exit();
}

function solusiovps_Status(array $params)
{
    $serviceId = (int) $params['serviceid'];
    $hosting = Hosting::getByServiceId($serviceId);

    $server = SolusServer::getByServiceId($serviceId);
    $serverId = (int) $hosting->server;
    $serverParams = Server::getParams($serverId);
    $serverResource = new ServerResource(Connector::create($serverParams));
    $serverResponse = $serverResource->get($server->server_id);

    exit($serverResponse['data']['status']);
}
