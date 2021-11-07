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
            'SRTF',
            'PRIOc'
        ];
        $tipo_algoritmo = '';

        if($request->has('numero_processos') && $request->numero_processos > 0){
            $numeroProcessos = (int) $request->numero_processos;
        }
        if($request->has('tipo_algoritmo') && in_array($request->tipo_algoritmo, ['FIFO','RR','SJF','SRTF', 'PRIOc'])){
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
        if($request->has("tempo_quantum")){
            $data["tempo_quantum"] = $request->tempo_quantum;
        }
        for($i = 0; $i < $data["numeroProcessos"]; $i++){
            $data["tempo_ingresso_" . $i] = $request['tempo_ingresso_' . $i];
            $data["tempo_duracao_" . $i] = $request['tempo_duracao_' . $i];
            if($request->tipo_algoritmo == 'PRIOc'){
                $data["prioridade_processo_" . $i] = $request['prioridade_processo_' . $i];
            }
        }

        return redirect()->route('simulador.resultado', $data);
    }

    public function resultado(Request $request)
    {
        $data["numeroProcessos"] = $request->numeroProcessos;
        $data["tipo_algoritmo"] = $request->tipo_algoritmo;
        if($request->has("tempo_quantum")){
            $data["tempo_quantum"] = $request->tempo_quantum;
        }

        $data['processos'] = [];
        if($data["tipo_algoritmo"] == 'SJF'){
            for($i = 0; $i < $data["numeroProcessos"]; $i++){
                $data['processos'][$i]["tempo_duracao"] = $request['tempo_duracao_' . $i];
                $data['processos'][$i]["tempo_ingresso"] = $request['tempo_ingresso_' . $i];
            }
        }elseif($data["tipo_algoritmo"] == 'PRIOc'){
            for($i = 0; $i < $data["numeroProcessos"]; $i++){
                $data['processos'][$i]["tempo_duracao"] = $request['tempo_duracao_' . $i];
                $data['processos'][$i]["tempo_ingresso"] = $request['tempo_ingresso_' . $i];
                $data['processos'][$i]["prioridade_processo"] = $request['prioridade_processo_' . $i];
            }
        }else{
            for($i = 0; $i < $data["numeroProcessos"]; $i++){
                $data['processos'][$i]["tempo_ingresso"] = $request['tempo_ingresso_' . $i];
                $data['processos'][$i]["tempo_duracao"] = $request['tempo_duracao_' . $i];
            }
        }

        $processosBySortAsc = $data['processos'];
        // calcula o tempo total de duração
        $tempoTotalDuracao = [];
        foreach($data['processos'] as $key => $item){
            $tempoTotalDuracao[] = $item['tempo_duracao'];
        }
        $tempo_total_duracao = array_sum($tempoTotalDuracao);

        // verifica o menor tempo de ingresso
        $tempoIngresso = [];
        foreach($data['processos'] as $key => $item){
            $tempoIngresso[] = $item['tempo_ingresso'];
        }
        $menorTempoIngresso = 0;
        asort($tempoIngresso);

        $menorTempoIngresso = array_shift($tempoIngresso);

        $diagramaTempo = [];
        $diagramaTempoTeste = [];

        // fifo
        if($request->tipo_algoritmo == 'FIFO'){
            $filaIngresso = collect($processosBySortAsc);
            $filaAptos = [];
            $clock = 0; // tempo de ingresso dos processos na fila de pronto
            $tempoFim = 0;
            $tempoInicio = 0;
            for ($i=0; $i <= $tempo_total_duracao; $i++) {
                if($i == $menorTempoIngresso){
                    $filaAptos = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortBy('tempo_ingresso')->toArray();
                    $keys = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortBy('tempo_ingresso')->keys();
                    $cnt = 0;
                    foreach ($filaAptos as $key => $n) {
                        $filaAptos[$key]['numero_processo'] = $keys[$cnt++];
                    }
                }else{
                    $filaAptos = $filaIngresso->where('tempo_ingresso', $i)->sortBy('tempo_ingresso')->toArray();
                    $keys = $filaIngresso->where('tempo_ingresso', $i)->sortBy('tempo_ingresso')->keys();
                    $cnt = 0;
                    foreach ($filaAptos as $key => $n) {
                        $filaAptos[$key]['numero_processo'] = $keys[$cnt++];
                    }
                }

                if($i == 0){
                    $tempoInicio = 0;
                }
                
                foreach ($filaAptos as $key => $processo) {
                    if($clock == 0){
                        $tempoFim = $processo['tempo_duracao'];
                    }
                    if($clock > 0){
                        $tempoInicio = $tempoFim;
                        $tempoFim += $processo['tempo_duracao'];
                    }
                    $diagramaTempoTeste['t'.$clock] = [
                        'quantidade_td' => $processo['tempo_duracao'],
                        'tempo_ingresso' => $processo['tempo_ingresso'],
                        'tempo_duracao' => $processo['tempo_duracao'],
                        'tempo_inicio' => $tempoInicio,
                        'tempo_fim' => $tempoFim,
                        'numero_processo' => $processo['numero_processo']
                    ];
                    $clock++;
                }
            }
        }

        // RR
        if($request->tipo_algoritmo == 'RR'){
            $filaIngresso = collect($processosBySortAsc);
            $filaAptos = [];
            $tempoFim = 0;
            $tempoInicio = 0;
            $tempoRestante = 0;
            $onProcessador = [];
            $offProcessador = [];
            $offProcessador['tempo_restante'] = 0;
            $count = 0;
            for ($i=0; $i <= $tempo_total_duracao; $i++) { 
                if($i == $menorTempoIngresso){
                    $array = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortBy('tempo_ingresso')->toArray();
                    $keys = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortBy('tempo_ingresso')->keys();
                    $cnt = 0;
                    foreach ($array as $key => $n) {
                        $array[$key]['numero_processo'] = $keys[$cnt++];
                    }
                    foreach ($array as $n) {
                        $filaAptos[] = $n;
                    }
                }else{
                    $array = $filaIngresso->where('tempo_ingresso', $i)->sortBy('tempo_ingresso')->toArray();
                    $keys = $filaIngresso->where('tempo_ingresso', $i)->sortBy('tempo_ingresso')->keys();
                    $cnt = 0;
                    foreach ($array as $key => $n) {
                        $array[$key]['numero_processo'] = $keys[$cnt++];
                    }
                    foreach ($array as $key => $n) {
                        $filaAptos[] = $n;
                    }
                }

                // if($tempoFim == $tempo_total_duracao){
                //     break;
                // }

                // if($pula){
                //     continue;
                // }
                

                // if($i == 5){
                //     dd($i,$filaAptos, $filaIngresso->where('tempo_ingresso', $i)->sortBy('tempo_ingresso')->toArray(), $diagramaTempoTeste);
                // }

                // if($i == 14){
                //     dd('fila de aptos:>', $filaAptos, 'saindo do processador:>', $offProcessador, 'no processador:>',$onProcessador, 'tempo inicio', $tempoInicio, 'tempo fim', $tempoFim, $count);
                // }
                if($i > 0 && ($count % $data["tempo_quantum"] == 0) && ($onProcessador['tempo_duracao'] >= $data['tempo_quantum'] || isset($onProcessador['tempo_restante']) && $onProcessador['tempo_restante'] >= $data['tempo_quantum'])
                    || $i > 0 && ($onProcessador['tempo_duracao'] < $data['tempo_quantum'] || isset($onProcessador['tempo_restante']) && $onProcessador['tempo_restante'] < $data['tempo_quantum'])){
                    
                    if(isset($onProcessador['tempo_restante'])){
                        $onProcessador['tempo_restante'] >= $data["tempo_quantum"] 
                            ? $tempoRestante = $onProcessador['tempo_restante'] - $data["tempo_quantum"]
                            : $tempoRestante = 0;
                    }else{
                        $onProcessador['tempo_duracao'] >= $data["tempo_quantum"] 
                            ? $tempoRestante = $onProcessador['tempo_duracao'] - $data["tempo_quantum"]
                            : $tempoRestante = 0;
                    }
    
                    if(isset($onProcessador['tempo_restante']) && $onProcessador['tempo_restante'] > 0){
                        $diagramaTempoTeste[$i] = [
                            'quantidade_td' => $onProcessador['tempo_restante'] >= $data['tempo_quantum'] ? $data['tempo_quantum'] : $onProcessador['tempo_restante'],
                            'tempo_ingresso' => $onProcessador['tempo_ingresso'],
                            'tempo_duracao' => $onProcessador['tempo_duracao'],
                            'tempo_inicio' => $tempoInicio,
                            'tempo_fim' => $tempoFim,
                            'tempo_restante' => $tempoRestante,
                            'numero_processo' => $onProcessador['numero_processo']
                        ];
                    }else{
                        $diagramaTempoTeste[$i] = [
                            'quantidade_td' => $onProcessador['tempo_duracao'] >= $data['tempo_quantum'] ? $data['tempo_quantum'] : $onProcessador['tempo_duracao'],
                            'tempo_ingresso' => $onProcessador['tempo_ingresso'],
                            'tempo_duracao' => $onProcessador['tempo_duracao'],
                            'tempo_inicio' => $tempoInicio,
                            'tempo_fim' => $tempoFim,
                            'tempo_restante' => $tempoRestante,
                            'numero_processo' => $onProcessador['numero_processo']
                        ];
                    }

                    $offProcessador = $onProcessador;
                    $offProcessador['tempo_restante'] = $tempoRestante;

                    if($offProcessador['tempo_restante'] > 0){
                        $filaAptos[] = $offProcessador;
                    }
                    $onProcessador = [];
                    $tempoInicio = $tempoFim;
                    $count = 0;
                }

                if(empty($onProcessador)){
                    $onProcessador = array_shift($filaAptos);
                }

                // if(isset($onProcessador['tempo_restante'])){
                //     for ($j=0; $j < ($onProcessador['tempo_restante'] >= $data['tempo_quantum'] ? $data['tempo_quantum'] : $onProcessador['tempo_restante']); $j++) { 
                //         $tempoFim++;
                //     }
                // }else{
                //     for ($j=0; $j < ($onProcessador['tempo_duracao'] >= $data['tempo_quantum'] ? $data['tempo_quantum'] : $onProcessador['tempo_duracao']); $j++) { 
                //         $tempoFim++;
                //     }
                // }

                $tempoFim++;
                $count++;

                // if($i == 2){
                //     dd($filaAptos, $onProcessador, $offProcessador);
                // }
            }

            $arrayDiagrama = $diagramaTempoTeste;
            $diagramaTempoTeste = [];
            foreach ($arrayDiagrama as $key => $item) {
                $diagramaTempoTeste[] = $item;
            }
        }

        // SJF
        $collection = collect([]);
        if($request->tipo_algoritmo == 'SJF'){
            $filaIngresso = collect($processosBySortAsc);
            $filaAptos = [];
            $clock = 0; // tempo de ingresso dos processos na fila de pronto
            $tempoFim = 0;
            $tempoInicio = 0;
            $onProcessador = [];
            $last = [];
            $first = [];
            for ($i=0; $i <= $tempo_total_duracao; $i++) {

                if($i == $menorTempoIngresso){
                    $array = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortBy('tempo_ingresso')->toArray();
                    if(count($array) > 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortBy('tempo_ingresso')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        $array = collect($array);
                        $array = $array->sortBy('tempo_duracao')->toArray();

                        $filaAptos[] = array_shift($array);
                        $tempoFim += $filaAptos[0]['tempo_duracao'];
                        while(!empty($array)){
                            $last[] = array_shift($array);
                        }
                    }elseif(count($array) == 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortBy('tempo_ingresso')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }
                        $array = collect($array);
                        $array = $array->sortBy('tempo_duracao')->toArray();

                        $filaAptos[] = array_shift($array);
                        $tempoFim += $filaAptos[0]['tempo_duracao'];
                    }
                }else{
                    $array = $filaIngresso->where('tempo_ingresso', $i)->sortBy('tempo_ingresso')->toArray();
                    if(count($array) > 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $i)->sortBy('tempo_ingresso')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        $array = collect($array);
                        $array = $array->sortBy('tempo_duracao')->toArray();

                        $first = array_shift($array);
                        $tempoFim += $first['tempo_duracao'];

                        if(!empty($array) && $last[0]['tempo_duracao'] < $first['tempo_duracao']){
                            $filaAptos[] = array_shift($last);
                        }else{
                            $filaAptos[] = $first;
                            while(!empty($array)){
                                $last[] = array_shift($array);
                            }
                        }
                    }elseif(count($array) == 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $i)->sortBy('tempo_ingresso')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        $array = collect($array);
                        $array = $array->sortBy('tempo_duracao')->toArray();

                        $first = array_shift($array);
                        $tempoFim += $first['tempo_duracao'];

                        if(!empty($array) && !empty($last) && $last[0]['tempo_duracao'] < $first['tempo_duracao']){
                            $filaAptos[] = array_shift($last);
                        }else{
                            $filaAptos[] = $first;
                        }
                    }elseif(empty($array) && $i == $tempoFim){
                        $filaAptos[] = array_shift($last);
                    }
                }
            }

            $tempoFim = 0;

            for ($i=0; $i <= $tempo_total_duracao; $i++) {

                $tempoInicio = $tempoFim;

                $onProcessador = array_shift($filaAptos);
                if(!empty($onProcessador)){
                    for ($j=0; $j < $onProcessador['tempo_duracao']; $j++) { 
                        $tempoFim++;
                    }
                    
                    $diagramaTempoTeste[$i] = [
                        'quantidade_td' => $onProcessador['tempo_duracao'],
                        'tempo_ingresso' => $onProcessador['tempo_ingresso'],
                        'tempo_duracao' => $onProcessador['tempo_duracao'],
                        'tempo_inicio' => $tempoInicio,
                        'tempo_fim' => $tempoFim,
                        'numero_processo' => $onProcessador['numero_processo']
                    ];
                }
            }
        }

        // SRTF
        if($request->tipo_algoritmo == 'SRTF'){
            $filaIngresso = collect($processosBySortAsc);
            $filaAptos = [];
            $clock = 0; // tempo de ingresso dos processos na fila de pronto
            $tempoFim = 0;
            $tempoInicio = 0;
            $onProcessador = [];
            $last = [];
            $first = [];
            for ($i=0; $i <= $tempo_total_duracao; $i++) {

                if($i == $menorTempoIngresso){
                    $array = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortBy('tempo_ingresso')->toArray();
                    if(count($array) > 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortBy('tempo_ingresso')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        $array = collect($array);
                        $array = $array->sortBy('tempo_duracao')->toArray();

                        $filaAptos[] = array_shift($array);
                        $tempoFim += $filaAptos[0]['tempo_duracao'];
                        while(!empty($array)){
                            $last[] = array_shift($array);
                        }
                    }elseif(count($array) == 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortBy('tempo_ingresso')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }
                        $array = collect($array);
                        $array = $array->sortBy('tempo_duracao')->toArray();

                        $filaAptos[] = array_shift($array);
                        $tempoFim += $filaAptos[0]['tempo_duracao'];
                    }
                }else{
                    $array = $filaIngresso->where('tempo_ingresso', $i)->sortBy('tempo_ingresso')->toArray();
                    if(count($array) > 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $i)->sortBy('tempo_ingresso')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        $array = collect($array);
                        $array = $array->sortBy('tempo_duracao')->toArray();

                        $first = array_shift($array);
                        $tempoFim += $first['tempo_duracao'];

                        if(!empty($array) && $last[0]['tempo_duracao'] < $first['tempo_duracao']){
                            $filaAptos[] = array_shift($last);
                        }else{
                            $filaAptos[] = $first;
                            while(!empty($array)){
                                $last[] = array_shift($array);
                            }
                        }
                    }elseif(count($array) == 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $i)->sortBy('tempo_ingresso')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        $array = collect($array);
                        $array = $array->sortBy('tempo_duracao')->toArray();

                        $first = array_shift($array);
                        $tempoFim += $first['tempo_duracao'];

                        if(!empty($array) && !empty($last) && $last[0]['tempo_duracao'] < $first['tempo_duracao']){
                            $filaAptos[] = array_shift($last);
                        }else{
                            $filaAptos[] = $first;
                        }
                    }elseif(empty($array) && $i == $tempoFim){
                        $filaAptos[] = array_shift($last);
                    }
                }
            }

            $tempoInicio = 0;
            $tempoFim = 0;
            $last= [];
            $tempoRestante = 0;
            $count = 0;

            for ($i=0; $i <= $tempo_total_duracao; $i++) {

                # pegar o primeiro processo da fila
                if(empty($onProcessador)){
                    $onProcessador = array_shift($filaAptos);
                }
                # calcula o tempo restante
                if(isset($onProcessador['tempo_restante'])){
                    $tempoRestante = $onProcessador['tempo_restante'] - $count;
                }else{
                    $tempoRestante = $onProcessador['tempo_duracao'] - $count;
                }

                if($tempoRestante == 0 && $i > 0){
                    
                    $diagramaTempoTeste[$i] = [
                        'quantidade_td' => $count,
                        'tempo_ingresso' => $onProcessador['tempo_ingresso'],
                        'tempo_duracao' => $onProcessador['tempo_duracao'],
                        'tempo_inicio' => $tempoInicio,
                        'tempo_fim' => $tempoFim,
                        'numero_processo' => $onProcessador['numero_processo'],
                        'tempo_restante' => $tempoRestante
                    ];
                    $tempoInicio = $tempoFim;
                    $count = 0;
                    if(!empty($last)){
                        $onProcessador = array_shift($last);
                    }else{
                        $onProcessador = array_shift($filaAptos);
                    }
                }

                # validar se o tempo duração do processo i+1 é < que o tempo restante do processo i
                if(array_key_exists(0, $filaAptos) && $filaAptos[0]['tempo_duracao'] < $tempoRestante && $filaAptos[0]['tempo_ingresso'] == $i 
                    || array_key_exists(0, $filaAptos) && $filaAptos[0]['tempo_duracao'] < $tempoRestante && $filaAptos[0]['tempo_ingresso'] < $i){
                    # se for menor pausa o processo
                    $onProcessador['tempo_restante'] = $tempoRestante;
                    $last[] = $onProcessador;
                    # monta o diagrama
                    $diagramaTempoTeste[$i] = [
                        'quantidade_td' => $count,
                        'tempo_ingresso' => $onProcessador['tempo_ingresso'],
                        'tempo_duracao' => $onProcessador['tempo_duracao'],
                        'tempo_inicio' => $tempoInicio,
                        'tempo_fim' => $tempoFim,
                        'numero_processo' => $onProcessador['numero_processo'],
                        'tempo_restante' => $tempoRestante
                    ];
                    #processo i+1 entra no processador
                    $onProcessador = array_shift($filaAptos);
                    # zera o count
                    $count = 0;
                    $tempoInicio = $tempoFim;
                }
                // elseif(!empty($last) && !empty($onProcessador)){
                //     $onProcessador = array_shift($last);
                // }
                $count++;
                $tempoFim++;

                // $tempoInicio = $tempoFim;

                // $onProcessador = array_shift($filaAptos);
                // if(array_key_exists($i, $filaAptos) && $filaAptos[$i+1]['tempo_duracao'] > $onProcessador['tempo_inicio'] - $i){
                //     if(!empty($onProcessador)){
                //         for ($j=0; $j < $onProcessador['tempo_duracao']; $j++) { 
                //             $tempoFim++;
                //         }
                        
                //         $diagramaTempoTeste[$i] = [
                //             'quantidade_td' => $onProcessador['tempo_duracao'],
                //             'tempo_ingresso' => $onProcessador['tempo_ingresso'],
                //             'tempo_duracao' => $onProcessador['tempo_duracao'],
                //             'tempo_inicio' => $tempoInicio,
                //             'tempo_fim' => $tempoFim,
                //             'numero_processo' => $onProcessador['numero_processo']
                //         ];
                //     }
                // }else{
                //     if(!empty($onProcessador)){
                //         for ($j=0; $j < $onProcessador['tempo_duracao']; $j++) { 
                //             $tempoFim++;
                //         }
                        
                //         $diagramaTempoTeste[$i] = [
                //             'quantidade_td' => $onProcessador['tempo_duracao'],
                //             'tempo_ingresso' => $onProcessador['tempo_ingresso'],
                //             'tempo_duracao' => $onProcessador['tempo_duracao'],
                //             'tempo_inicio' => $tempoInicio,
                //             'tempo_fim' => $tempoFim,
                //             'numero_processo' => $onProcessador['numero_processo']
                //         ];
                //     }
                // }
            }

            $arrayDiagrama = $diagramaTempoTeste;
            $diagramaTempoTeste = [];
            foreach ($arrayDiagrama as $key => $item) {
                $diagramaTempoTeste[] = $item;
            }
        }

        // PRIOc
        if($request->tipo_algoritmo == 'PRIOc'){
            $filaIngresso = collect($processosBySortAsc);
            $filaAptos = [];
            $clock = 0; // tempo de ingresso dos processos na fila de pronto
            $tempoFim = 0;
            $tempoInicio = 0;
            $onProcessador = [];
            $last = $filaIngresso->sortByDesc('prioridade_processo')->toArray();
            $first = [];
            for ($i=0; $i <= $tempo_total_duracao; $i++) {

                if($i == $menorTempoIngresso){
                    $array = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortByDesc('prioridade_processo')->toArray();
                    if(count($array) > 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortByDesc('prioridade_processo')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        $array = collect($array);
                        $array = $array->sortByDesc('prioridade_processo')->toArray();

                        foreach ($array as $item) {
                            $filaAptos[] = $item;
                        }

                    }elseif(count($array) == 1){
                        $array = $filaIngresso->sortByDesc('prioridade_processo')->toArray();
                        $keys = $filaIngresso->sortByDesc('prioridade_processo')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        $filaAptos[] = array_shift($array);
                    }
                }else{
                    $array = $filaIngresso->where('tempo_ingresso', $i)->sortByDesc('prioridade_processo')->toArray();
                    if(count($array) > 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $i)->sortByDesc('prioridade_processo')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        $array = collect($array);
                        $array = $array->sortByDesc('prioridade_processo')->toArray();

                        foreach ($array as $item) {
                            $filaAptos[] = $item;
                        }

                    }else{
                        $array = array_shift($last);
                        $filaAptos = collect($filaAptos);
                        if(!empty($array) && $filaAptos->where('tempo_duracao', $array['tempo_duracao'])->where('tempo_ingresso', $array['tempo_ingresso'])->where('prioridade_processo', $array['prioridade_processo'])->count() == 0){
                            $keys = $filaIngresso->where('tempo_ingresso', $array['tempo_ingresso'])->sortByDesc('prioridade_processo')->keys();
                            $cnt = 0;

                            foreach ($keys as $key => $k) {
                                $array['numero_processo'] = $k;
                            }

                            $filaAptos[] = $array;
                        }
                    }
                }
            }

            $tempoFim = 0;
            $filaAptos = $filaAptos->toArray();
            for ($i=0; $i <= $tempo_total_duracao; $i++) {

                $tempoInicio = $tempoFim;

                $onProcessador = array_shift($filaAptos);
                if(!empty($onProcessador)){
                    for ($j=0; $j < $onProcessador['tempo_duracao']; $j++) { 
                        $tempoFim++;
                    }
                    
                    $diagramaTempoTeste[$i] = [
                        'quantidade_td' => $onProcessador['tempo_duracao'],
                        'tempo_ingresso' => $onProcessador['tempo_ingresso'],
                        'tempo_duracao' => $onProcessador['tempo_duracao'],
                        'prioridade_processo' => $onProcessador['prioridade_processo'],
                        'tempo_inicio' => $tempoInicio,
                        'tempo_fim' => $tempoFim,
                        'numero_processo' => $onProcessador['numero_processo']
                    ];
                }
            }
        }
        
        $data['processosBySortAsc'] = $processosBySortAsc;
        $data['diagramaTempo'] = $diagramaTempo;
        $data['diagramaTempoTeste'] = $diagramaTempoTeste;
        $data['tempo_total_duracao'] = $tempo_total_duracao;

        return view('simulador.resultado', $data);
    }
}
