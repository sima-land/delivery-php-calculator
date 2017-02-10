# Расчет стоимости доставки [www.sima-land.ru](https://www.sima-land.ru)

[![Build Status](https://travis-ci.org/sima-land/delivery-php-calculator.svg?branch=master)](https://travis-ci.org/sima-land/delivery-php-calculator)
[![StyleCI](https://styleci.io/repos/73701387/shield?branch=master)](https://styleci.io/repos/73701387)


Основным способом расчета стоимость доставки является использование API
https://www.sima-land.ru/api/v3/help/#Стоимость-доставки

Однако, в ряде случаев, предпочтительно расчитывать стоимость досавки без 
использования API. Например:

- очень большое количество запросов к API, которое не укладывается в лимиты
- нужно показывать стомость доставки прямо в каталоге и нет возможности ждать ответа API

В данных случаях можно использовать данный класс, который позволить вычислять 
стоимость доставки "на лету".

Для расчета стоимости доставки товара необходимо три сущности:

- точка доставки, объект реализующий [PointInterface](src/PointInterface.php)
- товар, объект реализующий [ItemInterface](src/ItemInterface.php)
- источник данных о коэффициентах упаковки, объект реализующий [PackingVolumeFactorSourceInterface](src/PackingVolumeFactorSourceInterface.php)

Для того, чтобы  посчитать стоимость доставки товара нужно создать объект класса калькулятора
с указанием точки доставки и коэффициентов упаковки.

```php
$calc = new Calculator($defaultVolumeFactor, $point, false)
```

Третий аргумент обозначает признак "локальности", точки по отношению к складу. Если ```true```, то доставка будет 
считаться бесплатной для всех товаров кроме тех, у которых метод ```isPaidDeliveryLocal()``` возвращает ```true```

Для добавления товара к стоимости доставки используется функция ```addItem($item, $qty)```,
для получения результатов ```getResult()```. Для того чтобы обнулить результат используйте ```reset()```. 
В случае ошибки метод ```addItem($item, $qty)``` вернет false. Информацию об ошибках после этого можно 
посмотреть с помощью метода ```getErrors()```.

Пример:

```php
$calc->addItem($item1, 10)
$calc->addItem($item2, 1000)
echo $calc->getResult();  // вывод стоимости доставки item1 10 шт. и item2 1000 шт.

$calc->reset();
$calc->addItem($item3, 1)
echo $calc->getResult(); // вывод стоимости доставки item3 1 шт. 

// $item4->getWeight() = 0
if (!$calc->addItem($item4, 1000)) {
	return $calc->getErrors() // ['Weight must be positive, weight=0']
}
```

## Точка доставки 

Точка доставки, объект реализующий [PointInterface](src/PointInterface.php), данные по большинству 
городов доставки можно получить по API https://www.sima-land.ru/api/v3/help/#Города-доставки

Особые случаи:
- расчет доставки до пункта самовывоза в г. Москва можно используя модель [MoscowPoint](src/models/MoscowPoint.php)
- при расчете доставки в г. Екатеринбург признак "локальности" должен быть ```true```

## Товар

Все данные для реализации [ItemInterface](src/ItemInterface.php) можно получить 
по API https://www.sima-land.ru/api/v3/help/#Товар 

## Данные о коэффициентах упаковки

Данные о коэффициентах упаковки, объект реализующий [PackingVolumeFactorSourceInterface](src/PackingVolumeFactorSourceInterface.php). 
Можно воспользоваться готовой реализацией модели [DefaultPackingVolumeFactorSource](src/models/DefaultPackingVolumeFactorSource.php)
