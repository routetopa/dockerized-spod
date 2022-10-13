<li>
    <a class="dropdown-button" href="#!" data-activates="dropdown-lang">
        <i class="material-icons left">translate</i>
        {{ App::getLocale() }}
    </a>
    <ul id="dropdown-lang" class="dropdown-content">
        @foreach( [ 'en', 'it' ] as $lang )
            <li>
                <a href="{{ url( '/lang', [ 'lang' => $lang ] ) }}" onclick="event.preventDefault(); document.getElementById('lang-switch-{{ $lang }}').submit();">
                    <i class="material-icons left">{{ App::isLocale( $lang ) ? 'radio_button_checked' : 'radio_button_unchecked'  }}</i>
                    {{ $lang }}
                </a>
                <form id="lang-switch-{{ $lang }}" action="{{ url( '/lang', [ 'lang' => $lang ] ) }}" method="post" style="display: none;">
                    <input type="hidden" name="backuri" value="{{ Request::path() }}">
                    {{ csrf_field() }}
                </form>
            </li>
        @endforeach
    </ul>
</li>