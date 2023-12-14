<?php
namespace RootBundle\Faker\Provider;

use RootBundle\Service\DataList\TownListService;
use Faker\Provider\Base;

/**
 * @author Vivian NKOUANANG (https://github.com/vporel) <dev.vporel@gmail.com>
 */
class TownProvider extends Base{
    public function __construct(private TownListService $townListService)
    {}
    public function town(){
        return $this->randomElement($this->townListService->getList());
    }

}