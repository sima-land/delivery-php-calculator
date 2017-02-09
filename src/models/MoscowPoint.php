<?php

namespace SimaLand\DeliveryCalculator\models;

use SimaLand\DeliveryCalculator\PointInterface;

/**
 * Класс с фиксированной ценой до точки выгрузки в Москве
 *
 * Реализует PointInterface
 */
class MoscowPoint implements PointInterface
{
    /**
     * @return float Цена доставки до точки выгрузки в Москве
     */
    public function getDeliveryPricePerUnitVolume() : float
    {
        return 1068.75;
    }
}
