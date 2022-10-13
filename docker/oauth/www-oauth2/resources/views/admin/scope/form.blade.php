{{ csrf_field() }}

@row
    @col( s12 input-field )
        {!! MForm::input( 'scope' )->tlabel( 'scope.model.scope' )->value( $scope->scope ) !!}
    @endcol
@endrow

@row
    @col ( s12 )
        {!! MForm::checkbox( 'is_default' )->tlabel( 'scope.model.is_default' )->checked( $scope->is_default ) !!}
    @endcol
@endrow

@row
    @col( s12 right-align )
        {!! MForm::submit( trans( 'form.save' ) ) !!}
    @endcol
@endrow