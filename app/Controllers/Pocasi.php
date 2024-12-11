<?php

namespace App\Controllers;

class Pocasi extends BaseController
{
    public function index(): string
    {
        return view('index');
    }
}