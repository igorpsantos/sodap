@extends('layouts.app', ['class' => 'bg-default'])

@section('content')
    <div class="header bg-gradient-primary py-7 py-lg-8">
        <div class="container">
            <div class="header-body text-center mt-7 mb-7">
                <div class="row justify-content-center">
                    <div class="col-lg-5 col-md-6">
                        <h1 class="text-white">{{ __('Simulador Online de Algoritmos Para Escalonamento de Processos') }}</h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="separator separator-bottom separator-skew zindex-100">
            <svg x="0" y="0" viewBox="0 0 2560 100" preserveAspectRatio="none" version="1.1" xmlns="http://www.w3.org/2000/svg">
                <polygon class="fill-default" points="2560 0 2560 100 0 100"></polygon>
            </svg>
        </div>
    </div>

    <div class="container mt--10 pb-5">
        <p class="text-white text-justify lead">O objetivo geral deste simulador é demonstrar o funcionamento dos algoritmos de escalonamento de processos <strong>First-in, First-out (FIFO)</strong> , <strong>Round Robin (RR)</strong> , <strong>Shortest Job First (SJF)</strong> , <strong>Shortest Job Remaining Time First (SJRT)</strong> e <strong>Escalonamento por prioridades (PRIOc, PRIOp e PRIOd)</strong>, tal que o mesmo seja de fácil acesso tanto por alunos quanto por professores, os dados obtidos com as demonstrações do funcionamento dos algoritmos poderá ser visualizado através de gráficos ilustrativos e interativos.</p>
        <p class="text-white text-left"><strong>Autores:</strong> Igor Pereira e Luis Aurelio.</p>
    </div>
@endsection
