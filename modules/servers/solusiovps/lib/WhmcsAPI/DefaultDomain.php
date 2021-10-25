<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\WhmcsAPI;

use WHMCS\Module\Server\SolusIoVps\Helpers\Arr;

class DefaultDomain
{
    /**
     * @var bool
     */
    private $enabled;
    /**
     * @var string
     */
    private $mask;

    public function __construct(bool $enabled, string $mask)
    {
        $this->enabled = $enabled;
        $this->mask = $mask;
    }

    public static function createFromConfig(array $config): self
    {
        return new self(
            Arr::get($config, 'default_domain.enabled', false),
            Arr::get($config, 'default_domain.mask', '*.domain.tld'),
        );
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getDomainForName(string $serviceName): string
    {
        return str_replace('*', $serviceName, $this->mask);
    }
}
