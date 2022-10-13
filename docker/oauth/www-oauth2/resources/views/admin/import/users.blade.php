@extends( 'layouts.master' )

@section( 'title', trans( 'import.users.title' ) )

@section( 'heading', trans( 'import.users.meta_title' ) )

@section( 'main' )

    @include( 'errors.summary' )

    @if ( $users )
        <div class="card-panel green lighten-5">
            <i class="material-icons green-text">info</i><br>
            @lang( 'import.users.message_success' )
            <ol>
                @foreach( $users as $email )
                    <li>{{ $email }}</li>
                @endforeach
            </ol>
        </div>
    @endif

    <form method="post" enctype="multipart/form-data">
        {!! csrf_field() !!}

        @row
            @col( s12 )
                @lang( 'import.users.description' )
            @endcol
        @endrow

        @row
            @col( s12 )
                <div class="file-field input-field">
                    <div class="btn">
                        <span>@lang( 'import.users.file_button' )</span>
                        <input type="file" name="csv">
                    </div>
                    <div class="file-path-wrapper">
                        <input class="file-path validate" type="text">
                    </div>
                </div>
            @endcol
        @endrow

        @row
            @col( s12 )
                {!! MForm::checkbox( 'option_only_verified' )
                    ->label( trans( 'import.users.option_only_verified' ) ) !!}
            @endcol
        @endrow

        @row
            @col( s12 right-align )
                {!! MForm::submit( trans( 'import.users.action_submit' ) ) !!}
            @endcol
        @endrow

    </form>

@endsection