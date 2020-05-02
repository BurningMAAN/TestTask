<?php
namespace App\Http\Controllers\Classes;

use Illuminate\Support\Facades\Cache;

class Cats{

    public $ID;

    public $catsArray = array();



    function __construct($ID_){
        $this->ID = $ID_;
        $Cats = $this->getCatsBreeds();

        $catsForPage = $this->getCatsForPage($Cats);
        $this->catsArray = array(
            $Cats[$catsForPage[0]],
            $Cats[$catsForPage[1]],
            $Cats[$catsForPage[2]]
        );

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

}