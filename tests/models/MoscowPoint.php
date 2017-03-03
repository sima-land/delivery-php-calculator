<?php

namespace SimaLand\DeliveryCalculator\tests\models;

use SimaLand\DeliveryCalculator\models\MoscowPointAbstract;

class MoscowPoint extends MoscowPointAbstract
{
    protected $params = [];

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function isPaidDelivery() : bool
    {
        if (array_key_exists('is_paid_delivery', $this->params)) {
            return $this->params['is_paid_delivery'];
        }
        return false;
    }
}
