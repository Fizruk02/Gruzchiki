<style>
    .card-title {
        float: left;
        font-size: 1.1rem;
        font-weight: 400;
        margin: 0;
    }
    .users-list {
        padding-left: 0;
        list-style: none;
    }
    .users-list>li {
        float: left;
        padding: 10px;
        text-align: center;
        width: 113px;
    }
    .users-list>li img {
        border-radius: 50%;
        height: auto;
        max-width: 92px;
    }
    .users-list-name {
        color: #495057;
        font-size: .875rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: block;
    }
    .users-list-date {
        color: #748290;
        font-size: 12px;
        display: block;
    }
</style>

<div class="card" widgetId="<?=$widgetId?>">
    <div class="card-header border-0">
        <div class="d-flex justify-content-between">
            <h3 class="card-title">Последние пользователи</h3>
            <a href="data-users?order=last">Список</a>
        </div>
        <div class="card-body p-0">
            <ul class="users-list clearfix">
                <?
                $result = arrayQuery("SELECT u.*, f.small_size photo, DATE_FORMAT(u.t_date, '%d.%m.%Y %H:%i') first_visiting
                                  FROM `usersAll` u
                                  LEFT JOIN `files` f ON u.photo = f.id_group AND f.id_group>0
                                  LEFT JOIN users us ON us.id_chat = u.chat_id
                                  ORDER BY u.t_date DESC
                                  LIMIT 8", [], true);

                foreach($result as $row) {
                    ?>
                    <li>
                        <img src="<?= '/'.(is_file($_SERVER['DOCUMENT_ROOT'].'/'.$row['photo'])?$row['photo']:'files/systems/no_photo_150_150.png')?>" alt="User Image">
                        <a class="users-list-name" href="#"><?=$row['first_name'] ?></a>
                        <span class="users-list-date"><?=$row['first_visiting'] ?></span>
                    </li>
                <?}?>
            </ul>
        </div>
    </div>
</div>