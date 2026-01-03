<?php

namespace App\Models;

use App\Traits\ModelLogger;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\StreamInterface;

class OneSignal extends Model
{
    use ModelLogger;

    protected $table = 'onesignal';

    protected $fillable = [
        'app_id',
        'auth_key',
        'safari_web_id',
        'user_segmentation',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'app_id' => 'encrypted',
            'auth_key' => 'encrypted',
        ];
    }

    /**
     * @throws GuzzleException
     */
    public static function sendPush($content, $title, $url = null, $priority = 10): StreamInterface
    {
        $onesignal = self::first();

        $client = new \GuzzleHttp\Client;

        $response = $client->request('POST', 'https://onesignal.com/api/v1/notifications', [
            'body' => json_encode([
                'app_id' => $onesignal->app_id,
                'included_segments' => [
                    $onesignal->user_segmentation,
                ],
                'contents' => [
                    'en' => $content,
                ],
                'name' => $content,
                'headings' => [
                    'en' => $title,
                ],
                'priority' => $priority,
                'web_url' => $url,
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
