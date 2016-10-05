<?php

namespace maybeworks\seo;

/**
 * RedirectManager represents an redirect manager that stores redirect rules
 * information in terms of a Yii config file.
 *
 * Add to application config, in component section:
 *
 * ```php
 * 'redirectManager' => [
 *      'class' => 'maybeworks\seo\RedirectManager',
 *      'rules' => [...]
 * ]
 * ```
 *
 * And add to bootstrap section:
 * ```php
 * 'redirectManager'
 * ```
 */
class RedirectManager extends BaseRedirectManager
{
    /**
     * @var array redirect rules
     *
     * ```php
     * [
     *     ['rule' => '/blog', 'to' => '/b', 'code' => 301],
     * ];
     * ```
     *
     * Data for redirect:
     * rule - url pattern
     * to - string
     * code - int, optional
     */
    public $rules = [];

    /**
     * @inheritdoc
     */
    protected function getItem()
    {
        foreach ($this->rules as $row) {
            if (preg_match('#' . str_replace('#', '\#', $row['rule']) . '#', \Yii::$app->request->url)) {
                return $this->populateItem($row);
            }
        }

        return false;
    }
}