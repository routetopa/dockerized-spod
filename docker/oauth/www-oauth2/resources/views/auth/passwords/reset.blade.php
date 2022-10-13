@extends( 'layouts.master' )

@section( 'title', trans( 'auth.reset.meta_title' ) )

@section( 'heading', trans( 'auth.reset.title' ) )

@section( 'main' )

    @include( 'errors.summary' )

    @row
        @col( s12 )
            <div class="card">
                <div class="card-content">
                    <span class="card-title">
                        <i class="material-icons">live_help</i>
                        @lang( 'auth.reset.action_title' )
                    </span>

                    <p>@lang( 'auth.reset.action_message' )</p>

                    @row
                    <form class="col s12" action="{{ url( '/password/reset' ) }}" method="POST">
                        {{ csrf_field() }}
                        <input type="hidden" name="token" value="{{ $token }}">

                        @row
                            @col( s12 input-field )
                                {!! MForm::input( 'email' )->tlabel( 'auth.form.email' )->value( $email or old('email') ) !!}
                            @endcol
                        @endrow

                        @row
                            @col( s12 input-field )
                                {!! MForm::password( 'password' )->tlabel( 'auth.form.new_password' ) !!}
                            @endcol
                        @endrow

                        @row
                            @col( s12 input-field )
                                {!! MForm::password( 'password_confirmation' )->tlabel( 'auth.form.confirm_password' ) !!}
                            @endcol
                        @endrow

                        @row
                            @col( s12 right-align )
                                {!! MForm::submit( trans( 'auth.form.action_reset' ) ) !!}
                            @endcol
                        @endrow
                    </form>
                    @endrow
                </div>
            </div>
        @endcol

    @endrow

@endsection
