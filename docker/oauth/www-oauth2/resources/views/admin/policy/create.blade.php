@extends( 'layouts.master' )

@section( 'title', trans( 'policy.create.meta_title' ) )

@section( 'heading', trans( 'policy.create.title' ) )

@section( 'main' )

    @row
        <form class="col s12" method="post" action="{{ action( 'Admin\PolicyController@store', [ 'policy' => $policy ] ) }}">
            @include( 'admin.policy.form', [ 'policy' => $policy ] )
        </form>
    @endrow

@endsection
