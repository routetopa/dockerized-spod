@extends( 'layouts.master' )

@section( 'title', trans( 'user.create.meta_title' ) )

@section( 'heading', trans( 'user.create.title' ) )

@section( 'main' )

    @row
        <form class="col s12" method="post" action="{{ action( 'Admin\UserController@store', [ 'user' => $user ] ) }}">
            @include( 'admin.user.form', [ 'user' => $user ] )
        </form>
    @endrow

@endsection
