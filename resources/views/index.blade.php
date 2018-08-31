<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
<div id="app">
    <button id="menu-button" class="btn"><i class="material-icons">menu</i></button>
    <!--Sidebar-->
    <aside class="sidebar">
        <!-- Account -->
        <div class="account-card">
            <img src="1.jpg">
            <div>
                <span>{{ Auth::user()->name }} </span>

                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                    {{ __('Выйти') }}
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>

        <!-- Account -->

        <!-- Users list -->
        <span class="user-list-heading">Oнлайн: 20</span>
        <div class="users-list">
            <ul class="list-group">
                <li class="list-group-item user-list-item">
                    <img src="2.jpg">
                    <span style="color: #ef5350">Nick name4</span>
                    <div class="operations">
                        <i class="material-icons">volume_off</i>
                        <i class="material-icons">close</i>
                    </div>
                </li>
                </li>
            </ul>
        </div>
        <!-- Users list -->

    </aside>
    {{--Sidebar--}}

    <!--Content-->
    <div class="chat-container d-flex flex-column">

        <div class="message">
            <img class="avatar" src="2.jpg">
            <span class="name">
                Name
            </span>
            <div class="text">
                Lorem ipsum dolor sit amet, consectetuer adipiscing elit.  Donec qu
            </div>
        </div>

        <div class="message user-message">
            <div class="text">
                Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec qu
            </div>
            <span class="name" style="color: #DCE775">
                Name SecondName
            </span>
            <img class="user-avatar" src="1.jpg">
        </div>
    </div>

    <div class="message-input d-flex flex-row">
        <input type="text" class="form-control" maxlength="200" placeholder="Сообщение" aria-label="Сообщение" aria-describedby="basic-addon2" v-model="input">
        <button class="btn btn-outline-secondary" type="button" @click="clickButton">Отправить</button>
    </div>

    <div class="fogging"></div>
</div>

<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>

<script>
    function swipeMenu() {
        var that = this;

        this.menuWidth = $('aside').width();
        this.menu = $('aside');
        this.fogging = $('.fogging');
        this.startCoord = null;
        this.menuState = false;
        this.validSwipe = false;
        this.foggingIntensity = .8;

        $(document).bind('touchstart', (event) => that.swipeStart(event));
        $(document).bind('touchend', (event) => that.swipeEnd(event));

        $(window).keyup((event) => {
            if(event.keyCode == 27) that.closeMenu();
        });

        this.swipeStart = function(event) {
            var coord = Math.round(event.originalEvent.touches[0].pageX);
            that.startCoord = coord;

            if (that.menuState && coord > that.menuWidth || that.menuState == false && coord <= 10) {
                $(document).bind('touchmove', (event) => this.swipeMove(event));
                that.validSwipe = true;
            } else {
                that.validSwipe = false;
            }
        };

        this.swipeEnd = function(event) {
            var coord = Math.round(event.originalEvent.changedTouches[0].pageX);

            if (!that.validSwipe) return;

            that.fogging.removeAttr('style');

            if (coord > that.startCoord) {
                that.openMenu();
            } else {
                that.closeMenu();
            }
            $(document).unbind('touchmove');
        };

        this.swipeMove = function(event) {
            var coordX = Math.round(event.originalEvent.touches[0].pageX);
            var coord = coordX < that.menuWidth ? coordX : that.menuWidth;

            that.dragMenu(coord);
        };

        this.dragMenu = function(xCoord) {
            that.menu.css({
                'left': xCoord - that.menuWidth
            });
            that.fogging.addClass('fogged');
            that.fogging.css({
                'opacity': ((xCoord / that.menuWidth).toFixed(1)) * that.foggingIntensity
            });
        };

        this.openMenu = function() {
            that.menu.removeAttr('style');

            that.menu.addClass('menu-animation');
            that.menu.removeClass('menu-hide');
            that.menu.addClass('menu-show');

            that.menu.one('transitionend', () => that.menu.removeClass('menu-animation'));
            that.fogging.addClass('fogged');

            that.menuState = true;
        };

        this.closeMenu = function() {
            that.menu.removeAttr('style');

            that.menu.addClass('menu-animation');
            that.menu.removeClass('menu-show');
            that.menu.addClass('menu-hide');

            that.menu.one('transitionend', () => that.menu.removeClass('menu-animation'));
            that.fogging.removeClass('fogged');

            that.menuState = false;
        };
    };

    var menu = new swipeMenu();
    $("#menu-button").click(() => menu.openMenu());
    $(".fogging").click(() => menu.closeMenu());

</script>

</body>

</html>
