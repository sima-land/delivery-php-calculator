<?php

namespace SimaLand\DeliveryCalculator;

/**
 * Trait SettlementTrait.
 * Реализует SettlementInterface
 */
trait SettlementTrait
{
    /**
     * @var array Специальные цены на доставку
     */
    public $specialPrices = [
        // Стоимость доставки "до точки" в Москве
        1686293227 => 1068.75
    ];

    /**
     * @var int ID города Екатеринбург
     */
    public $ekbId = 27503892;

    /**
     * @return int Идентификатор openstreetmap.org
     */
    public function getId() : int
    {
        return 1;
    }

    /**
     * @return bool Екатеринбург ли?
     */
    public function isEkb() : bool
    {
        return $this->getId() == $this->ekbId;
    }

    /**
     * @return float Стоимость доставки за единицу объема до любого города
     */
    public function getRegularPointDeliveryPrice() : float
    {
        return 1;
    }

    /**
     * @param bool $isSpecialPrice
     * @return float Цена доставки до точки OSM за куб. метр
     */
    public function getDeliveryPricePerUnitVolume(bool $isSpecialPrice = false) : float
    {
        $id = $this->getID();
        $result = $this->getRegularPointDeliveryPrice();
        if ($isSpecialPrice && isset($this->specialPrices[$id])) {
            $result = $this->specialPrices[$id];
        }
        return $result;
    }
}
