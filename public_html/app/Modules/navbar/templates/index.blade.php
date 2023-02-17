<?php
use Illuminate\Support\Facades\DB;

$menu = $item['menu'] ?? false;
$colorStatus = $item['colorStatus'] ?? false;
$color = $item['color'] ?? false;

if (!class_exists('menuItems')) {
    class menuItems
    {
        public $items = [];
        public $multiarr = [];

        public function __construct($menu_id)
        {
            $this->items = \App\Models\WebMenuItems::where([
                ['menu_id', '=', $menu_id],
                ['display', '=', 1],
            ])
                ->orderBy('sort')
                ->orderBy('name')
                ->get()->toArray();
            $this->multiarr = $this->arr(0);
        }

        public function arr($p)
        {
            $d = array_filter($this->items, function ($it) use ($p) {
                return $it['parent_id'] == $p;
            });
            foreach ($d as &$r)
                if (count($x = $this->arr($r['id']))) $r['sub'] = $x;
            return $d;
        }

        public function complete()
        {
            $h = '';
            foreach ($this->multiarr as $r) {
                $h .= $this->template($r);
            }
            return $h;
        }

        public function template($it)
        {
            if (isset($it['sub'])) return $this->templateExpand($it);
            return '<li class="nav-item"> <a class="nav-link" href="' . $it['link'] . '">' . $it['name'] . '</a> </li>';
        }

        public function templateExpand($it)
        {
            $s = '';
            foreach ($it['sub'] as $r) $s .= '<li> <a class="dropdown-item" href="' . $r['link'] . '">' . $r['name'] . '</a> </li>';
            return '<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown' . $it['id'] . '" role="button" data-bs-toggle="dropdown" aria-expanded="false">' . $it['name'] . '</a>
<ul class="dropdown-menu" aria-labelledby="navbarDropdown' . $it['id'] . '">' . $s . '</ul>
</li>';
        }
    }
}

$menuItems = new menuItems($menu);
$items = $menuItems->complete();

//$asset->regCss(Bt::getAlias("@templates/sections/navbar/style.css"), ['bootstrap']);
//$asset->regCss('//cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css', null, 'bootstrap');
//$asset->regJs('//cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js', null, 'bootstrap');
?>
@vite([
    'app/Modules/navbar/templates/style.css',
])
@push('scripts')
    <script src="//cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
@endpush
@push('css')
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" />
    <style>
    <?php if($colorStatus){ ?>
    .navbar {
        background-color: <?php echo $color?>  !important;
    }
    <?php }?>
    .nav-right-static {
        display: flex;
        gap: 6px;
        align-items: center;
        justify-content: flex-end;
    }
    </style>
@endpush

<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="/"
           style="height:<?php echo isset($item['height']) && $item['height'] ? $item['height'] : 40?>px;">
            <img src="<?php echo $item['logo'] ?? '';?>" alt="">
        </a>


        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php echo $items?>


            </ul>
        <?php
        echo $item['right'] ?? "";
        ?>

        <!--            <form class="d-flex" role="search">-->
            <!--                <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">-->
            <!--                <button class="btn btn-outline-success" type="submit">Search</button>-->
            <!--            </form>-->
        </div>


        <div class="nav-right-static">
            <?php echo $item['right_static'] ?? ""; ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-list"
                     viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                          d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
                </svg>
            </button>
        </div>
    </div>
</nav>
