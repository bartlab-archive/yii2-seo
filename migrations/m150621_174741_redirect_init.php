<?php

use maybeworks\seo\DbRedirectManager;
use yii\base\InvalidConfigException;
use yii\db\Migration;

class m150621_174741_redirect_init extends Migration
{

    /**
     * Get redirect manager instance
     * @return DbRedirectManager|object
     * @throws InvalidConfigException
     */
    protected function getRedirectManager()
    {
        $redirectManager = Yii::$app->get('redirectManager');
        if (!$redirectManager instanceof DbRedirectManager) {
            throw new InvalidConfigException('You should configure "redirectManager" component to use database before executing this migration.');
        }
        return $redirectManager;
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
        $redirectManager = $this->getRedirectManager();
        $this->db = $redirectManager->db;

        $this->createTable($redirectManager->itemTable, [
            'id' => $this->primaryKey(),
            'rule' => $this->string()->notNull(),
            'to' => $this->string()->notNull(),
            'code' => $this->smallInteger()->defaultValue(301),
            'ordering' => $this->smallInteger(),
            'status' => $this->smallInteger()->notNull()->defaultValue($redirectManager::STATUS_ACTIVE),
        ], $this->getTableOptions());

        $this->createIndex('redirect-status', $redirectManager->itemTable, 'status');
    }

    public function down()
    {
        $redirectManager = $this->getRedirectManager();
        $this->dropTable($redirectManager->itemTable);
    }
}
