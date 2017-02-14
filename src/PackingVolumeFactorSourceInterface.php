<?php

namespace SimaLand\DeliveryCalculator;

/**
 * Interface PackingVolumeFactorSourceInterface.
 */
interface PackingVolumeFactorSourceInterface
{
    /**
     * @param float $volume
     * @return float
     */
    public function getPackingFactor(float $volume) : float;

    /**
     * @param float $volume
     * @return float
     */
    public function getPlacementFactor(float $volume) : float;
}
