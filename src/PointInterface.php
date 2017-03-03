<?php

namespace SimaLand\DeliveryCalculator;

/**
 * Interface PointInterface.
 *
 * https://www.sima-land.ru/api/v3/help/#Города-доставки
 */
interface PointInterface
{
    /**
     * @return float Цена доставки до точки за куб. метр
     */
    public function getDeliveryPricePerUnitVolume() : float;

    /**
     * Признак платной доставки
     *
     * https://www.sima-land.ru/api/v3/help/#Города-доставки is_paid_delivery
     *
     * @return bool Платная ли доставка в этот город?
     */
    public function isPaidDelivery() : bool;
}
