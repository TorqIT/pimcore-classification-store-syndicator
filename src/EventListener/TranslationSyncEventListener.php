<?php

namespace TorqIT\ClassificationStoreSyndicator\EventListener;

use App\Service\ClassificationStoreService;
use App\Service\PimSync\ClassificationStoreSyncService;
use Pimcore\Model\Translation;
use App\Service\Translation\AttributeTranslationsService;
use Pimcore\Bundle\ApplicationLoggerBundle\ApplicationLogger;
use Pimcore\Event\Model\DataObject\ClassificationStore\KeyConfigEvent;
use Pimcore\Event\Model\TranslationEvent;
use Pimcore\Model\DataObject\Classificationstore\KeyConfig;

class TranslationSyncEventListener
{
    public function __construct(
        private ClassificationStoreSyncService $classificationStoreSyncService,
    ) {}

    public function syncKeyConfigTranslations(TranslationEvent $translationEvent)
    {
        $args = $translationEvent->getArguments();
        if (array_key_exists(AttributeTranslationsService::DONT_PROPAGATE, $args) && $args[AttributeTranslationsService::DONT_PROPAGATE] === AttributeTranslationsService::DONT_PROPAGATE) {
            return;
        }
        $translation = $translationEvent->getTranslation();
        $affectedKeyIds = $this->getNowSyndicatableAttributeIdsUsingTranslation($translation);
        foreach ($affectedKeyIds as $affectedKeyId) {
            $this->classificationStoreSyncService->syncStoreKey(KeyConfig::getById($affectedKeyId));
        }
    }

    private function getNowSyndicatableAttributeIdsUsingTranslation(Translation $translation)
    {
        $dbConnector = \Pimcore\Db::get();

        $sql = <<<SQL
            SELECT matchingAttributeKeyAndTranslation.TranslationKey, matchingAttributeKeyAndTranslation.ClassStoreKeyId, matchingAttributeKeyAndTranslation.ClassStoreId
            FROM (
                SELECT translationAttributes.key as TranslationKey, translationAttributes.language as TranslationLanguage, classStoreKeyOptions.id as ClassStoreKeyId, classStoreKeyOptions.storeId as ClassStoreId
                FROM translations_attributes translationAttributes
                INNER JOIN (
                    SELECT id, name, storeId, type, options.key, options.value
                    FROM classificationstore_keys,
                        JSON_TABLE(definition, '$."options"[*]' COLUMNS (
                            `key` VARCHAR(140) PATH '$.key',
                            `value` VARCHAR(140) PATH '$.value')
                        ) options
                ) classStoreKeyOptions
                ON classStoreKeyOptions.value = translationAttributes.key
                UNION 
                SELECT translationAttributes.key, translationAttributes.language, classStoreKeys.id, classStoreKeys.storeId
                FROM translations_attributes translationAttributes
                INNER JOIN classificationstore_keys classStoreKeys
                ON classStoreKeys.name = translationAttributes.key
            ) as matchingAttributeKeyAndTranslation
            WHERE matchingAttributeKeyAndTranslation.TranslationKey = :translationKey AND matchingAttributeKeyAndTranslation.ClassStoreId IN (:classStoreIdList)
            GROUP BY matchingAttributeKeyAndTranslation.TranslationKey, matchingAttributeKeyAndTranslation.ClassStoreKeyId, matchingAttributeKeyAndTranslation.ClassStoreId
        SQL;

        $resultQuery = $dbConnector->executeQuery(
            $sql,
            [
                'translationKey' => $translation->getKey(),
                'classStoreIdList' => ClassificationStoreService::getSyncableClassificationStoreIds()

            ],
            ['classStoreIdList' => \Doctrine\DBAL\ArrayParameterType::INTEGER]
        );

        $data = $resultQuery->fetchAllAssociative();

        return array_column($data, 'ClassStoreKeyId');
    }
}
