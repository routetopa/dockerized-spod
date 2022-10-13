@if ( $errors->count() > 0 )
    <div class="card-panel red lighten-5">
        <i class="material-icons red-text">warning</i><br>
        @foreach( $errors->all() as $e )
            {{ $e }}<br>
        @endforeach
    </div>
@endif