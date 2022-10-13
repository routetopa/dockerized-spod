<div id="logout_modal" class="modal">
    <div class="modal-content">
        <h4>@lang( 'auth.logout.action_title' )</h4>
        <p>@lang( 'auth.logout.action_message' )</p>
    </div>
    <div class="modal-footer">
        <form method="POST" action="{{ route('logout') }}">
            {{ csrf_field() }}
            <button type="submit" class="modal-action modal-close waves-effect waves-green btn-flat">@lang( 'auth.logout.button_logout' )</button>
            <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat">@lang( 'auth.logout.button_cancel' )</a>
        </form>
    </div>
</div>