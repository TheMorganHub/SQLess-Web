@extends('master')
@push('css-extras')
    <link rel="stylesheet" href="{{ asset('css/codemirror/codemirror.css') }}">
@endpush
@section('content')
    <style>
        body {
            padding-left: 10px;
        }

        .box {
            display: flex;
            align-items: center;
            padding-top: 10px;
            padding-bottom: 10px;
        }

        #title {
            display: inline-block;
            font-family: Roboto-Light, serif;
            margin: 0;
        }

        @font-face {
            font-family: "Roboto-Light";
            src: url('{{ asset('fonts/Roboto-Light.ttf') }}');
        }

        .dropdown-item {
            width: auto;
        }

        .CodeMirror {
            display: inline-block;
            width: 49%;
        }

        form {
            display: inline;
        }

        #wrapper {
            padding: 10px;
        }

        .editor-title {
            display: inline-block;
            width: 49%;
            text-align: center;
        }

        #notice {
            padding: 15px 0;
            font-size: 12px;
        }

        .error {
            padding: 10px;
            color: #ff3c38;
        }

        .editor-title-responsive {
            display: none;
        }

        .loader {
            display: none;
            border: 3px solid #f3f3f3;
            border-radius: 50%;
            border-top: 3px solid #3498db;
            width: 15px;
            height: 15px;
            -webkit-animation: spin 1s linear infinite;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        @media screen and (max-width: 700px) {
            .CodeMirror {
                display: block;
                margin-top: 10px;
                width: 100%;
            }

            #wrapper {
                display: none;
            }

            .editor-title-responsive {
                display: block;
                text-align: center;
                padding: 10px;
            }

            .dropdown > button {
                display: block;
                margin: 3px;
            }

            #btn_submit {
                margin: 3px;
            }
        }
    </style>
    <div class="box">
        <a href="/" title="Go home"><img src="{{ asset('img/Maple-logo.png') }}" alt="SQLess logo" id="logo"></a>
        <h1 id="title">Maple</h1>
    </div>
    <div id="notice">Aviso importante: no ingrese datos confidenciales por este medio. Toda sentencia Maple ingresada es
        almacenada para mejorar la calidad del servicio.
    </div>
    <span class="dropdown">
    <span class="dropdown-menu" aria-labelledby="selectMenuButton">
        <a class="dropdown-item" href="javascript:selectActionEvent('simple')">Simple</a>
        <a class="dropdown-item" href="javascript:selectActionEvent('conditionals')">With conditionals</a>
        <a class="dropdown-item" href="javascript:selectActionEvent('innerjoin')">With INNER JOIN</a>
        <a class="dropdown-item" href="javascript:selectActionEvent('everything')">With everything</a>
    </span>
    <button class="btn dropdown-toggle" type="button" id="selectMenuButton" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">
        SELECT
    </button>
</span>
    <span class="dropdown">
    <span class="dropdown-menu" aria-labelledby="insertMenuButton">
        <a class="dropdown-item" href="javascript:insertActionEvent('implicit')">Implicit</a>
        <a class="dropdown-item" href="javascript:insertActionEvent('explicit')">Explicit</a>
    </span>
    <button class="btn dropdown-toggle" type="button" id="insertMenuButton" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">
        INSERT
    </button>
</span>
    <span class="dropdown">
    <span class="dropdown-menu" aria-labelledby="createMenuButton">
        <a class="dropdown-item" href="javascript:createActionEvent('create_simple')">Simple</a>
        <a class="dropdown-item"
           href="javascript:createActionEvent('create_with_nullable')">Simple with nullable column</a>
        <a class="dropdown-item" href="javascript:createActionEvent('create_with_joins')">With Join</a>
        <a class="dropdown-item" href="javascript:createActionEvent('create_with_join_and_default_value')">With Join and custom default value</a>
    </span>
    <button class="btn dropdown-toggle" type="button" id="createMenuButton" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">
        CREATE
    </button>
</span>
    <span class="dropdown">
    <span class="dropdown-menu" aria-labelledby="updateMenuButton">
        <a class="dropdown-item" href="javascript:updateActionEvent('update_one_value')">With one value</a>
        <a class="dropdown-item" href="javascript:updateActionEvent('update_two_values')">With two values</a>
        <a class="dropdown-item" href="javascript:updateActionEvent('update_with_conditionals')">With two values and a conditional</a>
    </span>
    <button class="btn dropdown-toggle" type="button" id="updateMenuButton" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">
        UPDATE
    </button>
</span>
    <span class="dropdown">
    <span class="dropdown-menu" aria-labelledby="deleteMenuButton">
        <a class="dropdown-item"
           href="javascript:deleteActionEvent('delete_with_conditionals')">Borrar con condicional</a>
        <a class="dropdown-item" href="javascript:deleteActionEvent('truncate')">Truncate</a>
    </span>
    <button class="btn dropdown-toggle" type="button" id="deleteMenuButton" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">
        DELETE
    </button>
</span>
    <span class="dropdown">
    <span class="dropdown-menu" aria-labelledby="alterTableButton">
        <a class="dropdown-item" href="javascript:alterTableActionEvent('add')">Agregar columna</a>
        <a class="dropdown-item" href="javascript:alterTableActionEvent('modify')">Modificar columna</a>
        <a class="dropdown-item" href="javascript:alterTableActionEvent('drop')">Eliminar columna</a>
    </span>
    <button class="btn dropdown-toggle" type="button" id="alterTableButton" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">
        ALTER TABLE
    </button>
</span>
    <div id='wrapper'>
        <div class="editor-title">
            Maple
        </div>
        <div class="editor-title">
            SQL
        </div>
    </div>
    <div class="editor-title-responsive">
        Maple
    </div>
    <textarea style="margin: 2px" name="txt_maple" id="maple_editor" spellcheck="false" cols="30" rows="10"
              class="form-control"></textarea>
    <div class="editor-title-responsive">
        SQL
    </div>
    <textarea style="margin: 2px" name="txt_maple" id="sql_editor" spellcheck="false" cols="30" rows="10"
              class="form-control"></textarea>
    <button class="btn btn-success" id="btn_submit">Convertir!</button>
    <div class="loader" id="loader"></div>
    <span id="error-container" class="error"></span>
    <script src="{{ asset('js/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/codemirror/codemirror.js') }}"></script>
    <script src="{{ asset('js/codemirror/active-line.js') }}"></script>
    <script src="{{ asset('js/codemirror/sql.js') }}"></script>
    <script>
        let editor = CodeMirror.fromTextArea(document.getElementById("maple_editor"), {
            lineNumbers: true,
            lineWrapping: true,
            styleActiveLine: true,
            styleActiveSelected: true,
            mode: "text/x-mysql"
        });
        let sqlEditor = CodeMirror.fromTextArea(document.getElementById("sql_editor"), {
            lineNumbers: true,
            lineWrapping: true,
            styleActiveLine: true,
            styleActiveSelected: true,
            readOnly: true,
            mode: "text/x-mysql"
        });

        function selectActionEvent(selectType) {
            switch (selectType) {
                case 'simple':
                    editor.setValue('$Personas;');
                    break;
                case 'conditionals':
                    editor.setValue("$Personas ? nombre = 'John';");
                    break;
                case 'innerjoin':
                    editor.setValue("$Personas > <> $Roles;");
                    break;
                case 'everything':
                    editor.setValue("$Personas > nombre, apellido <> $Roles ? nombre != 'John' && apellido != 'Smith';");
                    break;
            }
            sqlEditor.setValue("");
            document.getElementById("btn_submit").focus();
        }

        function insertActionEvent(insertType) {
            switch (insertType) {
                case 'implicit':
                    editor.setValue("$Personas < 70, 'Harry', 'Potter', 3;");
                    break;
                case 'explicit':
                    editor.setValue("$Personas < Nombre, Apellido < 'Ron', 'Weasley';");
                    break;
            }
            sqlEditor.setValue("");
            document.getElementById("btn_submit").focus();
        }

        function createActionEvent(createType) {
            switch (createType) {
                case 'create_simple':
                    editor.setValue("($Personas) + \n" +
                        "nombre varchar(255), \n" +
                        "apellido varchar(255);");
                    break;
                case 'create_with_nullable':
                    editor.setValue("($Personas) + \n" +
                        "nombre varchar(255), \n" +
                        "apellido varchar(255),\n" +
                        "telefono? varchar(255);");
                    break;
                case 'create_with_joins':
                    editor.setValue("($Personas) + \n" +
                        "nombre varchar(255), \n" +
                        "apellido varchar(255), \n" +
                        "telefono? varchar(255) \n" +
                        "<> $Roles;");
                    break;
                case 'create_with_join_and_default_value':
                    editor.setValue("($Personas) + \n" +
                        "nombre varchar(255), \n" +
                        "apellido varchar(255), \n" +
                        "telefono? varchar(255) \n" +
                        "<5> $Roles;");
                    break;
            }
            sqlEditor.setValue("");
            document.getElementById("btn_submit").focus();
        }

        function updateActionEvent(updateType) {
            switch (updateType) {
                case 'update_one_value':
                    editor.setValue("$Personas << Nombre << 'Harry';");
                    break;
                case 'update_two_values':
                    editor.setValue("$Personas << Estado, Dinero << 'Menor', 0;");
                    break;
                case 'update_with_conditionals':
                    editor.setValue("$Personas << Estado, Dinero << 'Menor', 0 ? Edad < 18;");
                    break;
            }
            sqlEditor.setValue("");
            document.getElementById("btn_submit").focus();
        }

        function alterTableActionEvent(alterType) {
            switch (alterType) {
                case 'add':
                    editor.setValue("($Personas) < nombre varchar(255);\n($Personas) < telefono? varchar(255);");
                    break;
                case 'modify':
                    editor.setValue("($Personas) << nombre? varchar(255);\n($Personas) << telefono int(10);");
                    break;
                case 'drop':
                    editor.setValue("($Personas) [x] telefono;");
                    break;
            }
            sqlEditor.setValue("");
            document.getElementById("btn_submit").focus();
        }

        function deleteActionEvent(deleteType) {
            switch (deleteType) {
                case 'delete_with_conditionals':
                    editor.setValue("$Personas [x] ? nombre = 'John';");
                    break;
                case 'truncate':
                    editor.setValue("$Personas [x];");
                    break;
            }
            sqlEditor.setValue("");
            document.getElementById("btn_submit").focus();
        }

        editor.focus();

        let btnSubmit = $('#btn_submit');
        let errorField = $('#error-container');
        let loader = $('#loader');
        btnSubmit.click(function (event) {
            btnSubmit.prop('disabled', true);
            loader.css('display', 'inline-block');
            errorField.text('');
            $.ajax({
                url: '/maple',
                type: 'post',
                data: {maple_statement: editor.getValue(), _token: '{{ csrf_token() }}'},
                timeout: 3000
            }).done(function (response) {
                if ('sql_from_maple' in response) {
                    sqlEditor.setValue(response['sql_from_maple']);
                } else if ('err' in response) {
                    errorField.text(response['err']);
                }
            }).fail(function (err) {
                console.log(err);
            }).always(function () {
                btnSubmit.prop('disabled', false);
                loader.css('display', 'none');
            });
        });
    </script>
@endsection