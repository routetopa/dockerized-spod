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
                    <p>@lang( 'auth.register.action_message' )</p>
                    @row
                    <form class="col s12" action="{{ url('/register') }}" method="POST">
                        {{ csrf_field() }}
                        @row
                            @col( s12 input-field )
                                {!! MForm::input( 'email' )->tlabel( 'auth.form.email' )->value( old( 'email' ) ) !!}
                            @endcol
                            @col( s12 input-field )
                                {!! MForm::password( 'password' )->tlabel( 'auth.form.new_password' ) !!}
                            @endcol
                            @col( s12 input-field )
                                {!! MForm::password( 'password_confirmation' )->tlabel( 'auth.form.confirm_password' ) !!}
                            @endcol
                        @endrow

                        @row
                            @foreach( $policies as $policy )
                                @col( s12 input-field )
                                    <?php
                                        $uri = $policy->uri ?: '#';
                                        $label_key = $policy->is_mandatory ? 'auth.register.accept_mandatory' : 'auth.register.accept';
                                    ?>
                                    {!! MForm::checkbox( "policy_{$policy->id}" )
                                        ->label( trans( $label_key, [ 'url' => $uri, 'title' => $policy->title ] ) )
                                        ->checked( old( "policy_{$policy->id}" ) ) !!}
                                @endcol
                            @endforeach
                        @endrow

                        @row( right-align )
                            @col( s12 )
                                {!! MForm::submit( trans( 'auth.form.action_register' ) ) !!}
                            @endcol
                        @endrow
                    </form>
                    @endrow
                </div>
            </div>
        @endcol
    @endrow

@endsection
