<?php

namespace TorqIT\ClassificationStoreSyndicatorBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\BundleAdminClassicTrait;
use Pimcore\Extension\Bundle\PimcoreBundleAdminClassicInterface;

class ClassificationStoreSyndicatorBundle extends AbstractPimcoreBundle implements PimcoreBundleAdminClassicInterface
{
    use BundleAdminClassicTrait;
    public function getInstaller(): ?InstallerInterface
    {
        return $this->container->get(ClassificationStoreSyndicatorBundleInstaller::class);
    }
}
