@extends( 'layouts.master' )

@section( 'title', trans( 'settings.meta_title' ) )

@section( 'heading', trans( 'settings.title' ) )

@section( 'main' )

    <form class="col s12" method="post" action="{{ action( 'Admin\SettingController@update' ) }}">
        {{ csrf_field() }}
        {{ method_field('PUT') }}

    @row
        @col( s12 input-field )
            {!! MForm::input( 'settings_instance_title' )
                ->label( trans( 'settings.instance_title.label' ) )
                ->value( $settings->get( 'instance_title' )->value ) !!}
        @endcol
    @endrow

    @row
        @col( s12 )
            {!! MForm::checkbox( 'settings_registration_open', $settings->get( 'registration_open' )->value )
                ->label( trans( 'settings.registration_open.label' ) ) !!}
        @endcol
    @endrow

    @row
        <h2>@lang( 'settings.api_check.heading' )</h2>
        @col( s12 )
            {!! MForm::checkbox( 'settings_api_check_jsonp_enable', $settings->get( 'api_check_jsonp_enable' )->value )
                ->label( trans( 'settings.api_check_jsonp_enable.label' ) )
                ->ghost( 0 ) !!}
        @endcol
        @col( s12 )
            {!! MForm::checkbox( 'settings_api_check_cors_enable', $settings->get( 'api_check_cors_enable' )->value )
                ->label( trans( 'settings.api_check_cors_enable.label' ) )
                ->ghost( 0 ) !!}
        @endcol

    @endrow

    @row
        <h2>@lang( 'settings.fb.heading' )</h2>
        @col( s6 input-field )
            {!! MForm::input( 'settings_fb_app_id' )
                ->label( trans( 'settings.fb_app_id.label' ) )
                ->value( $settings->get( 'fb_app_id' )->value ) !!}
        @endcol
        @col( s6 input-field )
        {!! MForm::input( 'settings_fb_app_secret' )
            ->label( trans( 'settings.fb_app_secret.label' ) )
            ->value( $settings->get( 'fb_app_secret' )->value ) !!}
        @endcol
    @endrow

    @row
        <h2>@lang( 'settings.jwt.heading' )</h2>
        @col( s12 )
        {!! MForm::checkbox( 'settings_jwt_enable', $settings->get( 'jwt_enable' )->value )
            ->label( trans( 'settings.jwt_enable.label' ) )
            ->ghost( 0 ) !!}
        @endcol
        @col( s6 input-field )
        {!! MForm::input( 'settings_key_public' )
            ->label( trans( 'settings.key_public.label' ) )
            ->value( $settings->get( 'key_public' )->value ) !!}
        @endcol
        @col( s6 input-field )
        {!! MForm::input( 'settings_key_private' )
            ->label( trans( 'settings.key_private.label' ) )
            ->value( $settings->get( 'key_private' )->value ) !!}
        @endcol
        @col( s12 )
            <a href="{{ action( 'Admin\SettingController@keys' ) }}">
                @lang( 'settings.key_generate' )
            </a>
        @endcol
    @endrow

    @row
        @col( s12 right-align )
            {!! MForm::submit( trans( 'settings.action' ) ) !!}
        @endcol

    @endrow

    </form>

@endsection