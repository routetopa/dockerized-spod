{{ csrf_field() }}

@row
    @col( s6 input-field )
        {!! MForm::input( 'client_id' )->tlabel( 'client.model.client_id' )->value( $client->client_id ) !!}
    @endcol
    @col( s6 input-field )
        {!! MForm::input( 'client_secret' )->tlabel( 'client.model.client_secret' )->value( $client->client_secret ) !!}
    @endcol
@endrow

@row
    @col( s12 input-field )
        {!! MForm::input( 'redirect_uri' )->tlabel( 'client.model.redirect_uri' )->value( $client->redirect_uri ) !!}
    @endcol
@endrow

@row
    @col( s12 input-field )
        {!! MForm::input( 'grant_types' )->tlabel( 'client.model.grant_types' )->value( $client->grant_types ) !!}
    @endcol
@endrow

@row
    @col( s12 input-field )
        {!! MForm::input( 'scope' )->tlabel( 'client.model.scope' )->value( $client->scope ) !!}
    @endcol
@endrow

@row
    @col( s12 input-field )
        {!! MForm::input( 'user_id' )->tlabel( 'client.model.user_id' )->value( $client->user_id ) !!}
    @endcol
@endrow

@row
    @col( s12 input-field )
        {!! MForm::input( 'title' )->tlabel( 'client.model.title' )->value( $client->title ) !!}
    @endcol
@endrow

@row
    @col( s6 input-field )
        {!! MForm::input( 'home_uri' )->tlabel( 'client.model.home_uri' )->value( $client->home_uri ) !!}
    @endcol
    @col( s6 input-field )
        {!! MForm::input( 'init_oauth_uri' )->tlabel( 'client.model.init_oauth_uri' )->value( $client->init_oauth_uri ) !!}
    @endcol
@endrow

@row
    @col( s12 )
        {!! MForm::checkbox( 'auto_authorize' )->tlabel( 'client.edit.auto_authorize' )->checked( $client->auto_authorize ) !!}
    @endcol
@endrow

@row
    @col( s12 right-align )
        {!! MForm::submit( trans( 'form.save' ) ) !!}
    @endcol
@endrow