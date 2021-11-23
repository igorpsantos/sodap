<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use Illuminate\Support\Facades\Validator;

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
            'PRIOc',
            'PRIOp'
        ];
        $tipo_algoritmo = '';

        if($request->has('numero_processos') && $request->numero_processos > 0){
            $numeroProcessos = (int) $request->numero_processos;
        }
        if($request->has('tipo_algoritmo') && in_array($request->tipo_algoritmo, ['FIFO','RR','SJF','SRTF', 'PRIOc', 'PRIOp'])){
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
        $validates = [];
        if($request->has('numeroProcessos')){
            for($i = 0; $i < $request->numeroProcessos; $i++){
                $validates["tempo_ingresso_" . $i] = 'required|numeric|min:0|max:7';
                $validates["tempo_duracao_" . $i] = 'required|numeric|min:0|max:7';
                if(in_array($request->tipo_algoritmo, ['PRIOc', 'PRIOp'])){
                    $validates["prioridade_processo_" . $i] = 'sometimes|required|numeric|min:0|max:15';
                }
            }
            $validates['numeroProcessos'] = 'required|numeric|min:1|max:7';
            $validates['tempo_quantum'] = 'sometimes|required|numeric|min:1|max:5';
            $validates['tipo_algoritmo'] = 'required';
        }

        $validator = Validator::make($request->all(), $validates);

        if ($validator->fails()) {
            session()->flash('error', 'Ocorreu um erro ao gerar a simulação, verifique os campos obrigatórios e tente novamente.');
            return back()->withInput();
        }

        $data["numeroProcessos"] = $request->numeroProcessos;
        $data["tipo_algoritmo"] = $request->tipo_algoritmo;
        if($request->has("tempo_quantum")){
            $data["tempo_quantum"] = $request->tempo_quantum;
        }
        for($i = 0; $i < $data["numeroProcessos"]; $i++){
            $data["tempo_ingresso_" . $i] = $request['tempo_ingresso_' . $i];
            $data["tempo_duracao_" . $i] = $request['tempo_duracao_' . $i];
            if(in_array($request->tipo_algoritmo, ['PRIOc', 'PRIOp'])){
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
        }elseif(in_array($data["tipo_algoritmo"], ['PRIOc', 'PRIOp'])){
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
        $tempo_total_duracao = 0;
        $tempo_total_inicio = 0;
        foreach($data['processos'] as $key => $item){
            $tempo_total_duracao += (int)$item['tempo_duracao'];
            $tempo_total_inicio += (int)$item['tempo_ingresso'];
        }
        $tempo_total_duracao += $tempo_total_inicio;

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

        $processosCor = [
            '#316ad0',
            '#e4e32b',
            '#9650cb',
            '#4bda3d',
            '#e0323c',
            '#FF8C00',
            '#B0E0E6'
        ];

        // fifo
        if($request->tipo_algoritmo == 'FIFO'){
            $filaIngresso = collect($processosBySortAsc);
            $filaAptos = [];
            $clock = 0; // contador do diagrama de tempo
            $tempoFim = 0;
            $tempoInicio = 0;
            $filaProntos = [];
            $first = true;
            $array = [];
            for ($i=0; $i <= $tempo_total_duracao; $i++) {
                // insere os processos na fila de prontos
                if($i == $menorTempoIngresso){
                    $array = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortBy('tempo_ingresso')->toArray();
                    if(count($array) > 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortBy('tempo_ingresso')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        $array = collect($array);
                        $array = $array->sortBy('tempo_ingresso')->toArray();

                        foreach ($array as $item) {
                            $filaProntos[] = $item;
                        }

                    }elseif(count($array) == 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortBy('tempo_ingresso')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        $filaProntos[] = array_shift($array);
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
                        $array = $array->sortBy('tempo_ingresso')->toArray();

                        foreach ($array as $item) {
                            $filaProntos[] = $item;
                        }

                    }elseif(!empty($array)){
                        $keys = $filaIngresso->where('tempo_ingresso', $i)->sortBy('tempo_duracao')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        $filaProntos[] = array_shift($array);
                    }
                }

                // ajusta o tempo inicio de acordo com o menor tempo de ingresso
                if($i == $menorTempoIngresso && $first){
                    $onProcessador = array_shift($filaProntos); // retira o primeiro processo da fila
                    $tempoInicio =  $onProcessador['tempo_ingresso'];
                    $first = false;
                }

                // retira o primeiro processo da fila
                if($i > $menorTempoIngresso && ($clock == 0 || empty($onProcessador))){
                    $onProcessador = array_shift($filaProntos);
                }
    
                // validação para quando o processador fica no tempo ocioso
                if($i > $menorTempoIngresso && isset($onProcessador['tempo_ingresso']) && $tempoFim >= $onProcessador['tempo_ingresso']){
                    $tempoInicio = $tempoFim;
                }elseif($i > $menorTempoIngresso && isset($onProcessador['tempo_ingresso']) && $tempoFim < $onProcessador['tempo_ingresso']){
                    $tempoInicio = $onProcessador['tempo_ingresso'];
                }

                // if($i == 1){
                //     dd($filaProntos, !empty($onProcessador) ? $onProcessador : [], $clock, $tempoInicio, $tempoFim, $diagramaTempoTeste, $first, $tempo_total_duracao);
                // }

                if(!empty($onProcessador)){
                    // processo sai do processador
                    if($clock == $onProcessador['tempo_duracao']){
                        $tempoFim = $tempoInicio + $clock;
                        $diagramaTempoTeste[$i] = [
                            'quantidade_td' => $onProcessador['tempo_duracao'],
                            'tempo_ingresso' => $onProcessador['tempo_ingresso'],
                            'tempo_duracao' => $onProcessador['tempo_duracao'],
                            'tempo_inicio' => $tempoInicio,
                            'tempo_fim' => $tempoFim,
                            'numero_processo' => $onProcessador['numero_processo'],
                            'processo_cor' => $processosCor[$onProcessador['numero_processo']]
                        ];
                        $clock = 0;
                        $onProcessador = [];
                    }
                    $clock++;
                }
            }

            // normalizar as keys do array
            $arrayDiagrama = $diagramaTempoTeste;
            $diagramaTempoTeste = [];
            foreach ($arrayDiagrama as $key => $item) {
                $diagramaTempoTeste[] = $item;
            }

            // ajusta tempo total duração
            $tempo_total_duracao = $tempoFim;
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
            $clock = 0;
            $first = true;
            $filaProntos = [];
            $tempoRestante = 0;
            for ($i=0; $i <= $tempo_total_duracao; $i++) {
                // insere os procesoss na fila de prontos
                if($i == $menorTempoIngresso){
                    $array = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortBy('tempo_ingresso')->toArray();
                    if(count($array) > 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortBy('tempo_ingresso')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        foreach ($array as $item) {
                            $filaProntos[] = $item;
                        }

                    }elseif(count($array) == 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortBy('tempo_ingresso')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        $filaProntos[] = array_shift($array);
                    }
                }else{
                    $array = $filaIngresso->where('tempo_ingresso', $i)->sortBy('tempo_ingresso')->toArray();
                    if(count($array) > 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $i)->sortBy('tempo_ingresso')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        foreach ($array as $item) {
                            $filaProntos[] = $item;
                        }

                    }elseif(!empty($array)){
                        $keys = $filaIngresso->where('tempo_ingresso', $i)->sortBy('tempo_ingresso')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        $filaProntos[] = array_shift($array);
                    }
                }

                // ajusta o tempo inicio de acordo com o menor tempo de ingresso
                if($i == $menorTempoIngresso && $first){
                    $onProcessador = array_shift($filaProntos); // retira o primeiro processo da fila
                    $tempoInicio =  $onProcessador['tempo_ingresso'];
                    $first = false;
                }

                // ajusta o tempo inicio de acordo com o menor tempo de ingresso
                if($i == $menorTempoIngresso){
                    $tempoInicio =  $onProcessador['tempo_ingresso'];
                }elseif($i > $menorTempoIngresso && isset($onProcessador['tempo_ingresso']) && $tempoFim >= $onProcessador['tempo_ingresso']){
                    $tempoInicio = $tempoFim;
                }elseif($i > $menorTempoIngresso && isset($onProcessador['tempo_ingresso']) && $tempoFim < $onProcessador['tempo_ingresso']){
                    $tempoInicio = $onProcessador['tempo_ingresso'];
                }

                if(!empty($onProcessador)){
                    if($onProcessador['tempo_duracao'] >= $data['tempo_quantum'] && $clock == $data['tempo_quantum'] && !isset($onProcessador['tempo_restante'])
                        || $onProcessador['tempo_duracao'] < $data['tempo_quantum'] && $clock == $onProcessador['tempo_duracao'] && !isset($onProcessador['tempo_restante'])
                        || isset($onProcessador['tempo_restante']) && $onProcessador['tempo_restante'] >= $data['tempo_quantum'] && $clock == $data['tempo_quantum']
                        || isset($onProcessador['tempo_restante']) && $onProcessador['tempo_restante'] < $data['tempo_quantum'] && $clock == $onProcessador['tempo_restante']){

                        $tempoFim = $tempoInicio + $clock;
                        if($onProcessador['tempo_duracao'] >= $data['tempo_quantum'] && !isset($onProcessador['tempo_restante'])){
                            $tempoRestante = $onProcessador['tempo_duracao'] - $data['tempo_quantum'];
                        }elseif(isset($onProcessador['tempo_restante']) && $onProcessador['tempo_restante'] >= $data['tempo_quantum']){
                            $tempoRestante = $onProcessador['tempo_restante'] - $data['tempo_quantum'];
                        }else{
                            $tempoRestante = 0;
                        }

                        // cria o diagrama de tempo de acordo com o tempo restante
                        if(isset($onProcessador['tempo_restante']) && $onProcessador['tempo_restante'] > 0){
                            $diagramaTempoTeste[$i] = [
                                'quantidade_td' => $onProcessador['tempo_restante'] >= $data['tempo_quantum'] ? $data['tempo_quantum'] : $onProcessador['tempo_restante'],
                                'tempo_ingresso' => $onProcessador['tempo_ingresso'],
                                'tempo_duracao' => $onProcessador['tempo_duracao'],
                                'tempo_inicio' => $tempoInicio,
                                'tempo_fim' => $tempoFim,
                                'tempo_restante' => $tempoRestante,
                                'numero_processo' => $onProcessador['numero_processo'],
                                'processo_cor' => $processosCor[$onProcessador['numero_processo']]
                            ];
                        }else{
                            $diagramaTempoTeste[$i] = [
                                'quantidade_td' => $onProcessador['tempo_duracao'] >= $data['tempo_quantum'] ? $data['tempo_quantum'] : $onProcessador['tempo_duracao'],
                                'tempo_ingresso' => $onProcessador['tempo_ingresso'],
                                'tempo_duracao' => $onProcessador['tempo_duracao'],
                                'tempo_inicio' => $tempoInicio,
                                'tempo_fim' => $tempoFim,
                                'tempo_restante' => $tempoRestante,
                                'numero_processo' => $onProcessador['numero_processo'],
                                'processo_cor' => $processosCor[$onProcessador['numero_processo']]
                            ];
                        }

                        $clock = 0;
                        $onProcessador['tempo_restante'] = $tempoRestante;
                        if(isset($onProcessador['tempo_restante']) && $onProcessador['tempo_restante'] > 0){
                            $filaProntos[] = $onProcessador;
                        }

                        $onProcessador = array_shift($filaProntos);
                    }
                    $clock++;
                }
            }

            // ajuste no indice da key do diagrama de testes
            $arrayDiagrama = $diagramaTempoTeste;
            $diagramaTempoTeste = [];
            foreach ($arrayDiagrama as $key => $item) {
                $diagramaTempoTeste[] = $item;
            }

            // ajusta tempo total duração
            $tempo_total_duracao = $tempoFim;
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
            $filaProntos = [];
            $first = true;
            for ($i=0; $i <= $tempo_total_duracao; $i++) {

                if($i == $menorTempoIngresso){
                    $array = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortBy('tempo_duracao')->toArray();
                    if(count($array) > 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortBy('tempo_duracao')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        // reeordena a fila de prontos pela prioridade do processo
                        $array = collect($array);
                        $array = $array->sortBy('tempo_duracao')->toArray();

                        foreach ($array as $item) {
                            $filaProntos[] = $item;
                        }

                    }elseif(count($array) == 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortBy('tempo_duracao')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        // reeordena a fila de prontos pela prioridade do processo
                        $array = collect($array);
                        $array = $array->sortBy('tempo_duracao')->toArray();

                        $filaProntos[] = array_shift($array);

                    }
                }else{
                    $array = $filaIngresso->where('tempo_ingresso', $i)->sortBy('tempo_duracao')->toArray();
                    if(count($array) > 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $i)->sortBy('tempo_duracao')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        // reeordena a fila de prontos pela prioridade do processo
                        $array = collect($array);
                        $array = $array->sortBy('tempo_duracao')->toArray();

                        foreach ($array as $item) {
                            $filaProntos[] = $item;
                        }

                    }elseif(!empty($array)){
                        $keys = $filaIngresso->where('tempo_ingresso', $i)->sortBy('tempo_duracao')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        // reeordena a fila de prontos pela prioridade do processo
                        $array = collect($array);
                        $array = $array->sortBy('tempo_duracao')->toArray();

                        $filaProntos[] = array_shift($array);
                    }
                }

                // ajusta o tempo inicio de acordo com o menor tempo de ingresso
                if($i == $menorTempoIngresso && $first){
                    $onProcessador = array_shift($filaProntos); // retira o primeiro processo da fila
                    $tempoInicio =  $onProcessador['tempo_ingresso'];
                    $first = false;
                }

                // retira o primeiro processo da fila
                if($i > $menorTempoIngresso && ($clock == 0 || empty($onProcessador))){
                    $onProcessador = array_shift($filaProntos);
                }

                // ajusta o tempo inicio de acordo com o menor tempo de ingresso
                if($i == $menorTempoIngresso){
                    $tempoInicio =  $onProcessador['tempo_ingresso'];
                }elseif($i > $menorTempoIngresso && isset($onProcessador['tempo_ingresso']) && $tempoFim >= $onProcessador['tempo_ingresso']){
                    $tempoInicio = $tempoFim;
                }elseif($i > $menorTempoIngresso && isset($onProcessador['tempo_ingresso']) && $tempoFim < $onProcessador['tempo_ingresso']){
                    $tempoInicio = $onProcessador['tempo_ingresso'];
                }

                if(!empty($onProcessador)){
                    if($clock == $onProcessador['tempo_duracao']){
                        $tempoFim = $tempoInicio + $clock;
                        $diagramaTempoTeste[$i] = [
                            'quantidade_td' => $onProcessador['tempo_duracao'],
                            'tempo_ingresso' => $onProcessador['tempo_ingresso'],
                            'tempo_duracao' => $onProcessador['tempo_duracao'],
                            'tempo_inicio' => $tempoInicio,
                            'tempo_fim' => $tempoFim,
                            'numero_processo' => $onProcessador['numero_processo'],
                            'processo_cor' => $processosCor[$onProcessador['numero_processo']]
                        ];
                        $clock = 0;
                        $onProcessador = [];

                        // reordena a fila de prontos
                        $filaProntos = collect($filaProntos);
                        $filaProntos = $filaProntos->sortBy('tempo_duracao')->toArray();
                    }
                    if(!empty($onProcessador)){
                        $clock++;
                    }
                }
            }

            // ajuste do indice do diagrama de tempo
            $arrayDiagrama = $diagramaTempoTeste;
            $diagramaTempoTeste = [];
            foreach ($arrayDiagrama as $key => $item) {
                $diagramaTempoTeste[] = $item;
            }
            
            // ajuste do tempo fim com o tempo duração
            $tempo_total_duracao = $tempoFim;
        }

        // SRTF
        if($request->tipo_algoritmo == 'SRTF'){
            $filaIngresso = collect($processosBySortAsc);
            $filaAptos = [];
            $clock = 0; // tempo de ingresso dos processos na fila de pronto
            $tempoFim = 0;
            $tempoInicio = 0;
            $onProcessador = [];
            $first = true;
            $last = [];
            $tempoRestante = 0;
            $count = 0;
            $filaProntos = [];
            for ($i=0; $i <= $tempo_total_duracao; $i++) {

                if($i == $menorTempoIngresso){
                    $array = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortBy('tempo_duracao')->toArray();
                    if(count($array) > 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortBy('tempo_duracao')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        // reeordena a fila de prontos pela prioridade do processo
                        $array = collect($array);
                        $array = $array->sortBy('tempo_duracao')->toArray();

                        foreach ($array as $item) {
                            $filaProntos[] = $item;
                        }

                    }elseif(count($array) == 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortBy('tempo_duracao')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        // reeordena a fila de prontos pela prioridade do processo
                        $array = collect($array);
                        $array = $array->sortBy('tempo_duracao')->toArray();

                        $filaProntos[] = array_shift($array);

                    }
                }else{
                    $array = $filaIngresso->where('tempo_ingresso', $i)->sortBy('tempo_duracao')->toArray();
                    if(count($array) > 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $i)->sortBy('tempo_duracao')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        // reeordena a fila de prontos pela prioridade do processo
                        $array = collect($array);
                        $array = $array->sortBy('tempo_duracao')->toArray();

                        foreach ($array as $item) {
                            $filaProntos[] = $item;
                        }

                    }elseif(!empty($array)){
                        $keys = $filaIngresso->where('tempo_ingresso', $i)->sortBy('tempo_duracao')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        // reeordena a fila de prontos pela prioridade do processo
                        $array = collect($array);
                        $array = $array->sortBy('tempo_duracao')->toArray();

                        $filaProntos[] = array_shift($array);
                    }
                }

                // ajusta o tempo inicio de acordo com o menor tempo de ingresso
                if($i == $menorTempoIngresso && $first){
                    $onProcessador = array_shift($filaProntos); // retira o primeiro processo da fila
                    $tempoInicio =  $onProcessador['tempo_ingresso'];
                    $first = false;
                }

                // retira o primeiro processo da fila
                if($i > $menorTempoIngresso && empty($onProcessador)){
                    $onProcessador = array_shift($filaProntos);
                }

                // ajusta o tempo inicio de acordo com o menor tempo de ingresso
                if($i == $menorTempoIngresso){
                    $tempoInicio =  $onProcessador['tempo_ingresso'];
                }elseif($i > $menorTempoIngresso && isset($onProcessador['tempo_ingresso']) && $tempoFim >= $onProcessador['tempo_ingresso']){
                    $tempoInicio = $tempoFim;
                }elseif($i > $menorTempoIngresso && isset($onProcessador['tempo_ingresso']) && $tempoFim < $onProcessador['tempo_ingresso']){
                    $tempoInicio = $onProcessador['tempo_ingresso'];
                }

                # calcula o tempo restante
                if(isset($onProcessador['tempo_restante'])){
                    $tempoRestante = $onProcessador['tempo_restante'] - $count;
                }elseif(isset($onProcessador['tempo_duracao'])){
                    $tempoRestante = $onProcessador['tempo_duracao'] - $count;
                }

                if(!empty($onProcessador)){
                    if($tempoRestante == 0 && $i > 0){
                        $tempoFim = $tempoInicio + $count;
                        $diagramaTempoTeste[$i] = [
                            'quantidade_td' => $count,
                            'tempo_ingresso' => $onProcessador['tempo_ingresso'],
                            'tempo_duracao' => $onProcessador['tempo_duracao'],
                            'tempo_inicio' => $tempoInicio,
                            'tempo_fim' => $tempoFim,
                            'numero_processo' => $onProcessador['numero_processo'],
                            'tempo_restante' => $tempoRestante,
                            'processo_cor' => $processosCor[$onProcessador['numero_processo']]
                        ];
                        $count = 0;

                        // reordena a fila de prontos
                        $filaProntos = collect($filaProntos);
                        $filaProntos = $filaProntos->sortBy('tempo_duracao')->toArray();

                        // reordena a fila que foi retirado pelo tempo duração
                        $last = collect($last);
                        $last = $last->sortBy('tempo_duracao')->toArray();

                        if(!empty($last)){
                            $onProcessador = array_shift($last);
                        }else{
                            $onProcessador = array_shift($filaProntos);
                        }
                    }

                    // reordena a fila de prontos
                    $filaProntos = collect($filaProntos);
                    $filaProntos = $filaProntos->sortBy('tempo_duracao')->toArray();
    
                    # validar se o tempo duração do processo i+1 é < que o tempo restante do processo i
                    if(array_key_exists(array_key_first($filaProntos), $filaProntos) && isset($filaProntos[array_key_first($filaProntos)]['tempo_duracao']) && $filaProntos[array_key_first($filaProntos)]['tempo_duracao'] < $tempoRestante && $filaProntos[array_key_first($filaProntos)]['tempo_ingresso'] == $i 
                        || array_key_exists(array_key_first($filaProntos), $filaProntos) && isset($filaProntos[array_key_first($filaProntos)]['tempo_duracao']) && $filaProntos[array_key_first($filaProntos)]['tempo_duracao'] < $tempoRestante && $filaProntos[array_key_first($filaProntos)]['tempo_ingresso'] < $i){
                        # se for menor pausa o processo
                        $onProcessador['tempo_restante'] = $tempoRestante;
                        $last[] = $onProcessador;
                        $tempoFim = $tempoInicio + $count;

                        # monta o diagrama
                        $diagramaTempoTeste[$i] = [
                            'quantidade_td' => $count,
                            'tempo_ingresso' => $onProcessador['tempo_ingresso'],
                            'tempo_duracao' => $onProcessador['tempo_duracao'],
                            'tempo_inicio' => $tempoInicio,
                            'tempo_fim' => $tempoFim,
                            'numero_processo' => $onProcessador['numero_processo'],
                            'tempo_restante' => $tempoRestante,
                            'processo_cor' => $processosCor[$onProcessador['numero_processo']]
                        ];

                        // if($i == 3){
                        //     dd($filaAptos, $filaProntos, $onProcessador, $tempoInicio, $tempoFim, $tempoRestante, $diagramaTempoTeste, $last);
                        // }

                        $onProcessador = [];
                        # zera o count
                        $count = 0;
                    }
                    $count++;
                }

            }

            // ajusta os indices do array de diagrama de tempo teste
            $arrayDiagrama = $diagramaTempoTeste;
            $diagramaTempoTeste = [];
            foreach ($arrayDiagrama as $key => $item) {
                $diagramaTempoTeste[] = $item;
            }

            // ajuste do tempo fim com o tempo duração
            $tempo_total_duracao = $tempoFim;
        }

        // PRIOc
        if($request->tipo_algoritmo == 'PRIOc'){
            $filaIngresso = collect($processosBySortAsc);
            $filaAptos = [];
            $clock = 0; // tempo de ingresso dos processos na fila de pronto
            $tempoFim = 0;
            $tempoInicio = 0;
            $onProcessador = [];
            $filaProntos = [];
            $first = true;
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
                            $filaProntos[] = $item;
                        }

                    }elseif(count($array) == 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $i)->sortByDesc('prioridade_processo')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        $filaProntos[] = array_shift($array);

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
                            $filaProntos[] = $item;
                        }

                    }elseif(!empty($array)){
                        $keys = $filaIngresso->where('tempo_ingresso', $i)->sortByDesc('prioridade_processo')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        $filaProntos[] = array_shift($array);
                    }
                }

                // ajusta o tempo inicio de acordo com o menor tempo de ingresso
                if($i == $menorTempoIngresso && $first){
                    $onProcessador = array_shift($filaProntos); // retira o primeiro processo da fila
                    $tempoInicio =  $onProcessador['tempo_ingresso'];
                    $first = false;
                }

                // retira o primeiro processo da fila
                if($i > $menorTempoIngresso && ($clock == 0 || empty($onProcessador))){
                    $onProcessador = array_shift($filaProntos);
                }

                // ajusta o tempo inicio de acordo com o menor tempo de ingresso
                if($i == $menorTempoIngresso){
                    $tempoInicio =  $onProcessador['tempo_ingresso'];
                }elseif($i > $menorTempoIngresso && isset($onProcessador['tempo_ingresso']) && $tempoFim >= $onProcessador['tempo_ingresso']){
                    $tempoInicio = $tempoFim;
                }elseif($i > $menorTempoIngresso && isset($onProcessador['tempo_ingresso']) && $tempoFim < $onProcessador['tempo_ingresso']){
                    $tempoInicio = $onProcessador['tempo_ingresso'];
                }

                if(!empty($onProcessador)){
                    if($clock == $onProcessador['tempo_duracao']){
                        $tempoFim = $tempoInicio + $clock;
                        $diagramaTempoTeste[$i] = [
                            'quantidade_td' => $onProcessador['tempo_duracao'],
                            'tempo_ingresso' => $onProcessador['tempo_ingresso'],
                            'tempo_duracao' => $onProcessador['tempo_duracao'],
                            'tempo_inicio' => $tempoInicio,
                            'tempo_fim' => $tempoFim,
                            'numero_processo' => $onProcessador['numero_processo'],
                            'processo_cor' => $processosCor[$onProcessador['numero_processo']]
                        ];
                        $clock = 0;
                        $onProcessador = [];

                        // reordena a fila de prontos
                        $filaProntos = collect($filaProntos);
                        $filaProntos = $filaProntos->sortByDesc('prioridade_processo')->toArray();
                    }
                    if(!empty($onProcessador)){
                        $clock++;
                    }
                }
            }

            // ajuste do indice do array de diagrama de tempo
            $arrayDiagrama = $diagramaTempoTeste;
            $diagramaTempoTeste = [];
            foreach ($arrayDiagrama as $key => $item) {
                $diagramaTempoTeste[] = $item;
            }

            // ajuste do tempo fim com o tempo duração
            $tempo_total_duracao = $tempoFim;
        }

        // PRIOp
        if($request->tipo_algoritmo == 'PRIOp'){
            $filaIngresso = collect($processosBySortAsc);
            $filaAptos = [];
            $clock = 0; // tempo de ingresso dos processos na fila de pronto
            $tempoFim = 0;
            $tempoInicio = 0;
            $onProcessador = [];
            $first = true;
            $last = [];
            $tempoRestante = 0;
            $count = 0;
            $filaProntos = [];
            for ($i=0; $i <= $tempo_total_duracao; $i++) {
                if($i == $menorTempoIngresso){
                    $array = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortByDesc('prioridade_processo')->toArray();
                    if(count($array) > 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortByDesc('prioridade_processo')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        // reeordena a fila de prontos pela prioridade do processo
                        $array = collect($array);
                        $array = $array->sortByDesc('prioridade_processo')->toArray();

                        foreach ($array as $item) {
                            $filaProntos[] = $item;
                        }

                    }elseif(count($array) == 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $menorTempoIngresso)->sortByDesc('prioridade_processo')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        // reeordena a fila de prontos pela prioridade do processo
                        $array = collect($array);
                        $array = $array->sortByDesc('prioridade_processo')->toArray();

                        $filaProntos[] = array_shift($array);

                    }
                }else{
                    $array = $filaIngresso->where('tempo_ingresso', $i)->sortByDesc('prioridade_processo')->toArray();
                    if(count($array) > 1){
                        $keys = $filaIngresso->where('tempo_ingresso', $i)->sortByDesc('prioridade_processo')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        // reeordena a fila de prontos pela prioridade do processo
                        $array = collect($array);
                        $array = $array->sortByDesc('prioridade_processo')->toArray();

                        foreach ($array as $item) {
                            $filaProntos[] = $item;
                        }

                    }elseif(!empty($array)){
                        $keys = $filaIngresso->where('tempo_ingresso', $i)->sortByDesc('prioridade_processo')->keys();
                        $cnt = 0;
                        foreach ($array as $key => $n) {
                            $array[$key]['numero_processo'] = $keys[$cnt++];
                        }

                        // reeordena a fila de prontos pela prioridade do processo
                        $array = collect($array);
                        $array = $array->sortByDesc('prioridade_processo')->toArray();

                        $filaProntos[] = array_shift($array);
                    }
                }

                // ajusta o tempo inicio de acordo com o menor tempo de ingresso
                if($i == $menorTempoIngresso && $first){
                    $onProcessador = array_shift($filaProntos); // retira o primeiro processo da fila
                    $tempoInicio =  $onProcessador['tempo_ingresso'];
                    $first = false;
                }

                // retira o primeiro processo da fila
                if($i > $menorTempoIngresso && empty($onProcessador)){
                    $onProcessador = array_shift($filaProntos);
                }

                // ajusta o tempo inicio de acordo com o menor tempo de ingresso
                if($i == $menorTempoIngresso){
                    $tempoInicio =  $onProcessador['tempo_ingresso'];
                }elseif($i > $menorTempoIngresso && isset($onProcessador['tempo_ingresso']) && $tempoFim >= $onProcessador['tempo_ingresso']){
                    $tempoInicio = $tempoFim;
                }elseif($i > $menorTempoIngresso && isset($onProcessador['tempo_ingresso']) && $tempoFim < $onProcessador['tempo_ingresso']){
                    $tempoInicio = $onProcessador['tempo_ingresso'];
                }

                # calcula o tempo restante
                if(isset($onProcessador['tempo_restante'])){
                    $tempoRestante = $onProcessador['tempo_restante'] - $count;
                }elseif(isset($onProcessador['tempo_duracao'])){
                    $tempoRestante = $onProcessador['tempo_duracao'] - $count;
                }

                // if($i == 6){
                //     dd($filaProntos, $onProcessador, $tempoInicio, $tempoFim, $tempoRestante, $diagramaTempoTeste, $count, $last);
                // }

                if(!empty($onProcessador)){
                    if($tempoRestante == 0 && $i > 0){
                        $tempoFim = $tempoInicio + $count;
                        $diagramaTempoTeste[$i] = [
                            'quantidade_td' => $count,
                            'tempo_ingresso' => $onProcessador['tempo_ingresso'],
                            'tempo_duracao' => $onProcessador['tempo_duracao'],
                            'tempo_inicio' => $tempoInicio,
                            'tempo_fim' => $tempoFim,
                            'numero_processo' => $onProcessador['numero_processo'],
                            'tempo_restante' => $tempoRestante,
                            'processo_cor' => $processosCor[$onProcessador['numero_processo']]
                        ];
                        $count = 0;

                        // reordena a fila de prontos
                        $filaProntos = collect($filaProntos);
                        $filaProntos = $filaProntos->sortByDesc('prioridade_processo')->toArray();

                        // reordena a fila que foi retirado pelo tempo duração
                        $last = collect($last);
                        $last = $last->sortByDesc('prioridade_processo')->toArray();

                        if(!empty($last)){
                            $onProcessador = array_shift($last);
                        }else{
                            $onProcessador = array_shift($filaProntos);
                        }
                    }

                    // reordena a fila de prontos
                    $filaProntos = collect($filaProntos);
                    $filaProntos = $filaProntos->sortByDesc('prioridade_processo')->toArray();
    
                    # validar se o tempo duração do processo i+1 é < que o tempo restante do processo i
                    if(array_key_exists(array_key_first($filaProntos), $filaProntos) && isset($filaProntos[array_key_first($filaProntos)]['prioridade_processo']) && $filaProntos[array_key_first($filaProntos)]['prioridade_processo'] > $onProcessador['prioridade_processo'] && $filaProntos[array_key_first($filaProntos)]['tempo_ingresso'] == $i 
                        || array_key_exists(array_key_first($filaProntos), $filaProntos) && isset($filaProntos[array_key_first($filaProntos)]['prioridade_processo']) && $filaProntos[array_key_first($filaProntos)]['prioridade_processo'] > $onProcessador['prioridade_processo'] && $filaProntos[array_key_first($filaProntos)]['tempo_ingresso'] < $i){
                        # se for menor pausa o processo
                        $onProcessador['tempo_restante'] = $tempoRestante;
                        $last[] = $onProcessador;
                        $tempoFim = $tempoInicio + $count;

                        # monta o diagrama
                        $diagramaTempoTeste[$i] = [
                            'quantidade_td' => $count,
                            'tempo_ingresso' => $onProcessador['tempo_ingresso'],
                            'tempo_duracao' => $onProcessador['tempo_duracao'],
                            'tempo_inicio' => $tempoInicio,
                            'tempo_fim' => $tempoFim,
                            'numero_processo' => $onProcessador['numero_processo'],
                            'tempo_restante' => $tempoRestante,
                            'processo_cor' => $processosCor[$onProcessador['numero_processo']]
                        ];

                        // if($i == 3){
                        //     dd($filaAptos, $filaProntos, $onProcessador, $tempoInicio, $tempoFim, $tempoRestante, $diagramaTempoTeste, $last);
                        // }

                        $onProcessador = [];
                        # zera o count
                        $count = 0;
                    }
                    $count++;
                }
            }

            // ajusta os indices do array de diagrama de tempo teste
            $arrayDiagrama = $diagramaTempoTeste;
            $diagramaTempoTeste = [];
            foreach ($arrayDiagrama as $key => $item) {
                $diagramaTempoTeste[] = $item;
            }

            // ajuste do tempo fim com o tempo duração
            $tempo_total_duracao = $tempoFim;
        }
        
        $data['processosBySortAsc'] = $processosBySortAsc;
        $data['diagramaTempo'] = $diagramaTempo;
        $data['diagramaTempoTeste'] = $diagramaTempoTeste;
        $data['tempo_total_duracao'] = $tempo_total_duracao;
        $data['screen'] = 1;

        return view('simulador.resultado', $data);
    }

    public function exportPdf(Request $request)
    {
        $data = $request->all();
        $data['screen'] = 0;
        $pdf = PDF::loadView('simulador.pdf',$data)->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);
        return $pdf->stream('resultado.pdf');
    }
}
