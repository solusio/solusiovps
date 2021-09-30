<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\Database\Migrations;

/**
 * Interface Migration
 * @package WHMCS\Module\Server\SolusIoVps\Database\Migrations
 */
interface Migration
{
    /**
     * Run the database migration
     */
    public static function run();
}
