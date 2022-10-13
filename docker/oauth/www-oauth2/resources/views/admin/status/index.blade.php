@extends( 'layouts.master' )

@section( 'title', trans( 'status.meta_title' ) )

@section( 'heading', trans( 'status.title' ) )

@section( 'main' )

    @foreach( $sections as $section_key => $section )
        <h2>@lang( $section[ 'title' ] )</h2>

        @row
            @foreach( $section[ 'items' ] as $item_key => $item_value )
                @col( s12 )
                    <div class="input-field col s12">
                        <input value="{{ $item_value }}" id="first_name" type="text" readonly>
                        <label for="first_name">@lang( $item_key )</label>
                    </div>
                @endcol
            @endforeach
        @endrow
    @endforeach

@endsection