<?php

namespace SimaLand\DeliveryCalculator;

/**
 * Interface VolumeFactorSourceInterface.
 */
interface VolumeFactorSourceInterface
{
    /**
     * @return array Получает факторы
     */
    public function getFactors() : array ;
}
