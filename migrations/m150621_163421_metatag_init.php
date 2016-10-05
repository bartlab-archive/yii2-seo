<?php

use maybeworks\seo\DbMetatagManager;
use yii\base\InvalidConfigException;
use yii\db\Migration;

class m150621_163421_metatag_init extends Migration
{

    /**
     * Get metatag manager instance
     * @return DbMetatagManager|object
     * @throws InvalidConfigException
     */
    protected function getMetatagManager()
    {
        $metatagManager = Yii::$app->get('metatagManager');
        if (!$metatagManager instanceof DbMetatagManager) {
            throw new InvalidConfigException('You should configure "metatagManager" component to use database before executing this migration.');
        }
        return $metatagManager;
    }

    protected function getTableOptions()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        return $tableOptions;
    }

    public function up()
    {
        $metatagManager = $this->getMetatagManager();
        $this->db = $metatagManager->db;

        $this->createTable($metatagManager->itemTable, [
            'id' => $this->primaryKey(),
            'url' => $this->string()->notNull(),
            'title' => $this->string(),
            'description' => $this->string(),
            'keywords' => $this->string(),
            'h1' => $this->string(),
            'custom' => $this->text(),
            'ordering' => $this->smallInteger(),
            'status' => $this->smallInteger()->notNull()->defaultValue($metatagManager::STATUS_ACTIVE),
        ], $this->getTableOptions());

        $this->createIndex('metatag-status', $metatagManager->itemTable, 'status');
    }

    public function down()
    {
        $metatagManager = $this->getMetatagManager();
        $this->dropTable($metatagManager->itemTable);
    }
}
