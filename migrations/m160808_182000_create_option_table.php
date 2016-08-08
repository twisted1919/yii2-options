<?php

use yii\db\Migration;

/**
 * Handles the creation for table `option`.
 */
class m160808_182000_create_option_table extends Migration
{
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
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%option}}');
    }
}