<?php

namespace SimaLand\DeliveryCalculator;

/**
 * Interface SettlementInterface
 *
 * https://www.sima-land.ru/api/v3/help/#Города-доставки
 */
interface SettlementInterface {

    /**
     * @return int Идентификатор openstreetmap.org
     */
    public function getID() : int;

    /**
     * @return float Цена доставки до точки OSM за куб. метр
     */
    public function getDeliveryPricePerUnitVolume() : float;
}


