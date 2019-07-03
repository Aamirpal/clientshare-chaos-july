@extends(session()->get('layout'))

@section('content')
Welcome <?= session()->get('key')[0]->first_name; ?> <?= session()->get('key')[0]->last_name; ?>
@endsection