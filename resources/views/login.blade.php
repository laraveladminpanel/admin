<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="none" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="admin login">
    <title>Admin - {{ Admin::setting("admin.title") }}</title>
    <link rel="stylesheet" href="{{ admin_asset('css/app.css') }}">
    <style>
        body {
            background-image:url('{{ Admin::image( Admin::setting("admin.bg_image"), admin_asset("images/bg.jpg") ) }}');
            background-color: {{ Admin::setting("admin.bg_color", "#FFFFFF" ) }};
        }
        .login-sidebar{
            border-top:5px solid {{ config('admin.primary_color','#22A7F0') }};
        }
        @media (max-width: 767px) {
            .login-sidebar {
                border-top:0px !important;
                border-left:5px solid {{ config('admin.primary_color','#22A7F0') }};
            }
        }
        body.login .form-group-default.focused{
            border-color:{{ config('admin.primary_color','#22A7F0') }};
        }
        .login-button, .bar:before, .bar:after{
            background:{{ config('admin.primary_color','#22A7F0') }};
        }
    </style>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">
</head>
<body class="login">
<div class="container-fluid">
    <div class="row">
        <div class="faded-bg animated"></div>
        <div class="hidden-xs col-sm-7 col-md-8">
            <div class="clearfix">
                <div class="col-sm-12 col-md-10 col-md-offset-2">
                    <div class="logo-title-container">
                        <?php $admin_logo_img = Admin::setting('admin.icon_image', ''); ?>
                        @if($admin_logo_img == '')
                        <img class="img-responsive pull-left logo hidden-xs animated fadeIn" src="{{ admin_asset('images/logo-icon-light.png') }}" alt="Logo Icon">
                        @else
                        <img class="img-responsive pull-left logo hidden-xs animated fadeIn" src="{{ Admin::image($admin_logo_img) }}" alt="Logo Icon">
                        @endif
                        <div class="copy animated fadeIn">
                            <h1>{{ Admin::setting('admin.title', 'Admin') }}</h1>
                            <p>{{ Admin::setting('admin.description', __('admin.login.welcome')) }}</p>
                        </div>
                    </div> <!-- .logo-title-container -->
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-sm-5 col-md-4 login-sidebar">
            
            <div class="login-container">
                
                <p>{{ __('admin.login.signin_below') }}</p>

                <form action="{{ route('admin.login') }}" method="POST">
                    {{ csrf_field() }}
                    <div class="form-group form-group-default" id="emailGroup">
                        <label>{{ __('admin.generic.email') }}</label>
                        <div class="controls">
                            <input type="text" name="email" id="email" value="{{ old('email', '') }}" placeholder="{{ __('admin.generic.email') }}" class="form-control" required>
                         </div>
                    </div>

                    <div class="form-group form-group-default" id="passwordGroup">
                        <label>{{ __('admin.generic.password') }}</label>
                        <div class="controls">
                            <input type="password" name="password" value="{{ old('password', '') }}" placeholder="{{ __('admin.generic.password') }}" class="form-control" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-block login-button">
                        <span class="signingin hidden"><span class="admin-refresh"></span> {{ __('admin.login.loggingin') }}...</span>
                        <span class="signin">{{ __('admin.generic.login') }}</span>
                    </button>

              </form>

              <div style="clear:both"></div>

              @if(!$errors->isEmpty())
              <div class="alert alert-red">
                <ul class="list-unstyled">
                    @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
              </div>
              @endif

            </div> <!-- .login-container -->

        </div> <!-- .login-sidebar -->
    </div> <!-- .row -->
</div> <!-- .container-fluid -->
<script>
    var btn = document.querySelector('button[type="submit"]');
    var form = document.forms[0];
    var email = document.querySelector('[name="email"]');
    var password = document.querySelector('[name="password"]');
    btn.addEventListener('click', function(ev){
        if (form.checkValidity()) {
            btn.querySelector('.signingin').className = 'signingin';
            btn.querySelector('.signin').className = 'signin hidden';
        } else {
            ev.preventDefault();
        }
    });
    email.focus();
    document.getElementById('emailGroup').classList.add("focused");
    
    // Focus events for email and password fields
    email.addEventListener('focusin', function(e){
        document.getElementById('emailGroup').classList.add("focused");
    });
    email.addEventListener('focusout', function(e){
       document.getElementById('emailGroup').classList.remove("focused");
    });

    password.addEventListener('focusin', function(e){
        document.getElementById('passwordGroup').classList.add("focused");
    });
    password.addEventListener('focusout', function(e){
       document.getElementById('passwordGroup').classList.remove("focused");
    });

</script>
</body>
</html>
