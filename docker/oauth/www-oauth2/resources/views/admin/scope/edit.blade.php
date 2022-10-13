@extends( 'layouts.master' )

@section( 'title', trans( 'scope.edit.title' ) )

@section( 'heading', trans( 'scope.edit.meta_title' ) )

@section( 'main' )

    @row
        <form class="col s12" method="post" action="{{ action( 'Admin\ScopeController@update', [ 'scope' => $scope ] ) }}">
            {{ method_field('PUT') }}
            @include( 'admin.scope.form', [ 'scope' => $scope ] )
        </form>
    @endrow

    @row
        <form class="col s12" method="post" action="{{ action( 'Admin\ScopeController@destroy', [ 'scope' => $scope ] ) }}">
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
