<?php
namespace maybeworks\seo;

use yii\base\BaseObject;

class Metatag extends BaseObject
{
    public $url;
    public $title;
    public $h1;
    public $keywords;
    public $description;
    public $custom = [];
}
