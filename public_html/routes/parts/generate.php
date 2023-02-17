<?php
use Illuminate\Support\Facades\Route;

/*
Route::get('/employees_generate', function () {
    $fs = [
        'Зайцев', 'Зайцева', 'Лисицын', 'Волков', 'Медведев', 'Куницын', 'Сапожников', 'Лосев', 'Жирафов', 'Зебрин',
        'Обезьянов', 'Волчков', 'Белкин', 'Ослов', 'Лошадкин', 'Коровин', 'Собакин', 'Кошкин', 'Крысин'
    ];
    $ns = [
        'Андрей', 'Алексей', 'Иван', 'Василий', 'Федор', 'Роман', 'Сергей', 'Марат', 'Ринат', 'Дмитрий', 'Ив',
        'Ростислав', 'Ярослав', 'Евгений', 'Михаил', 'Альберт', 'Кузьма', 'Илья', 'Данил', 'Ришат', 'Джон',
        'Арнольд', 'Джорж', 'Юрий', 'Нурым'
    ];
    $os = [
        'Андреевич', 'Алексеевич', 'Иванович', 'Васильевич', 'Федорович', 'Романович', 'Сергеич', 'Маратович',
        'Ринатович', 'Дмитриевич', 'Ивич', 'Ростиславович', 'Ярославович', 'Евгеньевич', 'Михайлович', 'Альбертович',
        'Кузьмич', 'Ильич', 'Данилович', 'Ришатович', 'Джонсович',
        'Арнольдович', 'Джоржович', 'Юриьевич', 'Нурымович'
    ];
    for ($u = 0; $u < 100; $u++) {
        $f = $fs[rand(0, count($fs)-1)];
        $i = $ns[rand(0, count($ns)-1)];
        $o = $os[rand(0, count($os)-1)];
        $fio = $f.' '.$i.' '.$o;
        $status = [-1,0,0,0,-10, -20];
        $cabinet = [1,3,6,9];

        $gen = new \Faker\Generator();
        $user = \App\Models\User::factory(1)->create([
            'status' => $status[rand(0, count($status) - 1)],
            'name' => $fio,
            'id_cms_privileges' => 2,
            'cabinet_id' => $cabinet[rand(0, count($cabinet) - 1)],
            'is_deleted' => rand(0, 10) == 9 ? 1 : 0,
            'phone' => $gen->numberBetween(70000000000, 79999999999),
        ]);
        $profile = new \App\Models\UsersProfiles();
        $is_worker = rand(0, 5) == 4 ? 1 : 0;
        $special = [
            '',
            'Дворник',
            'Верстальщик',
            'Носильщик',
            'Программист',
            'Тестировщик',
            'Директор',
            'Начальника',
            'Грузчик',
            'Экономист',
            'Водитель',
        ];
        $children = [
            '',
            '',
            '',
            '',
            '',
            '',
            '-',
            'нет',
            'двое',
            'сын',
            'дочь',
            'сын и дочь',
            'трое',
        ];
        $experience = [
            '',
            '-',
            'нет',
            'год',
            '5 лет',
            'с рождения',
            'всю жизнь',
        ];
        $district = [
            '',
            'Южный',
            'Северный',
            'Западный',
            'Восточный',
            'Центральный'
        ];
        $comment = [
            '',
            'Прогуливает',
            'Плохой работник',
            'Хилый',
            'Редко ходит',
            'Бухает',
            'Часто болеет',
            'Хороший работник',
            'Молодец',
        ];
        $city = ['Москва', 'Питер'];

        $id = DB::table('users_profiles')->insertGetId([
            //'id' => null,
            'users_id' => $user[0]->id,
            'f' => $f,
            'i' => $i,
            'o' => $o,
            'birthday_at' => rand(335883944, 1661259945),
            'special' => $special[rand(0, count($special) - 1)],
            'is_rf' => rand(0, 10) == 9 ? 1 : 0,
            'is_worker' => $is_worker,
            'work' => $is_worker ? rand(8,12).' '.rand(15,20) : '',
            'family' => rand(0,1),
            'children' => $children[rand(0, count($children) - 1)],
            'times' => rand(8,12).' '.rand(15,20),
            'experience' => $experience[rand(0, count($experience) - 1)],
            'is_criminal' => rand(0, 10) == 9 ? 1 : 0,
            'is_car' => rand(0, 2) == 1 ? 1 : 0,
            'city' => $city[rand(0, count($city) - 1)],
            'district' => $district[rand(0, count($district) - 1)],
            'passport' => $gen->numberBetween(10, 99).' '.$gen->numberBetween(10, 99).' '.$gen->numberBetween(100000, 999999),
            'snils' => $gen->numberBetween(100, 999).' '.$gen->numberBetween(100, 999).' '.$gen->numberBetween(100, 999).' '.$gen->numberBetween(10, 99),
            'comment' => $comment[rand(0, count($comment) - 1)],
        ]);
    }
})->name('employees_generate');

Route::get('/cabinet_generate', function () {
    $fields = \App\Models\OrdersFields::query()->whereRaw('cabinet_id IS NULL')->orderBy('sort')->get();
    //dd($fields);
    $cabinets = \App\Models\Cabinet::all();
    foreach ($cabinets as $cab) {
        dump($cab->toArray());
        foreach ($fields as $f) {
            //dd($f);
            $data = $f->toArray();
            $data['id'] = null;
            $data['cabinet_id'] = $cab->id;
            \App\Models\OrdersFields::insertGetId($data);
            //$id = DB::table($f->table)->insertGetId($data);
            dump($f->toArray());
        }
    }
})->name('cabinet_generate');

Route::get('/request_generate', function () {
    $fs = [
        'Зайцев', 'Зайцева', 'Лисицын', 'Волков', 'Медведев', 'Куницын', 'Сапожников', 'Лосев', 'Жирафов', 'Зебрин',
        'Обезьянов', 'Волчков', 'Белкин', 'Ослов', 'Лошадкин', 'Коровин', 'Собакин', 'Кошкин', 'Крысин'
    ];
    $ns = [
        'Андрей', 'Алексей', 'Иван', 'Василий', 'Федор', 'Роман', 'Сергей', 'Марат', 'Ринат', 'Дмитрий', 'Ив',
        'Ростислав', 'Ярослав', 'Евгений', 'Михаил', 'Альберт', 'Кузьма', 'Илья', 'Данил', 'Ришат', 'Джон',
        'Арнольд', 'Джорж', 'Юрий', 'Нурым'
    ];
    $os = [
        'Андреевич', 'Алексеевич', 'Иванович', 'Васильевич', 'Федорович', 'Романович', 'Сергеич', 'Маратович',
        'Ринатович', 'Дмитриевич', 'Ивич', 'Ростиславович', 'Ярославович', 'Евгеньевич', 'Михайлович', 'Альбертович',
        'Кузьмич', 'Ильич', 'Данилович', 'Ришатович', 'Джонсович',
        'Арнольдович', 'Джоржович', 'Юриьевич', 'Нурымович'
    ];

    $text = 'Вчера ВСУ после продолжительной артиллерийской подготовки по Балаклее, Изюму, логистическим маршрутам и складам ВС РФ начали наступление на Балаклею с запада со стороны Андреевки. Враг пытается выйти на трассу Балаклея-Волохов Яр-Шевченково-Купянск, чтобы окружить наши войска. ️По перехватам в атаке участвуют наёмники. В силу своего географического положения без контроля над Балаклеей тяжело обезопасить северо-западный фланг группировки, базирующейся в Изюме. Кроме того, если когда-нибудь ВС РФ снова обратят внимание на сам Харьков, из Балаклеи пролегает удобный маршрут через Змиев в Слободской и Индустриальный районы областного центра.';

    for ($g = 10; $g < 60; $g++) {
        $f = $fs[rand(0, count($fs)-1)];
        $i = $ns[rand(0, count($ns)-1)];
        $o = $os[rand(0, count($os)-1)];
        $fio = $f.' '.$i.' '.$o;

        $req = new \App\Models\Request();
        $req->cabinet_id = 3;
        $req->save();

        $f = new \App\Models\RequestValues();
        $f->request_id = $req->id;
        $f->request_fields_id = 94;
        $f->value = '07.09.2022 10:'.$g;
        $f->save();

        $f = new \App\Models\RequestValues();
        $f->request_id = $req->id;
        $f->request_fields_id = 95;
        $f->value = $fio;
        $f->save();

        $gen = new \Faker\Generator();

        $f = new \App\Models\RequestValues();
        $f->request_id = $req->id;
        $f->request_fields_id = 96;
        $f->value = $gen->numberBetween(70000000000, 79999999999);
        $f->save();

        $words = explode(' ', $text);
        shuffle($words);

        $f = new \App\Models\RequestValues();
        $f->request_id = $req->id;
        $f->request_fields_id = 97;
        $f->value = implode(' ', $words);
        $f->save();
    }
    return 'ok';
})->name('request_generate');

Route::get('/order_generate', function () {
    $fs = [
        'Зайцев', 'Зайцева', 'Лисицын', 'Волков', 'Медведев', 'Куницын', 'Сапожников', 'Лосев', 'Жирафов', 'Зебрин',
        'Обезьянов', 'Волчков', 'Белкин', 'Ослов', 'Лошадкин', 'Коровин', 'Собакин', 'Кошкин', 'Крысин'
    ];
    $ns = [
        'Андрей', 'Алексей', 'Иван', 'Василий', 'Федор', 'Роман', 'Сергей', 'Марат', 'Ринат', 'Дмитрий', 'Ив',
        'Ростислав', 'Ярослав', 'Евгений', 'Михаил', 'Альберт', 'Кузьма', 'Илья', 'Данил', 'Ришат', 'Джон',
        'Арнольд', 'Джорж', 'Юрий', 'Нурым'
    ];
    $os = [
        'Андреевич', 'Алексеевич', 'Иванович', 'Васильевич', 'Федорович', 'Романович', 'Сергеич', 'Маратович',
        'Ринатович', 'Дмитриевич', 'Ивич', 'Ростиславович', 'Ярославович', 'Евгеньевич', 'Михайлович', 'Альбертович',
        'Кузьмич', 'Ильич', 'Данилович', 'Ришатович', 'Джонсович',
        'Арнольдович', 'Джоржович', 'Юриьевич', 'Нурымович'
    ];

    $texts = [
        'Вынести мусор',
        'Вынести рояль',
        'Вынести пианино',
        'Перевести мебель',
        'Перевести станок',
        'Перевести гараж',
        'Перевести квартиру',
        'Помочь с переездом',
        'Переставить стиральную машинку',
        'Перевести холодильник',
    ];

    for ($g = 10; $g < 60; $g++) {
        $f = $fs[rand(0, count($fs)-1)];
        $i = $ns[rand(0, count($ns)-1)];
        $o = $os[rand(0, count($os)-1)];
        $fio = $f.' '.$i.' '.$o;

        $bots = [2,3,5,6];
        $o = new \App\Models\Orders();
        $o->cabinet_id = 3;
        $o->bot_id = $bots[rand(0, count($bots) - 1)];
        $o->status = 0;
        $o->active = 0;
        $o->type_send = 0;
        $o->save();

        $date = strtotime('2022-09-10 00:00') + rand(0, 19) * 3600 * 24;
        $f = new \App\Models\OrdersValues();
        $f->orders_id = $o->id;
        $f->orders_fields_id = 49;
        $f->value = date('d.m.Y', $date);
        $f->save();

        $f = new \App\Models\OrdersValues();
        $f->orders_id = $o->id;
        $f->orders_fields_id = 50;
        $f->value = rand(0,23).':'.(rand(1,5)*10);
        $f->save();

        $gen = new \Faker\Generator();

        $title = $texts[rand(0, count($texts) - 1)];
        $f = new \App\Models\OrdersValues();
        $f->orders_id = $o->id;
        $f->orders_fields_id = 42;
        $f->value = $title;
        $f->save();

        $f = new \App\Models\OrdersValues();
        $f->orders_id = $o->id;
        $f->orders_fields_id = 43;
        $f->value = $fio;
        $f->save();

        $f = new \App\Models\OrdersValues();
        $f->orders_id = $o->id;
        $f->orders_fields_id = 44;
        $f->value = $gen->numberBetween(70000000000, 79999999999);;
        $f->save();

        $raions = ['Ленинский', 'Октябрьский', 'Первомайский'];
        $f = new \App\Models\OrdersValues();
        $f->orders_id = $o->id;
        $f->orders_fields_id = 45;
        $f->value = $raions[rand(0, count($raions) - 1)];
        $f->save();

        $streets = ['Ивановская', 'Петровская', 'Ленина', 'Мира', 'Кирова'];
        $f = new \App\Models\OrdersValues();
        $f->orders_id = $o->id;
        $f->orders_fields_id = 46;
        $f->value = $streets[rand(0, count($streets) - 1)];
        $f->save();

        $f = new \App\Models\OrdersValues();
        $f->orders_id = $o->id;
        $f->orders_fields_id = 47;
        $f->value = rand(0, 100);
        $f->save();

        $f = new \App\Models\OrdersValues();
        $f->orders_id = $o->id;
        $f->orders_fields_id = 48;
        $f->value = rand(1,100);
        $f->save();

        $f = new \App\Models\OrdersValues();
        $f->orders_id = $o->id;
        $f->orders_fields_id = 51;
        $f->value = rand(1,5);
        $f->save();
        $counts = $f->value;

        $f = new \App\Models\OrdersValues();
        $f->orders_id = $o->id;
        $f->orders_fields_id = 52;
        $f->value = rand(1,5)*200;
        $f->save();
        $sums = $f->value;

        $reqs = ['Трезвые', 'Покоренастее', 'С инструментами', '', ''];
        $f = new \App\Models\OrdersValues();
        $f->orders_id = $o->id;
        $f->orders_fields_id = 53;
        $f->value = $reqs[rand(0, count($reqs) - 1)];
        $f->save();

        $tasks = ['Грузить', 'Разгружать', 'Перетаскивать'];
        $f = new \App\Models\OrdersValues();
        $f->orders_id = $o->id;
        $f->orders_fields_id = 54;
        $f->value = $reqs[rand(0, count($tasks) - 1)].' '.$title;
        $f->save();

        $res = ['Приятный заказчик','Еле ноги унесли','Получили чаевые','Больше с ним не работать', '', '', '', ''];
        $ob = new \App\Models\OrdersBalance();
        $ob->orders_id = $o->id;
        $ob->profit = intval($sums * $counts * 0.10);
        $ob->expense = $sums * $counts;         //Затраты
        $ob->debt = rand(0,10) == 1 ? 1000 : 0; //Долг
        $ob->comments = $res[rand(0, count($res) - 1)];
        $ob->save();
    }
    return 'ok';
})->name('order_generate');
*/
