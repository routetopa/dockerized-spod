{{ csrf_field() }}

@row
    @col( s12 input-field )
        {!! MForm::input( 'title' )->tlabel( 'policy.model.title' )->value( $policy->title ) !!}
    @endcol
@endrow

@row
    @col ( s12 input-field )
        {!! MForm::input( 'uri' )->tlabel( 'policy.model.uri' )->value( $policy->uri ) !!}
    @endcol
@endrow

@row
    @col ( s12 input-field )
        {!! MForm::input( 'content' )->tlabel( 'policy.model.content' )->value( $policy->content ) !!}
    @endcol
@endrow

@row
    @col ( s12 )
        {!! MForm::checkbox( 'is_mandatory' )->tlabel( 'policy.model.is_mandatory' )->checked( $policy->is_mandatory ) !!}
    @endcol
@endrow

@row
    @col( s12 right-align )
        {!! MForm::submit( trans( 'form.save' ) ) !!}
    @endcol
@endrow