<?php

namespace SimaLand\DeliveryCalculator\tests;

use PHPUnit\Framework\TestCase;
use SimaLand\DeliveryCalculator\Calculator;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use pahanini\Monolog\Formatter\CliFormatter;
use SimaLand\DeliveryCalculator\models\DefaultVolumeFactorSource;
use SimaLand\DeliveryCalculator\tests\models\MoscowPoint;

class CalculateTest extends TestCase
{
    protected function getCalc($point, $isLocal = false) : Calculator
    {
        $logger = new Logger('calc');
        $formatter = new CliFormatter();
        $streamHandler = new StreamHandler('php://stdout', Logger::DEBUG);
        $streamHandler->setFormatter($formatter);
        $logger->pushHandler($streamHandler);
        $volumeFactorSource = new DefaultVolumeFactorSource();
        $calc = new Calculator($volumeFactorSource, $point, $isLocal);
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
            'box_capacity' => 0,
            'box_volume' => 0,
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

    protected function getControlBigWeightRegularItem() : Item
    {
        return new Item([
            'weight' => 39000,
            'is_paid_delivery' => true,
            'product_volume' => 105.82,
            'package_volume' => 103.204,
            'packing_volume_factor' => 1.1,
            'is_boxed' => false,
            'box_volume' => 105,82,
            'box_capacity' => 1,
            'custom_box_capacity' => 1,
            'delivery_discount' => 0.0,
        ]);
    }

    protected function getControlSmallWeightRegularItem() : Item
    {
        return new Item([
            'weight' => 95,
            'is_paid_delivery' => true,
            'product_volume' => 0.4533,
            'package_volume' => 0.321,
            'packing_volume_factor' => 2.5,
            'is_boxed' => false,
            'box_volume' => 16.32,
            'box_capacity' => 36,
            'custom_box_capacity' => 36,
            'delivery_discount' => 0.0,
        ]);
    }

    protected function getControlSmallWeightBoxedItem() : Item
    {
        return new Item([
            'weight' => 150,
            'is_paid_delivery' => true,
            'product_volume' => 1.664,
            'package_volume' => 6.048,
            'packing_volume_factor' => 1.25,
            'is_boxed' => true,
            'box_volume' => 14.976,
            'box_capacity' => 9,
            'custom_box_capacity' => 18,
            'delivery_discount' => 0.0,
        ]);
    }

    protected function getControlBigWeightBoxedItem() : Item
    {
        return new Item([
            'weight' => 6200,
            'is_paid_delivery' => true,
            'product_volume' => 5.5733,
            'package_volume' => 6.8,
            'packing_volume_factor' => 1.1,
            'is_boxed' => true,
            'box_volume' => 50.16,
            'box_capacity' => 9,
            'custom_box_capacity' => 9,
            'delivery_discount' => 0.0,
        ]);
    }

    protected function getControlBigWeightRegularItemInBox() : Item
    {
        return new Item([
            'weight' => 250,
            'is_paid_delivery' => true,
            'product_volume' => 0.4737,
            'package_volume' => 0.32,
            'packing_volume_factor' => 2.5,
            'is_boxed' => false,
            'box_volume' => 14.21,
            'box_capacity' => 30,
            'custom_box_capacity' => 30,
            'delivery_discount' => 0.0,
        ]);
    }

    public function testControlCalc()
    {
        // Стандартная, не локальная точка
        $point = new Point(['id' => 1, 'delivery_price_per_unit_volume' => 1500]);
        $calc = $this->getCalc($point, false);

        $info = 'Regular, high density item. Calculated';
        $item = $this->getControlBigWeightRegularItem();
        $this->assertTrue($calc->addItem($item, 1), $info);
        $this->assertSame(234.0, $calc->getResult(), $info);

        $info = 'Regular, low density item. Calculated';
        $item = $this->getControlSmallWeightRegularItem();
        $calc->reset();
        $this->assertTrue($calc->addItem($item, 36), $info);
        $this->assertSame(29.37, $calc->getResult(), $info);

        $info = 'Boxed, low density item. Calculated';
        $item = $this->getControlSmallWeightBoxedItem();
        $calc->reset();
        $this->assertTrue($calc->addItem($item, 18), $info);
        $this->assertSame(46.91, $calc->getResult(), $info);

        $info = 'Boxed, hight density item. Calculated';
        $item = $this->getControlBigWeightBoxedItem();
        $calc->reset();
        $this->assertTrue($calc->addItem($item, 9), $info);
        $this->assertSame(334.8, $calc->getResult(), $info);

        $info = 'Regular, high density item in box. Calculated';
        $item = $this->getControlBigWeightRegularItemInBox();
        $calc->reset();
        $this->assertTrue($calc->addItem($item, 30), $info);
        $this->assertSame(45.0, $calc->getResult(), $info);
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

        $info = 'Regular, low density item (not check on volume delta)';
        $item2 = $this->getRegularItem()->param('package_volume', 16.048)->param('box_volume', 7.111);
        $calc->reset();
        $item2->param('weight', 200.00);
        $this->assertTrue($calc->addItem($item2, 69), $info);
        $this->assertSame(192.30, $calc->getResult(), $info);

        $info = 'Regular, low density multiple item';
        $item2 = $this->getRegularItem()->param('custom_box_capacity', 10)->param('box_volume', 20.111);
        $calc->reset();
        $item2->param('weight', 200.00);
        $this->assertTrue($calc->addItem($item2, 69), $info);
        $this->assertSame(207.5, $calc->getResult(), $info);

        $info = 'Multiple items calculate';
        $calc->reset();
        $this->assertTrue($calc->addItem($item1, 69), $info);
        $this->assertTrue($calc->addItem($item2, 69), $info);
        $this->assertSame(442.98, $calc->getResult(), $info);
        $this->assertEquals(count($calc->getTrace()), 10);

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

        $info = 'Boxed, very low density item with discount, but with city which not use this discount';
        $settlementWithoutDiscount = new Point([
            'delivery_price_per_unit_volume' => 1545.61,
            'is_paid_delivery' => true
        ]);
        $calcWithoutDiscount = $this->getCalc($settlementWithoutDiscount, false);
        $item = $this->getBoxedItem()->param("weight", 250.0)->param("delivery_discount", 0.4);
        $this->assertTrue($calcWithoutDiscount->addItem($item, 500), $info);
        $this->assertSame(3081.65, $calcWithoutDiscount->getResult(), $info);

        $info = 'Mandatory paid delivery to city';
        $calcWithoutDiscount = $this->getCalc($settlementWithoutDiscount, false);
        $item = $this->getBoxedItem()
            ->param("weight", 250.0)
            ->param("delivery_discount", 0.4)
            ->param('is_paid_delivery', false);
        $this->assertTrue($calcWithoutDiscount->addItem($item, 500), $info);
        $this->assertSame(3081.65, $calcWithoutDiscount->getResult(), $info);

        // Negative scenarios
        $info = 'To big delivery discount';
        $item = $this->getBoxedItem()->param('delivery_discount', 10);
        $calc->reset();
        $this->assertFalse($calc->addItem($item, 500), $info);
        $this->assertSame(['Delivery discount must be between 0 and 1, delivery discount=10'], $calc->getErrors(), $info);

        $info = 'Zero volume';
        $item = $this->getBoxedItem()->param('box_volume', 800000000.986);
        $calc->reset();
        $this->assertFalse($calc->addItem($item, 500), $info);
        $this->assertSame(['Zero volume'], $calc->getErrors(), $info);

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
        $settlementMoscow = new MoscowPoint([]);
        $item = $this->getRegularItem();
        $calc = $this->getCalc($settlementMoscow);
        $this->assertTrue($calc->addItem($item, 69), $info);
        $this->assertSame(162.83, $calc->getResult(), $info);

        $info = 'Regular, low density item for Moscow point without discount';
        $settlementMoscow = new MoscowPoint(['is_paid_delivery' => true]);
        $item = $this->getRegularItem();
        $calc = $this->getCalc($settlementMoscow);
        $this->assertTrue($calc->addItem($item, 69), $info);
        $this->assertSame(203.53, $calc->getResult(), $info);

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
