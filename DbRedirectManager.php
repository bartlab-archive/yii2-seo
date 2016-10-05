<?php

namespace maybeworks\seo;

use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\helpers\Url;

class DbRedirectManager extends BaseRedirectManager
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
     * After the DbRedirectManager object is created, if you want to change this property, you should only assign it
     * with a DB connection object.
     */
    public $db = 'db';

    /**
     * @var string the name of the table storing redirect items. Defaults to "redirect".
     */
    public $itemTable = '{{%redirect}}';

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
    protected function getItem()
    {
        $query = (new Query())
            ->from($this->itemTable)
            ->where([$this->statusColumn => self::STATUS_ACTIVE])
            ->orderBy([$this->orderColumn => 'DESC']);

        foreach ($query->all($this->db) as $row) {
            if (preg_match('#' . str_replace('#', '\#', $row['rule']) . '#', \Yii::$app->request->url)) {
                return $this->populateItem($row);
            }
        }

        return false;
    }
}