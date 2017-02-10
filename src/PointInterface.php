<?php

namespace SimaLand\DeliveryCalculator;

/**
 * Interface SettlementInterface.
 *
 * https://www.sima-land.ru/api/v3/help/#Города-доставки
 */
interface PointInterface
{
    /**
     * @return float Цена доставки до точки за куб. метр
     */
    public function getDeliveryPricePerUnitVolume() : float;
}
