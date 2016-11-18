<?php declare(strict_types = 1);

namespace SimaLand\DeliveryCalculator;

use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;
use \Psr\Log\LogLevel;

/**
 * Кальулятор платной доставки OOO Сима-ленд
 *
 * Пример использвания:
 *
 * $calc = new Calculator()
 * if ($calc->calculate($settlement, $items)) {
 *    echo "Стоимость доставки " . $calc->getResult()
 * } else {
 *    echo "Ошибка при расчете: " . $calc->getError();
 * }
 *
 * @package SimaLand\DeliveryCalculator
 */
class Calculator implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    // Ограничение на объем одной тоарной позиции
    const ITEM_VOLUME_LIMIT = 5000;

    // Граница плотности, делит товары на товары с высокой и низкой плотностью
    const ITEM_DENSITY_LIMIT = 250;

    /**
     * @var float Результат расчета
     */
    protected $result;

    /**
     * @var string Последнее сообщение об ошибке
     */
    protected $error = "";

    /**
     * Расчитывает стоимость доставки
     *
     * Функция возвращает true если расчет доставки завершился без ошибок. Результат расчета
     * можно получить функцией  getResult()
     *
     * Если в процессе расчета произошла ошибка, то функция вернет false. Подробную информацию об ошибке
     * можно получить воспользовавшись функцией getError()
     *
     * @param \SimaLand\DeliveryCalculator\SettlementInterface
     * @param []\SimaLand\DeliveryCalculator\ItemInterface
     * @return bool
     */
    public function calculate(SettlementInterface $settlement, array $items) : bool
    {
        $this->result = 0.0;
        $this->error = "";
        $this->log(LogLevel::DEBUG, "Settlement", [
            'id' => $settlement->getID(),
            'delivery_price_per_unit_volume' => $settlement->getDeliveryPricePerUnitVolume(),
        ]);
        foreach ($items as $item) {
            if ($this->addItem($settlement, $item)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param SettlementInterface $settlement
     * @param ItemInterface $item
     * @return bool
     */
    protected function addItem(SettlementInterface $settlement, ItemInterface $item) : bool
    {
        $this->log(LogLevel::DEBUG, "Item", [
            "id" => $item->getID(),
            "is_paid_delivery" => $item->isPaidDelivery(),
            "qty" => $item->getQty(),
            'weight' => $item->getWeight(),
            'productVolume' => $item->getProductVolume(),
            'packageVolume' => $item->getPackageVolume(),
            'packingVolumeFactor' => $item->getPackingVolumeFactor(),
            'is_boxed' => $item->isBoxed(),
            'box_volume' => $item->getBoxVolume(),
            'box_capacity' => $item->getBoxCapacity(),
            'delivery_discount' => $item->getDeliveryDiscount(),
        ]);
        if (!$this->isItemValid($item)) {
            return false;
        }
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
        $this->log(LogLevel::DEBUG, "Calculated volume=$calculatedVolume");

        $result = $calculatedVolume
            * $settlement->getDeliveryPricePerUnitVolume()
            * (1 - $item->getDeliveryDiscount());
        $this->log(LogLevel::DEBUG, "Result=$result");

        $this->result += $result;
        return true;
    }

    /**
     * Возвращает true если все методы $item возвращают корректные значения
     *
     * @param ItemInterface $item
     * @return bool
     */
    public function isItemValid(ItemInterface $item) : bool {
        if (($tmp = $item->getQty()) <= 0) {
            $this->error("Negative qty=$tmp");
        }
        if (($tmp=$item->getWeight()) <= 0) {
             $this->error("Negative weight=$tmp");
        }
        if (($tmp=$item->getPackingVolumeFactor()) < 1) {
            $this->error("PackingVolumeFactor=$tmp, must be equal or greater than one");
        }
        if ($item->isBoxed()) {
            if (($tmp = $item->getPackageVolume()) <= 0) {
                $this->error("PackageVolume=$tmp, must be positive");
            }
            if (($tmp = $item->getBoxVolume()) <= 0) {
                $this->error("BoxVolume=$tmp, must be positive");
            }
            if (($tmp = $item->getBoxCapacity()) <= 0) {
                $this->error("BoxCapacity=$tmp, must be positive");
            }
        } else {
            if (($tmp = $item->getProductVolume()) <= 0) {
                $this->error("ProductVolume=$tmp, must be positive");
            }
        }
        return $this->getError() === "";
    }

    /**
     * Возвращает расчетный объем для обычного товара
     *
     * @param float $productVolume
     * @param float $packingVolumeFactor
     * @param int $qty
     * @param float $weight
     * @return float
     * @throws Exception
     */
    protected function getRegularVolume(
        int $qty,
        float $weight,
        float $productVolume,
        float $packingVolumeFactor
    ) : float
    {
        $volume = $productVolume * $packingVolumeFactor;
        $totalVolume = $volume * $qty;
        if ($totalVolume > self::ITEM_VOLUME_LIMIT) {
            throw new Exception(sprintf("Total volume %f exceeds limit %f", $totalVolume, self::ITEM_VOLUME_LIMIT));
        }

        return $this->getDensityCorrectedVolume($weight * $qty, $totalVolume);
    }

    /**
     * Возвращает расчетный объем исходя для вкладываемого товара
     *
     * @param float $weight
     * @param float $qty
     * @param float $packageVolume
     * @param float $boxVolume
     * @param int $boxCapacity
     * @param float $packingVolumeFactor
     * @return float
     */
    protected function getBoxedVolume(
        float $qty,
        float $weight,
        float $packageVolume,
        float $boxVolume,
        int $boxCapacity,
        float $packingVolumeFactor
    ) : float
    {
        if ($qty > 1 && $boxCapacity > 1) {
            $volume = ($qty - 1) * ($boxVolume - $packageVolume) / ($boxCapacity - 1) + $packageVolume;
        } else {
            $volume = $packageVolume;
        }
        return $this->getDensityCorrectedVolume($weight * $qty, $volume * $packingVolumeFactor);
    }

    /**
     * Возвращает расчетный объем скорректированный с учетом плотности
     *
     * @param $weight
     * @param $volume
     * @return float
     */
    protected function getDensityCorrectedVolume($weight, $volume) : float
    {
        $density = $weight / $volume;
        if ($density <= self::ITEM_DENSITY_LIMIT) {
            $result = $volume / 1000;
            $this->log(LogLevel::DEBUG, "Low density=$density, volume=$result");
        } else {
            $result = $weight / (self::ITEM_DENSITY_LIMIT * 1000);
            $this->log(LogLevel::DEBUG, "High density=$density, volume=$result");
        }

        return $result;
    }

    /**
     * Возвращает результат расчета
     * @param int $precision Количество знаков после запятой
     * @return float Результат расчета
     */
    public function getResult($precision = 2) {
        return round($this->result, $precision);
    }

    /**
     * Возвращает сообщение об ошибке
     * @return string
     */
    public function getError() : string {
        return $this->error;
    }

    /**
     * Запоминает последнее сообщение об ошибке, также отправляя его в лог
     * @param $message
     */
    protected function error($message) {
        $this->error = $message;
        $this->log(LogLevel::ERROR, $message);
    }

    /**
     * Отправляет сообщение в логгер если он установлен
     *
     * @param $level
     * @param $message
     * @param array $context
     */
    protected function log($level, $message, array $context = [])
    {
        if (!is_null($this->logger)) {
            $this->logger->log($level, $message, $context);
        }
    }
}



