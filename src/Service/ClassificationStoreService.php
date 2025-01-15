<?php

namespace TorqIT\ClassificationStoreSyndicator\Service;

use Pimcore\Model\DataObject\Classificationstore\StoreConfig;

class ClassificationStoreService
{
    public const FAMILY_CLASSIFICATION_STORE = "Family Configuration";
    public const VARIANT_CLASSIFICATION_STORE = "Variant Configuration";
    public static function getSyncableClassificationStoreIds()
    {
        $storeIds = [];
        if ($store = StoreConfig::getByName(self::FAMILY_CLASSIFICATION_STORE)) {
            $storeIds[] = $store->getId();
        }
        if ($store = StoreConfig::getByName(self::VARIANT_CLASSIFICATION_STORE)) {
            $storeIds[] = $store->getId();
        }
        return $storeIds;
    }
}
