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
 * if ($calc->calculate($settlement, $items)) {
 *    echo "Стоимость доставки " . $calc->getResult()
 * } else {
 *    echo "Ошибка при расчете: " . $calc->getErrors();
 * }
 */
class calculator implements LoggerAwareInterface
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
     * Расчитывает стоимость доставки.
     *
     * Функция возвращает true если расчет доставки завершился без ошибок. Результат расчета
     * можно получить функцией  getResult()
     *
     * Если в процессе расчета произошла ошибка, то функция вернет false. Подробную информацию об ошибке
     * можно получить воспользовавшись функцией getError()
     *
     * @param \SimaLand\DeliveryCalculator\SettlementInterface
     * @param []\SimaLand\DeliveryCalculator\ItemInterface
     *
     * @return bool
     */
    public function calculate(SettlementInterface $settlement, array $items) : bool
    {
        $this->result = 0.0;
        $this->errors = [];
        $this->trace('Settlement', [
            'id' => $settlement->getID(),
            'delivery_price_per_unit_volume' => $settlement->getDeliveryPricePerUnitVolume(),
        ]);
        foreach ($items as $item) {
            $this->checkItem($item);
        }
        if (!$this->errors) {
            $this->addItem($settlement, $item);
        }

        return !(bool) $this->errors;
    }

    /**
     * @param SettlementInterface $settlement
     * @param ItemInterface       $item
     *
     * @return bool
     */
    protected function addItem(SettlementInterface $settlement, ItemInterface $item) : bool
    {
        $this->trace('Item', [
            'id' => $item->getID(),
            'is_paid_delivery' => $item->isPaidDelivery(),
            'qty' => $item->getQty(),
            'weight' => $item->getWeight(),
            'productVolume' => $item->getProductVolume(),
            'packageVolume' => $item->getPackageVolume(),
            'packingVolumeFactor' => $item->getPackingVolumeFactor(),
            'is_boxed' => $item->isBoxed(),
            'box_volume' => $item->getBoxVolume(),
            'box_capacity' => $item->getBoxCapacity(),
            'delivery_discount' => $item->getDeliveryDiscount(),
        ]);
        if ($item->isBoxed()) {
            $calculatedVolume = $this->getBoxedVolume(
                $item->getQty(),
                $item->getWeight(),
                $item->getPackageVolume(),
                $item->getBoxVolume(),
                $item->getBoxCapacity(),
                $item->getPackingVolumeFactor()
            );
        } else {
            $calculatedVolume = $this->getRegularVolume(
                $item->getQty(),
                $item->getWeight(),
                $item->getProductVolume(),
                $item->getPackingVolumeFactor()
            );
        }

        $result = $calculatedVolume
            * $settlement->getDeliveryPricePerUnitVolume()
            * (1 - $item->getDeliveryDiscount());
        $this->trace("Result=$result");

        $this->result += $result;

        return true;
    }

    /**
     * Проверяет все ли методы $item возвращают корректные значения.
     *
     * @param ItemInterface $item
     */
    protected function checkItem(ItemInterface $item)
    {
        if (($tmp = $item->getQty()) <= 0) {
            $this->error("Qty must be positive, qty=$tmp");
        }
        if (($tmp = $item->getWeight()) <= 0) {
            $this->error("Weight must be positive, weight=$tmp");
        }
        if (($tmp = $item->getPackingVolumeFactor()) < 1) {
            $this->error("PackingVolumeFactor=$tmp, must be equal or greater than one");
        }
        if ($item->isBoxed()) {
            if (($tmp = $item->getPackageVolume()) <= 0) {
                $this->error("PackageVolume must be positive, package_volume=$tmp, ");
            }
            if (($tmp = $item->getBoxVolume()) <= 0) {
                $this->error("BoxVolume must be positive, box_volume=$tmp, ");
            }
            if (($tmp = $item->getBoxCapacity()) <= 0) {
                $this->error("BoxCapacity must be positive, box_capacity=$tmp,");
            }
        } else {
            if (($tmp = $item->getProductVolume()) <= 0) {
                $this->error("ProductVolume must be positive, product_volume=$tmp, ");
            }
        }
    }

    /**
     * Возвращает расчетный объем для обычного товара.
     *
     * @param float $productVolume
     * @param float $packingVolumeFactor
     * @param int   $qty
     * @param float $weight
     *
     * @return float
     *
     * @throws Exception
     */
    protected function getRegularVolume(
        int $qty,
        float $weight,
        float $productVolume,
        float $packingVolumeFactor
    ) : float {
        $volume = $productVolume * $packingVolumeFactor;
        $totalVolume = $volume * $qty;
        if ($totalVolume > self::ITEM_VOLUME_LIMIT) {
            $this->error("Total volume $totalVolume exceeds volume limit");
        }

        return $this->getDensityCorrectedVolume($weight * $qty, $totalVolume);
    }

    /**
     * Возвращает расчетный объем для вкладываемого товара.
     *
     * @param float $weight
     * @param float $qty
     * @param float $packageVolume
     * @param float $boxVolume
     * @param int   $boxCapacity
     * @param float $packingVolumeFactor
     *
     * @return float
     */
    protected function getBoxedVolume(
        float $qty,
        float $weight,
        float $packageVolume,
        float $boxVolume,
        int $boxCapacity,
        float $packingVolumeFactor
    ) : float {
        if ($qty > 1 && $boxCapacity > 1) {
            $volume = ($qty - 1) * ($boxVolume - $packageVolume) / ($boxCapacity - 1) + $packageVolume;
        } else {
            $volume = $packageVolume;
        }

        return $this->getDensityCorrectedVolume($weight * $qty, $volume * $packingVolumeFactor);
    }

    /**
     * Возвращает расчетный объем скорректированный с учетом плотности.
     *
     * @param $weight
     * @param $volume
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
    public function getResult($precision = 2)
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
}
