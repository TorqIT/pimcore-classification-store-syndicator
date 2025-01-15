<?php

namespace TorqIT\ClassificationStoreSyndicator\Messenger\Message;

class ConfigurableSyncMessage
{
    public function __construct(
        private string $data,
    ) {}

    /**
     * Get the value of data
     */
    public function getData()
    {
        return $this->data;
    }
}
