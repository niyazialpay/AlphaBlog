<?php

namespace App\Models;


use MongoDB\Laravel\Eloquent\Model;
use Psr\Http\Message\StreamInterface;
use DateTime;
use onesignal\client\api\DefaultApi;
use onesignal\client\Configuration;
use onesignal\client\model\GetNotificationRequestBody;
use onesignal\client\model\Notification;
use onesignal\client\model\StringMap;
use onesignal\client\model\Player;
use onesignal\client\model\UpdatePlayerTagsRequestBody;
use onesignal\client\model\ExportPlayersRequestBody;
use onesignal\client\model\Segment;
use onesignal\client\model\FilterExpressions;
use PHPUnit\Framework\TestCase;
use GuzzleHttp;

class OneSignal extends Model
{
    protected $collection = 'onesignal';

    protected $fillable = [
        'app_id',
        'auth_key',
        'safari_web_id',
    ];

    public $timestamps = false;

    static function sendPush($content, $title, $priority = 10): StreamInterface
    {
        $onesignal = self::first();

        $client = new \GuzzleHttp\Client();

        $response = $client->request('POST', 'https://onesignal.com/api/v1/notifications', [
            'body' => json_encode([
                'app_id' => $onesignal->app_id,
                'included_segments' => [
                    'Subscribed Users'
                ],
                'contents' => $content,
                'name' => 'INTERNAL_CAMPAIGN_NAME',
                'headings' => $title,
                'priority' => $priority,
            ]),
            'headers' => [
                'Authorization' => 'Basic '.$onesignal->auth_key,
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
        ]);

        return $response->getBody();
    }
}
