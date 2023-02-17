<?php
use Illuminate\Support\Facades\DB;
use App\Http\Livewire\UsersTableView;

$menu = $item['menu'] ?? false;
$colorStatus = $item['colorStatus'] ?? false;
$color = $item['color'] ?? false;

if (!class_exists('menuItems')) {
    class menuItems extends DB{
        public $items = [];
        public $multiarr = [];
        public $currentUrl = [];
        public function __construct($menu_id) {
            $this->items = \App\Models\WebMenuItems::where([
                ['menu_id', '=', $menu_id],
                ['display', '=', 1],
            ])
                ->orderBy('sort')
                ->orderBy('name')
                ->get()->toArray();
            $this->multiarr = $this->arr(0);

            $url = $_SERVER['REQUEST_URI'];
            $url = explode('?', $url)[0];
            $this->currentUrl = trim($url, '/');
        }

        public function arr($p){
            $d=array_filter($this->items, function($it) use($p){ return $it['parent_id']==$p; });
            foreach($d as &$r)
                if(count($x=$this->arr($r['id']))) $r['sub']=$x;
            return $d;
        }

        public function complete(){
            $h='';
            foreach($this->multiarr as $r) {
                $h.=$this-> template($r);
            }
            return $h;
        }

        public function template($it){
            if(isset($it['sub'])) return $this-> templateExpand($it);

            $link=explode('?', $it['link'])[0];
            $link=trim($link, '/');

            return '      <li class="nav-item">
            <a href="'.$it['link'].'" class="nav-link text-white '.($link===$this->currentUrl?'active':'').'" aria-current="page">
              '.($it['icon']??'').'
              '.$it['name'].'
            </a>
          </li>';

        }

        public function templateExpand($it){
            $s='';
            foreach($it['sub'] as $r) $s.='<li> <a class="dropdown-item" href="'.$r['link'].'">'.$r['name'].'</a> </li>';
            return '<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown'.$it['id'].'" role="button" data-bs-toggle="dropdown" aria-expanded="false">'.$it['name'].'</a>
    <ul class="dropdown-menu" aria-labelledby="navbarDropdown'.$it['id'].'">'.$s.'</ul>
    </li>';
        }
    }
}

$menuItems = new menuItems($menu);
$items = $menuItems->complete();
?>
@vite(['app/Modules/slidebar/templates/style.css'])
@push('scripts')
    <script src="//cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
@endpush
@push('css')
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" />
@endpush

<main class="d-flex flex-nowrap">

    <div class="d-flex flex-column flex-shrink-0 p-3 text-bg-dark slidebar" style="width: 280px;">
        <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none header-info">
            <img class="company-logo" src="<?php echo $item['logo']??'';?>" alt="">
            <span class="fs-4"><?php echo $item['title'];?></span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <?php echo $items?>
        </ul>
        <hr>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="https://github.com/mdo.png" alt="" width="32" height="32" class="rounded-circle me-2">
                <strong>Денис</strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                <li><a class="dropdown-item" href="#">Профиль</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#">Выйти</a></li>
            </ul>
        </div>
    </div>

    <div class="submain-container"> <!-- открывающий блок -->

