@extends('layouts.app', ['title' => __('Exibição dos resultados')])

@section('content')
    @include('layouts.headers.cards')
    <div class="container-fluid mt--7 bg-gradient-default">
        <div class="row justify-content-center mb-5">
            <div class="col-xl-8 order-xl-1">
                <div class="card bg-secondary shadow">
                    <div class="card-header bg-white border-0">
                        <div class="row align-items-center">
                            <h3 class="mb-0">{{ __('Resultado da simulação - Tabela de processos na fila de pronto (' . $tipo_algoritmo . ')') }}</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Processos</th>
                                    <th scope="col">Tempo de ingresso</th>
                                    <th scope="col">Duração de Execução</th>
                                    @if ($tipo_algoritmo == 'PRIOc')
                                    <th scope="col">Prioridade</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($processos as $key => $item)
                                <tr>
                                    <th scope="row">T{{$key + 1}}</th>
                                    <td>{{$item['tempo_ingresso']}}</td>
                                    <td>{{$item['tempo_duracao']}}</td>
                                    @if (isset($item['prioridade_processo']))
                                        <td>{{$item['prioridade_processo']}}</td>
                                    @endif
                                </tr>                                 
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xl-8 order-xl-1 mt-3">
                <div class="card bg-secondary shadow">
                    <div class="card-header bg-white border-0">
                        <div class="row align-items-center">
                            <h3 class="mb-0">{{ __('Resultado da simulação - fila de processos prontos (' . $tipo_algoritmo . ')') }}</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-responsive">
                            <thead>
                                @php
                                    foreach ($diagramaTempoTeste as $key => $item) {
                                        $processosSortByProcessado[] = $item['numero_processo'];
                                    }
                                    krsort($processosSortByProcessado);
                                @endphp
                                <tr>
                                    @foreach ($processosSortByProcessado as $key => $item)
                                        @if (!$loop->last)
                                        <th scope="col">T{{$item+1}}</th>
                                        @endif
                                        @if ($loop->last)
                                        <th scope="col">T{{$item+1}}</th>
                                        <th scope="col"> --------> PROCESSADOR</th>
                                        @endif                               
                                    @endforeach
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center mb-5">
            <div class="col-xl-10 order-xl-1">
                <div class="card bg-secondary shadow">
                    <div class="card-header bg-white border-0">
                        <div class="row align-items-center">
                            <h3 class="mb-0">{{ __('Resultado da simulação - Diagrama de unidade de tempo de processamento (' . $tipo_algoritmo . ')') }}</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-xl table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">T</th>
                                    @for($i=0;$i <= $tempo_total_duracao;$i++)
                                        <th scope="col">{{$i}}</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    // if(isset($tipo_algoritmo) && $tipo_algoritmo == 'FIFO'){
                                    //     ksort($diagramaTempoTeste);
                                    // }
                                    krsort($diagramaTempoTeste);
                                    // dd($diagramaTempoTeste, $tempo_total_duracao);
                                @endphp
                                @if (isset($tipo_algoritmo) && $tipo_algoritmo == 'FIFO')
                                    @foreach ($diagramaTempoTeste as $key => $item)
                                        <tr>
                                            <th scope="row">{{$key+1}}</th>
                                            @if ($item['tempo_ingresso'] == 0 && $item['tempo_inicio'] == 0)
                                                @for ($i = 0; $i < $item['quantidade_td']; $i++)
                                                <td style="background-color: blue"><span style="color: white">T{{$key+1}}</span></td>                                                
                                                @endfor
                                            @else
                                                @for ($i = 0; $i < $item['tempo_fim']; $i++)
                                                    @if ($i == $item['tempo_ingresso'])
                                                        <td style="background-color: red"><span style="color: white">I</span></td>
                                                    @elseif ($i >= $item['tempo_inicio'] && $i < $item['tempo_fim'])
                                                        <td style="background-color: blue"><span style="color: white">T{{$key+1}}</span></td>                                                        
                                                    @else
                                                        <td></td>
                                                    @endif
                                                @endfor
                                            @endif
                                        </tr>
                                    @endforeach
                                @elseif(isset($tipo_algoritmo) && in_array($tipo_algoritmo,['RR','SJF', 'SRTF','PRIOc']))
                                    @for($i = 0; $i < $numeroProcessos; $i++)
                                        @php
                                            $tempoAnterior = 0;
                                        @endphp
                                        <tr>
                                            <th scope="row">T{{$i+1}}</th>
                                            @for ($j = 0; $j < count($diagramaTempoTeste); $j++)
                                                @if ($diagramaTempoTeste[$j]['numero_processo'] == $i)
                                                    @for ($k = $tempoAnterior; $k < $diagramaTempoTeste[$j]['tempo_fim']; $k++)
                                                        @if ($k == $diagramaTempoTeste[$j]['tempo_ingresso'] && $diagramaTempoTeste[$j]['tempo_ingresso'] > 0)
                                                            <td style="background-color: red"><span style="color: white">I</span></td>
                                                        @elseif ($k >= $diagramaTempoTeste[$j]['tempo_inicio'] && $k < $diagramaTempoTeste[$j]['tempo_fim'])
                                                            <td style="background-color: blue"><span style="color: white">T{{$i+1}}</span></td>
                                                        @else
                                                            <td></td>                                                     
                                                        @endif
                                                    @endfor
                                                    @php
                                                        $tempoAnterior = $diagramaTempoTeste[$j]['tempo_fim'];
                                                    @endphp
                                                @endif
                                            @endfor
                                        </tr>
                                    @endfor                                   
                                @endif
                            </tbody>
                        </table>
                        <table class="table table-xl table-bordered table-responsive mt-5">
                            <thead>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="row" style="background-color: blue"></th>
                                    <td >Tarefa em execução</td>
                                </tr>
                                <tr>
                                    <th scope="row" style="background-color: red"><span style="color: white">I</span></th>
                                    <td >Tarefa ingressou</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-xl-10 order-xl-1">
                <div class="card bg-secondary shadow">
                    <div class="card-header bg-white border-0">
                        <div class="row align-items-center">
                            <h3 class="mb-0">{{ __('Resultado da simulação - Tempo médio de vida e tempo médio de ingresso (' . $tipo_algoritmo . ')') }}</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h4>Tempo médio de vida</h4>
                                @php
                                    ksort($diagramaTempoTeste);
                                @endphp
                                @foreach ($diagramaTempoTeste as $key => $item)
                                    <p>tv({{$key}}) = {{$item['tempo_fim']}} - {{$item['tempo_ingresso']}} = {{$item['tempo_fim'] - $item['tempo_ingresso']}}</p>
                                @endforeach
                                @php
                                    // TODO
                                    // efetuar os calculos no controller
                                    $somaMediaTempoVida = 0;
                                    $somaMediaTempoIngresso = 0;
                                    foreach($diagramaTempoTeste as $key => $item){
                                        $somaMediaTempoVida += ($item['tempo_fim'] - $item['tempo_ingresso']);
                                    }
                                    foreach($diagramaTempoTeste as $key => $item){
                                        $somaMediaTempoIngresso += ($item['tempo_inicio'] - $item['tempo_ingresso']);
                                    }
                                    $resultMediaTempoIngresso = $somaMediaTempoIngresso / $numeroProcessos;
                                    $resultMediaTempoVida = $somaMediaTempoVida / $numeroProcessos;
                                @endphp
                                <p>tv = (
                                @foreach ($diagramaTempoTeste as $key => $item)
                                    @if ($loop->first)
                                    {{$item['tempo_fim'] - $item['tempo_ingresso']}} +
                                    @else
                                    {{$item['tempo_fim'] - $item['tempo_ingresso']}} +
                                    @endif
                                @endforeach
                                    ) / {{$numeroProcessos}} = {{$resultMediaTempoVida}}udt
                                </p>
                            </div>
                            <div class="col-md-4">
                                <h4>Tempo médio de ingresso</h4>
                                @foreach ($diagramaTempoTeste as $key => $item)
                                    <p>ti({{$key}}) = {{$item['tempo_inicio']}} - {{$item['tempo_ingresso']}} = {{$item['tempo_inicio'] - $item['tempo_ingresso']}}</p>
                                @endforeach
                                <p>tv = (
                                    @foreach ($diagramaTempoTeste as $key => $item)
                                        @if ($loop->first)
                                        {{$item['tempo_inicio'] - $item['tempo_ingresso']}} +
                                        @else
                                        {{$item['tempo_inicio'] - $item['tempo_ingresso']}} +
                                        @endif
                                    @endforeach
                                        ) / {{$numeroProcessos}} = {{$resultMediaTempoIngresso}}udt
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection