<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\Traits;

use WHMCS\Module\Server\SolusIoVps\Helpers\Arr;

/**
 * Trait ListRequest
 * @package WHMCS\Module\Server\SolusIoVps\SolusAPI\Resources\Traits
 */
trait ListRequest
{
    public function list(): array
    {
        $currentPage = 1;
        $results = [];

        do {
            $response = $this->processResponse($this->connector->get(
                sprintf('%s?page=%d', self::ENTITY, $currentPage)
            ));
            $lastPage = (int) Arr::get(
                $response,
                'meta',
                ['last_page' => $currentPage]
            )['last_page'];

            $results = array_merge($results, Arr::get($response, 'data', []));
            $currentPage++;
        } while ($currentPage <= $lastPage);

        return $results;
    }
}
