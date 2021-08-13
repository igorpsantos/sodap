@extends('layouts.app')

@section('content')
    @include('layouts.headers.cards')
    <form method="post" action="{{ route('simulador.store') }}" autocomplete="off">
        <div class="container-fluid">
            <div class="row">
                @csrf
                <div class="col-md-8">
                    <h6 class="heading-small text-muted mb-4 mt-4">{{ __('Simulador') }}</h6>
                    <div class="pl-lg-4">
                        <div class="form-group{{ $errors->has('nome') ? ' has-danger' : '' }}">
                            <label class="form-control-label" for="input-name">{{ __('Nome') }}</label>
                            <input type="text" name="nome" id="input-nome" class="form-control form-control-alternative{{ $errors->has('nome') ? ' is-invalid' : '' }}" placeholder="{{ __('nome') }}" value="{{ old('nome', (isset($especialidade->nome) ? $especialidade->nome : '')) }}" required autofocus>
                            @if ($errors->has('nome'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('nome') }}</strong>
                                </span>
                            @endif
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-success mt-4">{{ __('Salvar') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @include('layouts.footers.auth')
@endsection