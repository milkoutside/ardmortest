<?php
namespace Zoho\Services;
use Illuminate\Support\Facades\Http;


class ZohoApi
{
    private $zapi_key;

    private $url;

    private $accessToken;
    private static $instance = null;
    private function __construct()
    {
        $this->url = config('zoho.zohoConfig.deluge_api_url');
        $this->zapi_key = config('zoho.zohoConfig.zapi_key');
        $this->accessToken = $this->fetchAccessToken();
    }

    private function fetchAccessToken()
    {
        $url = 'https://accounts.zoho.com/oauth/v2/token';

        $params = [
            'client_id' => config('zoho.zohoConfig.client_id'),
            'client_secret' => config('zoho.zohoConfig.client_secret'),
            'grant_type' => 'refresh_token',
            'refresh_token' => config('zoho.zohoConfig.refresh_token'),
        ];

        $queryString = http_build_query($params);

        $fullUrl = $url . '?' . $queryString;

        $response = Http::post($fullUrl);

        $responseData = $response->json();
        $accessToken = $responseData['access_token'];

        return $accessToken;
    }
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    private function executeDeluge($type, $functionName, $params)
    {
        if ($type === 'post') {

            $response = Http::withHeaders([])->
            post($this->url . $functionName . '/actions/execute?auth_type=apikey&zapikey=' . $this->zapi_key, $params);

            return $response->json();
        }
    }

    public function getDelugeContact()
    {
        return $this->executeDeluge('post', 'testapi', []);
    }

    private function executeRequest($type, $functionName, $params)
    {
        if ($type === 'post') {

            $response = Http::withHeaders([
                //'Authorization'=> 'Zoho-oauthtoken'. $this->accessToken
                'Content-Type'=>'application/x-www-form-urlencoded'
            ])->
            post($this->url . $functionName . '/actions/execute?auth_type=apikey&zapikey=' . $this->zapi_key, $params);

            return $response->json();
        }
    }
    public function getRecordById($module,$id)
    {
        $response = Http::withHeaders([
            'Authorization'=> 'Zoho-oauthtoken '. $this->accessToken
        ])->get('https://www.zohoapis.com/crm/v2/'.$module.'/'.$id);

        return $response->json();
    }
    public function updateRecordById($module,$id,$data)
    {
        $response = Http::withHeaders([
            'Authorization'=> 'Zoho-oauthtoken '. $this->accessToken
        ])->put('https://www.zohoapis.com/crm/v2/'.$module.'/'.$id,['data'=>[$data]]);

        return $response->json();

    }
    public function createZohoCreatorRecord($data)
    {
        $url = 'https://accounts.zoho.com/oauth/v2/token';

        $params = [
            'client_id' => '1000.FWM7M2S5CNA1Y711AJZHDMSCGXTPYS',
            'client_secret' => '1ca967bd0f164a1dbca0858b0e662cb11098bbf7f1',
            'grant_type' => 'refresh_token',
            'refresh_token' => '1000.d0badfa171443f377bc716e38fa89b25.b5480a8e46a39342dc984f53484bab0b'
        ];
        $queryString = http_build_query($params);

        $fullUrl = $url . '?' . $queryString;

        $response = Http::post($fullUrl);

        $responseData = $response->json();
        $accessToken = $responseData['access_token'];

        $resp = Http::withHeaders([
            'Authorization'=> 'Zoho-oauthtoken '. $accessToken
        ])->post('https://creator.zoho.com/api/v2/user4_demo46/ardmor/form/testblank',$data);
        return $resp->json();
    }
}
