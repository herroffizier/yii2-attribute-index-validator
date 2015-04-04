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

class Model extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'model';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['attribute'],
                AttributeIndexValidator::className(),
                'on' => 'validator',
            ],
            [
                ['attribute'],
                AttributeIndexValidator::className(),
                'startIndex' => 2,
                'on' => 'validatorWithCustomStartIndex'
            ],
            [
                ['attribute'],
                AttributeIndexValidator::className(),
                'separator' => '*',
                'on' => 'validatorWithCustomSeparator'
            ],
            [
                ['attribute'],
                AttributeIndexValidator::className(),
                'separator' => '',
                'on' => 'validatorWithEmptySeparator'
            ],
            [['attribute'], 'required'],
            [['attribute'], 'unique'],
            [['attribute'], 'string', 'max' => 255],
        ];
    }
}
