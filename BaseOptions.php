<?php

namespace twisted1919\options;

use yii\db\Expression;
use yii\base\Component;

class BaseOptions extends Component
{
    /**
     * @var string
     */
    protected $defaultCategory = 'misc';

    /**
     * @var string
     */
    protected $tableName = '{{%option}}';

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $categories = [];

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        if (($existingValue = $this->get($key)) === $value || $value === null) {
            return $this;
        }

        $_key = $key;
        list($category, $key) = $this->getCategoryAndKey($key);
        $command = db()->createCommand();

        if ($this->get($_key) !== null) {
            $command->update($this->tableName, [
                'value'         => is_string($value) ? $value : serialize($value),
                'serialized'    => (int)(!is_string($value)),
                'updated_at'    => new Expression('NOW()')
            ], '`category` = :c AND `key` = :k', [':c' => $category, ':k' => $key]
            )->execute();
        } else {
            $command->insert($this->tableName, [
                'category'      => $category,
                'key'           => $key,
                'value'         => is_string($value) ? $value : serialize($value),
                'serialized'    => (int)(!is_string($value)),
                'created_at'    => new Expression('NOW()'),
                'updated_at'    => new Expression('NOW()')
            ])->execute();
        }
        $this->options[$category . '.' . $key] = $value;
        return $this;
    }

    /**
     * @param $key
     * @param null $defaultValue
     * @return mixed|null
     */
    public function get($key, $defaultValue = null)
    {
        // simple keys are set with default category, we need to retrieve them the same.
        $key = implode('.', $this->getCategoryAndKey($key));

        $this->loadCategory($key);
        return isset($this->options[$key]) ? $this->options[$key] : $defaultValue;
    }

    /**
     * @param $key
     * @return bool
     */
    public function remove($key)
    {
        if (isset($this->options[$key])) {
            unset($this->options[$key]);
        }

        list($category, $key) = $this->getCategoryAndKey($key);

        db()->createCommand()->delete(
            $this->tableName, 
            '`category` = :c AND `key` = :k', 
            [':c' => $category, ':k' => $key]
        )->execute();
        
        return true;
    }

    /**
     * @param $category
     * @return bool
     */
    public function removeCategory($category)
    {
        if (isset($this->categories[$category])) {
            unset($this->categories[$category]);
        }

        db()->createCommand()->delete($this->tableName, '`category` = :c', [':c' => $category])->execute();
        db()->createCommand()->delete($this->tableName, '`category` LIKE :c', [':c' => $category . '%'])->execute();

        foreach ($this->options as $key => $value) {
            if (strpos($key, $category) === 0) {
                unset($this->options[$key]);
            }
        }

        return true;
    }

    /**
     * @param $key
     * @return $this
     */
    protected function loadCategory($key)
    {
        list($category) = $this->getCategoryAndKey($key);

        if (isset($this->categories[$category])) {
            return $this;
        }
        
        $command = db()->createCommand(
            'SELECT `category`, `key`, `value`, `serialized` FROM '.$this->tableName.' WHERE `category` = :c'
        , [':c' => $category]);
        
        $rows = $command->queryAll();

        foreach ($rows as $row) {
            $this->options[$row['category'].'.'.$row['key']] = !$row['serialized'] ? $row['value'] : unserialize($row['value']);
        }

        $this->categories[$category] = true;

        return $this;
    }

    /**
     * @param $key
     * @return array
     */
    protected function getCategoryAndKey($key)
    {
        $category = $this->defaultCategory;

        if (strpos($key, '.') !== false) {
            $parts = explode('.', $key);
            $key = array_pop($parts);
            $category = implode('.', $parts);
        }

        return [$category, $key];
    }

    /**
     * @return $this
     */
    public function resetLoaded()
    {
        $this->options    = [];
        $this->categories = [];
        return $this;
    }
}