<?php

use yii\db\Migration;
use yii\base\Controller;

/**
 * Handles the creation for table `option`.
 */
class m160808_182000_create_option_table extends Migration
{
    /**
     * @var bool
     */
    public $removeMigrationHistory = true;
    
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName == 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%option}}', [
            'category'   => $this->string(150)->notNull(),
            'key'        => $this->string(150)->notNull(),
            'value'      => $this->binary(),
            'serialized' => $this->smallInteger(1)->notNull()->defaultValue(0),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ], $tableOptions);
        
        $this->addPrimaryKey('category_key', '{{%option}}', ['category', 'key']);
        
        // remove us from the migration table.
        if ($this->removeMigrationHistory) {
            app()->controller->on(Controller::EVENT_AFTER_ACTION, function () {
                $version = (new \ReflectionClass($this))->getShortName();
                db()->createCommand()->delete(app()->controller->migrationTable, 'version = :v', [':v' => $version])->execute();
            });
        }
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%option}}');
    }
}