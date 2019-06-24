@extends('master')
@push('css-extras')
    <link rel="stylesheet" href="{{ asset('css/bootstrap-toc.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/prism.css') }}">
@endpush
@section('body-params')
    data-spy="scroll" data-target="#toc"
@endsection
@section('content')
    <style>
        :not(pre) > code {
            background-color: #f0f2f1;
            padding: 1px 5px;
            color: #f4645f;
            border-radius: 3px;
            box-shadow: 0 1px 1px rgba(0, 0, 0, .125);
        }

        :not(pre) > code[class*="language-"], pre[class*="language-"] {
            background-color: #fcfcfc;
        }

        .arrow {
            font-size: 25px;
            text-align: center;
        }

        .code-example {
            background-color: #f0f2f1;
            border-radius: 3px;
            box-shadow: 0 1px 1px rgba(0, 0, 0, .125);
            margin-top: 1rem;
            margin-bottom: 1rem;
        }

        p {
            font-size: 14px;
        }

        blockquote {
            background-color: #f4645f;
            color: #fff;
            font-size: 14px;
            border-radius: 3px;
            padding: 10px 15px;
            margin: 10px 0 20px;
        }

        .error {
            background-color: #ffacb4 !important;
            color: #902426 !important;
            text-shadow: none !important;
        }

        .box {
            display: flex;
            align-items: center;
            padding-top: 10px;
            padding-bottom: 10px;
        }

        #title {
            font-size: 2rem;
        }
    </style>
    <?php
    function echo_code_example($maple, $sql) {
        echo '<div class="code-example">
<pre>
<code class="language-sql">' . $maple . '</code>
</pre>
<div class="arrow">↓</div>
<pre>
<code class="language-sql">' . $sql . '</code>
</pre>
</div>';
    }
    function echo_invalid_code_conversion($maple) {
        echo '<div class="code-example">
<pre>
<code class="language-sql">' . $maple . '</code>
</pre>
<div class="arrow">↓</div>
<pre class="error">
<code class="language-sql">' . "Conversión inválida" . '</code>
</pre>
</div>';
    }
    function echo_note($txt) {
        echo '<blockquote><strong>Nota:</strong> ' . $txt . '</blockquote>';
    }
    ?>
    <div class="container">
        <div class="row">
            <div class="col-sm-3">
                <nav id="toc" data-toggle="toc" class="sticky-top">
                    <div class="box">
                        <a href="/" title="Go home"><img src="/img/Maple-logo.png"
                                                         alt="SQLess logo" id="logo"></a>
                        <div id="title">Maple</div>
                    </div>
                </nav>
            </div>
            <div class="col-sm-9">
                <h1>Manual de usuario</h1>
                <h2>Referencias a tablas</h2>
                <p>
                    En Maple, toda referencia a tabla deberá ser procedida por el símbolo <code>$</code>. Esto
                    indica a
                    Maple que lo
                    que le sigue a este símbolo es el nombre de una tabla.
                    Por ejemplo, si tenemos una tabla Persona, cuando queramos hacer referencia a ella desde Maple, la
                    llamaremos <code>$Personas</code>.
                </p>
                <h2>Visualización de datos</h2>
                <p>
                    Operador: <code>></code><br>
                    Al saber referenciar tablas, ya podemos utilizar esas referencias para mostrar datos, haciendo uso
                    del operador <code>></code>.
                </p>
                <h3>Tipos de visualización</h3>
                <h4>Implícito</h4>
                <p>
                    Esta visualización sólo puede utilizarse sobre una sola tabla. En esta
                    visualización, no es necesario escribir el operador <code>></code>, ya que al darle a Maple una
                    referencia sobre
                    una tabla, Maple entiende que lo que deseamos hacer es una visualización de ella.
                </p>
                <?php echo_code_example('$Personas', 'SELECT * FROM `Personas`;') ?>
                <h4>Explícito</h4>
                <p>
                    Este tipo de visualización será utilizada si deseamos filtrar por columnas y/o generar uniones.
                    Para especificar columnas, pondremos los nombres de cada una de ellas después del operador
                    <code>></code>.
                    En este caso, es obligatorio utilizar el operador.
                </p>
                <?php
                echo_code_example('$Personas >', 'SELECT * FROM `Personas`;');
                echo_code_example('$Personas > nombre, apellido', 'SELECT `nombre`, `apellido` FROM `Personas`;');
                echo_code_example('$Personas > nombre, apellido, SUM(edad)', 'SELECT `nombre`, `apellido`, SUM(edad) FROM `Personas`;');
                ?>
                <blockquote>
                    <strong>Nota:</strong> no agregar <code>``</code> para especificar los nombres de las columnas.
                    Maple agregará estas comillas de forma automática al momento de convertir
                    la sentencia a SQL, salvo que la columna tenga una función o forme parte de un condicional.
                </blockquote>
                <h3>Condicionales</h3>
                <p>
                    Operador: <code>?</code><br>
                    En una sentencia Maple de visualización, podemos escribir condicionales con el fin de filtrar
                    resultados. Es
                    posible utilizar condicionales con ambos tipos de visualización, el implícito y explícito.
                    La sintaxis de los condicionales es igual a la de cualquier condicional MySQL. Maple acepta
                    conectores lógicos <code class="language-sql">&&</code>/<code class="language-sql">AND</code>
                    , <code class="language-sql">||</code>/<code class="language-sql">OR</code>,
                    e incluso <code class="language-sql">LIKE</code>.
                </p>
                <?php
                echo_code_example('$Personas ? nombre = \'John\'', 'SELECT * FROM `Personas` WHERE nombre = \'John\';');
                echo_code_example('$Personas > nombre, apellido ? edad > 18', 'SELECT `nombre`, `apellido` FROM `Personas` WHERE edad > 18;');
                echo_code_example('$Personas > nombre, apellido ? nombre LIKE \'%on\'', 'SELECT `nombre`, `apellido` FROM `Personas` WHERE nombre LIKE \'%on\';');
                ?>
                <blockquote>
                    <strong>Nota:</strong> en la versión actual de Maple, las columnas que se encuentren en los
                    condicionales no serán envueltas con <code>``</code>.
                </blockquote>
                <h3>Orden y agrupamiento</h3>
                <p>
                    Al utilizar la visualización explícita, podemos hacer uso de agrupamiento y ordenamiento. Esto se
                    logra
                    especificando columnas, y utilizando los operadores <code>*</code> y <code>^</code> respectivamente.
                </p>
                <?php
                echo_code_example('$Personas > ^nombre', 'SELECT `nombre` FROM `Personas` ORDER BY 1;');
                echo_code_example('$Personas > ^nombre, *edad', 'SELECT `nombre`, `edad` FROM `Personas` GROUP BY 2 ORDER BY 1;');
                echo_code_example('$Personas > ^*nombre, edad', 'SELECT `nombre`, `edad` FROM `Personas` GROUP BY 1 ORDER BY 1;');
                echo_note('Maple no soporta agrupar u ordenar por varias columnas a la vez. Esto
                    quiere decir, que no es posible marcar dos columnas con <code>^</code>. Es sólo una columna por
                    sentencia. Por ejemplo, al ejecutar <code>$Personas > ^nombre, ^apellido</code> obtenemos
                    <code class="language-sql">SELECT `nombre`, ^`apellido` FROM `Personas` ORDER BY 1;</code>. Esto es una sentencia SQL inválida.');
                ?>
                <h3>Uniones entre tablas</h3>
                <p>
                    Operador: <code><></code><br>
                    En las sentencias de visualización explícitas, es posible especificar tablas adicionales, las cuales
                    se unirán
                    a la consulta original en base a un <code class="language-sql">INNER JOIN</code> en SQL. Maple tiene
                    dos
                    tipos de
                    uniones.
                </p>
                <h4>Implícita</h4>
                <p>
                    En la unión implícita, Maple "asume" el nombre de las columnas a relacionar y las incluye
                    automáticamente en la consulta resultante. Para que este tipo de unión funcione, es necesario
                    que se respete la nomenclatura de nombres <code>tabla1.id_tabla2 = tabla2.id</code>.
                </p>
                <?php
                echo_code_example('$Personas > <> $Roles', 'SELECT * FROM `Personas` INNER JOIN `Roles` ON Personas.id_Roles = Roles.id;');
                echo_code_example('$Personas > <> $Ciudades <> $Paises', 'SELECT * FROM `Personas` INNER JOIN `Ciudades` ON Personas.id_Ciudades = Ciudades.id INNER JOIN `Paises` ON Ciudades.id_Paises = Paises.id;');
                ?>
                <p>
                    Esto quiere decir que, si utilizamos una unión implícita y nuestras tablas <strong>no</strong>
                    respetan esta
                    nomenclatura, la consulta SQL resultante no funcionará al ejecutarse contra la base de datos.
                </p>
                <h4>Explícita</h4>
                <p>
                    En este tipo de unión, le especificamos a Maple sobre qué columnas vamos a llevar a cabo la
                    unión. Es necesario utilizar este tipo si no deseamos seguir la nomenclatura descripta
                    anteriormente.
                </p>
                <?php echo_code_example('$Personas > ' . htmlspecialchars("<role_id = id_roles>") . ' $Roles', 'SELECT * FROM `Personas` INNER JOIN `Roles` ON Personas.role_id = Roles.id_roles;') ?>
                <p>
                    Como se puede ver, la unión se llevará a cabo entre la columna <code>role_id</code> en la tabla
                    <code>Personas</code> y <code>id_roles</code> en la tabla <code>Roles</code>.
                </p>
                <h4>Uso de condicionales</h4>
                <p>
                    Al ser la unión una extensión de la visualización explícita, es posible especificar condicionales.
                </p>
                <?php echo_code_example('$Personas > <> $Roles ? role_desc = \'admin\'', 'SELECT * FROM `Personas` INNER JOIN `Roles` ON Personas.id_Roles = Roles.id WHERE role_desc = \'admin\';') ?>
                <h3>Funciones nativas de SQL</h3>
                <p>
                    Maple es compatible con funciones nativas de SQL. Si la función SQL dada actúa sobre el nombre de
                    una columna, la visualización deberá ser explícita. Si utilizamos alguna función SQL que actúe sobre
                    alguno
                    de los valores en los condicionales, la visualización podrá ser implícita o explícita.
                </p>
                <?php
                echo_code_example('$Producto > AVG(precio), SUM(stock)', 'SELECT AVG(precio), SUM(stock) FROM `Producto`;');
                echo_code_example('$Producto > AVG(precio), SUM(stock) <> $Proveedor', 'SELECT AVG(precio), SUM(stock) FROM `Producto` INNER JOIN `Proveedor` ON Producto.id_Proveedor = Proveedor.id;');
                echo_code_example('$Producto > AVG(precio), SUM(stock) <> $Proveedor ? nombre_proveedor != \'Microsoft\'', 'SELECT AVG(precio), SUM(stock) FROM `Producto` INNER JOIN `Proveedor` ON Producto.id_Proveedor = Proveedor.id WHERE nombre_proveedor <> \'Microsoft\';');
                echo_note('Maple soporta funciones anidadas.');
                ?>
                <h3>Limitar el número de resultados</h3>
                <p>
                    Operador: <code>[]</code><br>
                    Maple permite limitar la cantidad de resultados que serán devueltos por la consulta generada
                    haciendo uso de la palabra clave <code class="language-sql">LIMIT</code> de MySQL. Para hacer uso de
                    esta
                    funcionalidad,
                    envolveremos un número entero con corchetes. Es posible utilizar <code>[]</code> con cualquier tipo
                    de
                    visualización, incluyendo con uniones, condicionales, explícitas e implícitas.
                </p>
                <?php
                echo_code_example('$Personas [1000]', 'SELECT * FROM `Personas` LIMIT 1000;');
                echo_code_example('$Personas > id, nombre ? nombre = \'John\' [1000]', 'SELECT `id`, `nombre` FROM `Personas` WHERE nombre = \'John\' LIMIT 1000;');
                ?>
                <p>
                    Maple también soporta limitación por rangos. Esto se logra especificando el rango entre los
                    corchetes.
                </p>
                <?php
                echo_code_example('$Personas [1000,2000];', 'SELECT * FROM `Personas` LIMIT 1000,2000;');
                ?>
                <h2>Ingreso de datos</h2>
                <p>
                    Operador: <code><</code><br>
                    Maple ofrece dos maneras de ingresar datos a una tabla. La manera implícita y la explícita. Al igual
                    que en las visualizaciones, la tabla que vamos a referenciar debe ser precedida por un signo
                    <code>$</code>.
                </p>
                <h3>Tipos de ingreso</h3>
                <h4>Implícito</h4>
                <p>
                    En este tipo de ingreso, no se especifican las columnas en las cuales se ingresarán datos, sino que
                    Maple asume que los datos ingresados son compatibles y en orden con las columnas en la tabla.
                </p>
                <?php echo_code_example('$Personas < 1, \'Harry\', \'Potter\';', "INSERT INTO `Personas` VALUES(1, 'Harry', 'Potter');") ?>
                <h4>Explícito</h4>
                <p>
                    En la mayoría de los casos, por temas de seguridad e integridad de los datos, es recomendable hacer
                    uso de esta inserción. A diferencia del implícito, aquí le damos a Maple las columnas en las cuales
                    se
                    insertarán los valores, en orden respectivo.
                </p>
                <?php
                echo_code_example('$Personas < Nombre, Apellido < \'Ron\', \'Weasley\';', "INSERT INTO `Personas`(`Nombre`, `Apellido`) VALUES('Ron', 'Weasley');");
                echo_note('Para que la sentencia se ejecute contra el motor de base de datos de manera exitosa, es necesario
                    que las otras columnas en la tabla tengan valores predeterminados o sean auto incrementables.');
                ?>
                <h2>Edición de datos</h2>
                <p>
                    Operador: <code><<</code><br>
                    Para llevar a cabo la edición de datos, tomaremos una referencia de tabla, elegiremos la columna en
                    la cual se editará el dato, y opcionalmente, le daremos un condicional.
                </p>
                <?php
                echo_code_example('$Facturas << Estado << \'Pagada\';', "UPDATE `Facturas` SET `Estado` = 'Pagada';");
                echo_code_example('$Personas << Grupo << \'Adulto\' ? edad > 18', "UPDATE `Personas` SET `Grupo` = 'Adulto' WHERE edad > 18;")
                ?>
                <p>
                    Si precisamos editar datos en más de una columna a la vez, usaremos la siguiente sintaxis. Los datos
                    serán ingresados a sus respectivas columnas.
                </p>
                <?php
                echo_code_example('$Personas << Grupo, Ingreso_Permitido << \'Adulto\', \'Sí\' ? edad > 18', "UPDATE `Personas` SET `Grupo` = 'Adulto', `Ingreso_Permitido` = 'Sí' WHERE edad > 18;");
                ?>
                <h2>Modificación del esquema de base de datos</h2>
                <p>
                    Maple ofrece funcionalidad y facilidades para la alteración del esquema de base de datos. Esto
                    incluye la creación de tablas, edición de definición, inserción, y eliminación de columnas en
                    tablas.<br>
                    Para que Maple sepa que haremos uso de operaciones que modifiquen el esquema, deberemos envolver
                    toda referencia a tabla a modificar en una serie de paréntesis.<br>
                    Por ejemplo, si queremos alterar cualquier factor en la estructura de una tabla Personas,
                    empezaremos nuestra sentencia con
                    <code>($Personas)</code>.
                </p>
                <h2>Creación de tablas</h2>
                <p>
                    Operador: <code>+</code><br>
                    Maple nos permite crear tablas haciendo uso de una sintaxis simple y fácil de recordar. La creación
                    de tablas en Maple soporta la creación de claves primarias por default, uniones entre tablas, y
                    otras funciones que ya exploraremos.<br>
                    Al crear una tabla, Maple automáticamente asume que esa tabla llevará una clave primaria. Esto
                    significa que no será necesario especificarle a Maple que existirá una columna de nombre
                    <code>id</code>, ya que
                    esta misma ya vendrá incluida en la sintaxis generada de forma predeterminada. Incluso si no
                    especificamos ninguna columna extra, Maple incluirá una columna <code>id</code> automáticamente,
                    siguiendo las buenas prácticas de SQL.
                </p>
                <?php echo_code_example('($Personas) +', "CREATE TABLE `Personas` (
`id` int NOT NULL AUTO_INCREMENT,
PRIMARY KEY (`id`)
) ENGINE=InnoDB ROW_FORMAT=COMPACT;") ?>
                <p>
                    Como podemos ver, la sentencia SQL resultante ya incluye una columna <code>id</code>,
                    automáticamente de tipo <code class="language-sql">int</code>, <code class="language-sql">AUTO_INCREMENT</code>,
                    y convertida en clave primaria.
                </p>
                <?php
                echo_note('Maple no soporta la creación de tablas con más de una clave primaria, la utilización de
                    otro <code class="language-sql">ENGINE</code> que no sea InnoDB, y otro <code class="language-sql">ROW_FORMAT</code> que no sea <code class="language-sql">COMPACT</code>.')
                ?>
                <h3>Creación de tablas con columnas</h3>
                <p>
                    Para especificar columnas que se crearán con la tabla, basta solo con escribir su definición luego
                    del operador <code>+</code>.
                </p>
                <?php echo_code_example('($Personas) +
nombre varchar(255),
apellido varchar(255);', "CREATE TABLE `Personas` (
`id` int NOT NULL AUTO_INCREMENT,
`nombre` varchar(255) NOT NULL,
`apellido` varchar(255) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB ROW_FORMAT=COMPACT;") ?>
                <p>
                    Por default, las columnas creadas serán <code class="language-sql">NOT NULL</code>. Si deseamos que
                    una de las columnas
                    creadas acepte
                    valores nulos, agregaremos un <code>?</code> luego del nombre de la columna. Esto es similar al
                    lenguaje de
                    programación C#, el cual identifica a variables que aceptan valores nulos de la misma manera. <br>
                    Por ejemplo, si deseamos que la columna <code>telefono</code> acepte valores nulos:
                </p>
                <?php echo_code_example('($Personas) +
nombre varchar(255),
apellido varchar(255),
telefono? varchar(255);', "CREATE TABLE `Personas` (
`id` int NOT NULL AUTO_INCREMENT,
`nombre` varchar(255) NOT NULL,
`apellido` varchar(255) NOT NULL,
`telefono` varchar(255),
PRIMARY KEY (`id`)
) ENGINE=InnoDB ROW_FORMAT=COMPACT;")  ?>
                <p>
                    Asimismo, Maple permite asignarle un valor default a la columna, siguiendo la misma metodología que
                    en MySQL.
                </p>
                <?php
                echo_code_example('($Personas) +
nombre varchar(255),
apellido varchar(255) DEFAULT \'Smith\';', "CREATE TABLE `Personas` (
`id` int NOT NULL AUTO_INCREMENT,
`nombre` varchar(255) NOT NULL,
`apellido` varchar(255) DEFAULT 'Smith' NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB ROW_FORMAT=COMPACT;");
                ?>
                <p>
                    En otras palabras, cualquier tipo de sintaxis válida en la declaración de columnas en una sentencia
                    <code class="language-sql">CREATE TABLE</code> de MySQL es también legal en Maple.
                </p>
                <h3>Uniones con tablas</h3>
                <p>
                    Operador: <code><></code><br>
                    Es posible indicarle a Maple que genere una sentencia de creación de tabla e incluya una clave
                    foránea que haga referencia a otra tabla. La tabla referenciada se escribirá precedida por un
                    símbolo <code>$</code> y luego de <code><></code>.<br>
                    Una de las ventajas que conlleva esta metodología, es que Maple automáticamente incluirá la creación
                    de una columna de referencia en la tabla original. Esta nueva columna llevará el nombre de
                    <code>id_[nombre_tabla_referenciada]</code>.<br>
                    Para que la creación de esta unión sea efectiva, es necesario que la tabla referenciada tenga una
                    columna llamada <code>id</code> y que ésta sea clave primaria.
                </p>
                <?php echo_code_example('($Personas) + <> $Roles', "CREATE TABLE `Personas` (
`id` int NOT NULL AUTO_INCREMENT,
`id_Roles` int(10) NOT NULL ,
PRIMARY KEY (`id`),
CONSTRAINT `fk_Personas_Roles` FOREIGN KEY (`id_Roles`) REFERENCES `Roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB ROW_FORMAT=COMPACT;");?>
                <p>
                    Como podemos ver en la sentencia SQL resultante, Maple se encargó de crear una columna llamada
                    <code>id_roles</code>, la cual es de tipo <code>int</code> y no acepta valores nulos.<br>
                    También es posible incluir varias tablas luego de la primera unión:
                </p>
                <?php echo_code_example('($Empleado) + <> $Ciudad <> $Pais', "CREATE TABLE `Empleado` (
`id` int NOT NULL AUTO_INCREMENT,
`id_Ciudad` int(10) NOT NULL ,
`id_Pais` int(10) NOT NULL ,
PRIMARY KEY (`id`),
CONSTRAINT `fk_Empleado_Ciudad` FOREIGN KEY (`id_Ciudad`) REFERENCES `Ciudad` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `fk_Empleado_Pais` FOREIGN KEY (`id_Pais`) REFERENCES `Pais` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB ROW_FORMAT=COMPACT;") ?>
                <?php echo_note('La creación de claves foráneas con Maple sólo soporta el modo <code class="language-sql">CASCADE</code> de eliminación y edición.') ?>
                <h2>Modificación de definición de tablas</h2>
                <p>
                    Maple ofrece la posibilidad de modificar la definición de las tablas. Esto quiere decir que es
                    posible agregar, eliminar, y modificar columnas. Es importante recordar que toda modificación a la
                    definición de una tabla deberá ser declarada con los operadores <code>()</code> envolviendo la
                    referencia de la tabla.
                </p>
                <h3>Agregar columna</h3>
                <p>
                    Operador: <code><</code><br>
                    Por default, la columna que se agregará no aceptará valores nulos.
                </p>
                <?php echo_code_example('($Personas) < nombre varchar(255)', "ALTER TABLE `Personas` ADD COLUMN `nombre` varchar(255) NOT NULL ;") ?>
                <p>
                    Al igual que la sentencia para
                    crear tablas, la sentencia para alterar tablas es compatible con el símbolo <code>?</code> para
                    especificar la nulidad de la columna.
                </p>
                <?php echo_code_example('($Personas) < nombre? varchar(255)', "ALTER TABLE `Personas` ADD COLUMN  `nombre` varchar(255);") ?>
                <h3>Editar columna</h3>
                <p>
                    Operador: <code><<</code><br>
                    La columna a modificar vendrá a continuación del operador. Cualquier sintaxis válida en SQL en este
                    contexto también lo será para Maple.
                </p>
                <?php
                echo_code_example('($Personas) << nombre varchar(255)', "ALTER TABLE `Personas` MODIFY COLUMN  `nombre` varchar(255) NOT NULL;");
                echo_code_example('($Personas) << nombre? varchar(255)', "ALTER TABLE `Personas` MODIFY COLUMN  `nombre` varchar(255);");
                echo_note('La versión actual de Maple no soporta el renombramiento de columnas, ya que internamente hace uso de <code class="language-sql">MODIFY COLUMN</code> y no <code class="language-sql">CHANGE COLUMN</code>.');
                ?>
                <h3>Eliminar columna</h3>
                <p>
                    Operador: <code>[x]</code><br>
                    Para eliminar una columna, sólo basta con especificar el nombre de la columna que queremos eliminar.
                    No es necesario especificar tipo de dato ni nulidad.
                </p>
                <?php echo_code_example('($Personas) [x] nombre', "ALTER TABLE `Personas` DROP COLUMN `nombre`;") ?>
                <h2>Funciones especiales de Maple</h2>
                <p>
                    Maple cuenta con funciones nativas que son procesadas por el conversor de forma automática. Hasta
                    ahora, estas funciones sirven para facilitar la escritura de sentencias Maple, haciéndolas más
                    acortadas.
                </p>
                <h3>Función t</h3>
                <p>
                    Parámetro: un número entero.<br>
                    La función <code class="language-sql">t</code> hace referencia a un nombre de tabla mencionado
                    anteriormente en la
                    sentencia. Si nuestra sentencia contiene varias tablas, el número de parámetro sirve
                    para identificar qué tabla queremos referenciar.
                </p>
                <?php echo_code_example('$Personas > <> $Roles ? t(1).id = \'1\'', "SELECT * FROM `Personas` INNER JOIN `Roles` ON Personas.id_Roles = Roles.id WHERE Personas.id = '1';") ?>
                <p>
                    Como podemos ver, la función <code class="language-sql">t(1)</code> nos devolvió el nombre <code>Personas</code>,
                    ya que
                    fue la primera tabla
                    que mencionamos. Si hubiésemos querido hacer referencia a la tabla <code>Roles</code>, hubiésemos
                    usado el número
                    <code>2</code> como parámetro. Al ser <code class="language-sql">t(1)</code> una referencia actual
                    al nombre de la tabla,
                    podemos
                    concatenarle la columna que queremos de esa tabla.<br>
                    La función <code class="language-sql">t</code> es muy útil para sentencias con uniones y
                    condicionales, en donde es
                    necesario especificar el nombre de la tabla con las columnas para evitar ambigüedades entre tablas.
                </p>
                <p>
                    La función <code class="language-sql">t</code> es aceptada en cualquier parte de la sentencia Maple,
                    siempre y cuando el número dado como parámetro sea compatible con el número de tablas dado en la
                    sentencia.<br>
                    Por ejemplo, es posible utilizar la función t para especificar tablas con columnas en una
                    visualización.
                </p>
                <?php echo_code_example('$Personas > t(1).nombre, t(1).apellido, t(2).descripcion <> $Roles', "SELECT `Personas`.`nombre`, `Personas`.`apellido`, `Roles`.`descripcion` FROM `Personas` INNER JOIN `Roles` ON Personas.id_Roles = Roles.id;") ?>
                <h3>Función c</h3>
                <p>
                    Parámetro: un número entero.<br>
                    La función <code class="language-sql">c</code> es idéntica a la función <code
                            class="language-sql">t</code>, pero con la diferencia que ésta hace referencia a columnas
                    escritas en la sentencia.
                </p>
                <?php echo_code_example('$Personas > nombre ? c(1) = \'John\'', "SELECT `nombre` FROM `Personas` WHERE nombre = 'John';") ?>
                <p>
                    En este caso, la columna <code>nombre</code> fue la primera columna, entonces, pasándole a la
                    función <code class="language-sql">c</code> el
                    número <code>1</code>, obtenemos como resultado <code>nombre</code>.
                </p>
                <?php echo_note('La función <code class="language-sql">c</code> no es recomendada para columnas afectadas por funciones SQL nativas. Esto se debe a que la función no le permitiría al conversor determinar el nombre exacto de la columna a referenciar. Este comportamiento es intencional, debido a que dentro de una función puede haber funciones anidadas u operaciones aritméticas') ?>
                <h3>Utilización de funciones t y c en una misma sentencia</h3>
                <p>
                    Ambas funciones pueden utilizarse en una misma sentencia Maple.<br>
                    Por ejemplo, es posible "encadenar" ambas funciones para llegar a un resultado <code>[tabla].[columna]</code>.<br>
                    Las encadenaciones siempre se deben hacer utilizando un punto.
                </p>
                <?php echo_code_example('$Personas > nombre, apellido ? t(1).c(1) = \'David\'', "SELECT `nombre`, `apellido` FROM `Personas` WHERE Personas.nombre = 'David';") ?>
                <p>O si se desea, se pueden encadenar estas funciones con nombres explícitos de columnas o tablas.</p>
                <?php
                echo_code_example('$Personas > nombre, apellido ? Personas.c(1) = \'John\'', "SELECT `nombre`, `apellido` FROM `Personas` WHERE Personas.nombre = 'John';");
                echo_code_example('$Personas > nombre, apellido ? t(1).nombre = \'Harry\'', "SELECT `nombre`, `apellido` FROM `Personas` WHERE Personas.nombre = 'Harry';");
                ?>
                <p>
                    Es importante tener en cuenta que la función <code class="language-sql">c</code> toma el nombre de
                    la columna tal y como la
                    escribimos.<br>
                    Si la misma es escrita con el nombre de la tabla precediéndola, ya sea por uso de la función
                    <code class="language-sql">t</code> o nombre explícito, la referencia devuelta por la función
                    <code class="language-sql">c</code> también tendrá el nombre de la tabla.<br>
                    Por ejemplo:
                </p>
                <?php
                echo_code_example('$Personas > Personas.nombre ? c(1) = \'John\'', "SELECT `Personas`.`nombre` FROM `Personas` WHERE Personas.nombre = 'John';");
                echo_code_example('$Personas > t(1).nombre ? c(1) = \'John\'', "SELECT `Personas`.`nombre` FROM `Personas` WHERE Personas.nombre = 'John';");
                ?>
                <h2>Embeber SQL en sentencias Maple</h2>
                <p>
                    Como hemos visto, no es posible llevar a cabo todas las operaciones que se pueden hacer con SQL con
                    Maple.
                    Maple ofrece una manera de realizar operaciones complejas con SQL sin tener que abandonar el
                    dialecto por completo. Esto quiere decir que podemos escribir sentencias SQL y Maple y enviar ambas
                    al conversor al mismo tiempo. Esto se logra envolviendo cualquier sentencia escrita en SQL con
                    etiquetas <code>&lt;? ?&gt</code>.<br>
                    Las etiquetas <code>&lt;? ?&gt</code> le indican al conversor que ignore, es decir, que no
                    convierta, todo lo que está envuelto por ellas y que continúe con la sentencia que sigue.
                </p>
                <?php
                echo_code_example("&lt;? SELECT * FROM `Personas` ?&gt", "SELECT * FROM `Personas`;");
                echo_code_example('&lt;? SELECT * FROM `Personas` ?&gt
$Roles;', "SELECT * FROM `Personas`;
SELECT * FROM `Roles`;");
                ?>
                <p>
                    A continuación, se mostrará un ejemplo más complejo, en donde se crea una nueva tabla con SQL, se
                    agregan datos mediante Maple, y finalmente se mostrarán sus contenidos mediante un
                    <code class="language-sql">SELECT</code> en SQL.
                </p>
                <?php
                echo_code_example('&lt;?
CREATE TABLE `Personas` (
`id` int NOT NULL AUTO_INCREMENT,
`nombre` varchar(255) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB ROW_FORMAT=COMPACT;
?&gt
$Personas < 1, \'John\';
&lt;? SELECT * FROM `Personas` ?&gt', "CREATE TABLE `Personas` (
`id` int NOT NULL AUTO_INCREMENT,
`nombre` varchar(255) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB ROW_FORMAT=COMPACT;
INSERT INTO `Personas` VALUES(1, 'John');
SELECT * FROM `Personas`;
")
                ?>
                <p>
                    Las operaciones pueden cambiarse, en el caso siguiente, crearemos una nueva tabla con Maple,
                    agregaremos datos mediante SQL, y finalmente mostraremos los contenidos mediante una visualización
                    de Maple.
                </p>
                <?php echo_code_example('($Personas) + nombre varchar(255);
&lt;? INSERT INTO Personas VALUES(1, \'John\'); ?&gt
$Personas', "CREATE TABLE `Personas` (
`id` int NOT NULL AUTO_INCREMENT,
`nombre` varchar(255) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB ROW_FORMAT=COMPACT;
INSERT INTO Personas VALUES(1, 'John');
SELECT * FROM `Personas`;
") ?>
                <h3>Múltiples sentencias SQL dentro de un par de etiquetas</h3>
                <p>
                    Es posible incluir múltiples sentencias SQL dentro de un par de etiquetas <code>&lt;?
                        ?&gt</code>.<br>
                    Por ejemplo, si deseamos crear una tabla exclusivamente con SQL y luego insertar un set de datos con
                    SQL, podemos ahorrarnos un par de etiquetas e incluir todas las sentencias necesarias para
                    llevar a cabo esta tarea en un par. El único requerimiento es que todas las sentencias SQL dentro de
                    las etiquetas deberán estar separadas <code>;</code> como en cualquier SQL válido.
                </p>
                <?php
                echo_code_example("&lt;?
CREATE TABLE `Personas` (
`id` int NOT NULL AUTO_INCREMENT,
PRIMARY KEY (`id`)
) ENGINE=InnoDB ROW_FORMAT=COMPACT;
INSERT INTO Personas VALUES(1, 'John');
?&gt", "CREATE TABLE `Personas` (
`id` int NOT NULL AUTO_INCREMENT,
PRIMARY KEY (`id`)
) ENGINE=InnoDB ROW_FORMAT=COMPACT;
INSERT INTO Personas VALUES(1, 'John');")
                ?>
                <p>
                    Al embeber SQL en Maple, se ahorra el paso de ejecutar una sentencia SQL aparte y luego hacer las
                    operaciones necesarias con Maple. Al hacerlo de esta manera, nos aseguramos de que todas las
                    operaciones se ejecuten una detrás de la otra, sin importar su complejidad.
                </p>
                <p>
                    Es importante recordar que, aunque se envíen los dos tipos de sentencias al motor, ambas no se
                    pueden mezclar en una misma sentencia. Esto se explicará en más detalle en <a
                            href="#limitaciones">limitaciones</a>.
                </p>
                <h3>Limitaciones</h3>
                <h4>SQL dentro de una sentencia Maple</h4>
                <p>
                    El uso de las etiquetas <code>&lt;? ?&gt</code> actúa como una sentencia individual. Esto significa
                    que no es posible incluir estos símbolos dentro de una sentencia Maple individual. Es decir, no
                    estaría permitido por ejemplo llevar a cabo una visualización utilizando sintaxis de Maple y
                    escribir los condicionales con sintaxis SQL. Esto resultará en comportamiento no definido e
                    indeseado.<br>
                    Por ejemplo:
                </p>
                <?php
                echo_invalid_code_conversion('$Personas > nombre, apellido &lt;? WHERE nombre = \'John\' ?&gt [1000]')
                ?>
                <h4>Maple dentro de una sentencia SQL</h4>
                <p>
                    Asimismo, incluir Maple envuelto por etiquetas <code>&lt;? ?&gt</code> es inválido y resultará en
                    comportamiento no definido.<br>
                    Por ejemplo:
                    <?php
                    echo_invalid_code_conversion('&lt;? SELECT * FROM Personas > <> $Roles ?&gt')
                    ?>
                </p>
                <h2>Comentarios</h2>
                <p>
                    Operador: <code>--</code><br>
                    Maple acepta comentarios. Estos comentarios serán removidos por el conversor y
                    no aparecerán en la sentencia SQL resultante.
                </p>
                <?php
                echo_code_example('$Personas; -- este es un comentario', "SELECT * FROM `Personas`;");
                echo_note('Maple sólo soporta este tipo de comentarios precedidos por <code>--</code>. Comentarios que pueden ser válidos en SQL como <code class="language-sql">/* */</code> son sólo permitidos en el contexto de SQL embebido.')
                ?>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-toc.min.js') }}"></script>
    <script src="{{ asset('js/prism.js') }}"></script>
@endsection