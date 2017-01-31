<?php
/**
 * Created by PhpStorm.
 * User: danil
 * Date: 31.01.17
 * Time: 13:37
 */

namespace SimaLand\DeliveryCalculator;


class PackingVolumeFaktor
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
     * @var array|null Загруженные факторы
     */
    protected $_factors = null;

    /**
     * @var VolumeFactorSourceInterface|null
     */
    protected $source = null;

    /**
     * PackingVolumeFaktor constructor.
     * @param VolumeFactorSourceInterface|null $source
     */
    public function __construct($source = null)
    {
        $this->source = $source;
    }

    /**
     * Получение факторов
     */
    protected function setFactors()
    {
        if (!is_array($this->_factors)) {
            if (!$this->source) {
                $this->_factors = self::DEFAULT_FACTORS;
            } else {
                $this->_factors = $this->source->getFactors();
            }
        }
    }

    /**
     * Получает фактор соответствующий объему
     *
     * @param float $volume
     * @return float
     */
    public function getFactor(float $volume) : float
    {
        $this->setFactors();

        $prev_volume = 0.0;
        foreach ($this->_factors as $key => $factor) {
            if ($volume > $prev_volume && $volume <= $key) {
                return $factor;
            }
            $prev_volume = $key;
        }

        return 0.0;
    }
}
