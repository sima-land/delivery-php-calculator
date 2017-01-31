<?php

namespace SimaLand\DeliveryCalculator\tests;

use PHPUnit\Framework\TestCase;
use SimaLand\DeliveryCalculator\Calculator;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use pahanini\Monolog\Formatter\CliFormatter;

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
        $this->assertTrue($calc->calculate($settlement, [$item1]), $info);
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
        $this->assertTrue($calc->calculate($settlement, [$item2]), $info);
        $this->assertSame(192.30, $calc->getResult(), $info);

        $info = 'Multiple items calculate';
        $this->assertTrue($calc->calculate($settlement, [$item1, $item2]), $info);
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
        $this->assertTrue($calc->calculate($settlement, [$item]), $info);
        $this->assertSame(235.48, $calc->getResult(), $info);

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
            'box_volume' => 40.986,
            'box_capacity' => 20,
            'delivery_discount' => 0.2,
        ]);
        $this->assertTrue($calc->calculate($settlement, [$item]), $info);
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
        $this->assertTrue($calc->calculate($settlement, [$item]), $info);
        $this->assertSame(1266.98, $calc->getResult(), $info);

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
        $this->assertTrue($calc->calculate($settlement, [$item]), $info);
        $this->assertSame(950.23, $calc->getResult(), $info);

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
        $this->assertFalse($calc->calculate($settlement, [$item]), $info);

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
        $this->assertFalse($calc->calculate($settlement, [$item]), $info);

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
        $this->assertFalse($calc->calculate($settlement, [$item]), $info);

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
        $this->assertFalse($calc->calculate($settlement, [$item]), $info);

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
        $this->assertFalse($calc->calculate($settlement, [$item]), $info);

        $info = 'Regular, low density item for Moscow point';
        $calc->moscowSettlementId = 1;
        $this->assertTrue($calc->calculate($settlement, [$item1], true), $info);
        $this->assertSame(162.83, $calc->getResult(), $info);
    }
}
