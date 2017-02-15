<?php

namespace SimaLand\DeliveryCalculator\models;

use SimaLand\DeliveryCalculator\VolumeFactorSourceInterface;

/**
 * Компонент для работы с общими коэффициентами упаковки
 *
 * @package SimaLand\DeliveryCalculator
 */
class DefaultVolumeFactorSource implements VolumeFactorSourceInterface
{
    // Факторы упаковки по умолчанию
    const DEFAULT_PACKING_FACTORS = [
        "0.1" => 2.8,
        "0.5" => 2.5,
        1 => 2.45,
        "1.5" => 2.35,
        2 => 2.25,
        "2.5" => 2.15,
        3 => 2.1,
        "3.5" => 2,
        4 => 1.9,
        5 => 1.8,
        10 => 1.45,
        20 => 1.35,
        50 => 1.25,
        99999999 => 1.1,
    ];

    // Факторы укладки по умолчанию
    const DEFAULT_PLACEMENT_FACTORS = [
        "0.1" => 1.2,
        "0.5" => 1.2,
        1 => 1.2,
        "1.5" => 1.2,
        2 => 1.2,
        "2.5" => 1.2,
        3 => 1.2,
        "3.5" => 1.2,
        4 => 1.2,
        5 => 1.2,
        10 => 1.2,
        20 => 1.2,
        50 => 1.2,
        99999999 => 1.1,
    ];

    /**
     * Получает фактор упаковки соответствующий объему
     *
     * @param float $volume
     * @return float
     */
    public function getPackingFactor(float $volume) : float
    {
        $prevVolume = 0.0;
        foreach (self::DEFAULT_PACKING_FACTORS as $key => $factor) {
            if ($volume > $prevVolume && $volume <= $key) {
                return $factor;
            }
            $prevVolume = $key;
        }

        return 0.0;
    }

    /**
     * Получает фактор укладки соответствующий объему
     *
     * @param float $volume
     * @return float
     */
    public function getPlacementFactor(float $volume) : float
    {
        $prevVolume = 0.0;
        foreach (self::DEFAULT_PLACEMENT_FACTORS as $key => $factor) {
            if ($volume > $prevVolume && $volume <= $key) {
                return $factor;
            }
            $prevVolume = $key;
        }

        return 0.0;
    }
}
