<?php

namespace TorqIT\ClassificationStoreSyndicator\Service\PimSync;

use App\Messenger\Message\ConfigurableSyncMessage;
use App\Model\ConfigModels\ConfigurableDataModel;
use Pimcore\Bundle\ApplicationLoggerBundle\ApplicationLogger;
use Pimcore\Db;
use Pimcore\Model\DataObject\Classificationstore\KeyConfig;
use Pimcore\Translation\Translator;
use Symfony\Component\Messenger\MessageBusInterface;

class ClassificationStoreSyncService
{
    public function __construct(
        private Translator $translator,
        private MessageBusInterface $messageBusInterface,
        private ApplicationLogger $applicationLogger,
    ) {}

    public function syncStoreKey(KeyConfig $keyConfig)
    {
        $db = Db::get();
        $db->beginTransaction();
        try {
            $this->messageBusInterface->dispatch(new ConfigurableSyncMessage(json_encode(ConfigurableDataModel::fromKey($keyConfig, $this->translator))));
            $db->executeStatement("INSERT INTO classification_store_sync_status SET `key` = ?, `status` = 'pending' ON DUPLICATE KEY UPDATE `status` = 'pending'", [$keyConfig->getId()]);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->applicationLogger->error("Failed to dispatch classification store sync message: " . $e->getMessage() . "\n\nstack: " . $e->getTraceAsString());
        }
        $db->close();
    }
}
