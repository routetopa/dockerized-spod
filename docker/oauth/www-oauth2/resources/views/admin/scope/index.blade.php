@extends( 'layouts.master' )

@section( 'title', trans( 'scope.index.meta_title' ) )

@section( 'heading', trans( 'scope.index.title' ) )

@section( 'main' )

    <div class="container">
    @row
        @col( s12 right-align )
            <a class="btn-floating btn-large waves-effect waves-light" href="{{ action( 'Admin\ScopeController@create' ) }}" title="@lang( 'scope.create.title' )"><i class="material-icons">add</i></a>
        @endcol
    @endrow
    </div>

    @row

        @col( s12 )

            <table class="striped">
                <thead>
                    <tr>
                        <th data-field="is_default">@lang( 'scope.model.is_default' )</th>
                        <th data-field="scope">@lang( 'scope.model.scope' )</th>
                        <th data-field="created_at">@lang( 'form.model.created_at' )</th>
                        <th data-field="updated_at">@lang( 'form.model.updated_at' )</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach( $scopes as $scope )
                    <tr>
                        <td>
                            @if ( $scope->is_default )
                                <i class="material-icons red-text" title="@lang( 'scope.model.is_default' )">grade</i>
                            @endif
                        </td>
                        <td>{{ $scope->scope }}</td>
                        <td>{{ $scope->created_at }}</td>
                        <td>{{ $scope->updated_at }}</td>
                        <td>
                            <a href="{{ action( 'Admin\ScopeController@edit', [ 'scope' => $scope ] ) }}" title="@lang( 'scope.edit.title' )"><i class="material-icons">mode edit</i></a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

        @endcol

    @endrow

@endsection