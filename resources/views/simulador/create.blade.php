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
                                    <h6 class="heading-small text-muted mb-4 mt-4">{{ __('Nº Processos') }}</h6>
                                    <div class="pl-lg-4">
                                        <div class="form-group{{ $errors->has('numero_processos') ? ' has-danger' : '' }}">
                                            <label class="form-control-label" for="input-name"></label>
                                            <input type="text" name="numero_processos" id="input-numero_processos" class="form-control form-control-alternative{{ $errors->has('numero_processos') ? ' is-invalid' : '' }}" placeholder="{{ __('nº Processos') }}" value="{{ old('numero_processos', $numeroProcessos) }}" required autofocus>
                                            @if ($errors->has('numero_processos'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('numero_processos') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="heading-small text-muted mb-4 mt-4">{{ __('Tipo do Algoritmo') }}</h6>
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
                            @if ($numeroProcessos > 0 && $numeroProcessos <= 5)
                                <input type="hidden" name="numeroProcessos" value={{$numeroProcessos}}>
                                <input type="hidden" name="tipo_algoritmo" value={{$tipo_algoritmo}}>
                                @for ($i = 0; $i < $numeroProcessos; $i++)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="heading-small text-muted mb-4 mt-4">{{ __('Tempo ingresso ' . $i + 1) }}</h6>
                                            <div class="pl-lg-4">
                                                <div class="form-group{{ $errors->has('tempo_ingresso') ? ' has-danger' : '' }}">
                                                    <label class="form-control-label" for="input-name"></label>
                                                    <input type="text" name="tempo_ingresso_{{$i}}" id="input-tempo_ingresso" class="form-control form-control-alternative{{ $errors->has('tempo_ingresso') ? ' is-invalid' : '' }}" placeholder="{{ __('Tempo de ingresso') }}" value="{{ old('tempo_ingresso', (isset($especialidade->tempo_ingresso) ? $especialidade->tempo_ingresso : '')) }}" required autofocus>
                                                    @if ($errors->has('tempo_ingresso'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('tempo_ingresso') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="heading-small text-muted mb-4 mt-4">{{ __('Tempo duração ' . $i + 1) }}</h6>
                                            <div class="pl-lg-4">
                                                <div class="form-group{{ $errors->has('tempo_duracao') ? ' has-danger' : '' }}">
                                                    <label class="form-control-label" for="input-name"></label>
                                                    <input type="text" name="tempo_duracao_{{$i}}" id="input-tempo_duracao" class="form-control form-control-alternative{{ $errors->has('tempo_duracao') ? ' is-invalid' : '' }}" placeholder="{{ __('Tempo duração') }}" value="{{ old('tempo_duracao', (isset($especialidade->tempo_duracao) ? $especialidade->tempo_duracao : '')) }}" required autofocus>
                                                    @if ($errors->has('tempo_duracao'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('tempo_duracao') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
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