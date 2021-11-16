@extends('layouts.app')

@section('content')
    @include('layouts.headers.cards')
    <div class="container-fluid mt--7 bg-gradient-default">
        <div class="row align-items-center justify-content-center">
            <div class="col-xl-8">
                <div class="card">
                    <h5 class="card-header">Simulador - Entrada de dados</h5>
                    <div class="card-body">
                        <h5 class="card-title">Insira os dados para gerar a simulação</h5>
                        {{-- <p class="card-text">With supporting text below as a natural lead-in to additional content.</p> --}}
                        <form method="get" action="{{ route('simulador.create') }}" autocomplete="off">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="heading-small text-muted mb-4 mt-4">{{ __('Nº Processos * ') }} <i class="ni ni-bulb-61" data-toggle="tooltip" data-placement="top" title="Obrigatório | Quantidade de processos que serão gerados pela simulação. Max: 0 a 7"></i></h6>
                                    <div class="pl-lg-4">
                                        <div class="form-group{{ $errors->has('numero_processos') ? ' has-danger' : '' }}">
                                            <label class="form-control-label" for="input-name"></label>
                                            <input type="text" name="numero_processos" id="input-numero_processos" class="form-control form-control-alternative{{ $errors->has('numero_processos') ? ' is-invalid' : '' }}" placeholder="0-7" value="{{old('numero_processos', $numeroProcessos)}}" required autofocus>
                                            @if ($errors->has('numero_processos'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('numero_processos') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="heading-small text-muted mb-4 mt-4">{{ __('Tipo do Algoritmo * ') }} <i class="ni ni-bulb-61" data-toggle="tooltip" data-placement="top" title="Obrigatório | Selecione o algoritmo a ser simulado entre FIFO, RR, SJF, SRTF, PRIOc e PRIOp."></i></h6>
                                    <div class="pl-lg-4">
                                        <div class="form-group{{ $errors->has('tipo_algoritmo') ? ' has-danger' : '' }}">
                                            <label class="form-control-label" for="tipo_algoritmo-name"></label>
                                            <select class="form-control form-control-alternative{{ $errors->has('tipo_algoritmo') ? ' is-invalid' : '' }}" name="tipo_algoritmo" id="tipo-algoritmo">
                                                <option value="">Selecione o Algoritmo</option>
                                                @foreach ($tiposAlgoritmo as $item)
                                                <option value="{{$item}}" name="tipo_algoritmo" @if(!empty($tipo_algoritmo) && $tipo_algoritmo == $item) selected="true" @endif>{{$item}}</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('tipo_algoritmo'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('tipo_algoritmo') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row justify-content-end">
                                <div class="col-auto">
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary mt-4">{{ __('Gerar Processos') }}</button>
                                    </div>
                                </div>
                            </div>                        
                        </form>
                        <form method="post" action="{{ route('simulador.store') }}" autocomplete="off">
                            @csrf
                            @if ($numeroProcessos > 0 && $numeroProcessos <= 7)
                                <input type="hidden" name="numeroProcessos" value={{$numeroProcessos}}>
                                <input type="hidden" name="tipo_algoritmo" value={{$tipo_algoritmo}}>
                                @if (isset($tipo_algoritmo) && $tipo_algoritmo == 'RR')
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="heading-small text-muted mb-4 mt-4">{{ __('Tempo Quantum (Apenas RR) * ') }} <i class="ni ni-bulb-61" data-toggle="tooltip" data-placement="top" title="Obrigatório | Tempo de quantum do processo, é o tempo que cada processo poderá utilizar o processador."></i></h6>
                                        <div class="pl-lg-4">
                                            <div class="form-group{{ $errors->has('tempo_quantum') ? ' has-danger' : '' }}">
                                                <label class="form-control-label" for="tempo_quantum"></label>
                                                <input type="text" name="tempo_quantum" id="tempo_quantum" class="form-control form-control-alternative{{ $errors->has('tempo_quantum') ? ' is-invalid' : '' }}" placeholder="1-5" value="{{old('tempo_quantum')}}" required autofocus>
                                                @if ($errors->has('tempo_quantum'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('tempo_quantum') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>                                    
                                @endif
                                @for ($i = 0; $i < $numeroProcessos; $i++)
                                    <div class="row justify-content-center">
                                        <div class="col-md-4">
                                            <h6 class="heading-small text-muted mb-4 mt-4">{{ __('Tempo ingresso ' . $i + 1) }} * <i class="ni ni-bulb-61" data-toggle="tooltip" data-placement="top" title="Obrigatório | Tempo de ingresso do processo na fila de pronto."></i></h6>
                                            <div class="pl-lg-4">
                                                <div class="form-group{{ $errors->has('tempo_ingresso') ? ' has-danger' : '' }}">
                                                    <label class="form-control-label" for="input-name"></label>
                                                    <input type="text" name="tempo_ingresso_{{$i}}" id="input-tempo_ingresso" class="form-control form-control-alternative{{ $errors->has('tempo_ingresso') ? ' is-invalid' : '' }}" placeholder="{{ __('Tempo de ingresso: 0-7') }}" value="{{old('tempo_ingresso_'.$i)}}" required autofocus>
                                                    @if ($errors->has('tempo_ingresso_'.$i))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('tempo_ingresso_'.$i) }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <h6 class="heading-small text-muted mb-4 mt-4">{{ __('Tempo duração ' . $i + 1) }} * <i class="ni ni-bulb-61" data-toggle="tooltip" data-placement="top" title="Obrigatório | Tempo de duração do processo na fila de pronto."></i></h6>
                                            <div class="pl-lg-4">
                                                <div class="form-group{{ $errors->has('tempo_duracao') ? ' has-danger' : '' }}">
                                                    <label class="form-control-label" for="input-name"></label>
                                                    <input type="text" name="tempo_duracao_{{$i}}" id="input-tempo_duracao" class="form-control form-control-alternative{{ $errors->has('tempo_duracao') ? ' is-invalid' : '' }}" placeholder="{{ __('Tempo duração: 0-7') }}" value="{{old('tempo_duracao_'.$i)}}" required autofocus>
                                                    @if ($errors->has('tempo_duracao_'.$i))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('tempo_duracao_'.$i) }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @if (isset($tipo_algoritmo) && in_array($tipo_algoritmo, ['PRIOc', 'PRIOp']))
                                        <div class="col-md-4">
                                            <h6 class="heading-small text-muted mb-4 mt-4">{{ __('Prioridade ' . $i + 1) }} * <i class="ni ni-bulb-61" data-toggle="tooltip" data-placement="top" title="Obrigatório | Prioridade do processo na fila de prontos."></i></h6>
                                            <div class="pl-lg-4">
                                                <div class="form-group{{ $errors->has('prioridade_processo') ? ' has-danger' : '' }}">
                                                    <label class="form-control-label" for="input-name"></label>
                                                    <input type="text" name="prioridade_processo_{{$i}}" id="input-prioridade_processo" class="form-control form-control-alternative{{ $errors->has('prioridade_processo') ? ' is-invalid' : '' }}" placeholder="{{ __('Prioridade: 0-15') }}" min="0" max="5" value="{{old('prioridade_processo_'.$i)}}" required autofocus>
                                                    @if ($errors->has('prioridade_processo_'.$i))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('prioridade_processo_'.$i) }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                @endfor
                                <div class="row justify-content-end">
                                    <div class="col-auto">
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-primary mt-4">{{ __('Gerar Simulação') }}</button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </form>                        
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection