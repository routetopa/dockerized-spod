@extends( 'layouts.master' )

@section( 'title', trans( 'auth.login.meta_title' ) )

@section( 'heading', trans( 'auth.login.title' ) )

@section( 'main' )

    @include( 'errors.summary' )

    @row
        @col( s12 m7 )
            <div class="card">
                <div class="card-content">
                    <span class="card-title">
                        <i class="material-icons">group</i>
                        @lang( 'auth.login.login_card.title' )
                    </span>
                    <p>@lang( 'auth.login.login_card.message' )</p>
                    @row
                        <form class="col s12" action="{{ route('login') }}" method="POST">
                            {{ csrf_field() }}
                            @row
                                @col( s12 input-field )
                                    {!! MForm::input( 'email' )->tlabel( 'auth.login.login_card.email' )->css( 'validate' )->value( old( 'email' ) ) !!}
                                @endcol
                                @col( s12 input-field )
                                    {!! MForm::password( 'password' )->tlabel( 'auth.login.login_card.password' )->css( 'validate' ) !!}
                                @endcol
                                @col( s12 )
                                    {!! MForm::checkbox( 'remember' )->tlabel( 'auth.login.login_card.remember_me' )->css( 'validate' ) !!}
                                @endcol
                            @endrow
                            @row
                                @col( s12 right-align )
                                    {!! MForm::submit( trans( 'auth.login.login_card.action' ) ) !!}
                                @endcol
                            @endrow
                        </form>
                    @endrow
                    <hr>
                    @row
                    <form class="col s12" action="{{ action('Auth\FacebookController@login') }}" method="POST">
                        {{ csrf_field() }}
                        @row
                            @col( s12 right-align )
                                {!! MForm::submit( trans( 'LOGIN WITH FB' ) ) !!}
                            @endcol
                        @endrow
                    </form>
                    @endrow
                </div>
            </div>
        @endcol

        @col( s12 m5 )
        @if ( setting( 'registration_open' ) )
            <div class="card green lighten-4">
                <div class="card-content">
                    <span class="card-title green-text">
                        <i class="material-icons">group</i>
                        @lang( 'auth.login.register_card.title' )
                    </span>
                    <p>@lang( 'auth.login.register_card.message' )</p>
                </div>
                <div class="card-action">
                    <a href="{{ url('/register') }}">@lang( 'auth.login.register_card.action' )</a>
                </div>
            </div>
        @endif
            <div class="card blue-grey lighten-4">
                <div class="card-content">
                    <span class="card-title blue-grey-text">
                        <i class="material-icons">live_help</i>
                        @lang( 'auth.login.password_reset_card.title' )
                    </span>
                    <p>@lang( 'auth.login.password_reset_card.message' )</p>
                </div>
                <div class="card-action">
                    <a class="blue-grey-text" href="{{ url('/password/reset') }}">@lang( 'auth.login.password_reset_card.action' )</a>
                </div>
            </div>
        @endcol
    @endrow

@endsection