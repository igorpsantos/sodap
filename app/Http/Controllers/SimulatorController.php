<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SimulatorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('simulador.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $numeroProcessos = 0;
        $tiposAlgoritmo = [
            'FIFO',
            'RR',
            'SJF',
            'SJRTF'
        ];
        $tipo_algoritmo = '';

        if($request->has('numero_processos') && $request->numero_processos > 0){
            $numeroProcessos = (int) $request->numero_processos;
        }
        if($request->has('tipo_algoritmo') && in_array($request->tipo_algoritmo, ['FIFO','RR','SJF','SJRTF'])){
            $tipo_algoritmo = $request->tipo_algoritmo;
        }

        return view('simulador.create', [
            'numeroProcessos' => $numeroProcessos,
            'tiposAlgoritmo' => $tiposAlgoritmo,
            'tipo_algoritmo' => $tipo_algoritmo
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data["numeroProcessos"] = $request->numeroProcessos;
        $data["tipo_algoritmo"] = $request->tipo_algoritmo;
        for($i = 0; $i < $data["numeroProcessos"]; $i++){
            $data["tempo_ingresso_" . $i] = $request['tempo_ingresso_' . $i];
            $data["tempo_duracao_" . $i] = $request['tempo_duracao_' . $i];
        }

        return redirect()->route('simulador.resultado', $data);
    }

    public function resultado(Request $request)
    {
        $data["numeroProcessos"] = $request->numeroProcessos;
        $data["tipo_algoritmo"] = $request->tipo_algoritmo;
        for($i = 0; $i < $data["numeroProcessos"]; $i++){
            $data['processos']['t'.$i]["tempo_ingresso"] = $request['tempo_ingresso_' . $i];
            $data['processos']['t'.$i]["tempo_duracao"] = $request['tempo_duracao_' . $i];
        }

        $processosBySortAsc = $data['processos'];
        // calcula o tempo total de duração
        $tempoTotalDuracao = [];
        foreach($data['processos'] as $key => $item){
            $tempoTotalDuracao[] = $item['tempo_duracao'];
        }
        $tempo_total_duracao = array_sum($tempoTotalDuracao);

        $diagramaTempo = [];
        $diagramaTempoTeste = [];

        $count = 0;
        $tempoFim = 0;
        $tempoInicio = $tempoFim;
        foreach($processosBySortAsc as $key => $item){
            if($count == 0 && $item['tempo_duracao'] > 0){
                $tempoFim = $item['tempo_duracao'];
            }
            if($count > 0 ){
                $tempoInicio = $tempoFim;
                $tempoFim += $item['tempo_duracao'];
            }
            $diagramaTempoTeste[$key] = [
                'quantidade_td' => $item['tempo_duracao'],
                'tempo_ingresso' => $item['tempo_ingresso'],
                'tempo_inicio' => $tempoInicio,
                'tempo_fim' => $tempoFim
            ];
            $count++;
        }

        // $diagramaTempo['t3'] = [
        //     'quantidade_td' => $processosBySortAsc['t3']['tempo_duracao'],
        //     'tempo_ingresso' => $processosBySortAsc['t3']['tempo_ingresso'],
        //     'tempo_inicio' => $processosBySortAsc['t3']['tempo_ingresso'],
        //     'tempo_fim' => $processosBySortAsc['t3']['tempo_duracao'] + $processosBySortAsc['t3']['tempo_ingresso']
        // ];

        // $diagramaTempo['t1'] = [
        //     'quantidade_td' => $processosBySortAsc['t1']['tempo_duracao'],
        //     'tempo_ingresso' => $processosBySortAsc['t1']['tempo_ingresso'],
        //     'tempo_inicio' => $diagramaTempo['t3']['tempo_fim'],
        //     'tempo_fim' => $processosBySortAsc['t1']['tempo_duracao'] + $diagramaTempo['t3']['tempo_fim']
        // ];

        // $diagramaTempo['t2'] = [
        //     'quantidade_td' => $processosBySortAsc['t2']['tempo_duracao'],
        //     'tempo_ingresso' => $processosBySortAsc['t2']['tempo_ingresso'],
        //     'tempo_inicio' => $diagramaTempo['t1']['tempo_fim'],
        //     'tempo_fim' => $processosBySortAsc['t2']['tempo_duracao'] + $diagramaTempo['t1']['tempo_fim']
        // ];

        // $diagramaTempo['t0'] = [
        //     'quantidade_td' => $processosBySortAsc['t0']['tempo_duracao'],
        //     'tempo_ingresso' => $processosBySortAsc['t0']['tempo_ingresso'],
        //     'tempo_inicio' => $diagramaTempo['t2']['tempo_fim'],
        //     'tempo_fim' => $processosBySortAsc['t0']['tempo_duracao'] + $diagramaTempo['t2']['tempo_fim']
        // ];

        // $diagramaTempo['t4'] = [
        //     'quantidade_td' => $processosBySortAsc['t4']['tempo_duracao'],
        //     'tempo_ingresso' => $processosBySortAsc['t4']['tempo_ingresso'],
        //     'tempo_inicio' => $diagramaTempo['t0']['tempo_fim'],
        //     'tempo_fim' => $processosBySortAsc['t4']['tempo_duracao'] + $diagramaTempo['t0']['tempo_fim']
        // ];

        $data['processosBySortAsc'] = $processosBySortAsc;
        $data['diagramaTempo'] = $diagramaTempo;
        $data['diagramaTempoTeste'] = $diagramaTempoTeste;
        $data['tempo_total_duracao'] = $tempo_total_duracao;

        return view('simulador.resultado', $data);
    }
}
