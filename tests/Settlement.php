<?php

namespace SimaLand\DeliveryCalculator\tests;

use SimaLand\DeliveryCalculator\SettlementTrait;

class Settlement implements \SimaLand\DeliveryCalculator\SettlementInterface
{
    use SettlementTrait;

    protected $params = [];

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function getID() : int
    {
        if (array_key_exists('id', $this->params)) {
            return $this->params['id'];
        }

        return 0;
    }

    public function getDeliveryPricePerUnitVolume() : float
    {
        if (array_key_exists('delivery_price_per_unit_volume', $this->params)) {
            return $this->params['delivery_price_per_unit_volume'];
        }

        return 0.0;
    }
}
