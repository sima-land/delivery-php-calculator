<?php

namespace SimaLand\DeliveryCalculator;

/**
 * Interface ItemInterface.
 *
 * Для реализации этого интерфейса можно воспользоваться данными возвращаемыми API
 * https://www.sima-land.ru/api/v3/help/#Товар
 *
 *
 *
 */
interface ItemInterface
{
    /**
     * Признак платной доставки
     *
     * https://www.sima-land.ru/api/v3/help/#Товар is_paid_delivery
     *
     * @return bool Является ли доставка товара платной?
     */
    public function isPaidDelivery() : bool;

    /**
     * Признак платной доставки для "локальных" по отношению к складу территорий
     *
     * https://www.sima-land.ru/api/v3/help/#Товар is_paid_delivery_ekb
     *
     * @return bool Является ли доставка товара платной для "локальных" по отношению к складу территорий
     */
    public function isPaidDeliveryLocal() : bool;

    /**
     * Масса продукта, г.
     *
     * https://www.sima-land.ru/api/v3/help/#Товар weight
     *
     * @return float Масса продукта, г.
     */
    public function getWeight() : float;

    /**
     * Объем продукта, л.
     *
     * https://www.sima-land.ru/api/v3/help/#Товар product_volume
     *
     * @return float Объем продукта, л.
     */
    public function getProductVolume() : float;

    /**
     * Объем упаковки, л.
     *
     * https://www.sima-land.ru/api/v3/help/#Товар package_volume
     *
     * @return float Объем упаковки, л.
     */
    public function getPackageVolume() : float;

    /**
     * Расчетный коэффициент объема упаковки (объема).
     *
     * https://www.sima-land.ru/api/v3/help/#Товар packing_volume_factor
     *
     * @return float Коэффициент объема упаковки
     */
    public function getPackingVolumeFactor() : float;

    /**
     * Возвращает признак вкладываемости
     **
     * https://www.sima-land.ru/api/v3/help/#Товар is_boxed
     *
     * @return bool Признак вкладываемости
     */
    public function isBoxed() : bool;

    /**
     * Объем бокса, л
     *
     * https://www.sima-land.ru/api/v3/help/#Товар box_volume
     *
     * @return float
     */
    public function getBoxVolume() : float;

    /**
     * Кол-во продукта помещающегося в бокс.
     *
     * @return int
     */
    public function getBoxCapacity() : int;

    /**
     * Доля скидки на доставку товара.
     *
     * @return float
     */
    public function getDeliveryDiscount() : float;
}
