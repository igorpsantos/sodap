@extends('layouts.app', ['title' => __('Manual do Usuário')])

@section('content')
    @include('layouts.headers.cards')
    <div class="container-fluid mt--7 bg-gradient-default">
        <div class="row align-items-center justify-content-center">
            <div class="col-xl-8">
                <div class="card">
                    <h5 class="card-header">Manual do Usuário</h5>
                    <div class="card-body" style="text-align: center;">
                        <p class="card-text">O manual do aplicação, é o arquivo que contêm passo a passo sobre como deve ser utilizado a aplicação.</p>
                        <p class="card-text">Para realizar o download do manual, clique no link abaixo:</p>
                        <p class="card-text"> <a href='https://docs.google.com/uc?export=download&id=1VPlVkN7osGNuOOD2VsOB6wBRPiU5MAur'>Download</a> </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection