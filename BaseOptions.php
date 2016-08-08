<?php

namespace twisted1919\options;

use yii\db\Expression;
use yii\base\Component;

class BaseOptions extends Component
{
    /**
     * @var string
     */
    public $defaultCategory = 'misc';

    /**
     * @var string
     */
    public $tableName = '{{%option}}';

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
                'is_serialized' => (int)(!is_string($value)),
                'last_updated'  => new Expression('NOW()')
            ], '`category` = :c AND `key`=:k', [':c' => $category, ':k' => $key]);
        } else {
            $command->insert($this->tableName, [
                'category'      => $category,
                'key'           => $key,
                'value'         => is_string($value) ? $value : serialize($value),
                'is_serialized' => (int)(!is_string($value)),
                'date_added'    => new Expression('NOW()'),
                'last_updated'  => new Expression('NOW()')
            ]);
        }
        $this->options[$_key] = $value;
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

        db()->createCommand()->delete($this->tableName, '`category` = :c AND `key` = :k', [':c' => $category, ':k' => $key]);
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

        db()->createCommand()->delete($this->tableName, '`category` = :c', [':c' => $category]);
        db()->createCommand()->delete($this->tableName, '`category` LIKE :c', [':c' => $category . '%']);

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
        
        $command = db()->createCommand('SELECT `category`, `key`, `value`, `is_serialized` FROM `'.$this->tableName.'` WHERE `category` = :c');
        $rows = $command->queryAll(true, [':c' => $category]);

        foreach ($rows as $row) {
            $this->options[$row['category'].'.'.$row['key']] = !$row['is_serialized'] ? $row['value'] : unserialize($row['value']);
        }

        $this->categories[$category] = true;

        return $this;
    }

    /**
     * @param $key
     * @return array
     */
    public function getCategoryAndKey($key)
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