<?php

namespace SimaLand\DeliveryCalculator;

/**
 * Interface VolumeFactorSourceInterface.
 */
interface VolumeFactorSourceInterface
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
