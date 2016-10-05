<?php

namespace maybeworks\seo;

use Yii;
use yii\base\ActionEvent;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\web\Application;

abstract class BaseRedirectManager extends Component
{
    /**
     * @var array default configuration of Redirect item. Individual item configurations
     */
    public $itemConfig = ['class' => 'maybeworks\seo\Redirect'];

    /**
     * Returns the redirect item
     * @return Redirect
     */
    abstract protected function getItem();

    public function init()
    {
        if (Yii::$app->request->isConsoleRequest) {
            return false;
        }

        Yii::$app->on(Application::EVENT_BEFORE_ACTION, [$this, 'onBeforeAction']);
    }

    public function onBeforeAction(ActionEvent $event)
    {
        if ($item = $this->getItem()) {
            Yii::$app->response->redirect(preg_replace('#' . $item->rule . '#', $item->to, Yii::$app->request->url), $item->code);
            Yii::$app->end();
        }
    }

    /**
     * Populates an item with the data
     * @param array $row data for item
     * @return object|Redirect
     */
    protected function populateItem($row)
    {
        return Yii::createObject(array_merge(
            $this->itemConfig,
            [
                'rule' => ArrayHelper::getValue($row, 'rule'),
                'to' => ArrayHelper::getValue($row, 'to','/'),
                'code' => ArrayHelper::getValue($row, 'code', 301)
            ]
        ));
    }
}