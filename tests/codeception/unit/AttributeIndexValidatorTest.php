<?php
/**
 * Yii2 Attribute Index Validator
 *
 * This file contains validator test.
 *
 * @author  Martin Stolz <herr.offizier@gmail.com>
 */

namespace herroffizier\yii2aiv\tests\codeception\unit;

use Codeception\Specify;
use yii\codeception\TestCase;
use herroffizier\yii2aiv\tests\helpers\Model;
use herroffizier\yii2aiv\tests\helpers\Model2;

class AttributeIndexValidatorTest extends TestCase
{
    use Specify;

    public function testCreate()
    {
        $this->specify('model is saved', function () {
            $model = new Model();
            $model->attribute = 'test';
            $this->assertTrue($model->save());
        });

        $this->specify('model with duplicate attribute value is not saved', function () {
            $model = new Model();
            $model->attribute = 'test';
            $this->assertFalse($model->save());
        });

        $this->specify('validator corrected collision', function () {
            $model = new Model();
            $model->attribute = 'test';
            $model->scenario = 'validator';
            $this->assertTrue($model->save());
            $this->assertEquals('test-1', $model->attribute);
        });

        $this->specify('picked correct index value from db', function () {
            $model = new Model();
            $model->attribute = 'test';
            $model->scenario = 'validator';
            $this->assertTrue($model->save());
            $this->assertEquals('test-2', $model->attribute);
        });

        $this->specify('validator corrected value index', function () {
            $model = new Model();
            $model->attribute = 'test-1';
            $model->scenario = 'validator';
            $this->assertTrue($model->save());
            $this->assertEquals('test-3', $model->attribute);
        });

        $this->specify('ignored correct value', function () {
            $model = new Model();
            $model->attribute = 'testtest';
            $model->scenario = 'validator';
            $this->assertTrue($model->save());
            $this->assertEquals('testtest', $model->attribute);
        });
    }

    public function testUpdate()
    {
        $this->specify('model is saved', function () {
            $model = new Model();
            $model->attribute = 'test';
            $this->assertTrue($model->save());
        });

        $this->specify('existing model is saved unchanged', function () {
            $model = Model::findOne(1);
            $model->scenario = 'validator';
            $this->assertTrue($model->save());
            $this->assertEquals('test', $model->attribute);
        });

        $this->specify('second model is saved', function () {
            $model = new Model();
            $model->attribute = 'testtest';
            $this->assertTrue($model->save());
            $this->assertEquals('testtest', $model->attribute);
        });

        $this->specify('validator corrected value', function () {
            $model = Model::findOne(2);
            $model->scenario = 'validator';
            $model->attribute = 'test';
            $this->assertTrue($model->save());
            $this->assertEquals('test-1', $model->attribute);
        });
    }

    public function testCreateWithCustomStartIndex()
    {
        $this->specify('model is saved', function () {
            $model = new Model();
            $model->attribute = 'test';
            $this->assertTrue($model->save());
        });

        $this->specify('validator corrected collision', function () {
            $model = new Model();
            $model->attribute = 'test';
            $model->scenario = 'validatorWithCustomStartIndex';
            $this->assertTrue($model->save());
            $this->assertEquals('test-2', $model->attribute);
        });
    }

    public function testCreateWithCustomSeparator()
    {
        $this->specify('model is saved', function () {
            $model = new Model();
            $model->attribute = 'test';
            $this->assertTrue($model->save());
        });

        $this->specify('validator corrected collision', function () {
            $model = new Model();
            $model->attribute = 'test';
            $model->scenario = 'validatorWithCustomSeparator';
            $this->assertTrue($model->save());
            $this->assertEquals('test%1', $model->attribute);
        });
    }

    public function testCreateWithEmptySeparator()
    {
        $this->specify('model is saved', function () {
            $model = new Model();
            $model->attribute = 'test';
            $this->assertTrue($model->save());
        });

        $this->specify('validator corrected collision', function () {
            $model = new Model();
            $model->attribute = 'test';
            $model->scenario = 'validatorWithEmptySeparator';
            $this->assertTrue($model->save());
            $this->assertEquals('test1', $model->attribute);
        });

        $this->specify('validator corrected collision second time', function () {
            $model = new Model();
            $model->attribute = 'test';
            $model->scenario = 'validatorWithEmptySeparator';
            $this->assertTrue($model->save());
            $this->assertEquals('test2', $model->attribute);
        });

        $this->specify('validator corrected value index', function () {
            $model = new Model();
            $model->attribute = 'test1';
            $model->scenario = 'validatorWithEmptySeparator';
            $this->assertTrue($model->save());
            $this->assertEquals('test3', $model->attribute);
        });
    }

    public function testCreateWithFilter()
    {
        $this->specify('model is saved', function () {
            $model = new Model2();
            $model->attribute_1 = 'test';
            $model->attribute_2 = 'test';
            $this->assertTrue($model->save());
        });

        $this->specify('model is not saved twice', function () {
            $model = new Model2();
            $model->attribute_1 = 'test';
            $model->attribute_2 = 'test';
            $this->assertFalse($model->save());
        });

        $this->specify('model with different attribute saved', function () {
            $model = new Model2();
            $model->attribute_1 = 'test';
            $model->attribute_2 = 'test1';
            $this->assertTrue($model->save());
        });

        $this->specify('collision corrected with closure filter', function () {
            $model = new Model2();
            $model->scenario = 'validatorWithFilterClosure';
            $model->attribute_1 = 'test';
            $model->attribute_2 = 'test';
            $this->assertTrue($model->save());
            $this->assertEquals('test-1', $model->attribute_2);
        });

        $this->specify('collision corrected with array filter', function () {
            $model = new Model2();
            $model->scenario = 'validatorWithFilterArray';
            $model->attribute_1 = 'test';
            $model->attribute_2 = 'test';
            $model->validate();
            $this->assertTrue($model->save());
            $this->assertEquals('test-2', $model->attribute_2);
        });
    }
}
