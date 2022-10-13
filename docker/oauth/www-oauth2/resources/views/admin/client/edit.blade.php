@extends( 'layouts.master' )

@section( 'title', trans( 'client.edit.title' ) )

@section( 'heading', trans( 'client.edit.meta_title' ) )

@section( 'main' )

    @row
        <form class="col s12" method="post" action="{{ action( 'Admin\ClientController@update', [ 'client' => $client ] ) }}">
            {{ method_field('PUT') }}
            @include( 'admin.client.form', [ 'client' => $client ] )
        </form>
    @endrow

    @row
        <form class="col s12" method="post" action="{{ action( 'Admin\ClientController@destroy', [ 'client' => $client ] ) }}">
            {{ method_field('DELETE') }}
            {{ csrf_field() }}

            @row( right-align )
                @col( s12 )
                    {!! MForm::submit()->content( trans( 'form.delete' ) . " <i class=\"material-icons right\">delete</i>" )->css( 'red lighten-2' ) !!}
                @endcol
            @endrow
        </form>
    @endrow

@endsection
