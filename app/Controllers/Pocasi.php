<?php

namespace App\Controllers;

use App\Models\Zeme;
use App\Models\Stanice;
use App\Models\SData;

class Pocasi extends BaseController
{
    var $bundesland;
    var $stations;
    var $sData;
    public function __construct()
    {
        $this->bundesland = new Zeme();
        $this->stations = new Stanice();
        $this->sData = new SData();
    }
    public function index()
    {
        $dataBL["bundesland"] = $this->bundesland->orderBy("name", 'ASC')->findAll();
        echo view('index',$dataBL);
    }
    public function zeme($id){
        $dataST["stanice"] = $this->stations->where('bundesland',$id)->findAll();
        echo view('zeme',$dataST);
    }
    public function stanice($id){
        $dataS["data"] = $this->sData->where('Stations_ID',$id)->orderBy('date','DESC')->findAll();
        echo view('stanice',$dataS);
    }
}