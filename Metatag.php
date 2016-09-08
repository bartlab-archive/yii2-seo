<?php
namespace maybeworks\seo;

use Yii;
use yii\base\Object;

class Metatag extends Object
{
    public $url;
    public $title;
    public $h1;
    public $keywords;
    public $description;
    public $custom = [];
}
