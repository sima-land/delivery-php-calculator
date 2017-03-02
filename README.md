# Расчет стоимости доставки [www.sima-land.ru](https://www.sima-land.ru)

[![Build Status](https://travis-ci.org/sima-land/delivery-php-calculator.svg?branch=master)](https://travis-ci.org/sima-land/delivery-php-calculator)
[![StyleCI](https://styleci.io/repos/73701387/shield?branch=master)](https://styleci.io/repos/73701387)


Основным способом расчета стоимости доставки является использование API
https://www.sima-land.ru/api/v3/help/#Стоимость-доставки

Однако в ряду случаев предпочтительно рассчитывать стоимость доставки без
использования API. Например:

- Количество запросов к API не укладывается в установленные лимиты.
- Требуемая скорость работы не позволяет ожидать ответа от API.

В описанных выше случаях рекомендуется использовать данный компонент, который позволяет вычислять
стоимость доставки "на лету".

Для расчета стоимости доставки товара необходимо три сущности:

- Точка доставки - объект, реализующий [PointInterface](src/PointInterface.php)
- Товар - объект, реализующий [ItemInterface](src/ItemInterface.php)
- Источник данных о коэффициентах упаковки - объект, реализующий [VolumeFactorSourceInterface](src/VolumeFactorSourceInterface.php)

Для того, чтобы произвести расчёт стоимости доставки товара, необходимо создать объект класса калькулятора
с указанием точки доставки и коэффициентов упаковки.

```php
$calc = new Calculator($defaultVolumeFactor, $point, false)
```

Третий аргумент обозначает признак "локальности" точки по отношению к складу. Если ```true```, то доставка будет
считаться бесплатной для всех товаров кроме тех, у которых метод ```isPaidDeliveryLocal()``` возвращает ```true```

Для добавления товара к расчёту стоимости доставки используется функция ```addItem($item, $qty)```,
для получения результатов - ```getResult()```. Для того, чтобы обнулить результат, используйте ```reset()```.
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

Точку доставки представляет объект, реализующий [PointInterface](src/PointInterface.php), данные по большинству
городов доставки можно получить по API https://www.sima-land.ru/api/v3/help/#Города-доставки

Особые случаи:
- Расчёт стоимости доставки до пункта самовывоза в г. Москва осуществляется с помощью класса [MoscowPointAbstract](src/models/MoscowPointAbstract.php)
Нужно расширить этот класс, переопределив метод ```hasNoDiscount```
- При расчёте доставки в г. Екатеринбург признак "локальности" должен быть ```true```

## Товар

Все данные для реализации [ItemInterface](src/ItemInterface.php) можно получить 
по API https://www.sima-land.ru/api/v3/help/#Товар 

## Данные о коэффициентах упаковки

Данные о коэффициентах упаковки представляет объект, реализующий [VolumeFactorSourceInterface](src/VolumeFactorSourceInterface.php).
Можно воспользоваться готовой реализацией модели [DefaultVolumeFactorSource](src/models/DefaultVolumeFactorSource.php)
