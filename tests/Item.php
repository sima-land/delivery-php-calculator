<?php

namespace SimaLand\DeliveryCalculator\tests;

use SimaLand\DeliveryCalculator\ItemInterface;

class Item implements ItemInterface
{
    /**
     * @var PackingVolumeFactor
     */
    public static $packingVolumeFactor;

    protected $params = [];

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function param($name, $value)
    {
        $this->params[$name] = $value;
        return $this;
    }

    public function isPaidDelivery() : bool
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

    public function getPackingVolumeFactor() : float
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
}
