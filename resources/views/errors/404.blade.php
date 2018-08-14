@extends('master', ['title' => "Página no encontrada"])
@section('content')
    <style>
        body {
            background: #1b1e21 url('{{ asset('img/404_background.png') }}') center no-repeat;
            color: #fff;
            padding: 0;
        }

        .center {
            display: block;
            margin-left: auto;
            margin-right: auto;
            width: 50%;
            max-width: 700px;
        }

        #volver {
            padding: 15px;
            background-color: #556080;
            text-align: center;
        }

        #btn-volver {
            margin-top: 10px;
        }
    </style>
    <img src="{{ asset('img/SQLess_logo_white_background.png') }}" alt="SQLess logo with white background"
         class="center"
         id="sqless_logo_404">
    <div id="volver">
        La página no pudo ser encontrada.
        <form action="https://sqless.ddns.net/">
            <input id="btn-volver" type="submit" value="Click aquí para volver a home" class="btn btn-dark"/>
        </form>
    </div>
@endsection
