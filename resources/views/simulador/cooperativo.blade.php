<div class="col-md-4">
    <h4>Tempo médio de vida</h4>
    @php
        ksort($diagramaTempoTeste);
    @endphp
    @foreach ($diagramaTempoTeste as $key => $item)
        <p>tv({{$key+1}}) = {{$item['tempo_fim']}} - {{$item['tempo_ingresso']}} = {{$item['tempo_fim'] - $item['tempo_ingresso']}}</p>
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
        @elseif(!$loop->last)
        {{$item['tempo_fim'] - $item['tempo_ingresso']}} +
        @else
        {{$item['tempo_fim'] - $item['tempo_ingresso']}}
        @endif
    @endforeach
        ) / {{$numeroProcessos}} = {{$resultMediaTempoVida}}udt
    </p>
</div>
<div class="col-md-4">
    <h4>Tempo médio de ingresso</h4>
    @foreach ($diagramaTempoTeste as $key => $item)
        <p>ti({{$key+1}}) = {{$item['tempo_inicio']}} - {{$item['tempo_ingresso']}} = {{$item['tempo_inicio'] - $item['tempo_ingresso']}}</p>
    @endforeach
    <p>tv = (
        @foreach ($diagramaTempoTeste as $key => $item)
            @if ($loop->first)
            {{$item['tempo_inicio'] - $item['tempo_ingresso']}} +
            @elseif(!$loop->last)
            {{$item['tempo_inicio'] - $item['tempo_ingresso']}} +
            @else
            {{$item['tempo_inicio'] - $item['tempo_ingresso']}}
            @endif
        @endforeach
            ) / {{$numeroProcessos}} = {{$resultMediaTempoIngresso}}udt
    </p>
</div>