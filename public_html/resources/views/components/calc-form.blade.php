<form wire:submit.prevent="calculate()" method="GET">
    <div class="calc-section__input-caption"><span class="calc-section__calc-step-highlight">Шаг 1.</span> Количество исполнителей, выводимых в день</div>
    @php
    $pix = 12;
    $width = $count_workers/2;
    if($count_workers >= 100) $pix -= intval(($count_workers - 50) / 16); //9 градаций от 3 до 12
    //dump($pix);

    @endphp
    <input wire:change="calculate()" wire:model="count_workers" class="calc-section__input" type="range"
           list="workers" id="count_workers" name="count_workers" min="0" max="200" value="{{@$count_workers}}" autocomplete="off"
    />
    <datalist id="workers" style="width: {{$width}}%; margin-left: 0px;">
        @if(!$count_workers)
            <option value="{{$count_workers}}" label="{{$count_workers}}">
        @else
            <option value="0" label="0">
            <option value="{{$count_workers}}" label="{{$count_workers}}">
        @endif
    </datalist>
    <div class="calc-section__input-caption"><span class="calc-section__calc-step-highlight">Шаг 2.</span> Количество рабочих дней в месяце</div>
    <input wire:change="calculate()" wire:model="count_days" class="calc-section__input" type="range"
           list="days" id="count_days" name="count_days" min="22" max="30" value="{{@$count_days}}"
    />
    <datalist id="days">
        @for($i=22;$i<=30;$i++)
            <option value="{{$i}}" label="{{$i}}" />
        @endfor
    </datalist>

    <div class="calc-section__input-caption"><span class="calc-section__calc-step-highlight">Шаг 3.</span> Время рабочего дня</div>
    <input wire:change="calculate()" wire:model="count_time" class="calc-section__input" type="range" list="times" id="count_time" name="time" min="8" max="12" value="{{@$count_time}}">
    <datalist id="times">
        <option value="8" label="8">
        <option value="9" label="9">
        <option value="10" label="10">
        <option value="11" label="11">
        <option value="12" label="12">
    </datalist>
    <div class="calc-section__result-wrapper">
        <div class="calc-section__result-text">Ваша выручка </div>
        <div class="calc-section__result">{{@$result}} ₽</div>
    </div>
</form>
<style>
    datalist {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        writing-mode: vertical-lr;
        width: 100%;
    }
    /*datalist#workers {
        flex-direction: column;
        justify-content: flex-start;
    }
    datalist#workers option {
        margin-left: 0px;
        width: 50%;
    }
    datalist#workers option:first-child {
        display: block;
        float: left;
        text-align: left;
    }
    datalist#workers option:last-child {
        display: block;
        text-align: right;
        width: calc(100% + {!! $pix !!}px);
    }*/
    option {
        padding: 0px 0px;
        text-align: center;
        transform: rotate(-90deg);
        margin-bottom: 15px;
    }

    input[type="range"] {
        margin: 0;
    }
</style>
