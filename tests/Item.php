<?php

namespace SimaLand\DeliveryCalculator\tests;

use SimaLand\DeliveryCalculator\ItemInterface;
use SimaLand\DeliveryCalculator\ItemTrait;
use SimaLand\DeliveryCalculator\PackingVolumeFactor;

class Item implements ItemInterface
{
    use ItemTrait;

    /**
     * @var PackingVolumeFactor
     */
    public static $packingVolumeFactor;

    protected $params = [];

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function getID() : int
    {
        if (array_key_exists('id', $this->params)) {
            return $this->params['id'];
        }

        return 0;
    }

    public function isPaidDeliveryRegular() : bool
    {
        if (array_key_exists('is_paid_delivery', $this->params)) {
            return $this->params['is_paid_delivery'];
        }

        return false;
    }

    public function isPaidDeliveryLocal() : bool
    {
        if (array_key_exists('is_paid_delivery_local', $this->params)) {
            return $this->params['is_paid_delivery_local'];
        }

        return false;
    }

    public function getQty() : int
    {
        if (array_key_exists('qty', $this->params)) {
            return $this->params['qty'];
        }

        return 0;
    }

    public function getWeight() : float
    {
        if (array_key_exists('weight', $this->params)) {
            return $this->params['weight'];
        }

        return 0.0;
    }

    public function getProductVolume() : float
    {
        if (array_key_exists('product_volume', $this->params)) {
            return $this->params['product_volume'];
        }

        return 0.0;
    }

    public function getPackageVolume() : float
    {
        if (array_key_exists('package_volume', $this->params)) {
            return $this->params['package_volume'];
        }

        return 0.0;
    }

    public function getOwnPackingVolumeFactor() : float
    {
        if (array_key_exists('packing_volume_factor', $this->params)) {
            return (float) $this->params['packing_volume_factor'];
        }

        return 1.0;
    }

    public function isBoxed() : bool
    {
        if (array_key_exists('is_boxed', $this->params)) {
            return $this->params['is_boxed'];
        }

        return false;
    }

    public function getBoxVolume() : float
    {
        if (array_key_exists('box_volume', $this->params)) {
            return $this->params['box_volume'];
        }

        return 0.0;
    }

    public function getBoxCapacity() : int
    {
        if (array_key_exists('box_capacity', $this->params)) {
            return $this->params['box_capacity'];
        }

        return 0;
    }

    public function getDeliveryDiscount() : float
    {
        if (array_key_exists('delivery_discount', $this->params)) {
            return $this->params['delivery_discount'];
        }

        return 0.0;
    }

    public function getPackingVolumeFactorComponent() : PackingVolumeFactor
    {
        return self::$packingVolumeFactor;
    }
}
