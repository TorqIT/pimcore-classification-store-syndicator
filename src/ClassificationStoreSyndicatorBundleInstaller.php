<?php

namespace TorqIT\ClassificationStoreSyndicatorBundle;

use Pimcore\Extension\Bundle\Installer\SettingsStoreAwareInstaller;

class ClassificationStoreSyndicatorBundleInstaller extends SettingsStoreAwareInstaller
{
    public function install(): void
    {
        //create sync status tables
        $this->markInstalled();
    }

    public function uninstall(): void
    {
        //delete sync status tables
        $this->markUninstalled();
    }
}
