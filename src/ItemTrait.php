<?php

namespace SimaLand\DeliveryCalculator;

use SimaLand\DeliveryCalculator\PackingVolumeFactor;

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

    public function getPackingVolumeFactor() : float
    {
        $packingVolumeFactorComponent = $this->getPackingVolumeFactorComponent();
        if ($this-> isBoxed()) {
            $factor = $packingVolumeFactorComponent->getFactor($this->getPackageVolume());
        } else {
            $itemPackingVolumeFactor = $this->getOwnPackingVolumeFactor();
            $factor = $itemPackingVolumeFactor ?: $packingVolumeFactorComponent->getFactor($this->getProductVolume());
        }

        return $factor;
    }

    /**
     * Чтобы получить расчетный коэффициент упаковки этот метод должен возвращать экземпляр класса
     * \SimaLand\DeliveryCalculator\PackingVolumeFactor
     * @return PackingVolumeFactor Компонент для работы с общими коэффициентами упаковки
     */
    abstract public function getPackingVolumeFactorComponent() : PackingVolumeFactor;

    /**
     * @return float Объем продукта
     */
    abstract public function getProductVolume() : float;

    /**
     * Возвращает признак вместимости.
     *
     * https://www.sima-land.ru/api/v3/help/#Товар is_boxed
     *
     * @return bool Признак вместимости
     */
    abstract public function isBoxed() : bool;

    /**
     * Возвращает коэффициент упаковки (объема) у конкретного товара.
     *
     * Данные для конкретного товара можно получить по API
     * https://www.sima-land.ru/api/v3/help/#Товар поле packing_volume_factor
     *
     * @return float Коэффициент упаковки (объема) конкретного товара
     */
    abstract public function getOwnPackingVolumeFactor();

    /**
     * @return bool Является ли доставка товара в локальные точки платной?
     */
    abstract public function isPaidDeliveryLocal() : bool;

    /**
     * @return bool Является ли доставка товара в стандартный населенный пункт платной?
     */
    abstract public function isPaidDeliveryRegular() : bool;
}
