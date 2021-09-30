<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\Database\Migrations;

use Illuminate\Database\Schema\Blueprint;
use WHMCS\Database\Capsule as DB;

/**
 * Class Servers
 * @package WHMCS\Module\Server\SolusIoVps\Database\Migrations
 */
class Servers implements Migration
{
    const TABLE = 'solusiovps_servers';

    /**
     * @inheritDoc
     */
    public static function run()
    {
        if (DB::schema()->hasTable(self::TABLE)) {
            return;
        }

        DB::schema()->create(self::TABLE, static function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('service_id');
            $table->bigInteger('server_id');
            $table->longText('payload');
        });
    }
}
