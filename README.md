# Dbf - обработка DBF-файлов от ЦБ РФ
[![Packagist](https://img.shields.io/packagist/v/SedovSG/cbrf-dbf.svg)](https://packagist.org/packages/sedovsg/cbrf-dbf)
[![Latest Stable Version](https://poser.pugx.org/sedovsg/cbrf-dbf/v/stable)](https://packagist.org/packages/sedovsg/cbrf-dbf)
[![License](https://poser.pugx.org/sedovsg/cbrf-dbf/license)](LICENSE)
[![Build Status](https://travis-ci.org/SedovSG/cbrf-dbf.svg?branch=master)](https://travis-ci.org/SedovSG/cbrf-dbf)
[![Total Downloads](https://poser.pugx.org/sedovsg/cbrf-dbf/downloads)](https://packagist.org/packages/sedovsg/cbrf-dbf)

Библиотека для работы с базой данных DBF, получения и обновления данных в формате DBF от Центрального банка Российской Федерации, которая проста в использовании.
С ней вы сможете получать актуальные данные о кредитных учреждениях, путём чтения и разбора файлов DBF.

Источник данных: [http://www.cbr.ru/Queries/FileSource/33411/GetBicCatalog.xml](http://www.cbr.ru/Queries/FileSource/33411/GetBicCatalog.xml)

## Требования
- php7.0-dbase
- php7.1-zip
- php7.1-xml

## Установка
Установка через Composer:

```bash
$ composer require sedovsg/cbrf-dbf
```

> Как установить сам [![Сomposer](https://getcomposer.org/download/)](https://getcomposer.org/download/)

## Использование

Со структурой DBF-файлов, предоставляемыми кредитными учреждениями, можно ознакомиться на ресурсе: [http://www.zakonprost.ru/content/base/part/407425](http://www.zakonprost.ru/content/base/part/407425)
### Подключение к источнику данных
```php
use Cbrf\Dbf;

$dbf = (new Dbf('dir_name'));
```

### Получение всех элементов

```php
$result = $dbf->
  select('NNP, NAMEP')->
  from('BNKSEEK.DBF')->
  exect()->
  fetchAll();
```

### Получение бщего количества элементов в источнике

```php
  $result = $dbf->
      select()->
      from('BNKSEEK.DBF')->
      exect()->
      rowCount();
```

### Получение информации о свойствах полей источника

```php
  $result = $dbf->
      select()->
      from('BNKSEEK.DBF')->
      exect()->
      getFieldsInfo();
```

### Получение количества полей источника

```php
  $result = $dbf->
      select()->
      from('BNKSEEK.DBF')->
      exect()->
      numFields();
```

### Получение полей источника

```php
  $result = $dbf->
      select()->
      from('BNKSEEK.DBF')->
      exect()->
      getFields();
```

### Фильтрация данных

**Выборка данных по условию "Равно"**

```php
  $result = $dbf->
      select()->
      from('KORREK.DBF')->
      equal('DT_IZM = 19970617,CHS = 1447936521')->
      exect()->
      fetch();
```

**Выборка данных по условию "Исключено"**

```php
  $result = $dbf->
      select()->
      from('BNKSEEK.DBF')->
      exclude('NNP = КРАСНОДАР')->
      exect()->
      fetch();
```

**Выборка данных по условию "Включает"**

```php
  $result = $dbf->
      select()->
      from('BNKSEEK.DBF')->
      include('KSNP = 3010181094525000')->
      exect()->
      fetch();
```

Методы установки полей ``` select() ``` и фильтрации данных ``` equal(), exclude(), include() ``` можно использовать несколько раз, через цепочку вызовов, например:

```php
  $result = $dbf->
      select('NNP, NAMEP')->
      select('KSNP')->
      from('BNKSEEK.DBF')->
      include('KSNP = 301018109')->
      exclude('NNP = КРАСНОДАР')->
      exect()->
      fetch();
```
Кроме того, можно указать данные какого фильтра будут включены в итоговую выборку:

```php
  $result = $dbf->
      select('NNP, NAMEP')->
      select('KSNP')->
      from('BNKSEEK.DBF')->
      include('KSNP = 301018109')->
      exclude('NNP = КРАСНОДАР')->
      exect()->
      fetch(Dbf::FETCH_INCLUDE);
```

### Загрузка архива DBF с сайта ЦБ РФ
```php
$dbf->download();
```

### Обновление DBF-файлов в директории
```php
$dbf->update();
```

### Закрытие соединения с источником
```php
$dbf->сlose();
```

## Журнал Изменений
Пожалуйста, смотрите [список изменений](https://github.com/SedovSG/cbrf-dbf/blob/master/CHANGELOG.md) для получения дополнительной информации о том, что изменилось в последнее время.

## Тестирование
```bash
$ vendor/bin/phpunit
```

## Лицензия
Лицензия BSD 3-Clause. Пожалуйста, см. [файл лицензии](LICENSE) для получения дополнительной информации.
