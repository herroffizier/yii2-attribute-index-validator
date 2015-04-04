Yii2 Attribute Index Validator
==============================

[![Build Status](https://travis-ci.org/herroffizier/yii2-attribute-index-validator.svg?branch=master)](https://travis-ci.org/herroffizier/yii2-attribute-index-validator) [![Build Status](https://scrutinizer-ci.com/g/herroffizier/yii2-attribute-index-validator/badges/build.png?b=master)](https://scrutinizer-ci.com/g/herroffizier/yii2-attribute-index-validator/build-status/master) 

This validator solves value collisions for unique model attributes by adding incrementals index to repeating values. E.g. ```title``` will become ```title-1``` if item with ```title``` already exists.

Such behavior may be useful for tasks like generating URLs and so on.

Installation
------------

Install validator with Composer:

```
composer require --prefer-dist herroffizier/yii2-attribute-index-validator "*"
```

Usage
-----

Add validator to your model's rules array before `required` and `unique` validators (if any).

```php
use herroffizier\yii2aiv\AttributeIndexValidator;

...

public function rules()
{
    return [
        [['attribute'], AttributeIndexValidator::className()],
        [['attribute'], 'required'],
        [['attribute'], 'unique'],
    ];
}
```

Validator has a few options to customize its behavor.

* ```separator``` sets separator between original value and index. Default separator is ```-```.
* ```startIndex``` defines start index. Default value is ```1```.