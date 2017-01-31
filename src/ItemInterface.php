<?php

namespace SimaLand\DeliveryCalculator;

/**
 * Interface ItemInterface.
 */
interface ItemInterface
{
    /**
     * @return int ID товра (нужен для лога)
     */
    public function getID() : int;

    /**
     * @return bool Является ли доставка товара платной?
     */
    public function isPaidDelivery() : bool;

    /**
     * @return bool Является ли доставка товара в Екатеринбург платной?
     */
    public function isPaidDeliveryEkb() : bool;

    /**
     * @return int Количество отправляемых товаров
     */
    public function getQty() : int;

    /**
     * Вес продукта.
     *
     * @return float Масса продукта,
     */
    public function getWeight() : float;

    /**
     * @return float Объем продукта
     */
    public function getProductVolume() : float;

    /**
     * Объем упаковки.
     *
     * @return float
     */
    public function getPackageVolume() : float;

    /**
     * Возвращает коэффициент упаковки (объема).
     *
     * Данные для конкретного товара можно получить по API
     * https://www.sima-land.ru/api/v3/help/#Товар поле packing_volume_factor
     * если у товара данное поле не задан то принимаем его равным 1
     *
     * @return float Коэффициент упаковки (объема)
     */
    public function getPackingVolumeFactor() : float;

    /**
     * Возвращает признак вместимости.
     *
     * todo: Очень непонятное название, по сути это значи может ли товар быть вложен один в другой?
     *
     * https://www.sima-land.ru/api/v3/help/#Товар is_boxed
     *
     * @return bool Признак вместимости
     */
    public function isBoxed() : bool;

    /**
     * Объем бокса.
     *
     * В который пакуется продукт.
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
