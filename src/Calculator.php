<?php

declare(strict_types=1);

namespace SimaLand\DeliveryCalculator;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;

/**
 * Кальулятор платной доставки OOO Сима-ленд.
 *
 * Пример использвания:
 *
 * $calc = new Calculator()
 * if ($calc->addItem($item, 10)) {
 *    echo "Стоимость доставки " . $calc->getResult()
 * } else {
 *    echo "Ошибка при расчете: " . $calc->getErrors();
 * }
 */
class Calculator implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    // Ограничение на объем одной товарной позиции
    const ITEM_VOLUME_LIMIT = 5000;

    // Граница плотности, делит товары на товары с высокой и низкой плотностью
    const ITEM_DENSITY_LIMIT = 250;

    /**
     * @var float Результат расчета
     */
    protected $result;

    /**
     * @var []string Массив с сообщениями об ошибках
     */
    protected $errors = [];

    /**
     * @var []string Массив с информацией о промежуточных расчетах
     */
    protected $trace = [];

    /**
     * @var bool
     */
    private $isLocal;

    /**
     * @var \SimaLand\DeliveryCalculator\PointInterface Массив с информацией о промежуточных расчетах
     */
    private $point;

    /**
     * @var VolumeFactorSourceInterface
     */
    private $volumeFactorSource;

    /**
     * Конструктор калькулятора
     *
     * @param VolumeFactorSourceInterface $volumeFactorSource
     * @param PointInterface $point точка доставки
     * @param bool $isLocal признак "локальной" по отношению к складу доставки
     */
    public function __construct(
        VolumeFactorSourceInterface $volumeFactorSource,
        PointInterface $point,
        bool $isLocal
    ) {
        $this->volumeFactorSource = $volumeFactorSource;
        $this->point = $point;
        $this->isLocal = $isLocal;
        $this->reset();
    }

    /**
     * @param ItemInterface $item
     * @param int $qty
     * @return bool
     */
    public function addItem(ItemInterface $item, int $qty) : bool
    {
        $this->trace(
            'Add item',
            [
                // point info
                'point' => $this->point,
                'point_delivery_price_per_unit_volume' => $this->point->getDeliveryPricePerUnitVolume(),

                // item info
                'item' => $item,
                'item_is_paid_delivery' => $item->isPaidDelivery(),
                'item_is_paid_delivery_local' => $item->isPaidDeliveryLocal(),
                'item_weight' => $item->getWeight(),
                'item_product_volume' => $item->getProductVolume(),
                'item_package_volume' => $item->getPackageVolume(),
                'item_packing_volume_factor' => $item->getPackingVolumeFactor(),
                'item_is_boxed' => $item->isBoxed(),
                'item_box_volume' => $item->getBoxVolume(),
                'item_box_capacity' => $item->getBoxCapacity(),
                'item_delivery_discount' => $item->getDeliveryDiscount(),

                // qty
                'qty' => $qty,
            ]
        );

        if (!$item->isPaidDelivery() || $this->isLocal && !$item->isPaidDeliveryLocal()) {
            $this->trace("Free delivery");

            return true;
        }

        $calculatedVolume = $this->getCalculatedVolume($item, $qty);
        if ($this->getErrors()) {
            return false;
        }

        $result = $calculatedVolume
            * $this->point->getDeliveryPricePerUnitVolume()
            * (1 - $item->getDeliveryDiscount());
        $this->result += $result;
        $this->trace("paid delivery=$result, overall={$this->result}");

        return true;
    }

    /**
     * @param ItemInterface $item
     * @param int $qty
     * @return float
     */
    public function getCalculatedVolume(ItemInterface $item, int $qty) : float
    {
        $calculatedVolume = 0.0;
        if ($qty <= 0) {
            $this->error("Qty must be positive, qty=$qty");
        }
        $this->validateItem($item);
        if (($tmp = $this->point->getDeliveryPricePerUnitVolume()) <= 0) {
            $this->error("Invalid delivery per unit price $tmp");
        }
        if (!$this->getErrors()) {
            if ($item->isBoxed() && $item->getBoxCapacity() > 1) {
                $calculatedVolume = $this->getBoxedVolume(
                    $qty,
                    $item->getWeight(),
                    $item->getPackageVolume(),
                    $item->getBoxVolume(),
                    $item->getBoxCapacity()
                );
            } else {
                $calculatedVolume = $this->getRegularVolume(
                    $qty,
                    $item->getWeight(),
                    $item->getProductVolume(),
                    $item->getPackingVolumeFactor(),
                    $item->getCustomBoxCapacity(),
                    $item->getBoxVolume()
                );
            }
        }

        return $calculatedVolume;
    }


    /**
     * Возвращает расчетный объем для обычного товара.
     *
     * @param int $qty
     * @param float $weight
     * @param float $productVolume
     * @param float $packingVolumeFactor
     * @param int $customBoxCapacity
     * @param float $boxVolume
     *
     * @return float
     */
    protected function getRegularVolume(
        int $qty,
        float $weight,
        float $productVolume,
        float $packingVolumeFactor,
        int $customBoxCapacity,
        float $boxVolume
    ) : float {
        $packingVolumeFactor = $packingVolumeFactor ?: $this->volumeFactorSource->getPackingFactor($productVolume);
        $this->trace('Packing volume factor ' . $packingVolumeFactor);
        $productVolumeWithFactor = $productVolume * $packingVolumeFactor;

        if ($customBoxCapacity > 1 && $boxVolume > 0) {
            $placementFactor = $this->volumeFactorSource->getPlacementFactor($boxVolume);
            $this->trace('Placement volume factor ' . $placementFactor);
            $boxVolumeWithFactor = $placementFactor * $boxVolume;

            // Вес бокса
            $boxWeight = $weight * $customBoxCapacity;
            // Колличество боксов с товаром
            $boxCount = floor($qty / $customBoxCapacity);
            // Колличество оставшихся товаров
            $restItemsCount = $qty % $customBoxCapacity;
            // Объем боксов
            $totalBoxVolume = $boxVolumeWithFactor * $boxCount;
            // Объем оставшихся товаров
            $totalRestItemsVolume = $productVolumeWithFactor * $restItemsCount;
            // Объем боксов, скорректированный по плотности
            $totalBoxVolume = $this->getDensityCorrectedVolume($boxWeight * $boxCount, $totalBoxVolume);
            // Объем товаров, скорректированный по плотности
            $totalRestItemsVolume = $this->getDensityCorrectedVolume($weight * $restItemsCount, $totalRestItemsVolume);
            $totalVolume = $totalBoxVolume + $totalRestItemsVolume;
        } else {
            $totalVolume = $productVolumeWithFactor * $qty;
            if ($totalVolume > self::ITEM_VOLUME_LIMIT) {
                $this->error("Total volume $totalVolume exceeds volume limit");
            }
            $totalVolume = $this->getDensityCorrectedVolume($weight * $qty, $totalVolume);
        }

        return $totalVolume;
    }

    /**
     * Возвращает расчетный объем для вкладываемого товара.
     *
     * @param float $qty
     * @param float $weight
     * @param float $packageVolume
     * @param float $boxVolume
     * @param int $boxCapacity
     *
     * @return float
     */
    protected function getBoxedVolume(
        float $qty,
        float $weight,
        float $packageVolume,
        float $boxVolume,
        int $boxCapacity
    ) : float {
        if ($qty > 1 && $boxCapacity > 1) {
            $volume = ($qty - 1) * ($boxVolume - $packageVolume) / ($boxCapacity - 1) + $packageVolume;
        } else {
            $volume = $packageVolume;
        }
        $packingVolumeFactor = $this->volumeFactorSource->getPackingFactor($volume);
        $this->trace('Packing volume factor ' . $packingVolumeFactor);

        return $this->getDensityCorrectedVolume($weight * $qty, $volume * $packingVolumeFactor);
    }

    /**
     * Возвращает расчетный объем скорректированный с учетом плотности.
     *
     * @param float $weight
     * @param float $volume
     *
     * @return float
     */
    protected function getDensityCorrectedVolume($weight, $volume) : float
    {
        $density = $weight / $volume;
        if ($density <= self::ITEM_DENSITY_LIMIT) {
            $result = $volume / 1000;
            $this->trace("Low density=$density, volume=$result");
        } else {
            $result = $weight / (self::ITEM_DENSITY_LIMIT * 1000);
            $this->trace("High density=$density, volume=$result");
        }

        return $result;
    }

    /**
     * Возвращает результат расчета.
     *
     * @param int $precision Количество знаков после запятой
     *
     * @return float Результат расчета
     */
    public function getResult($precision = 2) : float
    {
        return round($this->result, $precision);
    }

    /**
     * Возвращает массив сообщений об ошибках.
     *
     * @return string[]
     */
    public function getErrors() : array
    {
        return $this->errors;
    }

    /**
     * Обнуляет результат расчета
     *
     * @return $this
     */
    public function reset() : Calculator
    {
        $this->result = 0.0;
        $this->errors = [];
        $this->trace = [];
        return $this;
    }

    /**
     * Выводит лог расчета
     *
     * @return array
     */
    public function getTrace() : array
    {
        return $this->trace;
    }

    /**
     * Добавляет сообщение об ошибке, также отправляя его в лог.
     *
     * @param $message
     */
    protected function error($message)
    {
        $this->errors[] = $message;
        if (!is_null($this->logger)) {
            $this->logger->log(LogLevel::ERROR, $message);
        }
    }

    /**
     * Отправляет сообщение в логгер если он установлен.
     *
     * @param $message
     * @param array $context
     */
    protected function trace($message, array $context = [])
    {
        $this->trace[] = [$message, $context];
        if (!is_null($this->logger)) {
            $this->logger->log(LogLevel::INFO, $message, $context);
        }
    }

    /**
     * Проверяет все ли методы $item возвращают корректные значения.
     *
     * @param \SimaLand\DeliveryCalculator\ItemInterface $item
     */
    protected function validateItem(ItemInterface $item)
    {
        if (($tmp = $item->getWeight()) <= 0) {
            $this->error("Weight must be positive, weight=$tmp");
        }
        if ($item->isBoxed()) {
            if (($tmp = $item->getPackageVolume()) <= 0) {
                $this->error("PackageVolume must be positive, package_volume=$tmp");
            }
            if (($tmp = $item->getBoxVolume()) <= 0) {
                $this->error("BoxVolume must be positive, box_volume=$tmp");
            }
        } else {
            if (($tmp = $item->getProductVolume()) <= 0) {
                $this->error("ProductVolume must be positive, product_volume=$tmp");
            }
            if (($tmp = $item->getPackingVolumeFactor()) < 1 && $tmp != 0) {
                $this->error("PackingVolumeFactor=$tmp, must not be less than one or must be zero");
            }
        }
    }
}
