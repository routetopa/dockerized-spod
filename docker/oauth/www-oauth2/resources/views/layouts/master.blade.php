<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ url( '/css/app.css' ) }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>@yield( 'title' )</title>
</head>

<body>

<ul class="side-nav" id="mobile-menu">
    @if ( Auth::check() )
        <li><a class="subheader">{{ Auth::user()->email  }}</a></li>
        @include( 'layouts.menu-user' )
        <li>
            <a href="{{ action( 'Auth\LogoutController@logoutConfirm' ) }}">
                <i class="material-icons left">exit_to_app</i>
                @lang( 'auth.logout.action_title' )
            </a>
        </li>

        @if ( Auth::user()->isAdmin() )
            <li><div class="divider"></div></li>
            <li><a class="subheader">@lang( 'menu.admin.administration' )</a></li>
            @include( 'layouts.menu-admin' )
        @endif

        <li><div class="divider"></div></li>
    @endif
    <li><a class="subheader">@lang( 'menu.lang.lang' )</a></li>
    @include( 'layouts.menu-lang' )
</ul>

<header>
    <div class="navbar-fixed">
        <nav>
            <div class="nav-wrapper">
                <a href="{{ url( '/' ) }}" class="brand-logo">{{ setting( 'instance_title' ) }}</a>
                <a href="#" data-activates="mobile-menu" class="button-collapse"><i class="material-icons">menu</i></a>
                <ul id="nav-mobile" class="right hide-on-med-and-down">
                    @if ( Auth::check() )
                        <li>
                            <a class="dropdown-button" href="#!" data-activates="dropdown-user">
                                <i class="material-icons left">face</i>
                                {{ Auth::user()->email  }}
                            </a>
                            <ul id="dropdown-user" class="dropdown-content">
                                @include( 'layouts.menu-user' )
                            </ul>
                        </li>
                        @if ( Auth::user()->isAdmin() )
                            <li>
                                <a class="dropdown-button" href="#!" data-activates="dropdown-admin">
                                    <i class="material-icons left">settings</i>
                                    @lang( 'menu.admin.administration' )
                                </a>
                                <ul id="dropdown-admin" class="dropdown-content">
                                    @include( 'layouts.menu-admin' )
                                </ul>
                            </li>
                        @endif
                        <li>
                            <a class="modal-trigger" href="#logout_modal">
                                <i class="material-icons left">exit_to_app</i>
                                @lang( 'menu.logout' )
                            </a>
                        </li>
                    @endif
                    @include( 'layouts.menu-lang' )
                </ul>
            </div>
        </nav>
    </div>
</header>

<main>
    @if ( View::hasSection( 'heading' ) )
        <div class="container">
            <div class="row">
                <div class="col s12">
                    <h1>@yield( 'heading' )</h1>
                </div>
            </div>
        </div>
    @endif

    @section( 'stretch' )
        <div class="container">
            @yield( 'main' )
        </div>
    @show
</main>

<footer class="page-footer">
    <div class="container">
        <div class="row">
            <div class="col s12">
                <img src="/img/eu-flag.svg" style="height: 2em;">
            </div>
            <div class="col m4 s12">
                <p>
                    This project has received funding from <i>European Union's Horizon 2020 research and
                        innovation programme</i> under grant agreement No 645860.
                </p>
            </div>
            <div class="col m4 offset-m4 s12">
                <ul class="links-social right-align">
                    <li><a target="_blank" href="http://www.routetopa.eu" title="The ROUTE-TO-PA website"><i class="fa fa-desktop small" aria-hidden="true"></i></a></li>
                    <li><a target="_blank" href="https://www.facebook.com/routetopa" title="Facebook"><i class="fa fa-facebook-square small" aria-hidden="true"></i>
                        </a></li>
                    <li><a target="_blank" href="https://twitter.com/RouteToPA" title="Twitter"><i class="fa fa-twitter-square small" aria-hidden="true"></i></a></li>
                    <li><a target="_blank" href="https://www.linkedin.com/groups/8268551/profile" title="LinkedIn"><i class="fa fa-linkedin-square small" aria-hidden="true"></i></a></li>
                    <li><a target="_blank" href="https://www.youtube.com/channel/UCLQVcgwaYCmEiBRXAcz7S5A" title="YouTube"><i class="fa fa-youtube-square small" aria-hidden="true"></i></a></li>
                </ul>
                </p>
            </div>
        </div>
    </div>
    <div class="footer-copyright">
        <div class="container">

        </div>
    </div>
</footer>

@include( 'auth.logout' )

<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.7/js/materialize.min.js"></script>
<script src="{{ url( '/js/app.js' ) }}"></script>
</body>
</html>