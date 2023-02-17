if(typeof VARIABLES==="undefined"){
    var VARIABLES ={
        WRAPPER_SELECTOR: '.market_item_slider__wrapper',
        ITEMS_SELECTOR: '.market_item_slider__items',
        ITEM_SELECTOR: '.market_item_slider__item',
        CONTROL_CLASS: 'market_item_slider__control',
        SELECTOR_PREV: '.market_item_slider__control[data-slide="prev"]',
        SELECTOR_NEXT: '.market_item_slider__control[data-slide="next"]',
        SELECTOR_INDICATOR: '.market_item_slider__indicators>li',
        TRANSITION_OFF: 'market_item_slider_disable-transition',
        CLASS_CONTROL_HIDE: 'market_item_slider__control_hide',
        CLASS_ITEM_ACTIVE: 'market_item_slider__item_active',
        CLASS_INDICATOR_ACTIVE: 'active',
    }
}



function _market_item_slider(selector, config) {
    // СЌР»РµРјРµРЅС‚С‹ СЃР»Р°Р№РґРµСЂР°
    var $root = typeof selector === 'string' ?
        document.querySelector(selector) : selector;
    this._$root = $root;
    this._$wrapper = $root.querySelector(VARIABLES.WRAPPER_SELECTOR);
    this._$items = $root.querySelector(VARIABLES.ITEMS_SELECTOR);
    this._$itemList = $root.querySelectorAll(VARIABLES.ITEM_SELECTOR);
    this._$controlPrev = $root.querySelector(VARIABLES.SELECTOR_PREV);
    this._$controlNext = $root.querySelector(VARIABLES.SELECTOR_NEXT);
    this._$indicatorList = $root.querySelectorAll(VARIABLES.SELECTOR_INDICATOR);
    // СЌРєСЃС‚СЂРµРјР°Р»СЊРЅС‹Рµ Р·РЅР°С‡РµРЅРёСЏ СЃР»Р°Р№РґРѕРІ
    this._minOrder = 0;
    this._maxOrder = 0;
    this._$itemWithMinOrder = null;
    this._$itemWithMaxOrder = null;
    this._minTranslate = 0;
    this._maxTranslate = 0;
    // РЅР°РїСЂР°РІР»РµРЅРёРµ СЃРјРµРЅС‹ СЃР»Р°Р№РґРѕРІ (РїРѕ СѓРјРѕР»С‡Р°РЅРёСЋ)
    this._direction = 'next';
    // determines whether the position of item needs to be determined
    this._balancingItemsFlag = false;
    this._activeItems = [];
    // С‚РµРєСѓС‰РµРµ Р·РЅР°С‡РµРЅРёРµ С‚СЂР°РЅСЃС„РѕСЂРјР°С†РёРё
    this._transform = 0;
    // swipe РїР°СЂР°РјРµС‚СЂС‹
    this._hasSwipeState = false;
    this.__swipeStartPos = 0;
    // market_item_slider properties
    this._transform = 0; // С‚РµРєСѓС‰РµРµ Р·РЅР°С‡РµРЅРёРµ С‚СЂР°РЅСЃС„РѕСЂРјР°С†РёРё
    this._intervalId = null;
    // configuration of the market_item_slider
    this._config = {
        loop: true,
        autoplay: false,
        interval: 5000,
        refresh: true,
        swipe: true,
    };
    for (var key in config) {
        if (this._config.hasOwnProperty(key)) {
            this._config[key] = config[key];
        }
    }
    // create some constants
    var $itemList = this._$itemList;
    var widthItem = $itemList[0].offsetWidth;
    var widthWrapper = this._$wrapper.offsetWidth;
    var itemsInVisibleArea = Math.round(widthWrapper / widthItem);
    // initial setting properties
    this._widthItem = widthItem;
    this._widthWrapper = widthWrapper;
    this._itemsInVisibleArea = itemsInVisibleArea;
    this._transformStep = 100 / itemsInVisibleArea;
    // initial setting order and translate items
    for (var i = 0, length = $itemList.length; i < length; i++) {
        $itemList[i].dataset.index = i;
        $itemList[i].dataset.order = i;
        $itemList[i].dataset.translate = 0;
        if (i < itemsInVisibleArea) {
            this._activeItems.push(i);
        }
    }
    if (this._config.loop) {
        // РїРµСЂРµРјРµС‰Р°РµРј РїРѕСЃР»РµРґРЅРёР№ СЃР»Р°Р№Рґ РїРµСЂРµРґ РїРµСЂРІС‹Рј
        var count = $itemList.length - 1;
        var translate = -$itemList.length * 100;
        $itemList[count].dataset.order = -1;
        $itemList[count].dataset.translate = -$itemList.length * 100;
        $itemList[count].style.transform = 'translateX(' + translate + '%)';
        this.__refreshExtremeValues();
    } else {
        if (this._$controlPrev) {
            this._$controlPrev.classList.add(VARIABLES.CLASS_CONTROL_HIDE);
        }
    }
    this._setActiveClass();
    this._addEventListener();
    this._updateIndicators();
    this._autoplay();
}

// РїРѕРґРєР»СЋС‡РµРЅРёСЏ РѕР±СЂР°Р±РѕС‚С‡РёРєРѕРІ СЃРѕР±С‹С‚РёР№ РґР»СЏ СЃР»Р°Р№РґРµСЂР°
_market_item_slider.prototype._addEventListener = function() {
    var $root = this._$root;
    var $items = this._$items;
    var config = this._config;
    function onClick(e) {
        var $target = e.target;
        this._autoplay('stop');
        if ($target.classList.contains(VARIABLES.CONTROL_CLASS)) {
            e.preventDefault();
            this._direction = $target.dataset.slide;
            this._move();
        } else if ($target.dataset.slideTo) {
            var index = parseInt($target.dataset.slideTo);
            this._moveTo(index);
        }
        if (this._config.loop) {
            this._autoplay();
        }
    }
    function onMouseEnter(e) {
        this._autoplay('stop');
    }
    function onMouseLeave(e) {
        this._autoplay();
    }
    function onTransitionStart() {
        this._balancingItemsFlag = true;
        window.requestAnimationFrame(this._balancingItems.bind(this));
    }
    function onTransitionEnd() {
        this._balancingItemsFlag = false;
    }
    function onResize() {
        window.requestAnimationFrame(this._refresh.bind(this));
    }
    function onSwipeStart(e) {
        this._autoplay('stop');
        var event = e.type.search('touch') === 0 ? e.touches[0] : e;
        this._swipeStartPos = event.clientX;
        this._hasSwipeState = true;
    }
    function onSwipeEnd(e) {
        if (!this._hasSwipeState) {
            return;
        }
        var event = e.type.search('touch') === 0 ? e.changedTouches[0] : e;
        var diffPos = this._swipeStartPos - event.clientX;
        if (diffPos > 50) {
            this._direction = 'next';
            this._move();
        } else if (diffPos < -50) {
            this._direction = 'prev';
            this._move();
        }
        this._hasSwipeState = false;
        if (this._config.loop) {
            this._autoplay();
        }
    }
    function onDragStart(e) {
        e.preventDefault();
    }
    function onVisibilityChange() {
        if (document.visibilityState === 'hidden') {
            this._autoplay('stop');
        } else if (document.visibilityState === 'visible') {
            if (this._config.loop) {
                this._autoplay();
            }
        }
    }

    $root.addEventListener('click', onClick.bind(this));
    $root.addEventListener('mouseenter', onMouseEnter.bind(this));
    $root.addEventListener('mouseleave', onMouseLeave.bind(this));
    // on resize
    if (config.refresh) {
        window.addEventListener('resize', onResize.bind(this));
    }
    // on transitionstart and transitionend
    if (config.loop) {
        $items.addEventListener('transitionstart', onTransitionStart.bind(this));
        $items.addEventListener('transitionend', onTransitionEnd.bind(this));
    }
    // on touchstart and touchend
    if (config.swipe) {
        $root.addEventListener('touchstart', onSwipeStart.bind(this));
        $root.addEventListener('mousedown', onSwipeStart.bind(this));
        document.addEventListener('touchend', onSwipeEnd.bind(this));
        document.addEventListener('mouseup', onSwipeEnd.bind(this));
    }
    $root.addEventListener('dragstart', onDragStart.bind(this));
    // РїСЂРё РёР·РјРµРЅРµРЅРёРё Р°РєС‚РёРІРЅРѕСЃС‚Рё РІРєР»Р°РґРєРё
    document.addEventListener('visibilitychange', onVisibilityChange.bind(this));
};

// update values of extreme properties
_market_item_slider.prototype.__refreshExtremeValues = function() {
    var $itemList = this._$itemList;
    this._minOrder = +$itemList[0].dataset.order;
    this._maxOrder = this._minOrder;
    this._$itemByMinOrder = $itemList[0];
    this._$itemByMaxOrder = $itemList[0];
    this._minTranslate = +$itemList[0].dataset.translate;
    this._maxTranslate = this._minTranslate;
    for (var i = 0, length = $itemList.length; i < length; i++) {
        var $item = $itemList[i];
        var order = +$item.dataset.order;
        if (order < this._minOrder) {
            this._minOrder = order;
            this._$itemByMinOrder = $item;
            this._minTranslate = +$item.dataset.translate;
        } else if (order > this._maxOrder) {
            this._maxOrder = order;
            this._$itemByMaxOrder = $item;
            this._minTranslate = +$item.dataset.translate;
        }
    }
};

// update position of item
_market_item_slider.prototype._balancingItems = function() {
    if (!this._balancingItemsFlag) {
        return;
    }
    var $wrapper = this._$wrapper;
    var $wrapperClientRect = $wrapper.getBoundingClientRect();
    var widthHalfItem = $wrapperClientRect.width / this._itemsInVisibleArea / 2;
    var count = this._$itemList.length;
    var translate;
    var clientRect;
    if (this._direction === 'next') {
        var wrapperLeft = $wrapperClientRect.left;
        var $min = this._$itemByMinOrder;
        translate = this._minTranslate;
        clientRect = $min.getBoundingClientRect();
        if (clientRect.right < wrapperLeft - widthHalfItem) {
            $min.dataset.order = this._minOrder + count;
            translate += count * 100;
            $min.dataset.translate = translate;
            $min.style.transform = 'translateX('.concat(translate, '%)');
            // update values of extreme properties
            this.__refreshExtremeValues();
        }
    } else {
        var wrapperRight = $wrapperClientRect.right;
        var $max = this._$itemByMaxOrder;
        translate = this._maxTranslate;
        clientRect = $max.getBoundingClientRect();
        if (clientRect.left > wrapperRight + widthHalfItem) {
            $max.dataset.order = this._maxOrder - count;
            translate -= count * 100;
            $max.dataset.translate = translate;
            $max.style.transform = 'translateX('.concat(translate, '%)');
            // update values of extreme properties
            this.__refreshExtremeValues();
        }
    }
    // updating...
    requestAnimationFrame(this._balancingItems.bind(this));
};

// _setActiveClass
_market_item_slider.prototype._setActiveClass = function() {
    var activeItems = this._activeItems;
    var $itemList = this._$itemList;
    for (var i = 0, length = $itemList.length; i < length; i++) {
        var $item = $itemList[i];
        var index = +$item.dataset.index;
        if (activeItems.indexOf(index) > -1) {
            $item.classList.add(VARIABLES.CLASS_ITEM_ACTIVE);
        } else {
            $item.classList.remove(VARIABLES.CLASS_ITEM_ACTIVE);
        }
    }
};

// _updateIndicators
_market_item_slider.prototype._updateIndicators = function() {
    var $indicatorList = this._$indicatorList;
    var $itemList = this._$itemList;
    if (!$indicatorList.length) {
        return;
    }
    for (var index = 0, length = $itemList.length; index < length; index++) {
        var $item = $itemList[index];
        if ($item.classList.contains(VARIABLES.CLASS_ITEM_ACTIVE)) {
            $indicatorList[index].classList.add(VARIABLES.CLASS_INDICATOR_ACTIVE);
        } else {
            $indicatorList[index].classList.remove(VARIABLES.CLASS_INDICATOR_ACTIVE);
        }
    }
};

// move slides
_market_item_slider.prototype._move = function() {
    var step = this._direction ===
    'next' ? -this._transformStep : this._transformStep;
    var transform = this._transform + step;
    if (!this._config.loop) {
        var endTransformValue =
            this._transformStep * (this._$itemList.length - this._itemsInVisibleArea);
        transform = Math.round(transform * 10) / 10;
        if (transform < -endTransformValue || transform > 0) {
            return;
        }
        this._$controlPrev.classList.remove(VARIABLES.CLASS_CONTROL_HIDE);
        this._$controlNext.classList.remove(VARIABLES.CLASS_CONTROL_HIDE);
        if (transform === -endTransformValue) {
            this._$controlNext.classList.add(VARIABLES.CLASS_CONTROL_HIDE);
        } else if (transform === 0) {
            this._$controlPrev.classList.add(VARIABLES.CLASS_CONTROL_HIDE);
        }
    }
    var activeIndex = [];
    var i = 0;
    var length;
    var index;
    var newIndex;
    if (this._direction === 'next') {
        for (i = 0, length = this._activeItems.length; i < length; i++) {
            index = this._activeItems[i];
            newIndex = ++index;
            if (newIndex > this._$itemList.length - 1) {
                newIndex -= this._$itemList.length;
            }
            activeIndex.push(newIndex);
        }
    } else {
        for (i = 0, length = this._activeItems.length; i < length; i++) {
            index = this._activeItems[i];
            newIndex = --index;
            if (newIndex < 0) {
                newIndex += this._$itemList.length;
            }
            activeIndex.push(newIndex);
        }
    }
    this._activeItems = activeIndex;
    this._setActiveClass();
    this._updateIndicators();
    this._transform = transform;
    this._$items.style.transform = 'translateX('.concat(transform, '%)');
};

// _moveToNext
_market_item_slider.prototype._moveToNext = function() {
    this._direction = 'next';
    this._move();
};

// _moveToPrev
_market_item_slider.prototype._moveToPrev = function() {
    this._direction = 'prev';
    this._move();
};

// _moveTo
_market_item_slider.prototype._moveTo = function(index) {
    var $indicatorList = this._$indicatorList;
    var nearestIndex = null;
    var diff = null;
    var i;
    var length;
    for (i = 0, length = $indicatorList.length; i < length; i++) {
        var $indicator = $indicatorList[i];
        if ($indicator.classList.contains(VARIABLES.CLASS_INDICATOR_ACTIVE)) {
            var slideTo = +$indicator.dataset.slideTo;
            if (diff === null) {
                nearestIndex = slideTo;
                diff = Math.abs(index - nearestIndex);
            } else {
                if (Math.abs(index - slideTo) < diff) {
                    nearestIndex = slideTo;
                    diff = Math.abs(index - nearestIndex);
                }
            }
        }
    }
    diff = index - nearestIndex;
    if (diff === 0) {
        return;
    }
    this._direction = diff > 0 ? 'next' : 'prev';
    for (i = 1; i <= Math.abs(diff); i++) {
        this._move();
    }
};

// _autoplay
_market_item_slider.prototype._autoplay = function(action) {
    if (!this._config.autoplay) {
        return;
    }
    if (action === 'stop') {
        clearInterval(this._intervalId);
        this._intervalId = null;
        return;
    }
    if (this._intervalId === null) {
        this._intervalId = setInterval(
            function() {
                this._direction = 'next';
                this._move();
            }.bind(this),
            this._config.interval
        );
    }
};

// _refresh
_market_item_slider.prototype._refresh = function() {
    // create some constants
    var $itemList = this._$itemList;
    var widthItem = $itemList[0].offsetWidth;
    var widthWrapper = this._$wrapper.offsetWidth;
    var itemsInVisibleArea = Math.round(widthWrapper / widthItem);

    if (itemsInVisibleArea === this._itemsInVisibleArea) {
        return;
    }

    this._autoplay('stop');

    this._$items.classList.add(VARIABLES.TRANSITION_OFF);
    this._$items.style.transform = 'translateX(0)';

    // setting properties after reset
    this._widthItem = widthItem;
    this._widthWrapper = widthWrapper;
    this._itemsInVisibleArea = itemsInVisibleArea;
    this._transform = 0;
    this._transformStep = 100 / itemsInVisibleArea;
    this._balancingItemsFlag = false;
    this._activeItems = [];

    // setting order and translate items after reset
    for (var i = 0, length = $itemList.length; i < length; i++) {
        var $item = $itemList[i];
        var position = i;
        $item.dataset.index = position;
        $item.dataset.order = position;
        $item.dataset.translate = 0;
        $item.style.transform = 'translateX(0)';
        if (position < itemsInVisibleArea) {
            this._activeItems.push(position);
        }
    }

    this._setActiveClass();

    window.requestAnimationFrame(
        function() {
            this._$items.classList.remove(VARIABLES.TRANSITION_OFF);
        }.bind(this)
    );

    // hide prev arrow for non-infinite market_item_slider
    if (!this._config.loop) {
        if (this._$controlPrev) {
            this._$controlPrev.classList.add(VARIABLES.CLASS_CONTROL_HIDE);
        }
        return;
    }

    // translate last item before first
    var count = $itemList.length - 1;
    var translate = -$itemList.length * 100;
    $itemList[count].dataset.order = -1;
    $itemList[count].dataset.translate = -$itemList.length * 100;
    $itemList[count].style.transform = 'translateX('.concat(translate, '%)');
    // update values of extreme properties
    this.__refreshExtremeValues();
    this._updateIndicators();
    // calling _autoplay
    this._autoplay();
};

// public
_market_item_slider.prototype.next = function() {
    this._moveToNext();
};
_market_item_slider.prototype.prev = function() {
    this._moveToPrev();
};
_market_item_slider.prototype.moveTo = function(index) {
    this._moveTo(index);
};
_market_item_slider.prototype.refresh = function() {
    this._refresh();
};