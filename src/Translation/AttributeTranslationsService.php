<?php

namespace App\Service\Translation;

use Pimcore\Model\DataObject\Classificationstore\KeyConfig;
use Pimcore\Model\Translation;
use Pimcore\Tool;

class AttributeTranslationsService
{
    public const DONT_PROPAGATE = 'dont_propagate';

    public static string $ATTRIBUTES_TRANSLATION_STORE_NAME = 'attributes';
    public static string $CLASS_TRANSLATION_STORE_NAME = 'class_options';
    public const ENGLISH_LANGUAGE = 'en_US';
    public const FRENCH_LANGUAGE = 'fr';
    public const GERMAN_LANGUAGE = 'de';
    public const ITALIAN_LANGUAGE = 'it';
    public const CANADA_LANGUAGE = 'en_CA';
    public const FRENCH_CANADA_LANGUAGE = 'fr_CA';
    public static array $availableLanguages = [self::ENGLISH_LANGUAGE, self::CANADA_LANGUAGE, self::FRENCH_CANADA_LANGUAGE, self::GERMAN_LANGUAGE, self::ITALIAN_LANGUAGE];

    public function addKeyConfigToTranslationsStore(KeyConfig $keyConfig)
    {
        $this->addAttributeTranslationIfNotExists($keyConfig->getName(), $keyConfig->getTitle());

        $keyConfigDefinition = json_decode($keyConfig->getDefinition(), true);
        if (array_key_exists('options', $keyConfigDefinition)) {
            foreach ($keyConfigDefinition['options'] as $option) {
                $this->addAttributeTranslationIfNotExists($option['value'], $option['key']);
            }
        }

        if (array_key_exists('noLabel', $keyConfigDefinition)) {
            $this->addAttributeTranslationIfNotExists($keyConfigDefinition['noLabel'], $keyConfigDefinition['noLabel']);
        }

        if (array_key_exists('yesLabel', $keyConfigDefinition)) {
            $this->addAttributeTranslationIfNotExists($keyConfigDefinition['yesLabel'], $keyConfigDefinition['yesLabel']);
        }

        if (array_key_exists('emptyLabel', $keyConfigDefinition)) {
            $this->addAttributeTranslationIfNotExists($keyConfigDefinition['emptyLabel'], $keyConfigDefinition['emptyLabel']);
        }

        if ($keyConfig->getType() == 'checkbox') {
            $this->addAttributeTranslationIfNotExists('Yes', 'Yes');
            $this->addAttributeTranslationIfNotExists('No', 'No');
        }
    }

    private function addAttributeTranslationIfNotExists(string $key, $defaultEnglishValue = '')
    {
        $this->addTranslationIfNotExists($key, $defaultEnglishValue);
    }

    /**
     * Add translation if it doesn't exist. This will also default the english value. If the default english values
     * is a number, than the french will be populated with the english default value as well.
     */
    public function addTranslationIfNotExists(string $key, $defaultEnglishValue = '', $store = null)
    {
        if (!$store) {
            $store = static::$ATTRIBUTES_TRANSLATION_STORE_NAME;
        }
        $locals = static::$availableLanguages;
        if ($translation = self::getAttributeTranslation($key)) {
            foreach ($locals as $local) {
                $translationLocal = $translation->getTranslation($local);
                if ($defaultEnglishValue && ($translationLocal === null || $translationLocal === '')) {
                    $translation->addTranslation($local, $defaultEnglishValue);
                    $translation->save([self::DONT_PROPAGATE => self::DONT_PROPAGATE]);
                }
            }

            return;
        }
        $translation = new Translation();
        $translation->setDomain($store);
        $translation->setKey($key);

        foreach (Tool::getValidLanguages() as $lang) {
            $defaultValue = '';
            if ($lang == self::ENGLISH_LANGUAGE) {
                $defaultValue = $defaultEnglishValue;
            } //elseif ($lang == self::FRENCH_LANGUAGE) {
            //$defaultValue = is_numeric($defaultEnglishValue) ? $defaultEnglishValue : '';
            //}

            $translation->addTranslation($lang, $defaultValue);
        }

        $translation->save([self::DONT_PROPAGATE => self::DONT_PROPAGATE]);
    }

    public static function getAttributeTranslation(?string $key): ?Translation
    {
        return $key ? Translation::getByKey($key, self::$ATTRIBUTES_TRANSLATION_STORE_NAME) : null;
    }
}
