<?php

// Copyright 2021. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\SolusAPI\Requests;

use WHMCS\Module\Server\SolusIoVps\Database\Models\ProductConfigOption;
use WHMCS\Module\Server\SolusIoVps\Database\Models\SolusSshKey;
use WHMCS\Module\Server\SolusIoVps\Helpers\Arr;
use WHMCS\Module\Server\SolusIoVps\SolusAPI\Helpers\Strings;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\DefaultDomain;
use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Config;

final class ServerCreateRequestBuilder
{
    /**
     * @var string $domain
     */
    private $domain;

    /**
     * @var string $password
     */
    private $password;

    /**
     * @var int $serviceId
     */
    private $serviceId;

    /**
     * @var int $planId
     */
    private $planId;

    /**
     * @var int $locationId
     */
    private $locationId;

    /**
     * @var int $applicationId
     */
    private $applicationId;

    /**
     * @var int $osId
     */
    private $osId;

    /**
     * @var array $applicationData
     */
    private $applicationData;

    /**
     * @var string $userData
     */
    private $userData;

    /**
     * @var bool $isBackupsEnabled
     */
    private $isBackupsEnabled;

    /**
     * @var array $sshKeys
     */
    private $sshKeys;

    /**
     * @var ?int
     */
    private $userId;

    /**
     * @var ?array
     */
    private $customPlanData;

    public function __construct(
        $domain,
        $password,
        $serviceId,
        $planId,
        $locationId,
        $isBackupsEnabled
    )
    {
        $this->domain = $domain;
        $this->password = $password;
        $this->serviceId = $serviceId;
        $this->planId = $planId;
        $this->locationId = $locationId;
        $this->isBackupsEnabled = $isBackupsEnabled;
    }

    public static function fromWHMCSCreateAccountParams(array $params): self
    {
        $locationId = (int)$params['configoptions'][ProductConfigOption::LOCATION];

        if ($locationId === 0) {
            $locationId = (int)Arr::get($params, 'configoption2');
        }

        $builder = new self(
            Arr::get($params, 'domain', ''),
            $params['password'],
            (int)$params['serviceid'],
            (int)Arr::get($params, 'configoption1'),
            $locationId,
            Arr::get($params, 'configoption6') === 'on',
        );

        $appId = (int)Arr::get($params, 'configoption4');
        if ($appId > 0) {
            $builder->withApplication(
                $appId,
                Arr::except($params['customfields'], SolusSshKey::CUSTOM_FIELD_SSH_KEY),
            );
        } else {
            $osId = (int)Arr::get($params['configoptions'], ProductConfigOption::OPERATING_SYSTEM);

            if ($osId === 0) {
                $osId = (int)Arr::get($params, 'configoption3');
            }

            $userData = Arr::get($params, 'configoption5');

            if ($userData !== '') {
                $userData = Strings::convertToUserData($userData);
            }

            $builder->withOperatingSystem($osId, $userData);
        }

        $customPlanData = self::extractCustomPlanData($params);
        if ($customPlanData) {
            $builder->withCustomPlan($customPlanData);
        }

        return $builder;
    }

    public function withApplication(int $id, array $appData): self
    {
        $this->applicationId = $id;
        $this->applicationData = $appData;

        return $this;
    }

    public function withOperatingSystem(int $id, string $userData): self
    {
        $this->osId = $id;
        $this->userData = $userData;

        return $this;
    }

    public function withSshKeys(array $keys): self
    {
        $this->sshKeys = $keys;

        return $this;
    }

    public function withUser(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function withCustomPlan(array $customPlanData): self
    {
        $this->customPlanData = $customPlanData;

        return $this;
    }

    public function get(): array
    {
        $name = "vps-{$this->serviceId}";
        $request = [
            'name' => $name,
            'plan' => $this->planId,
            'location' => $this->locationId,
            'password' => $this->password,
        ];

        $domainConfig = DefaultDomain::createFromConfig(Config::loadModuleConfig());

        if (!empty($this->domain)) {
            $request['name'] = $this->domain;
            $request['fqdns'] = [ $this->domain ];
        } else if ($domainConfig->isEnabled()) {
            $domain = $domainConfig->getDomainForName($name);
            $request['name'] = $domain;
            $request['fqdns'] = [ $domain ];
        }

        if ($this->isBackupsEnabled) {
            $request['backup_settings'] = [
                'enabled' => true,
                'schedule' => [
                    'type' => 'daily',
                    'time' => [
                        'hour' => 0,
                        'minutes' => 0,
                    ],
                ],
            ];
        }

        if (!empty($this->applicationId)) {
            $request['application'] = $this->applicationId;
            $request['application_data'] = $this->applicationData;
        } else if (!empty($this->osId)) {
            $request['os'] = $this->osId;
            if (!empty($this->userData)) {
                $request['user_data'] = $this->userData;
            }
        }

        if (!empty($this->sshKeys)) {
            $request['ssh_keys'] = $this->sshKeys;
        }

        if ($this->userId) {
            $request['user'] = $this->userId;
        }

        if ($this->customPlanData) {
            $request['custom_plan'] = $this->customPlanData;
        }

        return $request;
    }

    private static function extractCustomPlanData(array $params): ?array
    {
        $customPlanData = [];

        if ($vcpu = self::getConfigOption($params, ProductConfigOption::VCPU)) {
            $customPlanData['params']['vcpu'] = (int)$vcpu;
        }
        if ($ram = self::getConfigOption($params, ProductConfigOption::MEMORY)) {
            $customPlanData['params']['ram'] = $ram * 1024 * 1024;
        }
        if ($disk = self::getConfigOption($params, ProductConfigOption::DISK_SPACE)) {
            $customPlanData['params']['disk'] = (int)$disk;
        }
        if ($vcpuUnits = self::getConfigOption($params, ProductConfigOption::VCPU_UNITS)) {
            $customPlanData['params']['vcpu_units'] = (int)$vcpuUnits;
        }
        if ($vcpuLimit = self::getConfigOption($params, ProductConfigOption::VCPU_LIMIT)) {
            $customPlanData['params']['vcpu_limit'] = (int)$vcpuLimit;
        }
        if ($ioPriority = self::getConfigOption($params, ProductConfigOption::IO_PRIORITY)) {
            $customPlanData['params']['io_priority'] = (int)$ioPriority;
        }
        if ($swap = self::getConfigOption($params, ProductConfigOption::SWAP)) {
            $customPlanData['params']['swap'] = $swap * 1024 * 1024;
        }
        if ($totalTrafficLimitMonthly =
            self::getConfigOption($params, ProductConfigOption::TOTAL_TRAFFIC_LIMIT_MONTHLY)
        ) {
            $customPlanData['limits']['network_total_traffic'] = [
                'limit' => (int)$totalTrafficLimitMonthly,
            ];
        }

        return count($customPlanData) > 0 ? $customPlanData : null;
    }

    private static function getConfigOption(array $params, string $optionName)
    {
        return Arr::get($params, sprintf('configoptions.%s', $optionName));
    }
}
