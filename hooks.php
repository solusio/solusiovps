<?php

// Copyright 2020. Plesk International GmbH.

use WHMCS\Module\Server\SolusIoVps\WhmcsAPI\Product;

add_hook('AfterProductUpgrade', 1, function (array $params) {
    Product::upgrade($params['upgradeid']);
});
