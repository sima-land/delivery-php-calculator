<?php

namespace SimaLand\DeliveryCalculator;

/**
 * Trait SettlementTrait.
 * Реализует SettlementInterface
 */
trait SettlementTrait
{
    /**
     * Стоимость доставки "до точки" в Москве
     */
    protected $_moscowPointDeliveryPrice = 1068.75;

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
     * @return bool Москва ли?
     */
    public function isMoscow() : bool
    {
        return $this->getId() == $this->moscowId;
    }

    /**
     * @return bool Екатеринбург ли?
     */
    public function isEkb() : bool
    {
        return $this->getId() == $this->ekbId;
    }

    /**
     * @return float Стоимость доставки за единицу объема до москвы
     */
    public function getMoscowPointDeliveryPrice() : float
    {
        return $this->_moscowPointDeliveryPrice;
    }

    /**
     * @return float Стоимость доставки за единицу объема до любого города
     */
    public function getRegularPointDeliveryPrice() : float
    {
        return 1;
    }

    /**
     * @param bool $forMoscowPoint
     * @return float Цена доставки до точки OSM за куб. метр
     */
    public function getDeliveryPricePerUnitVolume(bool $forMoscowPoint = false) : float
    {
        $deliveryPrice = $this->getRegularPointDeliveryPrice();
        if ($forMoscowPoint == true && $this->isMoscow()) {
            $deliveryPrice = $this->getMoscowPointDeliveryPrice();
        }

        return $deliveryPrice;
    }
}
