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
     * @param bool $forMoscowPoint
     * @return float Цена доставки до точки OSM за куб. метр
     */
    public function getDeliveryPricePerUnitVolume(bool $forMoscowPoint = false) : float;

    /**
     * @return float Стоимость доставки до москвы за единицу объема
     */
    public function getMoscowPointDeliveryPrice() : float;

    /**
     * @return float Стоимость доставки за единицу объема до любого города
     */
    public function getRegularPointDeliveryPrice() : float;

    /**
     * @return bool Москва ли?
     */
    public function isMoscow() : bool;

    /**
     * @return bool Екатеринбург ли?
     */
    public function isEkb() : bool;
}
