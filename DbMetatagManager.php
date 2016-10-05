<?php

namespace maybeworks\seo;

use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\helpers\Url;

/**
 * DbMetatagManager represents an metatag manager that stores metatag information in database.
 *
 * The database connection is specified by [[db]]. The database schema could be initialized by applying migration:
 *
 * ```
 * yii migrate --migrationPath=@vendor/maybeworks/yii2-seo/migrations/
 * ```
 *
 * Add to application config, in component section:
 *
 * ```php
 * 'metatagManager' => [
 *      'class' => 'maybeworks\seo\DbMetatagManager',
 * ]
 * ```
 *
 * And add to bootstrap section:
 * ```php
 * 'metatagManager'
 * ```
 *
 * You may change the names of the table used to store the metatag and rule data by setting [[itemTable]].
 */
class DbMetatagManager extends BaseMetatagManager
{
    /**
     * Active status for record
     */
    const STATUS_ACTIVE = 1;

    /**
     * Deactive status for record
     */
    const STATUS_DEACTIVE = 0;

    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     * After the DbMetatagManager object is created, if you want to change this property, you should only assign it
     * with a DB connection object.
     */
    public $db = 'db';

    /**
     * @var string the name of the table storing metatag items. Defaults to "metatag".
     */
    public $itemTable = '{{%metatag}}';

    /**
     * @var string column name for ordering items
     */
    public $orderColumn = 'ordering';

    /**
     * @var string column name for status items
     */
    public $statusColumn = 'status';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
    }

    /**
     * @inheritdoc
     */
    protected function getItems()
    {
        $result = [];

        $query = (new Query())
            ->from($this->itemTable)
            ->where([$this->statusColumn => self::STATUS_ACTIVE])
            ->orderBy([$this->orderColumn => 'ASC']);

        $url = Url::current();
        foreach ($query->all($this->db) as $row) {
            if (preg_match('#' . str_replace('#', '\#', $row['url']) . '#', $url)) {
                $result[] = $this->populateItem($row);
            }
        }

        return $result;
    }
}