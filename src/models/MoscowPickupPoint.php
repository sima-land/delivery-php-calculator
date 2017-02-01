<?php

namespace SimaLand\DeliveryCalculator\models;

use SimaLand\DeliveryCalculator\SettlementInterface;
use SimaLand\DeliveryCalculator\SettlementTrait;

/**
 * Класс с фиксированной ценой до точки выгрузки в Москве
 * Class MoscowPickupPoint
 * Реализует SettlementInterface
 */
class MoscowPickupPoint implements SettlementInterface
{
    use SettlementTrait;

    /**
     * @return int Фейковый ID точки выгрузки в Москве
     */
    public function getId() : int
    {
        return 10000000000000000;
    }

    /**
     * @return float Цена доставки до точки выгрузки в Москве
     */
    public function getDeliveryPricePerUnitVolume() : float
    {
        return 1068.75;
    }
}
