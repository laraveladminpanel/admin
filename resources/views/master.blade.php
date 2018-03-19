<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <title>@yield('page_title', setting('admin.title') . " - " . setting('admin.description'))</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ admin_asset('images/logo-icon.png') }}" type="image/x-icon">

    <!-- App CSS -->
    <link rel="stylesheet" href="{{ admin_asset('css/app.css') }}">

    @yield('css')

    <!-- Few Dynamic Styles -->
    <style type="text/css">
        .admin .side-menu .navbar-header {
            background:{{ config('admin.primary_color','#22A7F0') }};
            border-color:{{ config('admin.primary_color','#22A7F0') }};
        }
        .widget .btn-primary{
            border-color:{{ config('admin.primary_color','#22A7F0') }};
        }
        .widget .btn-primary:focus, .widget .btn-primary:hover, .widget .btn-primary:active, .widget .btn-primary.active, .widget .btn-primary:active:focus{
            background:{{ config('admin.primary_color','#22A7F0') }};
        }
        .admin .breadcrumb a{
            color:{{ config('admin.primary_color','#22A7F0') }};
        }
    </style>

    @if(!empty(config('admin.additional_css')))<!-- Additional CSS -->
        @foreach(config('admin.additional_css') as $css)<link rel="stylesheet" type="text/css" href="{{ asset($css) }}">@endforeach
    @endif

    @yield('head')
</head>

<body class="admin @if(isset($dataType) && isset($dataType->slug)){{ $dataType->slug }}@endif">

<div id="admin-loader">
    <?php $admin_loader_img = Admin::setting('admin.loader', ''); ?>
    @if($admin_loader_img == '')
        <img src="{{ admin_asset('images/admin_loader.png') }}" alt="Admin Loader">
    @else
        <img src="{{ Admin::image($admin_loader_img) }}" alt="Admin Loader">
    @endif
</div>

<?php
$user_avatar = Admin::image(Auth::user()->avatar);
if ((substr(Auth::user()->avatar, 0, 7) == 'http://') || (substr(Auth::user()->avatar, 0, 8) == 'https://')) {
    $user_avatar = Auth::user()->avatar;
}
?>

<div class="app-container">
    <div class="fadetoblack visible-xs"></div>
    <div class="row content-container">
        @include('admin::dashboard.navbar')
        @include('admin::dashboard.sidebar')
        <script>
            (function(){
                    var appContainer = document.querySelector('.app-container'),
                        sidebar = appContainer.querySelector('.side-menu'),
                        navbar = appContainer.querySelector('nav.navbar.navbar-top'),
                        loader = document.getElementById('admin-loader'),
                        hamburgerMenu = document.querySelector('.hamburger'),
                        sidebarTransition = sidebar.style.transition,
                        navbarTransition = navbar.style.transition,
                        containerTransition = appContainer.style.transition;

                    sidebar.style.WebkitTransition = sidebar.style.MozTransition = sidebar.style.transition =
                    appContainer.style.WebkitTransition = appContainer.style.MozTransition = appContainer.style.transition =
                    navbar.style.WebkitTransition = navbar.style.MozTransition = navbar.style.transition = 'none';

                    if (window.localStorage && window.localStorage['admin.stickySidebar'] == 'true') {
                        appContainer.className += ' expanded no-animation';
                        loader.style.left = (sidebar.clientWidth/2)+'px';
                        hamburgerMenu.className += ' is-active no-animation';
                    }

                   navbar.style.WebkitTransition = navbar.style.MozTransition = navbar.style.transition = navbarTransition;
                   sidebar.style.WebkitTransition = sidebar.style.MozTransition = sidebar.style.transition = sidebarTransition;
                   appContainer.style.WebkitTransition = appContainer.style.MozTransition = appContainer.style.transition = containerTransition;
            })();
        </script>
        <!-- Main Content -->
        <div class="container-fluid">
            <div class="side-body padding-top">
                @yield('page_header')
                <div id="admin-notifications"></div>
                @yield('content')
            </div>
        </div>
    </div>
</div>
@include('admin::partials.app-footer')

<!-- Javascript Libs -->


<script type="text/javascript" src="{{ admin_asset('js/app.js') }}"></script>


<script>
    @if(Session::has('alerts'))
        let alerts = {!! json_encode(Session::get('alerts')) !!};
        helpers.displayAlerts(alerts, toastr);
    @endif

    @if(Session::has('message'))

    // TODO: change Controllers to use AlertsMessages trait... then remove this
    var alertType = {!! json_encode(Session::get('alert-type', 'info')) !!};
    var alertMessage = {!! json_encode(Session::get('message')) !!};
    var alerter = toastr[alertType];

    if (alerter) {
        alerter(alertMessage);
    } else {
        toastr.error("toastr alert-type " + alertType + " is unknown");
    }

    @endif
</script>
@yield('javascript')

@if(!empty(config('admin.additional_js')))<!-- Additional Javascript -->
    @foreach(config('admin.additional_js') as $js)<script type="text/javascript" src="{{ asset($js) }}"></script>@endforeach
@endif

</body>
</html>
