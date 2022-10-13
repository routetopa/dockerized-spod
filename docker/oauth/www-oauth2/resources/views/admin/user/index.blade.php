@extends( 'layouts.master' )

@section( 'title', trans( 'user.index.meta_title' ) )

@section( 'heading', trans( 'user.index.title' ) )

@section( 'stretch' )

    <div class="container">
    @row
        @col( s12 right-align )
            <a class="btn-floating btn-large waves-effect waves-light" href="{{ action( 'Admin\UserController@create' ) }}" title="@lang( 'user.create.title' )"><i class="material-icons">add</i></a>
        @endcol
    @endrow
    </div>

    @row

        @col( s12 )

            <table class="striped">
                <thead>
                    <tr>
                        <th data-field="id">@lang( 'form.model.id' )</th>
                        <th data-field="username">@lang( 'user.model.username' )</th>
                        <th data-field="email">@lang( 'user.model.email' )</th>
                        <th data-field="roles">@lang( 'user.model.roles' )</th>
                        <th data-field="is_banned">@lang( 'user.index.is_banned_heading' )</th>
                        <th data-field="last_login_at">@lang( 'user.model.last_login_at' )</th>
                        <th data-field="created_at">@lang( 'user.model.created_at' )</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach( $users as $user )
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                        @foreach( explode( ' ', $user->roles ) as $role )
                            @if ( $role )
                                <div class="chip">{{ $role }}</div>
                            @endif
                        @endforeach
                        </td>
                        <td>
                        @if ( $user->is_banned )
                            <i class="material-icons red-text" title="@lang( 'user.index.is_banned_true' )">block</i>
                        @else
                            <i class="material-icons green-text" title="@lang( 'user.index.is_banned_false' )">done</i>
                        @endif
                        </td>
                        <td>{{ $user->last_login_at }}</td>
                        <td>{{ $user->created_at }}</td>
                        <td>
                            <a href="{{ action( 'Admin\UserController@edit', [ 'user' => $user ] ) }}" title="@lang( 'user.edit.title' )"><i class="material-icons">mode edit</i></a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

        @endcol

    @endrow

@endsection