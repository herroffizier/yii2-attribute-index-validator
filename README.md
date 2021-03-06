Yii2 Attribute Index Validator
==============================

[![Build Status](https://travis-ci.org/herroffizier/yii2-attribute-index-validator.svg?branch=master)](https://travis-ci.org/herroffizier/yii2-attribute-index-validator) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/herroffizier/yii2-attribute-index-validator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/herroffizier/yii2-attribute-index-validator/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/herroffizier/yii2-attribute-index-validator/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/herroffizier/yii2-attribute-index-validator/?branch=master) [![Code Climate](https://codeclimate.com/github/herroffizier/yii2-attribute-index-validator/badges/gpa.svg)](https://codeclimate.com/github/herroffizier/yii2-attribute-index-validator)

This validator solves value collisions for unique model attributes by adding incremental index to repeating values. E.g. ```title``` will become ```title-1``` if item with ```title``` already exists.

Such behavior may be useful for tasks like generating URLs and so on.

Installation
------------

Install validator with Composer:

```
composer require --prefer-dist "herroffizier/yii2-attribute-index-validator:@stable"
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

Validator has a few options to customize its behavior.

* ```separator``` sets separator between original value and index. Default separator is ```-```.
* ```startIndex``` defines start index. Default value is ```1```.
* ```filter``` defines additional filter to be applied to query used to check attribute uniqueness. May be either a string, an array or an anonymous function. In case of string or array ```filter``` value will be passed to ```\yii\web\ActiveQueryInterface::andWhere()``` method. In case of anonymous function its signature must be ```function($query)``` and instance of ```\yii\web\ActiveQueryInterface``` will be passed to it. Default value is ```null```.
