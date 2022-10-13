@extends( 'layouts.master' )

@section( 'title', 'Authorization request' )

@section( 'heading', 'Authorization request' )


@section( 'main' )

    @include( 'errors.summary' )

    @row
        <form class="col s12" method="POST">
            {{ csrf_field() }}

            @row
                @col( s12 )
                    Hi <b>{{ $user->email }}</b>,
                    <br>do you want to authorize <b>{{ $client->title ?: $client->id }}</b> to <b>{{ $scope->scope }}</b>?
                @endcol
            @endrow

            @row
                @col( s12 )
                    {!! MForm::submit( 'Yes' )->name( 'authorized' )->value( 'yes' ) !!}
                    {!! MForm::submit( 'No' )->name( 'authorized' )->value( 'no' ) !!}
                @endcol
            @endrow
        </form>
    @endrow

@endsection