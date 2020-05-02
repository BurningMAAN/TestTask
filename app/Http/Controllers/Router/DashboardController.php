<?php

namespace App\Http\Controllers\Router;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Classes\Cats;

class DashboardController extends Controller
{
    public function index($ID)
    {
        if(is_numeric($ID) == true &&  $ID >= 1 && $ID <= 1000000){

            $magicArray = array();
            $countAll = $this->CountAll();
            $countN = $this->CountN($ID);
            $isCached = $this->isCached($ID);

            if(!$isCached){
                $catsObject = new Cats($ID);
                $this->cachePage($ID, $catsObject->catsArray, 60);
                $magicArray = $catsObject->catsArray;
                $this->writeHistory($ID, $catsObject->catsArray, $countAll, $countN);
            }
            else{
                $magicArray = $isCached;
                $this->writeHistory($ID, $isCached, $countAll, $countN);
            }
            return $this->outputText($magicArray);

        }
        else{
            return "Neteisingas parametras";
        }
    }

    public function isCached($ID){
        $isCached = Cache::get('cats' . $ID);
        if($isCached){
            return $isCached;
        }
        else{
            return false;
        }
    }

    public function outputText($array){
        return $array[0] . ', ' . $array[1] . ', ' . $array[2];
    }

    public function cachePage($ID, $catsArray, $time){
        if(!$this->isCached($ID)){ //PATIKRINAM AR TIKRAI NEUZCASHINTA
            return Cache::put('cats' . $ID, $catsArray, $time);
        }
        else{
            return 0;
        }
    }


    public function CountN($ID){
        if(Cache::has('count' . $ID)){
            Cache::increment('count' . $ID);
        }
        else{
            Cache::put('count' . $ID, 0);
        }

        return Cache::get('count' . $ID);
    }

    public function CountAll(){
        if(Cache::has('countAll')){
            Cache::increment('countAll');
        }
        else{
            Cache::put('countAll', 1);
        }
        return Cache::get('countAll');
    }

    public function writeHistory($ID, $catsArray, $countAll, $countN){
        $logArray = array(
            "datetime"  => date("Y:m:d h:i:sa"),
            "N"         => $ID,
            "Cats"      => array(str_replace("\n", "",$catsArray[0]),
                                 str_replace("\n", "",$catsArray[1]), 
                                 str_replace("\n", "",$catsArray[2])),
            "countAll"  => $countAll,
            "count" . $ID => $countN
        );

        $logArray = json_encode($logArray);
        
        $myfile = file_put_contents(storage_path('app/logs.txt'), $logArray.PHP_EOL , FILE_APPEND | LOCK_EX);
        return 1;
    }
}
