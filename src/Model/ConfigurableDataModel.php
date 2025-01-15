<?php

namespace TorqIT\ClassificationStoreSyndicator\Model;

use JsonSerializable;
use Pimcore\Model\DataObject\Classificationstore\KeyConfig;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConfigurableDataModel implements JsonSerializable
{
    private TranslatorInterface $translator;
    public string $keyName;
    private string $title;
    public array $titles;
    public array $options;

    public function __construct() {}

    public function jsonSerialize(): mixed
    {
        return [
            'name' => $this->keyName,
            'title' => $this->titles,
            'options' => $this->options,
        ];
    }

    public static function fromKey(KeyConfig $key, TranslatorInterface $translatorInterface): static
    {
        $locals = [
            'en_US' => 'en-US'
        ];
        $model = new static();
        $model->translator = $translatorInterface;
        $model->keyName = $key->getName();
        //Dont worry about this name is just one time
        $model->title = $key->getTitle();
        $model->titles = self::translateTitle($model->title, $locals, $model->translator);
        if ($key->getType() === 'multiselect' || $key->getType() === 'select') {
            $json = $key->getDefinition();
            // Decode the JSON string into a PHP associative array
            $data = json_decode($json, true);
            // Extract the 'values' from the 'options' array
            if (!empty($data['options'])) {
                foreach ($data['options'] as $option) {
                    $value = $option['value'];
                    $model->options[] = self::translateValue($value, $locals, $model->translator);
                }
            } else {
                $model->options = [];
                //Maybe add a log to say data was empty
            }
        } else {
            //if it isnt a multiselct or select there will be no options therfore just return empty list of values should still translate title
            $model->options = [];
            //Maybe add a log that the data type was not a multiselect
        }

        return $model;
    }

    private static function translateValue(string $val, array $locals, TranslatorInterface $translator)
    {
        $translations = [];
        foreach ($locals as $localeKey => $localeValue) {
            if ($localeKey === 'en_US') {
                $translatedVal = $translator->trans($val, [], 'attributes', $localeKey);
                $translations[$localeValue] = $translatedVal;
            } else {
                $translatedVal = $translator->trans($val, [], 'attributes', $localeKey);
                $translations[$localeValue] = ($val === $translatedVal) ? null : $translatedVal;
            }
        }
        $options = [
            'option' => $val,
            'translated_option' => $translations
        ];

        return $options;
    }
    private static function translateTitle(string $title, array $locals, TranslatorInterface $translator)
    {
        $translatedTitles = [];
        foreach ($locals as $localeKey => $localeValue) {
            if ($localeKey === 'en_US') {
                $translatedTitle = $translator->trans($title, [], 'attributes', $localeKey);
                $translatedTitles[$localeValue] = $translatedTitle;
            } else {
                $translatedTitle = $translator->trans($title, [], 'attributes', $localeKey);
                $translatedTitles[$localeValue] = ($title === $translatedTitle) ? null : $translatedTitle;
            }
        }
        return $translatedTitles;
    }
}
