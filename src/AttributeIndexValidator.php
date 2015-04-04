<?php
/**
 * Yii2 Attribute Index Validator
 *
 * This file contains validator.
 *
 * @author  Martin Stolz <herr.offizier@gmail.com>
 */

namespace herroffizier\yii2aiv;

use yii\validators\Validator;

class AttributeIndexValidator extends Validator
{
    /**
     * Separator between base value and index.
     *
     * @var string
     */
    public $separator = '-';

    /**
     * Start index value.
     *
     * @var integer
     */
    public $startIndex = 1;

    /**
     * Escaped separator.
     *
     * @var string
     */
    protected $escapedSeparator = null;

    /**
     * Get escaped separator for regexps.
     *
     * @return string
     */
    protected function getEscapedSeparator()
    {
        if ($this->escapedSeparator === null) {
            $this->escapedSeparator = addcslashes($this->separator, '[]().?-*^$/:<>');
        }

        return $this->escapedSeparator;
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $currentValue = $model->$attribute;

        // If model is already stored in db, exclude it in further queries.
        if (array_filter($pk = $model->getPrimaryKey(true))) {
            $pkCondition = ['not', $pk];
        } else {
            $pkCondition = [];
        }

        // Check whether we have a collision. If no collision found just return.
        $hasCollision =
            $model->find()->
                andWhere([$attribute => $currentValue])->
                andWhere($pkCondition)->
                exists();
        if (!$hasCollision) {
            return;
        }

        // Find base attribute value by removing trailing separator and index for current value.
        $escapedSeparator = $this->getEscapedSeparator();
        $maskValue = preg_replace('/'.$escapedSeparator.'\d+$/', '', $currentValue);

        // Find value with maximum index.
        $maxValue =
            $model->find()->
                select([$attribute])->
                andWhere(['like', $attribute, $maskValue])->
                andWhere($pkCondition)->
                orderBy([$attribute => SORT_DESC])->
                scalar();

        // Create new index value.
        $matches = [];
        if (preg_match('/'.$escapedSeparator.'(\d+)$/', $maxValue, $matches)) {
            $index = ((int) $matches[1]) + 1;
        } else {
            $index = $this->startIndex;
        }

        $model->$attribute = $maskValue.$this->separator.$index;
    }
}
