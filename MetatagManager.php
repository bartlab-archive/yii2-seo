<?php

namespace maybeworks\seo;

use yii\helpers\Url;

/**
 * MetatagManager represents an metatag manager that stores metatag
 * information in terms of a Yii config file.
 *
 * Add to application config, in component section:
 *
 * ```php
 * 'metatagManager' => [
 *      'class' => 'maybeworks\seo\MetatagManager',
 *      'tags' => [...]
 * ]
 * ```
 *
 * And add to bootstrap section:
 * ```php
 * 'metatagManager'
 * ```
 */
class MetatagManager extends BaseMetatagManager
{
    /**
     * @var array metatags
     *
     * ```php
     * [
     *     ['url' => '/blog', 'title' => 'Blog', 'h1' => 'Our blog'],
     *     ['url' => '.*', 'title' => 'Title for all page', 'custom' => [
     *          'viewport' => ['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'],
     *          'yandex-verification' => ['name' => 'yandex-verification', 'content' => '1234567890']
     *      ]],
     * ];
     * ```
     *
     * Data for metatag:
     * url - url pattern
     * title - string
     * h1 - string
     * keywords - string
     * description - string
     * custom - array for View::registerMetaTag
     */
    public $tags = [];

    /**
     * @inheritdoc
     */
    protected function getItems()
    {
        $result = [];
        $url = Url::current();
        foreach ($this->tags as $row) {
            if (preg_match('#' . str_replace('#', '\#', $row['url']) . '#', $url)) {
                $result[] = $this->populateItem($row);
            }
        }

        return $result;
    }
}