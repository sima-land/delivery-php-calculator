<?php

namespace SimaLand\DeliveryCalculator\tests;

use SimaLand\DeliveryCalculator\VolumeFactorSourceInterface;

class VolumeFactorSource implements VolumeFactorSourceInterface
{
    public function getFactors() : array
    {
        return [
            "0.1" => 2.8,
            99999999 => 1.1,
        ];
    }
}
