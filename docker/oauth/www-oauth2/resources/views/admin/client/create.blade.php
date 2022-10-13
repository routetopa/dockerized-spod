@extends( 'layouts.master' )

@section( 'title', trans( 'client.create.title' ) )

@section( 'heading', trans( 'client.create.meta_title' ) )

@section( 'main' )

    @row
        <form class="col s12" method="post" action="{{ action( 'Admin\ClientController@store', [ 'client' => $client ] ) }}">
            @include( 'admin.client.form', [ 'client' => $client ] )
        </form>
    @endrow

@endsection
