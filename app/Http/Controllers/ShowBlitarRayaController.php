<?php

namespace App\Http\Controllers;

use Telegram\Bot\Laravel\Facades\Telegram;
use KubAT\PhpSimple\HtmlDomParser;
use App\VillageData;
use DB;

class ShowBlitarRayaController extends Controller
{
    private $blitarUrl = 'http://petasebaran.covid19.blitarkota.go.id';
    private $dailyCheckUpdate = 16; // 1-24 hour

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    private function getServerLastUpdate()
    {
        $html =  HtmlDomParser::file_get_html($this->blitarUrl);
    
        $tabel = $html->find('tbody',0);
        $lastUpdate = $html->find('center small i span',0)->innertext;

        return $lastUpdate;
    }

    private function checkIfNeedUpdate()
    {
        $hour = date('H');

        if($hour > $this->dailyCheckUpdate) { 
            $blitarLastUpdateLocal = VillageData::where('city', 'KOTA BLITAR')->orderBy('id', 'desc')->first();

            $lastUpdateLocal = VillageData::max('created_at');
            $dateTimestampLocal = strtotime($lastUpdateLocal);
            $dateLocal = date('Y-m-d', $dateTimestampLocal);
            $dataNow = date('Y-m-d');

            if( $dateLocal < $dataNow ){
                $ServerLastUpdate = $this->getServerLastUpdate();
                if( $blitarLastUpdateLocal->last_update != $ServerLastUpdate){
                    $this->getUpdate();
                }
            }
        }
    }

    public function showBlitarRaya()
    {
        $this->checkIfNeedUpdate();
        
        $dataKotaBlitar = VillageData::orderBy('created_at', 'desc')->limit(43)->get();

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
            $html =  HtmlDomParser::file_get_html($this->blitarUrl);
        
            $tabel = $html->find('tbody',0);
            $dataJadi = array();

            $blitar['odr'] = 0;
            $blitar['otg'] = 0;
            $blitar['odp'] = 0;
            $blitar['pdp'] = 0;
            $blitar['confirm'] = 0;
            
            $lastUpdate = $html->find('center small i span',0)->innertext;
            $messages = "*PEMBAHARUAN DATA COVID-19 KOTA BLITAR* \r\n";
            
            $messagesBlitar = '';
    
            foreach ($tabel->find('tr') as $key => $row) {
                if ($row->find('td',1)->innertext == 'TOTAL') {
                    continue;
                }
                $dataJadi[$key]['city'] = 'KOTA BLITAR';
                $dataJadi[$key]['village'] = $row->find('td',1)->innertext;
                $dataJadi[$key]['odr'] = $row->find('td',2)->innertext;
                $dataJadi[$key]['otg'] = $row->find('td',3)->innertext;
                $dataJadi[$key]['odp'] = $row->find('td',4)->innertext;
                $dataJadi[$key]['pdp'] = $row->find('td',5)->innertext;
                $dataJadi[$key]['confirm'] = $row->find('td',6)->innertext;
                $dataJadi[$key]['confirm_recover'] = $row->find('td',7)->innertext;
                $dataJadi[$key]['confirm_die'] = $row->find('td',8)->innertext;
                $dataJadi[$key]['last_update'] = $lastUpdate;
                
                // Messages by village
                $messagesBlitar .= "*".$dataJadi[$key]['village']."* \r\n";
                $messagesBlitar .= "+ *Positiv* : ". $dataJadi[$key]['confirm'] ." |   ";
                $messagesBlitar .= "+ *PDP* : ". $dataJadi[$key]['pdp'] ." |   ";
                $messagesBlitar .= "+ *ODP* : ". $dataJadi[$key]['odp'] ." |   ";
                $messagesBlitar .= "+ *OTG* : ". $dataJadi[$key]['otg'] ." |   ";
                $messagesBlitar .= "+ *ODR* : ". $dataJadi[$key]['odr'] ." |   ";
                $messagesBlitar .= "---------------------------------------\r\n";

                // Add to Blitar data
                $blitar['odr'] =  $blitar['odr'] + (int) $dataJadi[$key]['odr'];
                $blitar['otg'] =  $blitar['otg'] + (int) $dataJadi[$key]['otg'];
                $blitar['odp'] =  $blitar['odp'] + (int) $dataJadi[$key]['odp'];
                $blitar['pdp'] = $blitar['pdp'] + (int) $dataJadi[$key]['pdp'];
                $blitar['confirm'] = $blitar['confirm'] + (int) $dataJadi[$key]['confirm'];

                    // print_r($dataJadi);
                if ( ! $testing ) {
                    $data = VillageData::create($dataJadi[$key]);
                }
            }

            $messages .= "_".$this->getServerLastUpdate()."_ \r\n \r\n";
            $messages .= "\r\n";
            $messages .= "*Kota Blitar* \r\n";
            $messages .= "+ *Positiv* : ". $blitar['confirm'] ." \r\n";
            $messages .= "+ *PDP* : ". $blitar['pdp'] ." \r\n";
            $messages .= "+ *ODP* : ". $blitar['odp'] ." \r\n";
            $messages .= "+ *OTG* : ". $blitar['otg'] ." \r\n";
            $messages .= "+ *ODR* : ". $blitar['odr'] ." \r\n";
            $messages .= "---------------------------------------\r\n";
            $messages .= "\r\n";
            $messages .= "\r\n";
            $messages .= "*Data Kelurahan Se-Kota Blitar* \r\n";
            $messages .= "---------------------------------------\r\n";
            $messages .= $messagesBlitar;

            DB::commit();
            
            $this->sendNotifTelegram($messages);
            return true;

        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
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
}
