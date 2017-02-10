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
    public function getFactor(float $volume) : float;
}
