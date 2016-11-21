<?php

namespace SimaLand\DeliveryCalculator\tests;

/**
 * Created by PhpStorm.
 * User: pahanini
 * Date: 15/11/2016
 * Time: 19:00.
 */
class Settlement implements \SimaLand\DeliveryCalculator\SettlementInterface
{
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
