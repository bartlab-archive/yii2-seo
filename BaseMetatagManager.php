<?php

namespace maybeworks\seo;

use Yii;
use yii\base\Event;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\View;
use yii\web\Application;
use yii\web\Request;

abstract class BaseMetatagManager extends Component
{
    /**
     * @var bool if enable CSRF validation in Request component, then generate csrf-param and csrf-token meta
     */
    public $enableCsrfValidation = true;

    /**
     * @var bool cenerate charset meta from Yii::$app->charset
     */
    public $enableCharset = true;

    /**
     * @var array default configuration of Metatag item. Individual item configurations
     */
    public $itemConfig = ['class' => 'maybeworks\seo\Metatag'];

    protected $_tags = [];

    /**
     * Returns the metatag items.
     * @return Metatag[]
     */
    abstract protected function getItems();

    public function init()
    {
        if (Yii::$app->request->isConsoleRequest) {
            return false;
        }

        // view
        Yii::$app->view->on(View::EVENT_END_PAGE, [$this, 'onEndPage']);
        Yii::$app->view->on(View::EVENT_BEGIN_PAGE, [$this, 'onBeginPage']);
        // app
        Yii::$app->on(Application::EVENT_AFTER_ACTION, [$this, 'onAfterAction']);
    }

    /**
     * Application after action event for replace H1 in page
     * @param Event $event
     */
    public function onAfterAction(Event $event)
    {
        if ($h1 = $this->getH1()) {
            $event->result = preg_replace(
                '/\<h1(.*?)\>(.*?)\<\/h1\>/',
                '<h1$1>' . $this->proccessData($h1) . '</h1>',
                $event->result
            );
        }
    }

    /**
     * View begin page event for replace title in page
     * @param Event $event
     */
    public function onBeginPage(Event $event)
    {
        if ($title = $this->getTitle()) {
            $view = $event->sender;
            $view->title = $this->proccessData($title, $view);
        }
    }

    /**
     * View end page event for set charset, csrf token, description, keywords and custom tags
     * @param Event $event
     */
    public function onEndPage(Event $event)
    {
        /** @var View $view */
        $view = $event->sender;

        // addition meta-tags
        if ($this->enableCharset) {
            $view->registerMetaTag(['charset' => \Yii::$app->charset], 'charset');
        }

        // csrf meta-tags
        if ($this->enableCsrfValidation) {
            $request = \Yii::$app->getRequest();
            if ($request instanceof Request && $request->enableCsrfValidation) {
                $view->registerMetaTag(['name' => 'csrf-param', 'content' => $request->csrfParam], 'csrf-param');
                $view->registerMetaTag(['name' => 'csrf-token', 'content' => $request->getCsrfToken()], 'csrf-token');
            }
        }

        if ($description = $this->getDescription()) {
            $view->registerMetaTag(
                [
                    'name' => 'description',
                    'content' => $this->proccessData($description, $view)
                ],
                'description'
            );
        }
        if ($keywords = $this->getKeywords()) {
            $view->registerMetaTag(
                [
                    'name' => 'keywords',
                    'content' => $this->proccessData($keywords, $view)
                ],
                'keywords'
            );
        }
        if ($custom = $this->getCustom()) {
            foreach ($custom as $key => $tag) {
                $view->registerMetaTag($tag, is_int($key) ? null : $key);
            }
        }
    }

    /**
     * Preparation string by template. Data for templata use from View::params['data'].
     *
     * Example:
     * ```php
     * Yii::$app->getView()->params['data']['item']=['name'=>'Item name']
     * ```
     *
     * Title metatag template - 'discount for current item %item.name%'
     *
     * @param string $str
     * @param null|View $view
     * @return string
     */
    public function proccessData($str, $view = null)
    {
        // 'текущий аппарат %item.name% есть в продаже';
        if (preg_match_all('/%(.*?)%/', $str, $matchs)) {
            if (!$view) {
                $view = Yii::$app->view;
            }
            foreach ($matchs[1] as $m) {
                $m = trim($m);
                if ($m) {
                    $data = ArrayHelper::getValue($view->params, 'data.' . $m);
                    $str = str_replace('%' . $m . '%', $data, $str);
                }
            }
        }
        return $str;
    }

    /**
     * Current tags
     * @return array
     */
    public function tags()
    {
        if (!$this->_tags) {
            $this->_tags = $this->getItems();
        }

        return $this->_tags;
    }

    /**
     * Each current tags and return last not empty title
     * @return null|string
     */
    public function getTitle()
    {
        $title = null;
        foreach ($this->tags() as $tag) {
            if (!empty($tag->title)) {
                $title = $tag->title;
            }
        }
        return $title;
    }

    /**
     * Each current tags and return last not empty h1
     * @return null|string
     */
    public function getH1()
    {
        $h1 = null;
        foreach ($this->tags() as $tag) {
            if (!empty($tag->h1)) {
                $h1 = $tag->h1;
            }
        }
        return $h1;
    }

    /**
     * Each current tags and return last not empty description
     * @return null|string
     */
    public function getDescription()
    {
        $description = null;

        foreach ($this->tags() as $tag) {
            if (!empty($tag->description)) {
                $description = $tag->description;
            }
        }

        return $description;
    }

    /**
     * Each current tags and return last not empty keywords
     * @return null|string
     */
    public function getKeywords()
    {
        $keywords = null;

        foreach ($this->tags() as $tag) {
            if (!empty($tag->keywords)) {
                $keywords = $tag->keywords;
            }
        }

        return $keywords;
    }

    /**
     * Each current tags and return merged custom tags
     * @return array
     */
    public function getCustom()
    {
        $custom = [];

        foreach ($this->tags() as $tag) {
            if (!empty($tag->custom)) {
                $custom = array_merge($custom, $tag->custom);
            }
        }

        return $custom;
    }

    /**
     * Populates an item with the data
     * @param array $row data for item
     * @return object|Metatag
     */
    protected function populateItem($row)
    {
        $custom = ArrayHelper::getValue($row, 'custom');
        return Yii::createObject(array_merge(
            $this->itemConfig,
            [
                'url' => ArrayHelper::getValue($row, 'url'),
                'title' => ArrayHelper::getValue($row, 'title'),
                'h1' => ArrayHelper::getValue($row, 'h1'),
                'keywords' => ArrayHelper::getValue($row, 'keywords'),
                'description' => ArrayHelper::getValue($row, 'description'),
                'custom' => is_string($custom) ? Json::decode($custom) : $custom,
            ]
        ));
    }
}