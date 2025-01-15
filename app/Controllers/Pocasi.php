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
        $dataS["data"] = $this->sData->where('Stations_ID',$id)->orderBy('date','DESC')->paginate(25);
        $pager[] = $this->sData->pager;
        $dataS["pager"] = $pager;
        echo view('stanice',$dataS);
    }
    public function staniceAll(){
        $data["damik"] = $this->stations->join('pocasi_bundesland','pocasi_bundesland.id=pocasi_station.bundesland','inner')->orderBy('place')->findAll();
        echo view('staniceAll', $data);
    }

    public function zemeInfo($id){
        $dataBL["bundesland"] = $this->bundesland->find($id);
        echo view('zemeInfo',$dataBL);
    }
}