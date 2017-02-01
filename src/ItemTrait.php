<?php

namespace SimaLand\DeliveryCalculator;

/**
 * Trait ItemTrait.
 * Частично реализует ItemInterface
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
    abstract public function isPaidDeliveryLocal() : bool;

    /**
     * @return bool Является ли доставка товара в стандартный населенный пункт платной?
     */
    abstract public function isPaidDeliveryRegular() : bool;
}
