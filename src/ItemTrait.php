<?php

namespace SimaLand\DeliveryCalculator;

/**
 * Trait ItemTrait.
 * Реализует ItemInterface
 */
trait ItemTrait
{
    /**
     * @param SettlementInterface $settlement
     * @return bool Является ли доставка товара платной?
     */
    public function isPaidDelivery(SettlementInterface $settlement = null) : bool
    {
        $result = $this->isPaidDeliveryRegular();
        if ($settlement) {
            $result = $result && (!$settlement->isEkb() || $this->isPaidDeliveryEkb());
        }
        return $result;
    }

    /**
     * @return bool Является ли доставка товара в Екатеринбург платной?
     */
    public function isPaidDeliveryEkb() : bool
    {
        return true;
    }

    /**
     * @return bool Является ли доставка товара в стандартный населенный пункт платной?
     */
    public function isPaidDeliveryRegular() : bool
    {
        return true;
    }
}
