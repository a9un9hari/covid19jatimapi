<?php

namespace App\Http\Controllers;

use Telegram\Bot\Laravel\Facades\Telegram;
use KubAT\PhpSimple\HtmlDomParser;
use App\VillageData;
use DB;

class ShowBlitarRayaController extends Controller
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

    private function checkIfNeedUpdate()
    {
        $hour = date('H');

        if($hour > 15) { 
            
            $lastUpdateLocal = VillageData::max('created_at');
            $dateTimestampLocal = strtotime($lastUpdateLocal);
            $dateLocal = date('Y-m-d', $dateTimestampLocal);
            $dataNow = date('Y-m-d');

            if( $dateLocal < $dataNow ){
                $this->getUpdate();
            }
        }
    }

    public function showBlitarRaya()
    {
        $this->checkIfNeedUpdate();
        
        $dataKotaBlitar = VillageData::where('city', 'KOTA BLITAR')->orderBy('created_at', 'desc')->limit(21)->get();

        return ($dataKotaBlitar);
    }

    public function showVillage($village)
    {
        $this->checkIfNeedUpdate();
        
        $villageData = VillageData::where('village', $village)->orderBy('created_at', 'desc')->first();

        return ($villageData);
    }
    public function blitar()
    {

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
        $return['jatim']['odp'] =  (int) $dataJatimODP;
        $return['jatim']['pdp'] = (int) $dataJatimPDP;
        $return['jatim']['confirm'] = (int) $dataJatimConfirm;

        return response($return);
    }

    private function getUpdate( $testing = false )
    {
        DB::beginTransaction();
        try {
            $url = 'http://petasebaran.covid19.blitarkota.go.id';
            
            $html =  HtmlDomParser::file_get_html($url);
        
            $tabel = $html->find('tbody',0);
            $dataJadi = array();
            $blitar['odp'] = 0;
            $blitar['pdp'] = 0;
            $blitar['confirm'] = 0;
            $lastUpdate = $html->find('center small i span',0)->innertext;
            // $messages = "*PEMBAHARUAN DATA COVID-19 JAWA TIMUR* \r\n";
            
            // $messagesCity = '';
    
            foreach ($tabel->find('tr') as $key => $row) {
                if ($row->find('td',1)->innertext == 'TOTAL') {
                    continue;
                }
                $dataJadi[$key]['city'] = 'KOTA BLITAR';
                $dataJadi[$key]['village'] = $row->find('td',1)->innertext;
                $dataJadi[$key]['odr'] = $row->find('td',2)->innertext;
                $dataJadi[$key]['odp'] = $row->find('td',3)->innertext;
                $dataJadi[$key]['pdp'] = $row->find('td',4)->innertext;
                $dataJadi[$key]['confirm'] = $row->find('td',5)->innertext;
                $dataJadi[$key]['last_update'] = $lastUpdate;
                
                // $messagesCity .= "*".$dataJadi[$key]['city']."* \r\n";
                // $messagesCity .= "+ *Positiv* : ". $dataJadi[$key]['confirm'] ." \r\n";
                // $messagesCity .= "+ *PDP* : ". $dataJadi[$key]['pdp'] ." \r\n";
                // $messagesCity .= "+ *ODP* : ". $dataJadi[$key]['odp'] ." \r\n";
                // $messagesCity .= "---------------------------------------\r\n";
                    // print_r($dataJadi);
                if ( ! $testing ) {
                    $data = VillageData::create($dataJadi[$key]);
                }
            }
            // dd($dataJadi);
            // $messages .= "_".$lastUpdate."_ \r\n \r\n";
            // $messages .= "\r\n";
            // $messages .= "*Jawa Timur* \r\n";
            // $messages .= "+ *Positiv* : ". $jatim['confirm'] ." \r\n";
            // $messages .= "+ *PDP* : ". $jatim['pdp'] ." \r\n";
            // $messages .= "+ *ODP* : ". $jatim['odp'] ." \r\n";
            // $messages .= "---------------------------------------\r\n";
            // $messages .= "*Blitar Raya* \r\n";
            // $messages .= "+ *Positiv* : ". $blitar['confirm'] ." \r\n";
            // $messages .= "+ *PDP* : ". $blitar['pdp'] ." \r\n";
            // $messages .= "+ *ODP* : ". $blitar['odp'] ." \r\n";
            // $messages .= "---------------------------------------\r\n";
            // $messages .= "\r\n";
            // $messages .= "\r\n";
            // $messages .= "*Data Kota Se-Jawa Timur* \r\n";
            // $messages .= "---------------------------------------\r\n";
            // $messages .= $messagesCity;

            DB::commit();
            
            // $this->sendNotifTelegram($messages);
            return true;

        } catch (\Exception $e) {
            DB::rollback();
            return ['error' => $e->getMessage()];
        }
    }
    // private function sendNotifTelegram($messages)
    // {
    //     $response = Telegram::sendMessage([
    //         'chat_id' => '34560670', 
    //         'parse_mode' => 'markdown',
    //         'text' => $messages
    //     ]);
    //     return $response;
    // }
    // public function telegramWebhookUpdates()
    // {
    //     $updates = Telegram::getWebhookUpdates();
    //     return $update;
    // }
}
