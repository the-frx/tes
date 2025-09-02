<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Offer;
use GuzzleHttp\Client;
use App\Models\Network;
use App\Models\Traffic;
use App\Models\Promotion;
use Jenssegers\Agent\Agent;
use App\Helpers\SettingsHelper;
use Stevebauman\Location\Facades\Location;

class RedirectController extends Controller
{
    public function index()
    {
        $settings = SettingsHelper::getAllSettings();
        $ipAddress = $this->getIP();
        $defaultPromotion = $settings['Default_Promotion'];
        $agent = new Agent();
        $position = Location::get($ipAddress);
        $countryCode = $position->countryCode;
        $network = request('network');
        $id = request('id');
        $alias = Network::where('alias', $network)->first();
        $user = User::where('username', $id)->first();
        if (!$alias || !$user) {
            $promotion = Promotion::inRandomOrder()->first();
            if ($promotion) {
                $randomLink = $promotion->link;
            } else {
                $randomLink = $defaultPromotion;
            }
            $urlMobile = $urlDesktop = $defaultUrl = $randomLink;
            $urlBase = $this->determineUrlBase($agent, $urlMobile, $urlDesktop, $defaultUrl);
            $finalUrl = $urlBase;
        } else {
            $client = new Client();
            $response = $client->get("http://ip-api.com/json/{$ipAddress}");
            $body = $response->getBody();
            $data = json_decode($body);
            $isp = $data && isset($data->isp) ? $data->isp : "Tidak dapat mendapatkan informasi ISP.";
            $existingTraffic = Traffic::where('ip', $ipAddress)->first();
            $status = $existingTraffic ? false : true;
            $cid = substr(hash('sha256', $ipAddress . $agent->getUserAgent() . $user->id . $alias->id), 0, 50);
            Traffic::create([
                'network_id' => $alias->id,
                'ip' => $ipAddress,
                'status' => $status,
                'country' => $countryCode,
                'browser' => $agent->browser(),
                'device' => $agent->device(),
                'platform' => $agent->platform(),
                'bot' => $agent->isRobot(),
                'isp' => $isp,
                'useragent' => $agent->getUserAgent(),
                'cid' =>  $cid,
                'user_id' => $user->id,
            ]);
            $offer = Offer::where('country', $countryCode)
                ->where('network_id', $alias->id)
                ->first();
            $defaultUrl = $alias->smartlink;
            $urlMobile = $offer ? $offer->url_mobile : $defaultUrl;
            $urlDesktop = $offer ? $offer->url_desktop : $defaultUrl;
            $urlBase = $this->determineUrlBase($agent, $urlMobile, $urlDesktop, $defaultUrl);
            $referalName = ($user->referal == 'system') ? $settings['Site_Name'] : $user->referal;
            $params = [];
            if ($alias->tracker) {
                $params[$alias->tracker] = $referalName;
            }
            if ($alias->sub1) {
                $params[$alias->sub1] = $id;
            }
            if ($alias->cid) {
                $params[$alias->cid] =  $cid;
            }
            $params = array_filter($params, function ($value) {
                return !is_null($value) && $value !== '';
            });
            $queryString = http_build_query($params);
            if ($countryCode == 'ID' || $agent->isRobot()) {
                $promotion = Promotion::inRandomOrder()->first();
                if ($promotion) {
                    $randomLink = $promotion->link;
                } else {
                    $randomLink = $defaultPromotion;
                }
                $urlMobile = $urlDesktop = $defaultUrl = $randomLink;
                $urlBase = $this->determineUrlBase($agent, $urlMobile, $urlDesktop, $defaultUrl);
                $finalUrl = $urlBase;
            }
            $finalUrl = $urlBase . (parse_url($urlBase, PHP_URL_QUERY) ? '&' : '?') . $queryString;
        }
        return response("
        <script type=\"text/javascript\">
            window.onload = function() {
                window.location.href = '{$finalUrl}';
            }
        </script>
        ", 200)->header('Content-Type', 'text/html');
    }
    private function determineUrlBase(Agent $agent, $urlMobile, $urlDesktop, $defaultUrl)
    {
        if ($agent->isMobile() || $agent->isTablet()) {
            return $urlMobile;
        }

        if ($agent->isDesktop()) {
            return $urlDesktop;
        }

        return $defaultUrl;
    }
    private function getIP()
    {
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }

        $client = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = $_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }
        return $ip;
    }
}
