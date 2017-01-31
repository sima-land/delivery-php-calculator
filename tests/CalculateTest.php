<?php

namespace SimaLand\DeliveryCalculator\tests;

use PHPUnit\Framework\TestCase;
use SimaLand\DeliveryCalculator\Calculator;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use pahanini\Monolog\Formatter\CliFormatter;
use SimaLand\DeliveryCalculator\PackingVolumeFactor;

class CalculateTest extends TestCase
{
    public function testCalc()
    {
        $logger = new Logger('calc');
        $formatter = new CliFormatter();
        $streamHandler = new StreamHandler('php://stdout', Logger::DEBUG);
        $streamHandler->setFormatter($formatter);
        $logger->pushHandler($streamHandler);

        $calc = new Calculator();
        $calc->setLogger($logger);

        $settlement = new Settlement([
            'id' => 1,
            'delivery_price_per_unit_volume' => 1545.61,
        ]);
        $packingVolumeFactor = new PackingVolumeFactor();

        $info = 'Regular, low density item';
        $logger->info($info);
        $item1 = new Item([
            'id' => 1,
            'weight' => 690.0,
            'qty' => 69,
            'is_paid_delivery' => true,
            'product_volume' => 2.049,
            'package_volume' => 0.759,
            'packing_volume_factor' => 1.1,
            'is_boxed' => false,
            'delivery_discount' => 0.2,
        ]);
        $this->assertTrue($calc->calculate($settlement, [$item1], $packingVolumeFactor), $info);
        $this->assertSame(235.48, $calc->getResult(), $info);

        $info = 'Regular, high density item';
        $logger->info($info);
        $item2 = new Item([
            'id' => 1,
            'weight' => 200.0,
            'qty' => 69,
            'is_paid_delivery' => true,
            'product_volume' => 2.049,
            'package_volume' => 0.759,
            'packing_volume_factor' => 1.1,
            'is_boxed' => false,
            'delivery_discount' => 0.2,
        ]);
        $this->assertTrue($calc->calculate($settlement, [$item2], $packingVolumeFactor), $info);
        $this->assertSame(192.30, $calc->getResult(), $info);

        $info = 'Multiple items calculate';
        $this->assertTrue($calc->calculate($settlement, [$item1, $item2], $packingVolumeFactor), $info);
        $this->assertSame(427.77, $calc->getResult(), $info);

        $info = 'Boxed, low density item';
        $logger->info($info);
        $item = new Item([
            'id' => 1,
            'weight' => 690.0,
            'qty' => 69,
            'is_paid_delivery' => true,
            'product_volume' => 2.049,
            'package_volume' => 2.049,
            'packing_volume_factor' => 1.1,
            'is_boxed' => true,
            'box_volume' => 40.986,
            'box_capacity' => 20,
            'delivery_discount' => 0.2,
        ]);
        $this->assertTrue($calc->calculate($settlement, [$item], $packingVolumeFactor), $info);
        $this->assertSame(375.91, $calc->getResult(), $info);

        $info = 'Boxed, low density item';
        $logger->info($info);
        $item = new Item([
            'id' => 1,
            'weight' => 690.0,
            'qty' => 500,
            'is_paid_delivery' => true,
            'product_volume' => 2.049,
            'package_volume' => 2.049,
            'packing_volume_factor' => 1.1,
            'is_boxed' => true,
            'box_volume' => 20.986,
            'box_capacity' => 20,
            'delivery_discount' => 0.2,
        ]);
        $this->assertTrue($calc->calculate($settlement, [$item], $packingVolumeFactor), $info);
        $this->assertSame(1706.35, $calc->getResult(), $info);

        $info = 'Boxed, very low density item';
        $logger->info($info);
        $item = new Item([
            'id' => 1,
            'weight' => 250.0,
            'qty' => 500,
            'is_paid_delivery' => true,
            'product_volume' => 2.049,
            'package_volume' => 2.049,
            'packing_volume_factor' => 1.1,
            'is_boxed' => true,
            'box_volume' => 40.986,
            'box_capacity' => 20,
            'delivery_discount' => 0.2,
        ]);
        $this->assertTrue($calc->calculate($settlement, [$item], $packingVolumeFactor), $info);
        $this->assertSame(2724.0, $calc->getResult(), $info);

        $info = 'Boxed, very low density item with discount';
        $logger->info($info);
        $item = new Item([
            'id' => 1,
            'weight' => 250.0,
            'qty' => 500,
            'is_paid_delivery' => true,
            'product_volume' => 2.049,
            'package_volume' => 2.049,
            'packing_volume_factor' => 1.1,
            'is_boxed' => true,
            'box_volume' => 40.986,
            'box_capacity' => 20,
            'delivery_discount' => 0.4,
        ]);
        $this->assertTrue($calc->calculate($settlement, [$item], $packingVolumeFactor), $info);
        $this->assertSame(2043.0, $calc->getResult(), $info);

        $settlementWithoutDeliveryPrice = new Settlement([
            'id' => 1,
            'delivery_price_per_unit_volume' => 0,
        ]);
        $info = 'Settlement does not have delivery price per unit volume';
        $logger->info($info);
        $item = new Item([
            'id' => 1,
            'weight' => 690.0,
            'qty' => 20000,
            'is_paid_delivery' => true,
            'product_volume' => 2.049,
            'package_volume' => 0.759,
            'packing_volume_factor' => 1.1,
            'is_boxed' => false,
            'box_volume' => 0.0,
            'box_capacity' => 0,
            'delivery_discount' => 0.2,
        ]);
        $this->assertFalse($calc->calculate($settlementWithoutDeliveryPrice, [$item], $packingVolumeFactor), $info);

        $info = 'Volume out of limits';
        $logger->info($info);
        $item = new Item([
            'id' => 1,
            'weight' => 690.0,
            'qty' => 20000,
            'is_paid_delivery' => true,
            'product_volume' => 2.049,
            'package_volume' => 0.759,
            'packing_volume_factor' => 1.1,
            'is_boxed' => false,
            'box_volume' => 0.0,
            'box_capacity' => 0,
            'delivery_discount' => 0.2,
        ]);
        $this->assertFalse($calc->calculate($settlement, [$item], $packingVolumeFactor), $info);

        $info = 'Zero qty';
        $logger->info($info);
        $item = new Item([
            'id' => 1,
            'weight' => 690.0,
            'qty' => 0,
            'is_paid_delivery' => true,
            'product_volume' => 2.049,
            'package_volume' => 0.759,
            'packing_volume_factor' => 1.1,
            'is_boxed' => false,
            'box_volume' => 0.0,
            'box_capacity' => 0,
            'delivery_discount' => 0.2,
        ]);
        $this->assertFalse($calc->calculate($settlement, [$item], $packingVolumeFactor), $info);

        $info = 'Zero weight';
        $logger->info($info);
        $item = new Item([
            'id' => 1,
            'weight' => 0.0,
            'qty' => 1,
            'is_paid_delivery' => true,
            'product_volume' => 2.049,
            'package_volume' => 0.759,
            'packing_volume_factor' => 1.1,
            'is_boxed' => false,
            'box_volume' => 0.0,
            'box_capacity' => 0,
            'delivery_discount' => 0.2,
        ]);
        $this->assertFalse($calc->calculate($settlement, [$item], $packingVolumeFactor), $info);

        $info = 'Zero box capacity';
        $logger->info($info);
        $item = new Item([
            'id' => 1,
            'weight' => 12.0,
            'qty' => 1,
            'is_paid_delivery' => true,
            'product_volume' => 2.049,
            'package_volume' => 0.759,
            'packing_volume_factor' => 1.1,
            'is_boxed' => true,
            'box_volume' => 1.0,
            'box_capacity' => 0,
            'delivery_discount' => 0.2,
        ]);
        $this->assertFalse($calc->calculate($settlement, [$item], $packingVolumeFactor), $info);

        $info = 'Zero product volume';
        $logger->info($info);
        $item = new Item([
            'id' => 1,
            'weight' => 12.0,
            'qty' => 1,
            'is_paid_delivery' => true,
            'product_volume' => -1.0,
            'package_volume' => 0.759,
            'packing_volume_factor' => 1.1,
            'is_boxed' => false,
            'box_volume' => 1.0,
            'box_capacity' => 2,
            'delivery_discount' => 0.2,
        ]);
        $this->assertFalse($calc->calculate($settlement, [$item], $packingVolumeFactor), $info);

        $info = 'Regular, low density item for Moscow point';
        $settlementMoscow = new Settlement([
            'id' => 1686293227,
            'delivery_price_per_unit_volume' => 1545.61,
        ]);
        $this->assertTrue($calc->calculate($settlementMoscow, [$item1], $packingVolumeFactor, true), $info);
        $this->assertSame(162.83, $calc->getResult(), $info);

        $info = 'Regular, low density item for Ekb. Not calculated';
        $settlementEkb = new Settlement([
            'id' => 27503892,
            'delivery_price_per_unit_volume' => 500.77,
        ]);
        $item = new Item([
            'id' => 1,
            'weight' => 690.0,
            'qty' => 69,
            'is_paid_delivery' => true,
            'product_volume' => 2.049,
            'package_volume' => 0.759,
            'packing_volume_factor' => 1.1,
            'is_boxed' => false,
            'delivery_discount' => 0.2,
        ]);
        $this->assertTrue($calc->calculate($settlementEkb, [$item], $packingVolumeFactor), $info);
        $this->assertSame(0.0, $calc->getResult(), $info);

        $info = 'Regular, low density item for Ekb. Calculated';
        $item = new Item([
            'id' => 1,
            'weight' => 690.0,
            'qty' => 69,
            'is_paid_delivery' => true,
            'is_paid_delivery_ekb' => true,
            'product_volume' => 2.049,
            'package_volume' => 0.759,
            'packing_volume_factor' => 1.1,
            'is_boxed' => false,
            'delivery_discount' => 0.2,
        ]);
        $this->assertTrue($calc->calculate($settlementEkb, [$item], $packingVolumeFactor), $info);
        $this->assertSame(76.29, $calc->getResult(), $info);

        $info = 'Boxed, low density item with volume factor source';
        $packingVolumeFactor = new PackingVolumeFactor(new VolumeFactorSource());
        $logger->info($info);
        $item = new Item([
            'id' => 1,
            'weight' => 690.0,
            'qty' => 69,
            'is_paid_delivery' => true,
            'product_volume' => 2.049,
            'package_volume' => 2.049,
            'packing_volume_factor' => 1.1,
            'is_boxed' => true,
            'box_volume' => 40.986,
            'box_capacity' => 20,
            'delivery_discount' => 0.2,
        ]);
        $this->assertTrue($calc->calculate($settlement, [$item], $packingVolumeFactor), $info);
        $this->assertSame(235.48, $calc->getResult(), $info);
    }
}
