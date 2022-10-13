@extends( 'layouts.master' )

@section( 'title', trans( 'auth.logout.meta_title' ) )

@section( 'heading', trans( 'auth.logout.title' ) )

@section( 'main' )

    @include( 'errors.summary' )

    @row
        @col( s12 )
            <p>@lang( 'auth.logout.action_message' )</p>

            <form method="POST" action="{{ route('logout') }}">
                {{ csrf_field() }}
                {!! MForm::submit( trans( 'auth.logout.button_logout' ) ) !!}

                {{-- <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat">@lang( 'auth.logout.button_cancel' )</a>--}}
            </form>
        @endcol
    @endrow


@endsection