<?php

namespace SimaLand\DeliveryCalculator;

/**
 * Interface SettlementInterface.
 *
 * https://www.sima-land.ru/api/v3/help/#Города-доставки
 */
interface SettlementInterface
{
    /**
     * @return int Идентификатор openstreetmap.org
     */
    public function getID() : int;

    /**
     * @return float Цена доставки до точки OSM за куб. метр
     */
    public function getDeliveryPricePerUnitVolume() : float;

    /**
     * @return float Стоимость доставки за единицу объема до любого города
     */
    public function getRegularPointDeliveryPrice() : float;

    /**
     * @return bool Локальная ли точка доставки?
     */
    public function isLocal() : bool;

    /**
     * @return bool Москва ли?
     */
    public function isMoscow() : bool;
}
