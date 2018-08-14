@extends('master')
@section('content')
    <style>
        .container {
            margin: 0;
        }

        @font-face {
            font-family: "Roboto-Light";
            src: url('{{ asset('fonts/Roboto-Light.ttf') }}');
        }

        #title {
            display: inline-block;
            font-family: Roboto-Light, serif;
            margin: 0;
        }

        .box {
            display: flex;
            align-items: center;
            padding-top: 10px;
            padding-bottom: 10px;
        }
    </style>

    <div class="container">
        <div class="box">
            <img src="{{ asset('img/SQLess-logo.png') }}" alt="">
            <h1 id="title">SQLess</h1>
        </div>
        <h2>Releases</h2>
        <ul>
            <li><a href="https://drive.google.com/open?id=1BX0OMa2thizXeEE5gZ_nLzIDLketYIAP">SQLess JAR</a></li>
            <li><a href="https://drive.google.com/open?id=1RJDKQIS2FoZaKFq-ERp6KcychWbsqZnS">SQLess APK</a></li>
        </ul>
        <h2>Maple</h2>
        <ul>
            <li><a href="https://sqless.ddns.net/maple/docs">Docs</a></li>
            {{--se accede a la ruta mediante named routes--}}
            <li><a href="{{ route('trial') }}">Try it!</a></li>
        </ul>
        <h2>GitHub</h2>
        <ul>
            <li><a href="https://github.com/TheMorganHub/SQLess">SQLess</a></li>
            <li><a href="https://github.com/TheMorganHub/SQLess-Mobile">SQLess mobile</a></li>
        </ul>
    </div>
@endsection