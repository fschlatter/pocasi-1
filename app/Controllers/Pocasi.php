<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Bundesland;
use App\Models\Data;
use App\Models\Station;


class Pocasi extends BaseController
{
    var $bundesland;
    var $data;
    var $station;
    public function __construct()
    {
        $this->bundesland = new Bundesland();
        $this->data = new Data();
        $this->station = new Station();
    }
    
    public function index()
    {
        $dataB['bundesland'] = $this->bundesland->findAll();
        
        echo view('stranka1', $dataB);
    }
    public function Stanice($idZeme)
    {
        echo view('stranka2');
        $dataS["station"] = $this->station->findAll();
    }
    public function bundesland()
    {
        $dataB['bundesland'] = $this->bundesland->findAll();
        
        echo view('bundesland', $dataB);
    }
    public function data()
    {
        $dataD["data"] = $this->data->findAll();;
        
        echo view('data', $dataD);
    }
    public function station()
    {
        $dataS["station"] = $this->station->findAll();;
        
        echo view('station', $dataS);
    }
}
