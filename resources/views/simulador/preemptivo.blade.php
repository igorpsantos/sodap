<div class="col-md-4">
    <h4>Tempo médio de vida</h4>
    @php
        ksort($diagramaTempoTeste);

        $processosMedia = [];
        $tempoInicio = [];
        $tempoFim = [];
        $tempoIngresso = [];

        for ($i=0; $i < $numeroProcessos; $i++) { 
            foreach ($diagramaTempoTeste as $key => $item) {
                if($item['numero_processo'] == $i){
                    $tempoInicio[] = $item['tempo_inicio'];
                    $tempoFim[] = $item['tempo_fim'];
                    $tempoIngresso[] = $item['tempo_ingresso'];
                }
            }
            sort($tempoInicio);
            arsort($tempoFim);
            
            $processosMedia[$i] = [
                'tempo_inicio' => array_shift($tempoInicio),
                'tempo_fim' => array_shift($tempoFim),
                'tempo_ingresso' => array_shift($tempoIngresso),
                'tempos_fim' => $tempoFim,
                'tempos_inicio' => $tempoInicio
            ];
            
            $tempoInicio = [];
            $tempoFim = [];
            $tempoIngresso = [];
        }
    @endphp
    @foreach ($processosMedia as $key => $item)
    @php
        $temposFims[] = $item['tempo_fim'] - $item['tempo_ingresso'];
    @endphp
        <p>tv({{$key+1}}) = {{$temposFims[$key]}}</p>
    @endforeach
    @php
        $temposIngressos = [];
        foreach($processosMedia as $key => $item){
            $temposIngressos[] = ((array_sum($item['tempos_inicio']) - array_sum($item['tempos_fim'])) + ($item['tempo_inicio'] - $item['tempo_ingresso']));
        }

        foreach ($temposIngressos as $key => $value) {
            $processosMedia[$key]['tempo_ingresso'] = $value;
        }

        $somaMediaTempoVida = 0;
        $somaMediaTempoIngresso = 0;
        foreach($processosMedia as $key => $item){
            $somaMediaTempoVida += $temposFims[$key];
        }
        foreach($processosMedia as $key => $item){
            $somaMediaTempoIngresso += $item['tempo_ingresso'];
        }
        $resultMediaTempoIngresso = $somaMediaTempoIngresso / $numeroProcessos;
        $resultMediaTempoVida = $somaMediaTempoVida / $numeroProcessos;
    @endphp
    <p>tv = (
    @foreach ($processosMedia as $key => $item)
        @if ($loop->first)
        {{$temposFims[$key]}} +
        @elseif(!$loop->last)
        {{$temposFims[$key]}} +
        @else
        {{$temposFims[$key]}}
        @endif
    @endforeach
        ) / {{$numeroProcessos}} = {{$resultMediaTempoVida}}udt
    </p>
</div>
<div class="col-md-4">
    <h4>Tempo médio de ingresso</h4>
    @foreach ($processosMedia as $key => $item)
        <p>ti({{$key+1}}) = {{$item['tempo_ingresso']}}</p>
    @endforeach
    <p>ti = (
        @foreach ($processosMedia as $key => $item)
            @if ($loop->first)
            {{$item['tempo_ingresso']}} +
            @elseif(!$loop->last)
            {{$item['tempo_ingresso']}} +
            @else
            {{$item['tempo_ingresso']}}
            @endif
        @endforeach
            ) / {{$numeroProcessos}} = {{$resultMediaTempoIngresso}}udt
    </p>
</div>