@extends( 'layouts.master' )

@section( 'title', trans( 'auth.forgot.meta_title' ) )

@section( 'heading', trans( 'auth.forgot.title' ) )

@section( 'main' )

    @include( 'errors.summary' )

    @if (session('status'))
        <div class="card-panel green lighten-5">
            <i class="material-icons green-text">info_outline</i><br>
            {{ session('status') }}
        </div>
    @endif
    
    @row
        @col( s12 )
            <div class="card">
                <div class="card-content">
                    <span class="card-title">
                        <i class="material-icons">live_help</i>
                        @lang( 'auth.forgot.action_title' )
                    </span>
                    <p>@lang( 'auth.forgot.action_message' )</p>
                    @row
                    <form class="col s12" action="{{ url('/password/email') }}" method="POST">
                        {{ csrf_field() }}
                        @row
                            @col( s12 input-field )
                                {!! MForm::input( 'email' )->tlabel( 'auth.form.email' ) !!}
                            @endcol
                        @endrow
                        @row
                            @col( s12 right-align )
                                {!! MForm::submit( trans( 'auth.form.action_reset') ) !!}
                            @endcol
                        @endrow
                    </form>
                    @endrow
                </div>
            </div>
        @endcol
    @endrow

@endsection
