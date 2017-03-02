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
     * Признак скидки
     *
     * https://www.sima-land.ru/api/v3/help/#Города-доставки is_paid_delivery
     *
     * @return bool Не учитывать скидку при доставке в этот в этот город?
     */
    public function hasNoDiscount() : bool;
}
