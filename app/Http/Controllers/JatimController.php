<?php

namespace App\Http\Controllers;

use Telegram\Bot\Laravel\Facades\Telegram;
use App\DataJatim;
use DB;

class JatimController extends Controller
{
    private $jatimUrl = 'http://covid19dev.jatimprov.go.id/xweb/draxi';
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
    
    private function getStringBetween($text, $before, $after, $ajust = 0 )
    {
        $text = ' '.$text;
        $ini = strpos($text, $before);
        if( $ini == 0 ) return '';
        $ini += strlen($before);
        $lenght = strpos($text, $after, $ini) - $ini - $ajust;
        return substr($text, $ini, $lenght);
    }
    
    private function getServerLastUpdate()
    {
        try {
            $html = file_get_contents($this->jatimUrl);
            $return  = $this->getStringBetween($html, 'var datakabupaten=', 'var spesimen=', 2 );
            $arr = json_decode($return);
            $lastUpdate = '';
            foreach ($arr as $key => $value) {
                if( $lastUpdate < $value->updated_at ){
                    $lastUpdate = $value->updated_at;
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
    private function checkIfNeedUpdate()
    {
        $hour = date('H');
        
        if($hour > $this->dailyCheckUpdate) { 
            $lastUpdateLocal = DataJatim::max('updated_at');
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
    }

    public function status()
    {
        if ( ! empty(DB::connection()->getDatabaseName()) ) {
            $return['local'] = [
                'connection' => 'up',
                'last_update' => DataJatim::max('updated_at')
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

    public function forceUpdate()
    {
        $this->getUpdate();
    }

    public function blitar()
    {
        $this->checkIfNeedUpdate();

        $return = [];
        $dataKotaBlitar = DataJatim::where('id_kabko', 33)->orderBy('updated_at', 'desc')->first();
        $dataKabBlitar = DataJatim::where('id_kabko', 7)->orderBy('updated_at', 'desc')->first();
        $dataJatimODR = DataJatim::latest()->limit(40)->get()->sum('odr');
        $dataJatimOTG = DataJatim::latest()->limit(40)->get()->sum('otg');
        $dataJatimODP = DataJatim::latest()->limit(40)->get()->sum('odp');
        $dataJatimPDP = DataJatim::latest()->limit(40)->get()->sum('pdp');
        $dataJatimConfirm = DataJatim::latest()->limit(40)->get()->sum('confirm');
        $lastUpdate = DataJatim::max('updated_at');
        
        $return['last_update'] =  $lastUpdate;
        $return['blitarkota'] =  $dataKotaBlitar;
        $return['blitarkab'] =  $dataKabBlitar;
        $return['jatim']['odr'] =  (int) $dataJatimODR;
        $return['jatim']['otg'] =  (int) $dataJatimOTG;
        $return['jatim']['odp'] =  (int) $dataJatimODP;
        $return['jatim']['pdp'] = (int) $dataJatimPDP;
        $return['jatim']['confirm'] = (int) $dataJatimConfirm;

        return response($return);
    }

    private function getUpdate( $testing = false )
    {
        DB::beginTransaction();
        try {
            $html = file_get_contents($this->jatimUrl);
            $return  = $this->getStringBetween($html, 'var datakabupaten=', 'var spesimen=', 2 );
            $arr = json_decode($return, true);
            
            $jatim['odr'] = 0;
            $jatim['otg'] = 0;
            $jatim['odp'] = 0;
            $jatim['pdp'] = 0;
            $jatim['confirm'] = 0;
            
            $messages = "*PEMBAHARUAN DATA COVID-19 JAWA TIMUR* \r\n";
            $messagesCity = '';
            
            foreach ($arr as $key => $value) {
                // Check data on local server first
                $isExist = DataJatim::where('id_old', $value['id'])->first();
                if ( ! $isExist ) {

                    // Messages by city
                    $messagesCity .= "*".$value['kabko']."* \r\n";
                    $messagesCity .= "+ *Positiv* : ". $value['confirm'] ." |   ";
                    $messagesCity .= "+ *PDP* : ". $value['pdp'] ." |   ";
                    $messagesCity .= "+ *ODP* : ". $value['odp'] ." |   ";
                    $messagesCity .= "+ *OTG* : ". $value['otg'] ." |   ";
                    $messagesCity .= "+ *ODR* : ". $value['odr'] ." \r\n\r\n";

                    // Add to jatim data
                    $jatim['odr'] =  $jatim['odr'] + (int) $value['odr'];
                    $jatim['otg'] =  $jatim['otg'] + (int) $value['otg'];
                    $jatim['odp'] =  $jatim['odp'] + (int) $value['odp'];
                    $jatim['pdp'] = $jatim['pdp'] + (int) $value['pdp'];
                    $jatim['confirm'] = $jatim['confirm'] + $value['confirm'];

                    // Insert data
                    if ( ! $testing ) { // if not testing insert data
                        $value['odr'] = (int) $value['odr'];
                        $value['id_old'] = $value['id'];
                        $value['kode'] = ( ! empty($value['kode']) ) ? $value['kode'] : 0;
                        $value['id_kabko'] = ( ! empty($value['id_kabko']) ) ? $value['id_kabko'] : 0;
                        $value['lat'] = ( ! empty($value['lat']) ) ? $value['lat'] : 0;
                        $value['lon'] = ( ! empty($value['lon']) ) ? $value['lon'] : 0;
                        $value['lat1'] = ( ! empty($value['lat1']) ) ? $value['lat1'] : 0;
                        $value['lon1'] = ( ! empty($value['lon1']) ) ? $value['lon1'] : 0;
                        $value['lat2'] = ( ! empty($value['lat2']) ) ? $value['lat2'] : 0;
                        $value['lon2'] = ( ! empty($value['lon2']) ) ? $value['lon2'] : 0;
                        $value['lat3'] = ( ! empty($value['lat3']) ) ? $value['lat3'] : 0;
                        $value['lon3'] = ( ! empty($value['lon3']) ) ? $value['lon3'] : 0;
                        $data = DataJatim::create($value);
                    }

                }
            }
            $messages .= "_".$this->getServerLastUpdate()."_ \r\n \r\n";
            $messages .= "\r\n";
            $messages .= "*Jawa Timur* \r\n";
            $messages .= "+ *Positiv* : ". $jatim['confirm'] ." \r\n";
            $messages .= "+ *PDP* : ". $jatim['pdp'] ." \r\n";
            $messages .= "+ *ODP* : ". $jatim['odp'] ." \r\n";
            $messages .= "+ *OTG* : ". $jatim['otg'] ." \r\n";
            $messages .= "+ *ODR* : ". $jatim['odr'] ." \r\n";
            $messages .= "---------------------------------------\r\n";
            $messages .= "\r\n";
            $messages .= "\r\n";
            $messages .= "*Data Kota Se-Jawa Timur* \r\n";
            $messages .= "---------------------------------------\r\n";
            $messages .= $messagesCity;
            DB::commit();
            $telegram = $this->sendNotifTelegram($messages);

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
    public function telegramWebhookUpdates()
    {
        $updates = Telegram::getWebhookUpdates();
        return $update;
    }
}
