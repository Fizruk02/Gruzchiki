<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Delo 24</title>

    <!-- Scripts -->
    @vite(['resources/js/app.js'])
    @livewireStyles
    @laravelViewsStyles
    <link rel="stylesheet" href="./style.css">
    <style>
        .alert.alert-danger {
            color:#ff3d00;
        }
        .alert.alert-success {
            font-size: 24px;
            color:#00ff77;
        }
        div.fixed.inset-0.z-10.overflow-y-auto {
            z-index: 100;
        }
    </style>
</head>

<body>
<section class="nav-section">
    <div class="container nav-container">
        <div class="logo"><img src="./assets/logo.svg" alt=""></div>
        <ul class="nav-menu">
            <li><a href="#why-section">Почему мы</a></li>
            <li><a href="#calc-section">Прибыль</a></li>
            <li><a href="#tariffs-section">План</a></li>
            <li><a href="#tariffs-section">Решение</a></li>
            <li><a href="#compare-section">Сравнение</a></li>
            <li><a href="#review-section">Отзывы</a></li>
        </ul>
        <div class="nav-callback">
            <div class="nav-callback__phone-number-wrapper">
                <div class="nav-callback__phone-number"><a href="tel:+79030244523">+7 (903) 024-45-23</a></div>
                <div class="nav-callback__phone-number-caption">Позвони сейчас!</div>
            </div>
            <button class="nav-callback__button button" onclick="Livewire.emit('openModal', 'phone-main-modal')">Заказать звонок</button>
        </div>
    </div>
</section>

<section class="main-section">
    <div class="container main-container">
        <div class="main-section__text-wrapper">
            <div class="main-section__header-wrapper">
                <div class="main-section__header">Откройте антикризисный бизнес и зарабатывайте</div>
            </div>
            <div class="main-section__price-from-wrapper ">
                <div class="main-section__price-from-text">от 150 000 рублей в месяц</div>
                <div class="main-section__price-from-caption-wrapper arrow-caption">
                    <div class="main-section__price-from-caption-arrow"><img
                            src="./assets/main-section-caption-arrow.svg" alt=""></div>
                    <div class="main-section__price-from-caption-text">
                        <div class="main-section__price-from-caption-top">Опыт не обязателен!</div>
                        <div class="main-section__price-from-caption-bottom">Обучаем с нуля</div>
                    </div>
                </div>
            </div>
            <div class="main-section__text">Скачайте презентацию с подробным разбором прибыльного бизнеса в вашем
                городе</div>
            <div class="main-section__buttons-wrapper">
                <button class="main-section__presentation-button" onclick="Livewire.emit('openModal', 'phone-main-modal')">Получить презентацию</button>
                <button class="main-section__callback-button button" onclick="Livewire.emit('openModal', 'phone-main-modal')">Заказать звонок</button>
            </div>
            <div class="main-section__mobile-image"><img src="./assets/main-section-image.png" alt=""></div>
        </div>
    </div>
</section>

<section class="why-section" id="why-section">
    <div class="container">
        <div class="why-section__heading-wrapper">
            <div class="why-section__heading-top-caption">Почему мы ?</div>
            <div class="why-section__heading-header header">Управляйте своими финансами</div>
            <div class="why-section__heading-text">Мы предлагаем лучший учет и отслеживание расходов для амбициозных предприятий.</div>
        </div>
        <div class="why-section__cards-wrapper">
            <div class="why-section__card">
                <div class="why-section__card-icon"><img src="./assets/why-section-icon-1.svg" alt=""></div>
                <div class="why-section__card-text">Управляйте бизнесом удалённо в любой точке мира</div>
            </div>
            <div class="why-section__card">
                <div class="why-section__card-icon"><img src="./assets/why-section-icon-2.svg" alt=""></div>
                <div class="why-section__card-text">Возможен перезапуск бизнеса в другую СНГ страну</div>
            </div>
            <div class="why-section__card">
                <div class="why-section__card-icon"><img src="./assets/why-section-icon-3.svg" alt=""></div>
                <div class="why-section__card-text">Быстрый запуск первого бизнеса где угодно </div>
            </div>
        </div>
    </div>
</section>

<section class="calc-section" id="calc-section">
    <div class="container">
        <div class="calc-section__heading-wrapper">
            <div class="calc-section__heading-top-caption">Прибыль</div>
            <div class="calc-section__heading-header header">Калькулятор прибыли</div>
            <div class="calc-section__header-caption-wrapper arrow-caption">
                <div class="calc-section__header-caption-arrow"><img
                        src="./assets/calc-section-caption-arrow.svg" alt=""></div>
                <div class="calc-section__header-caption-text">
                    <div class="calc-section_header-caption">С каждого часа исполнителя партнёр сети зарабатывает около 50₽ чистой прибыли.</div>
                </div>
            </div>
            <div class="calc-section__heading-text">Посчитайте, сколько будете зарабатывать</div>
        </div>
        <div class="calc-section__wrapper">
            <div class="calc-section__calc-wrapper">
                @livewire('calc-form')
            </div>
            <div class="calc-section__form-wrapper">
                <div class="calc-section__form-caption"><span class="calc-section__form-step-highlight">Шаг 4.</span> Оставьте свои контакты и получите финансовый план для вашего города</div>
                @livewire('request-main-form', ['is_section' => true])
            </div>
        </div>
    </div>
</section>

<section class="why2-section">
    <div class="container why2-container">
        <div class="why2-section__heading-wrapper">
            <div class="why2-section__heading-icon"><img src="./assets/why2-section-icon.svg" alt=""></div>
            <div class="why2-section__heading-header header">Почему более 200 партнёров присоединились к нам?</div>
            <div class="why2-section__heading-caption">Самое важное о франшизе</div>
        </div>
        <div class="why2-section__items-wrapper">
            <div class="why2-section__items-column">
                <div class="why2-section__item">
                    <div class="why2-section__item-header">Антикризисный бизнес</div>
                    <div class="why2-section__item-text">Работаем на внутреннем рынке, не привязаны к курсу валют и ценам на нефть.
                        Не нужен офис и закупка дорогостоящего оборудования. Спрос на услуги бизнеса не падает, а возрастает в
                        период кризиса.</div>
                </div>
                <div class="why2-section__item">
                    <div class="why2-section__item-header">Передаём клиентов</div>
                    <div class="why2-section__item-text">Мы даём партнёрам доступ к бирже федеральных и региональных клиентов</div>
                </div>
            </div>
            <div class="why2-section__items-column">
                <div class="why2-section__item">
                    <div class="why2-section__item-header">Небольшие инвестиции в бизнес</div>
                    <div class="why2-section__item-text">От 179 тысяч рублей. Банки-партнёры подготовили для нас лучшие условия по
                        рассрочкам</div>
                </div>
                <div class="why2-section__item">
                    <div class="why2-section__item-header">Бизнес функционирует круглый год</div>
                    <div class="why2-section__item-text">Вы получаете прибыль, когда другие простаивают</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="tariffs-section" id="tariffs-section">
    <div class="container">
        <div class="tariffs-section__heading-wrapper">
            <div class="tariffs-section__heading-top-caption">План</div>
            <div class="tariffs-section__heading-header header">Что входит в продвинутый тариф</div>
            <div class="tariffs-section__heading-text">Что входит в продвинутый тариф</div>
        </div>
        <div class="tariffs-section__tariffs-wrapper">

            <div class="tariffs-section__tariff">
                <div class="tariffs-section__tariff-heading">
                    <div class="tariffs-section__tariff-name">Начинающий</div>
                    <div class="tariffs-section__tariff-price">179 990 ₽</div>
                    <div class="tariffs-section__tariff-start-button button" onclick="Livewire.emit('openModal', 'phone-main-modal', {tariff: 'Начинающий'})">Начать</div>
                </div>
                <div class="tariffs-section__tariff-features">
                    <div class="tariffs-section__tariff-features-header">ОСОБЕННОСТИ</div>
                    <div class="tariffs-section__tariff-features-caption">Что входит в начинающий тариф</div>
                    <div class="tariffs-section__tariff-features-list">
                        <div class="tariffs-section__tariff-feature">
                            <div class="tariffs-section__tariff-feature-icon"><img src="./assets/tariff-section-enabled-icon.svg" alt=""></div>
                            <div class="tariffs-section__tariff-feature-text">Кэшбэк 30 000 на рекламу</div>
                        </div>
                        <div class="tariffs-section__tariff-feature">
                            <div class="tariffs-section__tariff-feature-icon"><img src="./assets/tariff-section-disabled-icon.svg" alt=""></div>
                            <div class="tariffs-section__tariff-feature-text__disabled">Холодный обзвон организаций и от 30 потенциальных клиентов-компаний</div>
                        </div>
                        <div class="tariffs-section__tariff-feature">
                            <div class="tariffs-section__tariff-feature-icon"><img src="./assets/tariff-section-disabled-icon.svg" alt=""></div>
                            <div class="tariffs-section__tariff-feature-text__disabled">Работа под брендом Дело 24</div>
                        </div>
                        <div class="tariffs-section__tariff-feature">
                            <div class="tariffs-section__tariff-feature-icon"><img src="./assets/tariff-section-disabled-icon.svg" alt=""></div>
                            <div class="tariffs-section__tariff-feature-text__disabled">Возможность смены города работы, если пошло что-то не так</div>
                        </div>
                        <div class="tariffs-section__tariff-feature">
                            <div class="tariffs-section__tariff-feature-icon"><img src="./assets/tariff-section-disabled-icon.svg" alt=""></div>
                            <div class="tariffs-section__tariff-feature-text__disabled">СРМ-система и IP-телефония для отслеживания рекламных каналов и ведения базы клиентов. Исправление ошибок в работе</div>
                        </div>
                        <div class="tariffs-section__tariff-feature">
                            <div class="tariffs-section__tariff-feature-icon"><img src="./assets/tariff-section-disabled-icon.svg" alt=""></div>
                            <div class="tariffs-section__tariff-feature-text__disabled">Работа нашего менеджера по закрытию лидов в сделки в течение 2 месяцев</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tariffs-section__tariff">
                <div class="tariffs-section__tariff-heading">
                    <div class="tariffs-section__popular-tariff-name-wrapper">
                        <div class="tariffs-section__tariff-name">Продвинутый</div>
                        <div class="tariffs-section__popular-tariff-tag">Популярный</div>
                    </div>
                    <div class="tariffs-section__tariff-price">299 990 ₽</div>
                    <div class="tariffs-section__tariff-start-button button" onclick="Livewire.emit('openModal', 'phone-main-modal', {tariff: 'Продвинутый'})">Начать</div>
                </div>
                <div class="tariffs-section__tariff-features">
                    <div class="tariffs-section__tariff-features-header">ОСОБЕННОСТИ</div>
                    <div class="tariffs-section__tariff-features-caption">Что входит в начинающий тариф</div>
                    <div class="tariffs-section__tariff-features-list">
                        <div class="tariffs-section__tariff-feature">
                            <div class="tariffs-section__tariff-feature-icon"><img src="./assets/tariff-section-enabled-icon.svg" alt=""></div>
                            <div class="tariffs-section__tariff-feature-text">Кэшбэк 30 000 на рекламу</div>
                        </div>
                        <div class="tariffs-section__tariff-feature">
                            <div class="tariffs-section__tariff-feature-icon"><img src="./assets/tariff-section-enabled-icon.svg" alt=""></div>
                            <div class="tariffs-section__tariff-feature-text">Холодный обзвон организаций и от 30 потенциальных клиентов-компаний</div>
                        </div>
                        <div class="tariffs-section__tariff-feature">
                            <div class="tariffs-section__tariff-feature-icon"><img src="./assets/tariff-section-enabled-icon.svg" alt=""></div>
                            <div class="tariffs-section__tariff-feature-text">Работа под брендом Дело 24</div>
                        </div>
                        <div class="tariffs-section__tariff-feature">
                            <div class="tariffs-section__tariff-feature-icon"><img src="./assets/tariff-section-enabled-icon.svg" alt=""></div>
                            <div class="tariffs-section__tariff-feature-text">Возможность смены города работы, если пошло что-то не так</div>
                        </div>
                        <div class="tariffs-section__tariff-feature">
                            <div class="tariffs-section__tariff-feature-icon"><img src="./assets/tariff-section-enabled-icon.svg" alt=""></div>
                            <div class="tariffs-section__tariff-feature-text">СРМ-система и IP-телефония для отслеживания рекламных каналов и ведения базы клиентов. Исправление ошибок в работе</div>
                        </div>
                        <div class="tariffs-section__tariff-feature">
                            <div class="tariffs-section__tariff-feature-icon"><img src="./assets/tariff-section-disabled-icon.svg" alt=""></div>
                            <div class="tariffs-section__tariff-feature-text__disabled">Работа нашего менеджера по закрытию лидов в сделки в течение 2 месяцев</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tariffs-section__tariff">
                <div class="tariffs-section__tariff-heading">
                    <div class="tariffs-section__tariff-name">Улучшенный</div>
                    <div class="tariffs-section__tariff-price">399 990 ₽</div>
                    <div class="tariffs-section__tariff-start-button button" onclick="Livewire.emit('openModal', 'phone-main-modal', {tariff: 'Улучшенный'})">Начать</div>
                </div>
                <div class="tariffs-section__tariff-features">
                    <div class="tariffs-section__tariff-features-header">ОСОБЕННОСТИ</div>
                    <div class="tariffs-section__tariff-features-caption">Что входит в начинающий тариф</div>
                    <div class="tariffs-section__tariff-features-list">
                        <div class="tariffs-section__tariff-feature">
                            <div class="tariffs-section__tariff-feature-icon"><img src="./assets/tariff-section-enabled-icon.svg" alt=""></div>
                            <div class="tariffs-section__tariff-feature-text">Кэшбэк 30 000 на рекламу</div>
                        </div>
                        <div class="tariffs-section__tariff-feature">
                            <div class="tariffs-section__tariff-feature-icon"><img src="./assets/tariff-section-enabled-icon.svg" alt=""></div>
                            <div class="tariffs-section__tariff-feature-text">Холодный обзвон организаций и от 30 потенциальных клиентов-компаний</div>
                        </div>
                        <div class="tariffs-section__tariff-feature">
                            <div class="tariffs-section__tariff-feature-icon"><img src="./assets/tariff-section-enabled-icon.svg" alt=""></div>
                            <div class="tariffs-section__tariff-feature-text">Работа под брендом Дело 24</div>
                        </div>
                        <div class="tariffs-section__tariff-feature">
                            <div class="tariffs-section__tariff-feature-icon"><img src="./assets/tariff-section-enabled-icon.svg" alt=""></div>
                            <div class="tariffs-section__tariff-feature-text">Возможность смены города работы, если пошло что-то не так</div>
                        </div>
                        <div class="tariffs-section__tariff-feature">
                            <div class="tariffs-section__tariff-feature-icon"><img src="./assets/tariff-section-enabled-icon.svg" alt=""></div>
                            <div class="tariffs-section__tariff-feature-text">СРМ-система и IP-телефония для отслеживания рекламных каналов и ведения базы клиентов. Исправление ошибок в работе</div>
                        </div>
                        <div class="tariffs-section__tariff-feature">
                            <div class="tariffs-section__tariff-feature-icon"><img src="./assets/tariff-section-enabled-icon.svg" alt=""></div>
                            <div class="tariffs-section__tariff-feature-text">Работа нашего менеджера по закрытию лидов в сделки в течение 2 месяцев</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<section class="form-section">
    <div class="container">

        <div class="form-section__form-wrapper">
            <div class="form-section__form-header header">Время — деньги</div>
            <div class="form-section__form-caption">Но если вы готовы изучить финансовый план и действовать, то оставьте свои
                контакты прямо сейчас.</div>
            @livewire('request-main-form')
        </div>

    </div>
</section>

<section class="compare-section" id="compare-section">
    <div class="container">
        <div class="compare-section__heading-wrapper">
            <div class="compare-section__heading-top-caption header-caption">Сравнение</div>
            <div class="compare-section__heading-header header">Другие франшизы VS «Дело 24»</div>
        </div>
        <div class="compare-section__rows-wrapper">
            <div class="compare-section__row">
                <div class="compare-section__card">
                    <div class="compare-section__card-icon"><img src="./assets/comapre-section-no-icon.svg" alt=""></div>
                    <div class="compare-section__card-header">Зависят от состояния экономики</div>
                    <div class="compare-section__card-caption">Замедляются в развитии в период кризиса</div>
                    <div class="compare-section__card-examples">
                        <div class="compare-section__card-examples-column">
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">спортивные клубы</div>
                            </div>
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">одежда, украшения</div>
                            </div>
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">цветы, подарки</div>
                            </div>
                        </div>
                        <div class="compare-section__card-examples-column">
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">кофейни</div>
                            </div>
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">турагентства</div>
                            </div>
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">вендинг, аппараты</div>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="compare-section__card">
                    <div class="compare-section__card-icon"><img src="./assets/comapre-section-yes-icon.svg" alt=""></div>
                    <div class="compare-section__card-header">Не зависит от курса валют и состояния экономики</div>
                    <div class="compare-section__card-caption">Бизнес развивается и приносит прибыль, даже если остальные направления заморожены</div>
                </div>
            </div>
            <div class="compare-section__row">
                <div class="compare-section__card">
                    <div class="compare-section__card-icon"><img src="./assets/comapre-section-no-icon.svg" alt=""></div>
                    <div class="compare-section__card-header">Привязаны к локации</div>
                    <div class="compare-section__card-caption">Ограничены потенциалом конкретной местности</div>
                    <div class="compare-section__card-examples">
                        <div class="compare-section__card-examples-column">
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">общепит</div>
                            </div>
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">салоны красоты</div>
                            </div>
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">образование</div>
                            </div>
                        </div>

                        <div class="compare-section__card-examples-column">
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">детские франшизы</div>
                            </div>
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">лаборатории</div>
                            </div>
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">ремонтные мастерские</div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="compare-section__card">
                    <div class="compare-section__card-icon"><img src="./assets/comapre-section-yes-icon.svg" alt=""></div>
                    <div class="compare-section__card-header">Не привязано к локации</div>
                    <div class="compare-section__card-caption">Можно работать не только в своём регионе, но и отправлять рабочих на вахту</div>
                </div>
            </div>
            <div class="compare-section__row">
                <div class="compare-section__card">
                    <div class="compare-section__card-icon"><img src="./assets/comapre-section-no-icon.svg" alt=""></div>
                    <div class="compare-section__card-header">Требуют огромных стартовых вложений</div>
                    <div class="compare-section__card-caption">Чтобы стартовать, вам нужно рискнуть десятками миллионов</div>
                    <div class="compare-section__card-examples">
                        <div class="compare-section__card-examples-column">
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">общепит</div>
                            </div>
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">товары для дома</div>
                            </div>
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">школа или детский сад</div>
                            </div>
                        </div>

                        <div class="compare-section__card-examples-column">
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">АЗС или СТО</div>
                            </div>
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">услуги для бизнеса</div>
                            </div>
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">спортивные клубы</div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="compare-section__card">
                    <div class="compare-section__card-icon"><img src="./assets/comapre-section-yes-icon.svg" alt=""></div>
                    <div class="compare-section__card-header">Для старта вам потребуется только ноутбук и телефон</div>
                    <div class="compare-section__card-caption">Не надо покупать оборудование, товар, снимать склад, офис, внедрять сложное ПО и т. п.</div>
                </div>
            </div>
            <div class="compare-section__row">
                <div class="compare-section__card">
                    <div class="compare-section__card-icon"><img src="./assets/comapre-section-no-icon.svg" alt=""></div>
                    <div class="compare-section__card-header">Окупаются годами</div>
                    <div class="compare-section__card-caption">Сможете вернуть свои инвестиции не раньше, чем через 3 года</div>
                    <div class="compare-section__card-examples">
                        <div class="compare-section__card-examples-column">
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">спортивные клубы</div>
                            </div>
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">одежда, украшения</div>
                            </div>
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">цветы, подарки</div>
                            </div>
                        </div>

                        <div class="compare-section__card-examples-column">
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">кофейни</div>
                            </div>
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">турагентства</div>
                            </div>
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">вендинг, аппараты</div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="compare-section__card">
                    <div class="compare-section__card-icon"><img src="./assets/comapre-section-yes-icon.svg" alt=""></div>
                    <div class="compare-section__card-header">Окупаемость — 4 месяца, первая прибыль уже через 3 недели</div>
                    <div class="compare-section__card-caption">Мы доводим вас за руку до полной окупаемости вашего бизнеса с помощью отработанной схемы</div>
                </div>
            </div>
            <div class="compare-section__row">
                <div class="compare-section__card">
                    <div class="compare-section__card-icon"><img src="./assets/comapre-section-no-icon.svg" alt=""></div>
                    <div class="compare-section__card-header">Требуют вашего постоянного присутствия</div>
                    <div class="compare-section__card-caption">Нельзя будет отлучаться: без вас качество сервиса будет падать</div>
                    <div class="compare-section__card-examples">
                        <div class="compare-section__card-examples-column">
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">салон красоты</div>
                            </div>
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">барбершоп</div>
                            </div>
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">услуги для бизнеса</div>
                            </div>
                        </div>

                        <div class="compare-section__card-examples-column">
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">пекарни и фастфуд</div>
                            </div>
                            <div class="compare-section__card-example-wrapper">
                                <div class="compare-section__card-example-icon"><img src="./assets/comapre-section-red-dot.svg" alt="">
                                </div>
                                <div class="compare-section__card-example-text">образование</div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="compare-section__card">
                    <div class="compare-section__card-icon"><img src="./assets/comapre-section-yes-icon.svg" alt=""></div>
                    <div class="compare-section__card-header">Всю работу можно организовать удалённо</div>
                    <div class="compare-section__card-caption">Нет необходимости находиться на объектах, чтобы всё работало</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="form2-section">
    <div class="container">
        <div class="form-section__form-wrapper">
            <div class="form-section__form-header header">Получите бизнес-план</div>
            <div class="form-section__form-caption">Скачайте презентацию с подробным разбором прибыльного бизнеса в вашем городе</div>
            @livewire('request-main-form')
        </div>
    </div>
</section>

<section class="who-section">
    <div class="container">

        <div class="who-section__heading-wrapper">
            <div class="who-section__heading-top-caption header-caption">Кому подходит</div>
            <div class="who-section__heading-header header">Франшиза подходит всем, кто хочет присоединиться к быстрорастущему бизнесу</div>
        </div>
        <div class="who-section__cards-wrapper">
            <div class="who-section__card">
                <div class="who-section__card-icon"><img src="./assets/who-section-icon-1.svg" alt=""></div>
                <div class="who-section__card-header">Новичкам</div>
                <div class="who-section__card-text">Тем, кому надоела рутинная работа в офисе, кому не пробить «потолок» в доходе, кто мечтает о собственном стабильном бизнесе</div>
            </div>
            <div class="who-section__card">
                <div class="who-section__card-icon"><img src="./assets/who-section-icon-2.svg" alt=""></div>
                <div class="who-section__card-header">Предпринимателям</div>
                <div class="who-section__card-text">Тем, у кого есть свой бизнес, но отрасль прибило кризисом; тем, кто ищет ещё один источник дохода</div>
            </div>
            <div class="who-section__card">
                <div class="who-section__card-icon"><img src="./assets/who-section-icon-3.svg" alt=""></div>
                <div class="who-section__card-header">Инвесторам</div>
                <div class="who-section__card-text">Тем, кто ищет перспективные направления с высокой рентабельностью и быстрой окупаемостью инвестиций</div>
            </div>
        </div>
    </div>
</section>

<section class="review-section" id="review-section">
    <div class="container">
        <div class="review-section__heading-wrapper">
            <div class="review-section__heading-top-caption header-caption">Отзывы</div>
            <div class="review-section__heading-header header">«Дело 24» — бизнес без ограничений и потолка в развитии доходов</div>
            <div class="review-section__header-caption-wrapper arrow-caption">
                <div class="review-section__header-caption-arrow"><img
                        src="./assets/review-section-caption-arrow.svg" alt=""></div>
                <div class="review-section__header-caption-text">
                    <div class="review-section_header-caption">так говорят наши франчайзи</div>
                </div>
            </div>
        </div>
        <div class="review-section__reviews-wrapper">
            <div class="review-section__review-card">
                <div class="review-section__person-wrapper">
                    <div class="review-section__person-photo"><img src="./assets/person-1.png" alt=""></div>
                    <div class="review-section__person-info">
                        <div class="review-section__person-name">Софронов Ярослав</div>
                        <div class="review-section__person-city">Казань</div>
                    </div>
                </div>
                <div class="review-section__review-text">Франшиза отличный способ начать свой бизнес, особенно для тех, кто не имеет много опыта. При покупке франшизы вы получаете доступ к бренду, который уже имеет успешную репутацию.</div>
            </div>
            <div class="review-section__review-card">
                <div class="review-section__person-wrapper">
                    <div class="review-section__person-photo"><img src="./assets/person-2.png" alt=""></div>
                    <div class="review-section__person-info">
                        <div class="review-section__person-name">Смирнова Алиса</div>
                        <div class="review-section__person-city">Новосибирск</div>
                    </div>
                </div>
                <div class="review-section__review-text">Считаю, что женщине в аутсорсинге должно быть достаточно комфортно, потому что сфера аутсорсинга очень близка к сфере HR. Всем известно, что абсолютное большинство HR-ов —женщины. В бизнесе меня мотивирует возможность зарабатывать столько, сколько мне необходимо. Развивать своё детище и видеть значимость своёго бизнеса, как для людей, ищущих работу, так и для своих клиентов. Моя цель: к концу этого года выводить ежедневно на объекты 100 человек, формировать и развивать команду. Цель — 5 млн прибыли за год.</div>
            </div>
            <div class="review-section__review-card">
                <div class="review-section__person-wrapper">
                    <div class="review-section__person-photo"><img src="./assets/person-3.png" alt=""></div>
                    <div class="review-section__person-info">
                        <div class="review-section__person-name">Власов Дмитрий</div>
                        <div class="review-section__person-city">Ростов-на-Дону</div>
                    </div>
                </div>
                <div class="review-section__review-text">Всего за три месяца я окупил франшизу, несмотря на то, что приобрёл её в самый разгар пандемии, а до этого всю жизнь работал в найме.</div>
            </div>
            <div class="review-section__review-card">
                <div class="review-section__person-wrapper">
                    <div class="review-section__person-photo"><img src="./assets/person-4.png" alt=""></div>
                    <div class="review-section__person-info">
                        <div class="review-section__person-name">Емельянов Иван</div>
                        <div class="review-section__person-city">Светлоград</div>
                    </div>
                </div>
                <div class="review-section__review-text">В локдаун у нас выходило 117 человек. 2020-й год мы закрыли с оборотом 50 000 000 рублей. В 2021-м планируем ещё больше вырасти. </div>
            </div>
        </div>
    </div>
</section>

<section class="form3-section">
    <div class="container">
        <div class="form-section__form-wrapper">
            <div class="form-section__form-header header">Запишите меня на удобное время</div>
            @livewire('request-main-form', ['align' => 'left'])
        </div>
    </div>
</section>
<section class="footer-section">
    <div class="container">
        <div class="footer-section__footer-wrapper">
            <div class="footer-section__copyright">© 2023 Дело 24. All rights reserved.</div>
            <div class="footer-section__menu" style="display: none;">
                <div class="footer-section__menu-item">Terms</div>
                <div class="footer-section__menu-item">Privacy</div>
                <div class="footer-section__menu-item">Cookies</div>
            </div>
        </div>
    </div>
</section>

<script src="./script.js"></script>
@livewire('livewire-ui-modal')
@laravelViewsScripts
@stack('scripts')
@stack('bottom')
</body>

</html>
