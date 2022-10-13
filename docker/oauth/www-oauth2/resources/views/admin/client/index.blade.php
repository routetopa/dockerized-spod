@extends( 'layouts.master' )

@section( 'title', trans( 'client.index.title' ) )

@section( 'heading', trans( 'client.index.meta_title' ) )

@section( 'stretch' )

    <div class="container">
    @row
        @col( s12 right-align )
            <a class="btn-floating btn-large waves-effect waves-light" href="{{ action( 'Admin\ClientController@create' ) }}" title="@lang( 'client.create.title' )"><i class="material-icons">add</i></a>
        @endcol
    @endrow
    </div>

    @row

        @col( s12 )

            <table class="striped">
                <thead>
                    <tr>
                        <th data-field="client_id">@lang( 'client.model.client_id' )</th>
                        <th data-field="auto_authorize">@lang( 'client.index.model.title' )</th>
                        <th data-field="client_secret">@lang( 'client.model.client_secret' )</th>
                        <th data-field="grant_types">@lang( 'client.model.grant_types' )</th>
                        <th data-field="scope">@lang( 'client.model.scope' )</th>
                        <th data-field="user_id">@lang( 'client.model.user_id' )</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach( $clients as $client )
                    <tr>
                        <td>{{ $client->client_id }}</td>
                        <td>
                            @if ( $client->auto_authorize )
                                <i class="material-icons green-text" title="@lang( 'client.index.auto_authorize_true' )">done</i>
                            @endif
                        </td>
                        <td>{{ $client->title }}</td>
                        <td>{{ $client->grant_types }}</td>
                        <td>{{ $client->scope }}</td>
                        <td>{{ $client->user_id }}</td>
                        <td>
                            <a href="{{ action( 'Admin\ClientController@edit', [ 'client' => $client ] ) }}"  title="@lang( 'client.edit.title' )"><i class="material-icons">mode edit</i></a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

        @endcol

    @endrow

@endsection