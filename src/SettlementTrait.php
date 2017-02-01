<?php

namespace SimaLand\DeliveryCalculator;

/**
 * Trait SettlementTrait.
 * Реализует SettlementInterface
 */
trait SettlementTrait
{
    protected $priceForMoscowPoint = 1068.75;

    protected $_forMoscowPoint = false;

    /**
     * @var int ID города Москва
     */
    public $moscowId = 1686293227;

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
     * @return bool Москва ли?
     */
    public function isMoscow() : bool
    {
        return $this->getId() == $this->moscowId;
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
    public function getDeliveryPricePerUnitVolume() : float
    {
        $result = $this->getRegularPointDeliveryPrice();
        if ($this->_forMoscowPoint && $this->isMoscow()) {
            $result = $this->priceForMoscowPoint;
        }
        return $result;
    }

    /**
     * @param bool $forMoscowPoint
     * @return $this Расчитывать ли доставку до точки в Москве
     */
    public function switchForMoscowPoint($forMoscowPoint = true)
    {
        $this->_forMoscowPoint = $forMoscowPoint;
        return $this;
    }
}
