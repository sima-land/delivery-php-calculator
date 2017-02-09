<?php

namespace SimaLand\DeliveryCalculator\models;

use SimaLand\DeliveryCalculator\PackingVolumeFactorSourceInterface;

/**
 * Компонент для работы с общими коэффициентами упаковки
 *
 * @package SimaLand\DeliveryCalculator
 */
class DefaultPackingVolumeFactorSource implements PackingVolumeFactorSourceInterface
{
    // Факторы по умолчанию
    const DEFAULT_FACTORS = [
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

    /**
     * Получает фактор соответствующий объему
     *
     * @param float $volume
     * @return float
     */
    public function getFactor(float $volume) : float
    {
        $prevVolume = 0.0;
        foreach (self::DEFAULT_FACTORS as $key => $factor) {
            if ($volume > $prevVolume && $volume <= $key) {
                return $factor;
            }
            $prevVolume = $key;
        }

        return 0.0;
    }
}
