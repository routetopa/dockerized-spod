@extends( 'layouts.master' )

@section( 'title', trans( 'auth.register.meta_title' ) )

@section( 'heading', trans( 'auth.register.title' ) )

@section( 'main' )

    @include( 'errors.summary' )

    @row
        @col( s12 )
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <i class="material-icons">group</i>
                    @lang( 'auth.register.action_title' )
                </span>
                <p>@lang( 'auth.register.sent_message' )</p>
            </div>
            <div class="card-action">
                <form action="{{ action( 'Auth\RegisterController@sendValidation' ) }}" method="post">
                    {{ csrf_field() }}
                    {!! MForm::submit( trans( 'auth.register.sent_action' ) ) !!}
                </form>
            </div>
        </div>
        @endcol
    @endrow

@endsection
