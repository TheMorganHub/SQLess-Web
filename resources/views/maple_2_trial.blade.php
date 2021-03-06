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
            font-family: monospace;
            color: #b90d0d;
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
        <h1 id="title">Maple 2</h1><sup>Alpha</sup>
    </div>
    <span class="dropdown">
    <span class="dropdown-menu" aria-labelledby="selectMenuButton">
        <a class="dropdown-item" href="javascript:selectActionEvent('simple')">Simple</a>
        <a class="dropdown-item" href="javascript:selectActionEvent('conditionals')">With conditionals</a>
        <a class="dropdown-item" href="javascript:selectActionEvent('innerjoin')">With INNER JOIN</a>
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
        <a class="dropdown-item" href="javascript:createActionEvent('create_with_foreign_key')">With Foreign key (default)</a>
        <a class="dropdown-item" href="javascript:createActionEvent('create_with_custom_fk_mode')">With Foreign key (custom mode)</a>
        <a class="dropdown-item" href="javascript:createActionEvent('create_with_default_value')">With default value</a>
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
        <button class="btn dropdown-toggle" type="button" id="deleteMenuButton" data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false">
            DELETE
        </button>
    </span>
    <span class="dropdown">
        <span class="dropdown-menu" aria-labelledby="procedureButton">
            <a class="dropdown-item"
               href="javascript:procedureActionEvent('procedure_conditionals')">Con condicionales</a>
        </span>
        <button class="btn dropdown-toggle" type="button" id="alterTableButton" data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false">
            PROCEDURES
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
    <button class="btn btn-success" id="btn_submit">Convert!</button>
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
                    editor.setValue('personas;');
                    break;
                case 'conditionals':
                    editor.setValue("personas -> ? nombre = 'John';");
                    break;
                case 'innerjoin':
                    editor.setValue("personas p -> <> roles r ON p.id_roles = r.id;");
                    break;
                //TODO: subqueries, implicit joins
            }
            sqlEditor.setValue("");
            document.getElementById("btn_submit").focus();
        }

        function insertActionEvent(insertType) {
            switch (insertType) {
                case 'implicit':
                    editor.setValue("personas <- (70, 'Harry', 'Potter', 3);");
                    break;
                case 'explicit':
                    editor.setValue("personas(nombre, apellido) <- ('Ron', 'Weasley');");
                    break;
            }
            sqlEditor.setValue("");
            document.getElementById("btn_submit").focus();
        }

        function createActionEvent(createType) {
            switch (createType) {
                case 'create_simple':
                    editor.setValue("personas( \n" +
                        "nombre varchar, \n" +
                        "apellido varchar\n);");
                    break;
                case 'create_with_nullable':
                    editor.setValue("personas( \n" +
                        "nombre varchar, \n" +
                        "apellido varchar,\n" +
                        "?telefono varchar\n);");
                    break;
                case 'create_with_foreign_key':
                    editor.setValue("personas(\n" +
                        "nombre varchar,\n" +
                        "apellido varchar,\n" +
                        "telefono varchar,\n" +
                        "ciudad\n" +
                        ");");
                    break;
                case 'create_with_custom_fk_mode':
                    editor.setValue("personas(\n" +
                        "nombre varchar,\n" +
                        "apellido varchar,\n" +
                        "telefono varchar,\n" +
                        "ciudad (on update cascade on delete cascade)\n" +
                        ");");
                    break;
                case 'create_with_default_value':
                    editor.setValue(
                        "personas( \n" +
                        "nombre varchar, \n" +
                        "apellido varchar,\n" +
                        "telefono varchar '11645455555'\n" +
                        ");"
                    );
                    break;
            }
            sqlEditor.setValue("");
            document.getElementById("btn_submit").focus();
        }

        function updateActionEvent(updateType) {
            switch (updateType) {
                case 'update_one_value':
                    editor.setValue("personas(nombre) <<- 'Harry';");
                    break;
                case 'update_two_values':
                    editor.setValue("personas(estado, dinero) <<- 'Menor', 0;");
                    break;
                case 'update_with_conditionals':
                    editor.setValue("personas(estado, dinero) <<- 'Menor', 0 ? edad < 18;");
                    break;
            }
            sqlEditor.setValue("");
            document.getElementById("btn_submit").focus();
        }

        function procedureActionEvent(procedureType) {
            switch (procedureType) {
                case 'procedure_conditionals':
                    editor.setValue("procedure checkNota(int nota) {\n" +
                        "\tif (nota >= 4 and nota <= 10) {\n" +
                        "    \tprint 'Aprobado';\n" +
                        "    } elseif (nota >= 1) {\n" +
                        "    \tprint 'Reprobado';\n" +
                        "    } else {\n" +
                        "    \tprint 'Nota inválida';\n" +
                        "    }\n" +
                        "}");
                    break;
            }
            sqlEditor.setValue("");
            document.getElementById("btn_submit").focus();
        }

        function alterTableActionEvent(alterType) {
            // switch (alterType) {
            //     case 'add':
            //         editor.setValue("($Personas) < nombre varchar(255);\n($Personas) < telefono? varchar(255);");
            //         break;
            //     case 'modify':
            //         editor.setValue("($Personas) << nombre? varchar(255);\n($Personas) << telefono int(10);");
            //         break;
            //     case 'drop':
            //         editor.setValue("($Personas) [x] telefono;");
            //         break;
            // }
            sqlEditor.setValue("");
            document.getElementById("btn_submit").focus();
        }

        function deleteActionEvent(deleteType) {
            switch (deleteType) {
                case 'delete_with_conditionals':
                    editor.setValue("personas <- ? id = 5");
                    break;
                case 'truncate':
                    editor.setValue("personas <-;");
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
                url: '/maple2',
                type: 'post',
                data: {maple_statement: editor.getValue(), _token: '{{ csrf_token() }}'},
                timeout: 17000
            }).done(function (response) {
                if ('sql_from_maple' in response) {
                    sqlEditor.setValue(response['sql_from_maple'].trim());
                } else if ('err' in response) {
                    errorField.text(response['err']);
                }
            }).fail(function (jqXHR, textStatus) {
                if (textStatus === 'timeout') {
                    errorField.text("Request to Maple web service timed out. Please try again.");
                }
            }).always(function () {
                btnSubmit.prop('disabled', false);
                loader.css('display', 'none');
            });
        });
    </script>
@endsection