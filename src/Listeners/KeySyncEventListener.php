<?php

namespace TorqIT\ClassificationStoreSyndicator\Listeners;

use App\Service\PimSync\ClassificationStoreSyncService;
use App\Service\Translation\AttributeTranslationsService;
use Pimcore\Event\Model\DataObject\ClassificationStore\KeyConfigEvent;

class KeySyncEventListener
{
    public function __construct(private ClassificationStoreSyncService $classificationStoreSyncService) {}

    public function syncKeyConfig(KeyConfigEvent $keyConfigEvent)
    {
        $keyConfig = $keyConfigEvent->getKeyConfig();
        $this->classificationStoreSyncService->syncStoreKey($keyConfig);
    }
}
