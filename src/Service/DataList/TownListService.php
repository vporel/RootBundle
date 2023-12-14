<?php

namespace RootBundle\Service\DataList;

use RootBundle\RootBundle as RootBundle;

class TownListService extends AbstractDataListService{
    protected function getFilePath(): string
    {
        return RootBundle::resourcesPath() . "/datalists/towns.json";
    }
}