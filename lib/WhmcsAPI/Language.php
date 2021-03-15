<?php

// Copyright 2020. Plesk International GmbH.

namespace WHMCS\Module\Server\SolusIoVps\WhmcsAPI;

/**
 * @package WHMCS\Module\Server\SolusIoVps\WhmcsAPI
 */
class Language
{
    const DEFAULT_LANGUAGE = 'english';

    public static function load(): void
    {
        global $CONFIG, $_LANG;

        if (isset($_SESSION['Language'])) {
            $language = $_SESSION['Language'];
        } elseif (isset($_SESSION['adminlang'])) {
            $language = $_SESSION['adminlang'];
        } else {
            $language = $CONFIG['Language'];
        }

        if (!in_array($language, self::getAvailableLanguages())) {
            $language = self::DEFAULT_LANGUAGE;
        }

        require self::getLangDir() . '/' . $language . '.php';
    }

    public static function trans(string $key): string
    {
        global $_LANG;

        return isset($_LANG[$key]) ? $_LANG[$key] : $key;
    }

    private static function getLangDir(): string
    {
        return dirname(__DIR__, 2) . '/lang';
    }

    private static function getAvailableLanguages(): array
    {
        $languages = [];

        foreach (glob(self::getLangDir() . '/*.php') as $filename) {
            $languages[] = pathinfo($filename, PATHINFO_FILENAME);
        }

        return $languages;
    }
}
