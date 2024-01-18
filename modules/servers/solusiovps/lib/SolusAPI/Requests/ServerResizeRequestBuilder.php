<?php

// Copyright 1999-2024. WebPros International GmbH. All rights reserved.

namespace WHMCS\Module\Server\SolusIoVps\SolusAPI\Requests;

use WHMCS\Module\Server\SolusIoVps\Helpers\Arr;

class ServerResizeRequestBuilder
{
    /**
     * @var int
     */
    private $planId;

    /**
     * @var ?array
     */
    private $customPlanData;

    private function __construct(int $planId)
    {
        $this->planId = $planId;
    }

    public static function fromWHMCSUpgradeDowngradeParams(array $params): self
    {
        $planId = (int)Arr::get($params, 'configoption1');
        $builder = new self($planId);

        $customPlanData = CustomPlanData::fromModuleParams($params);
        if ($customPlanData) {
            $builder->withCustomPlan($customPlanData);
        }

        return $builder;
    }

    public function get(): array
    {
        $request = [
            'plan_id' => $this->planId,
            'preserve_disk' => !isset($this->customPlanData['params']['disk']),
        ];

        if ($this->customPlanData) {
            $request['custom_plan'] = $this->customPlanData;
        }

        return $request;
    }

    public function withCustomPlan(array $customPlanData): self
    {
        $this->customPlanData = $customPlanData;

        return $this;
    }
}