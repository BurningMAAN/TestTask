<?php

namespace App\Http\Controllers\Router;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index($ID)
    {
        if(is_numeric($ID) == true &&  $ID >= 1 && $ID <= 1000000){

            $countAll = $this->countAll();
            $countN = $this->CountN($ID);
  
            $retrieved = Cache::get('cats' . $ID);
            $catsList = $this->getCatsBreeds();
            if(!$retrieved){
                $uniqueCats = $this->getCatsForPage($catsList);
                $toCache = Cache::put('cats' . $ID, $uniqueCats, 60);
                $quickHelper = array($catsList[$uniqueCats[0]], $catsList[$uniqueCats[1]], $catsList[$uniqueCats[2]]);
                $this->writeHistory($ID, $quickHelper, $countAll, $countN);
            }


            else{
                $quickHelper = array($catsList[$retrieved[0]], $catsList[$retrieved[1]], $catsList[$retrieved[2]]);
                $this->writeHistory($ID, $quickHelper, $countAll, $countN);
            }

            return $quickHelper[0] . ', ' . $quickHelper[1] . ' ,' . $quickHelper[2];


        }
        else{
            return "Neteisingas parametras";
        }
    }

    public function getCatsBreeds(){
        $catsArray = array();
        $catsShelterFile = storage_path('app/cats.txt');
        $file = fopen($catsShelterFile, "r");

        while(!feof($file)){
            $catsArray[] = fgets($file);
        }
        fclose($file);
        return $catsArray;
    } 

    public function getCatsForPage($catsArray){
        $test = $this->randomizeCats($catsArray);
        return $test;
    }

    public function randomizeCats($catsArray){
        $minCatID = 0;
        $maxCatID = sizeof($catsArray);

        $numbers = range($minCatID, $maxCatID);
        shuffle($numbers);
        return array_slice($numbers, 0, 3);
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
