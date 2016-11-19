<?php
namespace SimaLand\DeliveryCalculator\Tests;

use PHPUnit\Framework\TestCase;
use SimaLand\DeliveryCalculator\Calculator;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use pahanini\Monolog\Formatter\CliFormatter;

class ItemListTest extends TestCase
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

        $info = "Regular, low density item";
        $logger->info($info);
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
        $this->assertTrue($calc->calculate($settlement, [$item]), $info);
        $this->assertSame(235.48, $calc->getResult(), $info);

        $info = "Regular, high density item";
        $logger->info($info);
        $item = new Item([
            'id' => 2,
            'weight' => 200.0,
            'qty' => 69,
            'is_paid_delivery' => true,
            'product_volume' => 2.049,
            'package_volume' => 0.759,
            'packing_volume_factor' => 1.1,
            'is_boxed' => false,
            'delivery_discount' => 0.2,
        ]);
        $this->assertTrue($calc->calculate($settlement, [$item]), $info);
        $this->assertSame(192.30, $calc->getResult(), $info);

        $info = "Boxed, low density item";
        $logger->info($info);
        $item = new Item([
            'id' => 3,
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

        $info = "Boxed, low density item";
        $logger->info($info);
        $item = new Item([
            'id' => 4,
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

        $info = "Boxed, very low density item";
        $logger->info($info);
        $item = new Item([
            'id' => 10,
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
        $this->assertSame(1393.67, $calc->getResult(), $info);

        $info = "Boxed, very low density item";
        $logger->info($info);
        $item = new Item([
            'id' => 10,
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
        $this->assertSame(1393.67, $calc->getResult(), $info);

        $info = "Boxed, very low density item with discount";
        $logger->info($info);
        $item = new Item([
            'id' => 11,
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
        $this->assertSame(1045.26, $calc->getResult(), $info);





    }
}

/**
<
    <item
        id="5"
        name="превышен лимит на максимальный объем товарной позиции"
        weight="690"
        qty="20000"
        is_paid_delivery="1"
        product_volume="2.049"
        package_volume="0.759"
        packing_volume_factor="1.1"
        is_boxed="0"
        box_volume=""
        box_capacity=""
        delivery_discount="0.2" />
    <item
        id="6"
        name="кол-во ноль"
        weight="690"
        qty="0"
        is_paid_delivery="1"
        product_volume="2.049"
        package_volume="0.759"
        packing_volume_factor="1.1"
        is_boxed="0"
        box_volume=""
        box_capacity=""
        delivery_discount="0.2" />
    <item
        id="7"
        name="вес ноль"
        weight=""
        qty="1"
        is_paid_delivery="1"
        product_volume="2.049"
        package_volume="0.759"
        packing_volume_factor="1.1"
        is_boxed="0"
        box_volume=""
        box_capacity=""
        delivery_discount="0.2" />
    <item
        id="8"
        name="нет box_capacity"
        weight="12"
        qty="1"
        is_paid_delivery="1"
        product_volume="2.049"
        package_volume="0.759"
        packing_volume_factor="1.1"
        is_boxed="1"
        box_volume=""
        box_capacity=""
        delivery_discount="0.2" />
    <item
        id="9"
        name="нет product_volume"
        weight="12"
        qty="1"
        is_paid_delivery="1"
        product_volume="0"
        package_volume="0.759"
        packing_volume_factor="1.1"
        is_boxed="0"
        box_volume=""
        box_capacity=""
        delivery_discount="0.2" />
    <item
        id="10"
        name="боксовый товар очень низкой плотости"
        weight="250"
        qty="500"
        is_paid_delivery="1"
        product_volume="2.049"
        package_volume="2.049"
        packing_volume_factor="1.1"
        is_boxed="1"
        box_volume="40.986"
        box_capacity="20"
        delivery_discount="0.2" />



    <settlement
        id="1"
        name="Просто город"
        delivery_price_per_unit_volume="1545.61"/>
    <settlement
        id="27503892"
        name="Екатеринбург"
        delivery_price_per_unit_volume="123"/>
    <settlement
        id="2"
        name="Некорректный город"
        delivery_price_per_unit_volume=""/>
    <settlement
        id="1686293227"
        name="Москва"
        delivery_price_per_unit_volume="123"/>
</dataset>
*/