<?php
/**
 * Yii2 Attribute Index Validator
 *
 * This file contains validator.
 *
 * @author  Martin Stolz <herr.offizier@gmail.com>
 */

namespace herroffizier\yii2aiv;

use Closure;
use yii\validators\Validator;
use yii\db\ActiveQueryInterface;

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
     * Additional filter applied to query used to check uniqueness.
     *
     * @var string|array|\Closure
     */
    public $filter = null;

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
     * Add filter to query (if any exists).
     *
     * @param ActiveQueryInterface $query
     */
    protected function addFilterToQuery(ActiveQueryInterface $query)
    {
        if (!$this->filter) {
            return;
        }

        if ($this->filter instanceof Closure) {
            call_user_func_array($this->filter, [$query]);
        } else {
            $query->andWhere($this->filter);
        }
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
        $collisionQuery =
            $model->find()->
                andWhere([$attribute => $currentValue])->
                andWhere($pkCondition);
        $this->addFilterToQuery($collisionQuery);

        $hasCollision = $collisionQuery->exists();
        if (!$hasCollision) {
            return;
        }

        // Find base attribute value by removing trailing separator and index for current value.
        $escapedSeparator = $this->getEscapedSeparator();
        $maskValue = preg_replace('/'.$escapedSeparator.'\d+$/', '', $currentValue).$this->separator;

        // Find value with maximum index.
        $maxValueQuery =
            $model->find()->
                select([$attribute])->
                andWhere(['like', $attribute, $maskValue])->
                andWhere($pkCondition)->
                orderBy([$attribute => SORT_DESC]);
        $this->addFilterToQuery($maxValueQuery);

        $maxValue = $maxValueQuery->scalar();

        // Create new index value.
        $matches = [];
        if (preg_match('/'.$escapedSeparator.'(\d+)$/', $maxValue, $matches)) {
            $index = ((int) $matches[1]) + 1;
        } else {
            $index = $this->startIndex;
        }

        $model->$attribute = $maskValue.$index;
    }
}
