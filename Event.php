<?php
/**
 * @link https://github.com/AnatolyRugalev
 * @copyright Copyright (c) AnatolyRugalev
 * @license https://tldrlegal.com/license/gnu-general-public-license-v3-(gpl-3)
 */

namespace understeam\httpclient;

use Yii;

/**
 * Class Event TODO: Write class description
 * @author Anatoly Rugalev
 * @link https://github.com/AnatolyRugalev
 */
class Event extends \yii\base\Event
{

    /**
     * @var \GuzzleHttp\Message\RequestInterface|\GuzzleHttp\Message\ResponseInterface
     */
    public $message;

}
