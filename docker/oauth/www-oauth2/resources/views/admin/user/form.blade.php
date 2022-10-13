{{ csrf_field() }}

@row
    @col( s10 input-field )
        {!! MForm::input( 'email' )->tlabel( 'user.model.email' )->value( $user->email ) !!}
    @endcol
    @col( s2 input-field )
        @if ( $user->id )
        {!! MForm::input( 'id' )->tlabel( 'form.model.id' )->value( $user->id )->disabled() !!}
        @endif
    @endcol
@endrow

@row
    @col( s6 input-field )
        {!! MForm::password( 'password' )->tlabel( 'user.model.password' ) !!}
    @endcol
    @col( s6 input-field )
        {!! MForm::password( 'password_confirm' )->tlabel( 'user.model.password_confirm' ) !!}
    @endcol
@endrow

@row
    @col ( s12 )
        {!! MForm::checkbox( 'is_banned' )->tlabel( 'user.edit.is_banned' )->checked( $user->is_banned ) !!}
    @endcol
@endrow

@row
    @col ( s12 )
        {!! MForm::checkbox( 'is_admin' )->tlabel( 'user.edit.roles_is' )->checked( $user->isAdmin() ) !!}
    @endcol
@endrow

@row
    @col( s12 right-align )
        {!! MForm::submit( trans( 'form.save' ) ) !!}
    @endcol
@endrow