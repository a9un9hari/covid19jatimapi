<?php

namespace App\Http\Controllers;

use Telegram\Bot\Laravel\Facades\Telegram;
use KubAT\PhpSimple\HtmlDomParser;
use App\Data;
use DB;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    private $jatimUrl = 'http://covid19dev.jatimprov.go.id/xweb/draxi';
    
    private function getServerLastUpdate()
    {

        try {
            $html =  HtmlDomParser::file_get_html($this->jatimUrl);

            $tabel = $html->find('tbody',0);
            $lastUpdate = null;
            foreach ($tabel->find('tr') as $key => $row) {
                if( empty($row->find('td',4)->innertext) ) {
                    $lastUpdate = $row->find('td',4)->innertext;
                }else{
                    if( $lastUpdate < $row->find('td',4)->innertext ){
                        $lastUpdate = $row->find('td',4)->innertext;
                    }
                }
            }
            return $lastUpdate;
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            if( $error == 'file_get_contents('. $this->jatimUrl .'): failed to open stream: HTTP request failed!' ){
                return 'down';
            }else{
                return ['error' => $e->getMessage()];
            }
        }
    }

    public function index()
    {
        $dataKotaBlitar = Data::where('city', 'KOTA BLITAR')->orderBy('last_update', 'desc')->first();
        $data = Data::where('updated_at', $dataKotaBlitar->updated_at)->get();

        return ($data);
    }
    public function status()
    {
        if ( ! empty(DB::connection()->getDatabaseName()) ) {
            $return['local'] = [
                'connection' => 'up',
                'last_update' => Data::max('last_update')
            ];
        }else{
            $return['local'] = [
                'connection' => 'down',
                'last_update' => ''
            ];
        }

        if ($this->getServerLastUpdate() == 'down') {
            $return['jatim'] = [
                'connection' => 'down',
                'last_update' => ''
            ];
        }else{
            $return['jatim'] = [
                'connection' => 'up',
                'last_update' => $this->getServerLastUpdate()
            ];
        }

        return response($return);
    }
    public function blitar()
    {
        $hour = date('H');
        
        if($hour > 16) { 
            $lastUpdateLocal = Data::max('last_update');
            $dateTimestampLocal = strtotime($lastUpdateLocal);
            $dateLocal = date('Y-m-d', $dateTimestampLocal);
            $dataNow = date('Y-m-d');

            if( $dateLocal < $dataNow ){
                $ServerLastUpdate = $this->getServerLastUpdate();
                if( $ServerLastUpdate != 'down' && $lastUpdateLocal != $ServerLastUpdate){
                    $this->getUpdate();
                }
            }
        }

        $return = [];
        $dataKotaBlitar = Data::where('city', 'KOTA BLITAR')->orderBy('last_update', 'desc')->first();
        $dataKabBlitar = Data::where('city', 'KAB. BLITAR')->orderBy('last_update', 'desc')->first();
        $dataJatimODP = Data::where('updated_at', $dataKabBlitar->updated_at)->sum('odp');
        $dataJatimPDP = Data::where('updated_at', $dataKabBlitar->updated_at)->sum('pdp');
        $dataJatimConfirm = Data::where('updated_at', $dataKabBlitar->updated_at)->sum('confirm');
        $lastUpdate = Data::max('last_update');
        
        $return['last_update'] =  $lastUpdate;
        $return['blitar']['odp'] =  $dataKotaBlitar->odp + $dataKabBlitar->odp;
        $return['blitar']['pdp'] = $dataKotaBlitar->pdp + $dataKabBlitar->pdp;
        $return['blitar']['confirm'] = $dataKotaBlitar->confirm + $dataKabBlitar->confirm;
        $return['blitarkota']['odp'] =  $dataKotaBlitar->odp;
        $return['blitarkota']['pdp'] = $dataKotaBlitar->pdp;
        $return['blitarkota']['confirm'] = $dataKotaBlitar->confirm;
        $return['blitarkab']['odp'] =  $dataKabBlitar->odp;
        $return['blitarkab']['pdp'] = $dataKabBlitar->pdp;
        $return['blitarkab']['confirm'] = $dataKabBlitar->confirm;
        $return['jatim']['odp'] =  (int) $dataJatimODP;
        $return['jatim']['pdp'] = (int) $dataJatimPDP;
        $return['jatim']['confirm'] = (int) $dataJatimConfirm;

        return response($return);
    }

    private function getUpdate( $testing = false )
    {
        DB::beginTransaction();
        try {
            $html =  HtmlDomParser::file_get_html($this->jatimUrl);
        
            $tabel = $html->find('tbody',0);
            $dataJadi = array();
            $jatim['odp'] = 0;
            $jatim['pdp'] = 0;
            $jatim['confirm'] = 0;
            $lastUpdate = '';
            
            $messages = "*PEMBAHARUAN DATA COVID-19 JAWA TIMUR* \r\n";
            
            $messagesCity = '';
    
            foreach ($tabel->find('tr') as $key => $row) {
                $dataJadi[$key]['city'] = $row->find('td',0)->innertext;
                $dataJadi[$key]['odp'] = $row->find('td',1)->innertext;
                $dataJadi[$key]['pdp'] = $row->find('td',2)->innertext;
                $dataJadi[$key]['confirm'] = $row->find('td',3)->innertext;
                $dataJadi[$key]['last_update'] = $row->find('td',4)->innertext;
                
                $messagesCity .= "*".$dataJadi[$key]['city']."* \r\n";
                $messagesCity .= "+ *Positiv* : ". $dataJadi[$key]['confirm'] ." \r\n";
                $messagesCity .= "+ *PDP* : ". $dataJadi[$key]['pdp'] ." \r\n";
                $messagesCity .= "+ *ODP* : ". $dataJadi[$key]['odp'] ." \r\n";
                $messagesCity .= "---------------------------------------\r\n";

                if ( !$testing ) {
                    $data = Data::create($dataJadi[$key]);
                }
                $jatim['odp'] =  $jatim['odp'] + $dataJadi[$key]['odp'];
                $jatim['pdp'] = $jatim['pdp'] + $dataJadi[$key]['pdp'];
                $jatim['confirm'] = $jatim['confirm'] + $dataJadi[$key]['confirm'];
                
                if( empty($lastUpdate) ) {
                    $lastUpdate = $dataJadi[$key]['last_update'];
                }else{
                    if( $lastUpdate < $dataJadi[$key]['last_update'] ){
                        $lastUpdate = $dataJadi[$key]['last_update'];
                    }
                }
            }
            $messages .= "_".$lastUpdate."_ \r\n \r\n";
            $messages .= "\r\n";
            $messages .= "*Jawa Timur* \r\n";
            $messages .= "+ *Positiv* : ". $jatim['confirm'] ." \r\n";
            $messages .= "+ *PDP* : ". $jatim['pdp'] ." \r\n";
            $messages .= "+ *ODP* : ". $jatim['odp'] ." \r\n";
            $messages .= "---------------------------------------\r\n";
            $messages .= "\r\n";
            $messages .= "\r\n";
            $messages .= "*Data Kota Se-Jawa Timur* \r\n";
            $messages .= "---------------------------------------\r\n";
            $messages .= $messagesCity;

            DB::commit();
            
            $this->sendNotifTelegram($messages);
            return true;

        } catch (\Exception $e) {
            DB::rollback();
            return ['error' => $e->getMessage()];
        }
    }
    private function sendNotifTelegram($messages)
    {
        $response = Telegram::sendMessage([
            'chat_id' => '34560670', 
            'parse_mode' => 'markdown',
            'text' => $messages
        ]);
          
        return $response;
    }
    public function telegramWebhookUpdates()
    {
        $updates = Telegram::getWebhookUpdates();
        return $update;
    }
    public function tester()
    {
        $html = file_get_contents($this->jatimUrl);
        $return  = $this->getStringBetween($html, 'var datakabupaten=', 'var hariini=', 2 );

        return response($return)->header('Content-Type', 'application/json');
    }
    
    private function getStringBetween($text, $before, $after, $ajust = 0 )
    {
        $text = ' '.$text;
        $ini = strpos($text, $before);
        if( $ini == 0 ) return '';
        $ini += strlen($before);
        $lenght = strpos($text, $after, $ini) - $ini - $ajust;
        return substr($text, $ini, $lenght);
    }
}
