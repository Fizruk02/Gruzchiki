<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Login</title>
    <? include($_SERVER['DOCUMENT_ROOT']."/admin/resources/_ass.php") ?>
    <? $refdata=parse_url($_SERVER['HTTP_REFERER']);
    $ref = $refdata['host']==$_SERVER['HTTP_HOST']?$refdata['path']:'';?>
</head>
<body>
<div class="container" style="max-width:600px;">
    <div class="row" style="min-height: 100vh;">
        <div class="col align-self-center text-center">
            <div class="form-floating mb-3">
                <input class="form-control" id="FLogin" placeholder="Логин" value="<?=$_GET['log']?>">
                <label class="fw-normal" for="FLogin">Username</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="FPassword" placeholder="Пароль" value="<?=$_GET['pass']?>">
                <label class="fw-normal" for="FPassword">Password</label>
            </div>
            <button type="button" class="btn btn-primary btn-lg px-5" <?=$_GET['log']&&$_GET['pass']?'clck':''?>  onclick="login('<?=$_GET['target']?"/admin/".$_GET['target']:$ref?>')">Войти</button>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"></script>
</body>
<script>
    $(function() {$('[clck]').click();$('input').keydown( function(e){if(e.keyCode === 13){login('<?=$_GET['target']?"/admin/".$_GET['target']:$ref?>')}})});
    function login(target){
        let FLogin = $("#FLogin").val();
        if(!FLogin) return alert("введите логин");
        let FPassword = $("#FPassword").val();
        if(!FPassword) return alert("введите пароль");
        $.post("/admin/functions/actionlogin.php", {TFInputLogin:FLogin, TFInputPassword:FPassword},function(res) {
            if(res.success!='ok') return alert(res.err??'ошибка');
            document.location.href = document.location.origin+(target&&target!==""&&target!=="/admin/login.php" ? target : "/admin");
        },'json');
    }
</script>
</html>