<?php

namespace SimaLand\DeliveryCalculator\tests;

use PHPUnit\Framework\TestCase;
use SimaLand\DeliveryCalculator\Calculator;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use pahanini\Monolog\Formatter\CliFormatter;
use SimaLand\DeliveryCalculator\models\DefaultPackingVolumeFactorSource;
use SimaLand\DeliveryCalculator\models\MoscowPoint;

class CalculateTest extends TestCase
{
    protected function getCalc($point, $isLocal = false) : Calculator
    {
        $logger = new Logger('calc');
        $formatter = new CliFormatter();
        $streamHandler = new StreamHandler('php://stdout', Logger::DEBUG);
        $streamHandler->setFormatter($formatter);
        $logger->pushHandler($streamHandler);
        $packingVolumeFactorSource = new DefaultPackingVolumeFactorSource();
        $calc = new Calculator($packingVolumeFactorSource, $point, $isLocal);
        $calc->setLogger($logger);
        return $calc;
    }

    protected function getRegularItem() : Item
    {
        return new Item([
            'weight' => 690.0,
            'is_paid_delivery' => true,
            'product_volume' => 2.049,
            'package_volume' => 0.759,
            'packing_volume_factor' => 1.1,
            'is_boxed' => false,
            'delivery_discount' => 0.2,
        ]);
    }

    protected function getBoxedItem() : Item
    {
        return new Item([
            'weight' => 690.0,
            'is_paid_delivery' => true,
            'product_volume' => 2.049,
            'package_volume' => 2.049,
            'packing_volume_factor' => 1.1,
            'is_boxed' => true,
            'box_volume' => 70.986,
            'box_capacity' => 20,
            'delivery_discount' => 0.2,
        ]);
    }

    public function testCalc()
    {
        // Стандартная, не локальная точка
        $point = new Point(['id' => 1, 'delivery_price_per_unit_volume' => 1545.61]);
        $calc = $this->getCalc($point, false);

        $info = 'Regular, high density item';
        $item1 = $this->getRegularItem();
        $calc->reset();
        $this->assertTrue($calc->addItem($item1, 69), $info);
        $this->assertSame(235.48, $calc->getResult(), $info);

        $info = 'Regular, low density item';
        $item2 = $this->getRegularItem();
        $calc->reset();
        $item2->param('weight', 200.00);
        $this->assertTrue($calc->addItem($item2, 69), $info);
        $this->assertSame(192.30, $calc->getResult(), $info);

        $info = 'Multiple items calculate';
        $calc->reset();
        $this->assertTrue($calc->addItem($item1, 69), $info);
        $this->assertTrue($calc->addItem($item2, 69), $info);
        $this->assertSame(427.77, $calc->getResult(), $info);
        $this->assertEquals(count($calc->getTrace()), 6);

        $info = 'Boxed, low density item';
        $item = $this->getBoxedItem();
        $calc->reset();
        $this->assertTrue($calc->addItem($item, 69), $info);
        $this->assertSame(338.36, $calc->getResult(), $info);

        $info = 'No paid delivery';
        $calc->reset();
        $item = $this->getBoxedItem()->param('is_paid_delivery', false);
        $this->assertTrue($calc->addItem($item, 69), $info);
        $this->assertSame(0.0, $calc->getResult(), $info);

        $info = 'Boxed, low density item';
        $item = $this->getBoxedItem()->param('box_volume', 20.986);
        $calc->reset();
        $this->assertTrue($calc->addItem($item, 500), $info);
        $this->assertSame(1706.35, $calc->getResult(), $info);

        $info = 'Boxed, very low density item';
        $item = $this->getBoxedItem()->param('box_volume', 80.986);
        $calc->reset();
        $this->assertTrue($calc->addItem($item, 500), $info);
        $this->assertSame(2822.53, $calc->getResult(), $info);

        $info = 'Boxed, very low density item with discount';
        $item = $this->getBoxedItem()->param("weight", 250.0)->param("delivery_discount", 0.4);
        $calc->reset();
        $this->assertTrue($calc->addItem($item, 500), $info);
        $this->assertSame(1848.99, $calc->getResult(), $info);


        // Negative scenarios
        $info = 'Settlement does not have delivery price per unit volume';
        $invalidPoint = new Point(['id' => 1, 'delivery_price_per_unit_volume' => 0]);
        $calc1= $this->getCalc($invalidPoint);
        $item = $this->getRegularItem();
        $this->assertFalse($calc1->addItem($item, 20000), $info);

        $info = 'Volume out of limits';
        $calc->reset();
        $item = $this->getRegularItem();
        $this->assertFalse($calc->addItem($item, 20000), $info);

        $info = 'Zero qty';
        $item = $this->getRegularItem();
        $this->assertFalse($calc->addItem($item, 0), $info);

        $info = 'Zero weight';
        $item = $this->getRegularItem()->param("weight", 0);
        $this->assertFalse($calc->addItem($item, 1), $info);

        $info = 'Zero box capacity';
        $this->getBoxedItem()->param("box_capacity", 0);
        $this->assertFalse($calc->addItem($item, 1), $info);

        $info = 'Negative product volume';
        $item = $this->getBoxedItem()->param("product_volume", -1);
        $this->assertFalse($calc->addItem($item, 1), $info);

        $info = 'Regular, low density item for Moscow point';
        $settlementMoscow = new MoscowPoint();
        $item = $this->getRegularItem();
        $calc = $this->getCalc($settlementMoscow);
        $this->assertTrue($calc->addItem($item, 69), $info);
        $this->assertSame(162.83, $calc->getResult(), $info);

        $info = 'Regular, low density item for local point. Not calculated';
        $settlementLocal = new Point([
            'delivery_price_per_unit_volume' => 500.77,
        ]);
        $item = $this->getRegularItem();
        $calc = $this->getCalc($settlementLocal, true);
        $this->assertTrue($calc->addItem($item, 69), $info);
        $this->assertSame(0.0, $calc->getResult(), $info);

        $info = 'Regular, low density item for local point. Calculated';
        $item = $this->getRegularItem()->param("is_paid_delivery_local", true);
        $this->assertTrue($calc->addItem($item, 69), $info);
        $this->assertSame(76.29, $calc->getResult(), $info);
    }
}
