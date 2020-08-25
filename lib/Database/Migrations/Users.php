<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\Database\Migrations;

use Illuminate\Database\Schema\Blueprint;
use WHMCS\Database\Capsule as DB;

/**
 * @package WHMCS\Module\Server\SolusIoVps\Database\Migrations
 */
class Users implements Migration
{
    const TABLE = 'solusiovps_users';

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
            $table->bigInteger('whmcs_user_id');
            $table->bigInteger('solus_user_id');
        });
    }
}
