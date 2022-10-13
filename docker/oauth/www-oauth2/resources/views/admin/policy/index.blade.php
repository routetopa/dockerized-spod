@extends( 'layouts.master' )

@section( 'title', trans( 'policy.index.meta_title' ) )

@section( 'heading', trans( 'policy.index.title' ) )

@section( 'main' )

    <div class="container">
    @row
        @col( s12 right-align )
            <a class="btn-floating btn-large waves-effect waves-light" href="{{ action( 'Admin\PolicyController@create' ) }}" title="@lang( 'policy.create.title' )"><i class="material-icons">add</i></a>
        @endcol
    @endrow
    </div>

    @row

        @col( s12 )

            <table class="striped">
                <thead>
                    <tr>
                        <th data-field="title">@lang( 'policy.model.title' )</th>
                        <th>@lang( 'policy.model.uri' ) / @lang( 'policy.model.content' )</th>
                        <th data-field="created_at">@lang( 'form.model.created_at' )</th>
                        <th data-field="updated_at">@lang( 'form.model.updated_at' )</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach( $policies as $policy )
                    <tr>
                        <td>{{ $policy->title }}</td>
                        <td>
                            @if ( $policy->uri )
                                <i class="material-icons left">link</i>
                                {{ $policy->uri }}
                            @else
                                <i class="material-icons left">text_fields</i>
                                {{ str_limit( strip_tags( $policy->content ), 60 ) }}
                            @endif
                        <td>{{ $policy->created_at }}</td>
                        <td>{{ $policy->updated_at }}</td>
                        <td>
                            <a href="{{ action( 'Admin\PolicyController@edit', [ 'policy' => $policy ] ) }}" title="@lang( 'policy.edit.title' )"><i class="material-icons">mode edit</i></a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

        @endcol

    @endrow

@endsection