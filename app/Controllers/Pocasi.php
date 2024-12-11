<?php

namespace App\Controllers;

use App\Models\Zeme;

class Pocasi extends BaseController
{
    var $bundesland;
    public function __construct()
        {
            $this->bundesland = new Zeme();
        }
    public function index()
    {
        $dataBL["bundesland"] = $this->bundesland->findAll();
        echo view('index',$dataBL);
    }
    public function zeme($id){
        $dataBL = $this->bundesland->find($id);
        echo view('zeme',$dataBL);
    }
}