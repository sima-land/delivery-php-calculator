<?php

namespace SimaLand\DeliveryCalculator\Tests;

use \SimaLand\DeliveryCalculator\ItemInterface;

class Item implements ItemInterface
{
    protected $params = [];

    function __construct(array $params)
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

    public function isPaidDelivery() : bool
    {
        if (array_key_exists('is_paid_delivery', $this->params)) {
            return $this->params['is_paid_delivery'];
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

    public function getPackingVolumeFactor() : float
    {
        if (array_key_exists('packing_volume_factor', $this->params)) {
            return (float)$this->params['packing_volume_factor'];
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