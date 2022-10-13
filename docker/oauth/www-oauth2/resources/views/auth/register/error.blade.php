@extends( 'layouts.master' )

@section( 'heading', 'New user' )

@section( 'heading', 'New user' )

@section( 'main' )

    @row
        @col( s12 )
        <div class="card">
            <div class="card-content">
                <span class="card-title">
                    <i class="material-icons">group</i>
                    {!! trans('laravel-user-verification::user-verification.verification_error_header') !!}
                </span>

                <p>
                    <strong>{!! trans('laravel-user-verification::user-verification.verification_error_message') !!}</strong>
                </p>

                <p>
                    <a href="{{ url( '/' ) }}" class="waves-effect waves-light btn">
                        <i class="material-icons left">navigate_before</i>
                        {!! trans('laravel-user-verification::user-verification.verification_error_back_button') !!}
                    </a>
                </p>
            </div>
        </div>
        @endcol
    @endrow

@endsection
