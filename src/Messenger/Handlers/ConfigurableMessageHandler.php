<?php

namespace TorqIT\ClassificationStoreSyndicator\Messenger\Handlers;

use TorqIT\ClassificationStoreSyndicator\Messenger\Messages\ConfigurableSyncMessage;

class ConfigurableMessageHandler
{
    public function __invoke(ConfigurableSyncMessage $message)
    {
        //send to p21 api
    }
}
