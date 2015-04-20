<?php
/**
 * Yii2 Attribute Index Validator
 *
 * This file contains model for testing.
 *
 * @author  Martin Stolz <herr.offizier@gmail.com>
 */

namespace herroffizier\yii2aiv\tests\helpers;

use herroffizier\yii2aiv\AttributeIndexValidator;

class Model2 extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'model_2';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['attribute_1', 'attribute_2'], 'required'],
            [['attribute_1', 'attribute_2'], 'string', 'max' => 255],
            [
                ['attribute_2'],
                AttributeIndexValidator::className(),
                'on' => 'validatorWithFilterClosure',
                'filter' => function ($query) {
                    $query->andWhere(['attribute_1' => $this->attribute_1]);
                }
            ],
            [
                ['attribute_2'],
                AttributeIndexValidator::className(),
                'on' => 'validatorWithFilterArray',
                'filter' => ['attribute_1' => 'test'],
            ],
            [
                ['attribute_2'],
                'unique',
                'filter' => function ($query) {
                    $query->andWhere(['attribute_1' => $this->attribute_1]);
                }
            ],
        ];
    }
}
