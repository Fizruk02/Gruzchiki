
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ответственные разнорабочие в г.{{$city}}. Не подводим!</title>
    <base href="/">
    <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon">

    @stack('head')
    <!-- Scripts -->
    @vite(['resources/js/app.js'])
    @livewireStyles
    @laravelViewsStyles

    <link rel="stylesheet" href="css/main.css?v=5">

    <style>
        .rightBlock form {
            height: auto;
        }
        .inputs textarea {
            width: 100%;
        }
    </style>
</head>
<body>
{{ $slot }}
<div class="wrapper">
    <!-- Header begin ********************************************************************************** -->
    <header>
        <div class="content">
            <div class="logo">
                <img src="img/logo2.jpg" alt="logo">
            </div>
            <div class="rightModule">
                <nav>
                    <ul>
                        <li><a href="#mainPart">Главная</a></li>
                        <li><a href="#whyWe">Почему мы?</a></li>
                        <li><a href="#ourServices">Наши услуги</a></li>
                    </ul>
                </nav>
                <button class="collButton" onclick="Livewire.emit('openModal', 'phone-modal', {'cabinet_id': {{intval($cabinet_id->toHtml())}}})">Заказать звонок</button>
                <div class="numberPhone">
                    <a href="tel:+7(902)343-80-40">{{ $phone }}</a>
                    <p>Звоните прямо сейчас!</p>
                </div>
            </div>
        </div>
    </header>
    <!-- Header end ********************************************************************************** -->
    <div class="topBlock" id="mainPart"></div>
    <!-- firstBlock begin ********************************************************************************** -->
    <div class="firstBlock" >
        <div class="firstBlockContent">
            <div class="firstBlockContentWrapper" >
                <div class="leftBlock">
                    @php
                    $land = null;
                    $cab = \App\Models\Cabinet::where('id', $cabinet_id)->first();
                    if ($cab && trim($cab->land_title)) $land = json_decode($cab->land_title);
                    @endphp
                    @if($land)
                        <h1>{{ $land->title }}</h1>
                        <ul>
                        @foreach(explode("\n", $land->block) as $block)
                                <li>{{$block}}</li>
                        @endforeach
                        </ul>
                    @else
                        <h1>Услуги ОТВЕТСТВЕННЫХ разнорабочих г.{{$city}}</h1>
                        <ul>
                            <li>Лучшее соотношение цена-качество в городе!</li>
                            <li>Звоните с 7.00 до 23.00  - ЕЖЕДНЕВНО! </li>
                            <li>Работаем во всех районах!</li>
                            <li>Работаем с юр.лицами по Безналу на особых условиях!</li>
                        </ul>
                    @endif
                    <div class="phoneNumberBlock">
                        <h3>Звоните прямо сейчас!</h3>
                        <a href="tel:{{ $phone_src }}">{{ $phone }}</a>
                    </div>
                    <div class="button" onclick="Livewire.emit('openModal', 'request-modal', {'cabinet_id': {{$cabinet_id}}})">Оставить заявку</div>
                </div>
                <div class="rightBlock">
                    @livewire('request-form', ['cabinet_id' => intval($cabinet_id->toHtml())])
                </div>
            </div>
        </div>
    </div>
    <div id="whyWe" class="anchor"></div>
    <!--firstBlock end ********************************************************************************** -->
    <!-- secondBlock begin********************************************************************************** -->
    <div class="secondBlock" >
        <div class="secondBlockContent">
            <div class="secondBlockContentWrapper">
                @if($land)
                    <h3><strong>{{ $land->title2 }}</strong></h3>
                    <div class="centerElements">
                        @php
                        $i = 0;
                        $img = 0;
                        $imgs = [20, 2, 8, 102, 124];
                        $items = explode("\n", $land->block2);
                        while ($i < count($items)) {
                            echo '<div class="item">
                            <img src="img/'.$imgs[$img].'.png" alt="icon">
                            <div class="itemText" >
                                <h4><strong>'.$items[$i].'</strong></h4>
                                <p>'.$items[$i+1].'</p>
                            </div>
                        </div>';
                            $i+=2;
                            $img++;
                            if ($img > 4) $img = 0;
                        }
                        @endphp
                    </div>
                @else
                    <h3><strong>ПОЧЕМУ МЫ?</strong></h3>
                    <div class="centerElements">
                        <div class="item">
                            <img src="img/20.png" alt="icon">
                            <div class="itemText" >
                                <h4><strong>ОПЫТ</strong></h4>
                                <p>Большой опыт в данной сфере услуг</p>
                            </div>
                        </div>
                        <div class="item">
                            <img src="img/2.png" alt="icon">
                            <div class="itemText" >
                                <h4><strong style="text-transform: uppercase;">Лучшие цены</strong></h4>
                                <p>Мы мониторим цены в городе, и постоянно стараемся соответствовать лучшему соотношению цена-качество</p>
                            </div>
                        </div>
                        <div class="item">
                            <img src="img/8.png" alt="icon">
                            <div class="itemText" >
                                <h4><strong>ОПЕРАТИВНОЕ РАЗМЕЩЕНИЕ ЗАЯВКИ</strong></h4>
                                <p>Наша команда незамедлительно получает задание</p>
                            </div>
                        </div>
                        <div class="item">
                            <img src="img/102.png" alt="icon">
                            <div class="itemText" >
                                <h4><strong>ДОГОВОР</strong></h4>
                                <p>При вашем желании, мы можем работать по договору</p>
                            </div>
                        </div>
                        <div class="item">
                            <img src="img/124.png" alt="icon">
                            <div class="itemText" >
                                <h4><strong style="text-transform: uppercase;">Оплата по безналу</strong></h4>
                                <p>Принимаем оплату как наличным, так и безналичным путем с предоставлением всех документов.</p>
                            </div>
                        </div>
                    </div>
                @endif
                <button onclick="Livewire.emit('openModal', 'request-modal', {'cabinet_id': {{$cabinet_id}}})">Оставить заявку</button>
            </div>
        </div>
    </div>
    <!-- thirdBlock begin********************************************************************************** -->





    <div id="ourServices" class="anchor"></div>
    <div class="thirdBlock">
        <div class="thirdBlockContent">
            <div class="thirdBlockContentWrapper">
                @if($land)
                    <h3><strong style="text-transform: uppercase;">{{ $land->title3 }}</strong></h3>
                    <div class="thirdCenterElements">
                        @php
                            $i = 0;
                            $img = 0;
                            $imgs = [124, 137, 15, 45, 25, 132, 124];
                            $items = explode("\n", $land->block3);
                            while ($i < count($items)) {
                                echo '<div class="item">
                                        <img src="img/'.$imgs[$img].'.png" alt="icon">
                                        <div class="itemText" >
                                            <h4><strong>'.$items[$i].'</strong></h4>
                                        </div>
                                      </div>';
                                $i++;
                                $img++;
                                if ($img > 6) $img = 0;
                            }
                        @endphp
                    </div>
                @else
                    <h3><strong style="text-transform: uppercase;">Услуги разнорабочих от нашей компании</strong></h3>
                    <div class="thirdCenterElements">
                        <div class="item">
                            <img src="img/124.png" alt="icon">
                            <div class="itemText" >
                                <h4><strong>Черновые, подсобные, общестроительные и вспомогательные работы.</strong></h4>
                            </div>
                        </div>
                        <div class="item">
                            <img src="img/137.png" alt="icon">
                            <div class="itemText" >
                                <h4><strong>Промышленный демонтаж, работа с отбойниками.</strong></h4>
                            </div>
                        </div>
                        <div class="item">
                            <img src="img/15.png" alt="icon">
                            <div class="itemText" >
                                <h4><strong>Строительство фундаментов, сборка домов и бань.</strong></h4>
                            </div>
                        </div>
                        <div class="item">
                            <img src="img/45.png" alt="icon">
                            <div class="itemText" >
                                <h4><strong>Бетонные работы, арматура, опалубка.</strong></h4>
                            </div>
                        </div>
                        <div class="item">
                            <img src="img/25.png" alt="icon">
                            <div class="itemText" >
                                <h4><strong>Гипсокартонные работы.</strong></h4>
                            </div>
                        </div>
                        <div class="item">
                            <img src="img/132.png" alt="icon">
                            <div class="itemText" >
                                <h4><strong>Кровельные работы.</strong></h4>
                            </div>
                        </div>
                        <div class="item">
                            <img src="img/124.png" alt="icon">
                            <div class="itemText" >
                                <h4><strong>Штробление, сверление.</strong></h4>
                            </div>
                        </div>
                    </div>
                @endif
                <button onclick="Livewire.emit('openModal', 'request-modal', {'cabinet_id': {{$cabinet_id}}})">Оставить заявку</button>
            </div>
        </div>
    </div>
    <!-- thirdBlock end********************************************************************************** -->
    <!-- footer begin********************************************************************************** -->
    <footer>
        <div class="content">
            <div class="logo">
                <img src="img/logo2.jpg" alt="logo">
            </div>
            <nav>
                <ul>
                    <li><a href="#mainPart">Главная</a></li>
                    <li><a href="#whyWe">Почему мы?</a></li>
                    <li><a href="#ourServices">Наши услуги</a></li>
                </ul>
            </nav>
            <div class="numberPhone">
                <a href="tel:{{ $phone_src }}">{{ $phone }}</a>
                <p>Звоните прямо сейчас!</p>
            </div>
        </div>
    </footer>
    <!-- footer end********************************************************************************** -->
</div>
<script src="js/js.js"></script>
@livewire('livewire-ui-modal')
@laravelViewsScripts
@stack('scripts')
@stack('bottom')
</body>
</html>
