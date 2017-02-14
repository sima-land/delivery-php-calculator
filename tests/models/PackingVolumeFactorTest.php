<?php

namespace SimaLand\DeliveryCalculator\tests\models;

use PHPUnit\Framework\TestCase;
use SimaLand\DeliveryCalculator\models;

class PackingVolumeFactorTest extends TestCase
{
    public function testCalc()
    {
        $model = new models\DefaultPackingVolumeFactorSource();
        $this->assertSame(2.8, $model->getPackingFactor(0.05));
        $this->assertSame(2.35, $model->getPackingFactor(1.5));
        $this->assertSame(1.1, $model->getPackingFactor(21000));
        $this->assertSame(0.0, $model->getPackingFactor(PHP_INT_MAX));
    }
}
