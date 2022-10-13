@extends( 'layouts.master' )

@section( 'title', trans( 'errors.csrf.title' ) )

@section( 'heading', trans( 'errors.csrf.meta_title' ) )

@section( 'main' )

    @include( 'errors.summary', [ 'errors' => new \Illuminate\Support\MessageBag( [ trans( 'errors.csrf.message' ) ] ) ] )

@endsection