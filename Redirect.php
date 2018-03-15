<?php
namespace maybeworks\seo;

use yii\base\BaseObject;

class Redirect extends BaseObject
{
    public $rule;
    public $to;
    public $code;
}
