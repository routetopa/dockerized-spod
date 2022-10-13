@extends( 'layouts.master' )

@section( 'title', trans( 'scope.create.title' ) )

@section( 'heading', trans( 'scope.create.meta_title' ) )

@section( 'main' )

    @row
        <form class="col s12" method="post" action="{{ action( 'Admin\ScopeController@store', [ 'scope' => $scope ] ) }}">
            @include( 'admin.scope.form', [ 'scope' => $scope ] )
        </form>
    @endrow

@endsection
