<li>
    <a href="{{ action( 'Admin\UserController@index' ) }}">
        <i class="material-icons left">people</i>
        @lang( 'menu.admin.user' )
    </a>
</li>
<li>
    <a href="{{ action( 'Admin\PolicyController@index' ) }}">
        <i class="material-icons left">description</i>
        @lang( 'menu.admin.policy' )
    </a>
</li>
    </a>
</li>

<li class="divider"></li>

<li>
    <a href="{{ action( 'Admin\ClientController@index' ) }}">
        <i class="material-icons left">https</i>
        @lang( 'menu.admin.client' )
    </a>
</li>
<li>
    <a href="{{ action( 'Admin\ScopeController@index' ) }}">
        <i class="material-icons left">https</i>
        @lang( 'menu.admin.scope' )
    </a>
</li>

<li class="divider"></li>

<li>
    <a href="{{ action( 'Admin\SettingController@edit' ) }}">
        <i class="material-icons left">build</i>
        @lang( 'menu.admin.setting' )
    </a>
</li>

<li class="divider"></li>

<li>
    <a href="{{ action( 'Admin\StatusController@index' ) }}">
        <i class="material-icons left">info</i>
        @lang( 'menu.admin.status' )
    </a>
</li>