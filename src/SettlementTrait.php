<?php

namespace SimaLand\DeliveryCalculator;

/**
 * Trait SettlementTrait.
 * Частично реализует SettlementInterface
 */
trait SettlementTrait
{
    /**
     * @return int Идентификатор openstreetmap.org
     */
    public abstract function getId() : int;

    /**
     * @return bool Локальная ли точка доставки?
     */
    public function isLocal() : bool
    {
        // ID Екатеринбурга
        $localId = 27503892;
        return $this->getId() == $localId;
    }
}
