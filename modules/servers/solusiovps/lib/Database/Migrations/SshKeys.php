<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\Database\Migrations;

use Illuminate\Database\Schema\Blueprint;
use WHMCS\Database\Capsule as DB;

/**
 * @package WHMCS\Module\Server\SolusIoVps\Database\Migrations
 */
class SshKeys implements Migration
{
    const TABLE = 'solusiovps_ssh_keys';

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
            $table->bigInteger('solus_key_id');
            $table->string('key_hash', 50);
        });
    }
}
