@extends( 'layouts.master' )

@section( 'title', trans( 'settings.keys.meta_title' ) )

@section( 'heading', trans( 'settings.keys.title' ) )

@section( 'main' )

    <form class="col s12" method="post"">
        {{ csrf_field() }}

    @row
        @col( s12 )
            @lang( 'settings.keys.description' )
        @endcol
    @endrow


    @row
        @col( s12 right-align )
            {!! MForm::submit( trans( 'settings.keys.action' ) ) !!}
        @endcol

    @endrow

    </form>

@endsection