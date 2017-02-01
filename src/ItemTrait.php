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
    public function isPaidDelivery(SettlementInterface $settlement) : bool
    {
        return $this->isPaidDeliveryRegular() && (!$settlement->isLocal() || $this->isPaidDeliveryLocal());
    }

    /**
     * @return bool Является ли доставка товара в локальные точки платной?
     */
    public function isPaidDeliveryLocal() : bool
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
