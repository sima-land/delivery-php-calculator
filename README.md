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

Пример:

```php

$calc = new Calculator()
if ($calc->calculate($settlement, $items, $packingVolumeFactor)) {
    echo "Стоимость доставки " . $calc->getResult()
} else {
    echo "Ошибка при расчете: " . $calc->getErrors();
}
```
Чтобы посчитать стоимость доставки для Москвы нужно передать в метод calculate 
четвертым параметром true

Пример:

```php

$calc = new Calculator()
$calc->calculate($settlement, $items, $packingVolumeFactor)
```