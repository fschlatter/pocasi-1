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
    public function staniceAll()
    {
        helper(['url', 'html']); // jistota, že anchor() a img() fungují

        $stations['stations'] = $this->stations
            ->join('pocasi_bundesland', 'pocasi_bundesland.id = pocasi_station.bundesland', 'inner')
            ->orderBy('place')
            ->findAll();

        return view('staniceAll', $stations);
    }

    public function zemeInfo($id){
        $dataBL["bundesland"] = $this->bundesland->find($id);
        echo view('zemeInfo',$dataBL);
    }

    /**
     * Zobrazí formulář pro (soft) smazání záznamů z pocasi_data
     * pro vybranou stanici a konkrétní měsíc/rok.
     */
    public function mazani()
    {
        $data['stations'] = $this->stations->orderBy('place')->findAll();
        $data['months']   = [
            1 => 'Leden', 2 => 'Únor', 3 => 'Březen', 4 => 'Duben',
            5 => 'Květen', 6 => 'Červen', 7 => 'Červenec', 8 => 'Srpen',
            9 => 'Září', 10 => 'Říjen', 11 => 'Listopad', 12 => 'Prosinec',
        ];

        return view('mazani', $data);
    }

    /**
     * Zpracuje formulář z mazani() a provede soft delete
     * (nastaví deleted_at) na odpovídajících řádcích v pocasi_data.
     */
    public function mazaniSmazat()
    {
        $rules = [
            'station_id' => 'required|integer',
            'month'      => 'required|integer|greater_than[0]|less_than_equal_to[12]',
            'year'       => 'required|integer|greater_than[1900]|less_than_equal_to[' . date('Y') . ']',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('mazani')->withInput()->with('errors', $this->validator->getErrors());
        }

        $stationId = (int) $this->request->getPost('station_id');
        $month     = (int) $this->request->getPost('month');
        $year      = (int) $this->request->getPost('year');

        // Kolik řádků to bude ovlivňovat (pro hlášku uživateli).
        $count = $this->sData
            ->where('Stations_ID', $stationId)
            ->where('YEAR(date)', $year, false)
            ->where('MONTH(date)', $month, false)
            ->countAllResults();

        // Vlastní soft delete — díky useSoftDeletes = true v modelu SData
        // toto NEmaže řádky fyzicky, jen nastaví sloupec deleted_at.
        $this->sData
            ->where('Stations_ID', $stationId)
            ->where('YEAR(date)', $year, false)
            ->where('MONTH(date)', $month, false)
            ->delete();

        $station = $this->stations->find($stationId);
        $label   = $station['place'] ?? $stationId;

        return redirect()->to('mazani')->with(
            'message',
            "Smazáno (soft delete) {$count} záznam(ů) pro stanici \"{$label}\", {$month}/{$year}."
        );
    }
    public function earthquakes()
    {
        return view('earthquakes');
    }
}