@extends( 'layouts.master' )

@section( 'title', trans( 'home.index.meta_title' ) )

@section( 'heading', trans( 'home.index.title' ) )

@section( 'main' )

    @row

        @col( s12 )
            <p>@lang( 'home.index.logged_message' )</p>
        @endcol

    @endrow

    @row
        @col( s12 )
            @foreach ( $clients as $client )
                @if ( $client->init_oauth_uri )
                    <a class="waves-effect waves-light btn" href="{{ $client->init_oauth_uri }}">
                        <i class="material-icons left">https</i>
                        @lang( 'home.index.go_to', [ 'title' => $client->title ?: $client->client_id ] )
                    </a>
                @elseif ( $client->home_uri )
                    <a class="waves-effect waves-light btn" href="{{ $client->home_uri }}">
                        <i class="material-icons left">submit</i>
                        @lang( 'home.index.go_to', [ 'title' => $client->title ?: $client->client_id ] )
                    </a>
                @endif
            @endforeach
        @endcol
    @endrow

@endsection
