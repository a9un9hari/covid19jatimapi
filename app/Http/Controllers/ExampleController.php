<?php

namespace App\Http\Controllers;

use KubAT\PhpSimple\HtmlDomParser;
use App\Data;

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

    public function index()
    {
        $url = 'http://covid19dev.jatimprov.go.id/xweb/draxi';
        
        $html =  HtmlDomParser::file_get_html($url);
    
        $tabel = $html->find('tbody',0);
    
        $dataJadi = array();
        $return = [];
        $jatim['odp'] = 0;
        $jatim['pdp'] = 0;
        $jatim['confirm'] = 0;
        $blitar['odp'] = 0;
        $blitar['pdp'] = 0;
        $blitar['confirm'] = 0;
    
        foreach ($tabel->find('tr') as $key => $row) {
            $dataJadi[$key]['city'] = $row->find('td',0)->innertext;
            $dataJadi[$key]['odp'] = $row->find('td',1)->innertext;
            $dataJadi[$key]['pdp'] = $row->find('td',2)->innertext;
            $dataJadi[$key]['confirm'] = $row->find('td',3)->innertext;
            $dataJadi[$key]['last_update'] = $row->find('td',4)->innertext;

            $data = Data::create($dataJadi[$key]);
    
            // $jatim['odp'] =  $jatim['odp'] + $dataJadi[$key]['odp'];
            // $jatim['pdp'] = $jatim['pdp'] + $dataJadi[$key]['pdp'];
            // $jatim['confirm'] = $jatim['confirm'] + $dataJadi[$key]['confirm'];
    
            // if( $dataJadi[$key]['kota'] == 'KAB. BLITAR' || $dataJadi[$key]['kota'] == 'KOTA BLITAR'){
            //     $blitar['odp'] =  $blitar['odp'] + $dataJadi[$key]['odp'];
            //     $blitar['pdp'] = $blitar['pdp'] + $dataJadi[$key]['pdp'];
            //     $blitar['confirm'] = $blitar['confirm'] + $dataJadi[$key]['confirm'];
            // }
            // if( empty($return['last_update']) ) {
            //     $return['last_update'] = $dataJadi[$key]['date'];
            // }else{
            //     $lastUpdate = strtotime($return['last_update']); 
            //     $othersDate = strtotime($dataJadi[$key]['date']); 
            //     if( $lastUpdate < $othersDate ){
            //         $return['last_update'] = $dataJadi[$key]['date'];
            //     }
            // }
        }
        // $return['blitar'] = $blitar; 
        // $return['jatim'] = $jatim; 
    
    
        return response($dataJadi);
    }
    public function blitar()
    {
        $return = [];
        $dataKotaBlitar = Data::where('city', 'KOTA BLITAR')->first();
        $dataKabBlitar = Data::where('city', 'KAB. BLITAR')->first();
        $dataJatimODP = Data::sum('odp');
        $dataJatimPDP = Data::sum('pdp');
        $dataJatimConfirm = Data::sum('confirm');
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
}
