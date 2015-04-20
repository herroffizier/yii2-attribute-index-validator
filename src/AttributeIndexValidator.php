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
use yii\db\ActiveRecordInterface;

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
     * @var string|array|Closure
     */
    public $filter = null;

    /**
     * Escaped separator.
     *
     * @var string
     */
    protected $escapedSeparator = null;

    /**
     * Escape string for regexp.
     *
     * @param  string $string
     * @return string
     */
    protected function escapeForRegexp($string)
    {
        return addcslashes($string, '[]().?-*^$/:<>');
    }

    /**
     * Get escaped separator for regexps.
     *
     * @return string
     */
    protected function getEscapedSeparator()
    {
        if ($this->escapedSeparator === null) {
            $this->escapedSeparator = $this->escapeForRegexp($this->separator);
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
     * Get condition to exclude current model by it's primay key.
     *
     * If model is new, empty array will be returned.
     *
     * @param  ActiveRecordInterface $model
     * @return array
     */
    protected function getExcludeByPkCondition(ActiveRecordInterface $model)
    {
        if (array_filter($pk = $model->getPrimaryKey(true))) {
            $condition = ['not', $pk];
        } else {
            $condition = [];
        }

        return $condition;
    }

    /**
     * Whether there are an attribute value collision.
     *
     * @param  ActiveRecordInterface $model
     * @param  string                $attribute
     * @return boolean
     */
    protected function hasCollision(ActiveRecordInterface $model, $attribute)
    {
        $query =
            $model->find()->
                andWhere($this->getExcludeByPkCondition($model))->
                andWhere([$attribute => $model->$attribute]);
        $this->addFilterToQuery($query);

        return $query->exists();
    }

    /**
     * Get attribute value common part, e. g. part without index but with separator.
     *
     * For example, common part for 'test' and 'test-1' will be 'test-'.
     *
     * @param  ActiveRecordInterface $model
     * @param  string                $attribute
     * @return string
     */
    protected function getCommonPart(ActiveRecordInterface $model, $attribute)
    {
        $escapedSeparator = $this->getEscapedSeparator();

        return preg_replace('/'.$escapedSeparator.'\d+$/', '', $model->$attribute).$this->separator;
    }

    /**
     * Find max index stored in database.
     *
     * If no index found, startIndex - 1 will be returned.
     *
     * @param  ActiveRecordInterface $model
     * @param  string                $attribute
     * @param  string                $commonPart
     * @return integer
     */
    protected function findMaxIndex(ActiveRecordInterface $model, $attribute, $commonPart)
    {
        // Find all possible max values.
        $db = $model::getDb();
        $indexExpression = 'SUBSTRING('.$db->quoteColumnName($attribute).', :commonPartOffset)';
        $query =
            $model->find()->
                select(['_index' => $indexExpression])->
                andWhere($this->getExcludeByPkCondition($model))->
                andWhere(['like', $attribute, $commonPart])->
                andHaving(['not in', '_index', [0]])->
                orderBy(['CAST('.$db->quoteColumnName('_index').' AS UNSIGNED)' => SORT_DESC])->
                addParams(['commonPartOffset' => mb_strlen($commonPart) + 1])->
                asArray();
        $this->addFilterToQuery($query);
        foreach ($query->each() as $row) {
            $index = $row['_index'];
            if (!preg_match('/^\d+$/', $index)) {
                continue;
            }

            return $index;
        }

        return $this->startIndex - 1;
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        if (!$this->hasCollision($model, $attribute)) {
            return;
        }

        $commonPart = $this->getCommonPart($model, $attribute);
        $maxIndex = $this->findMaxIndex($model, $attribute, $commonPart);

        $model->$attribute = $commonPart.($maxIndex + 1);
    }
}
