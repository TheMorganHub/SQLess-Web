<?php

namespace sqless\Maple;

use sqless\Logger;

class Parser {
    private $maple_statement;
    private $maple_statement_no_conds;
    private $tablas;
    private $op;
    private $condicionales;
    private $innerJoinSets;
    private $columnas;
    private $innerJoinResultado;
    private $finalInnerJoinStr;
    private $finalSql;
    private $valores;
    private $modo;
    private const MAPLE_FUNCTIONS = ['c', 't'];
    private $loadedFunctions;

    /**
     * El constructor del parseador. Éste cumple varias tareas, entre ellas: cargar las funciones nativas de Maple,
     * remplazar caracteres de espacio provenientes de Android con caracteres ASCII compatibles (ej: espacios), etc.
     * @param string $maple_statement
     */
    private function __construct($maple_statement) {
        $this->loadMapleFunctions();
        $this->maple_statement = str_replace("\xc2\xa0", " ", $maple_statement); //arregla bug con caracteres de espacio (ascii 160) provenientes del teléfono
        $this->maple_statement = strpos($this->maple_statement, ';') !== false ? $this->maple_statement : $this->maple_statement . ';'; //necesario para regex columnas
        $this->maple_statement_no_conds = $this->maple_statement;
    }

    private function loadMapleFunctions() {
        $this->loadedFunctions = implode('|', self::MAPLE_FUNCTIONS);
    }

    /**
     * Parsea las tablas dentro de una sentencia Maple.
     */
    private function parseTablas() {
        if (preg_match_all('/(\$\w+)|(\(\$\w+\))/', $this->maple_statement, $matches)) {
            $this->tablas = $matches[0];
        } else {
            throw new MapleException("No se encontraron tablas SQL en la sentencia Maple dada:\n'$this->maple_statement'");
        }
        if (preg_match('/(\(\$\w+\))/', $this->tablas[0]) === 1) {
            $this->modo = 1;
        } else if (preg_match('/\$\w+/', $this->tablas[0]) === 1) {
            $this->modo = 2;
        }
        for ($i = 0; $i < count($this->tablas); $i++) {
            $this->tablas[$i] = str_replace(['$', '(', ')'], '', $this->tablas[$i]);
            $this->tablas[$i] = "`" . $this->tablas[$i] . "`";
        }
    }

    /**
     * Parsea los condicionales dentro de una sentencia Maple, es decir, lo que viene después de '?'. Las expresiones
     * lógicas estilo C serán convertidas a formato SQL. E.j: '&&' a 'AND'. Si la sentencia no tiene condicionales, este
     * método no hace nada.
     */
    private function parseCondicionales() {
        if (Utils::str_contains($this->maple_statement, '?')) { //si hay condicionales
            $this->maple_statement_no_conds = substr($this->maple_statement, 0, strpos($this->maple_statement, '?'));
            $this->condicionales = " WHERE " . str_replace(';', "", trim(substr($this->maple_statement, strpos($this->maple_statement, '?') + 1)));
            $this->condicionales = str_replace(['&&', '||', '!=', '!= null', '= null', '$'], ['AND', 'OR', '<>', 'IS NOT NULL', 'IS NULL', ''], $this->condicionales);
            $this->condicionales = preg_replace('/(?<!\')\[\s*(\w*|\w*,\s*\w*)\s*\]/', '', $this->condicionales); //remove LIMIT
            if ($this->condicionales == ' WHERE ') {
                throw new MapleException('El condicional dado está vacío.');
            }
            return;
        }
        $this->maple_statement_no_conds = $this->maple_statement;
    }

    private function parseFunctions() {
        $afterFunctionParse = preg_replace_callback("/(?<![\'\w])($this->loadedFunctions)\((\w+|[^)]*,*)\)/", function ($array) { ///(?<!['\w])(\w+)\((\w+|[^)]*,*)\)/
            $fullFunction = $array[0];
            $funcName = $array[1];
            $params = explode(',', $array[2]);
            return $this->processFunction($fullFunction, $funcName, $params);
        }, $this->maple_statement, 1);
        if ($this->maple_statement !== $afterFunctionParse) {
            $this->maple_statement = $afterFunctionParse;
            $this->parseTablas();
            $this->parseCondicionales();
            $this->parseColumnas();
            $this->parseFunctions();
        }
    }

    private function processFunction($fullFunction, $name, array $params) {
        switch ($name) {
            case 't':
                if (empty($params) || $params[0] == '' || count($params) > 1) {
                    $foundParams = $params[0] == '' ? 0 : count($params);
                    throw new MapleException("t(): Esta función requiere de 1 parámetro. Parámetros encontrados: $foundParams.");
                }
                if (!ctype_digit($params[0])) {
                    throw new MapleException("t(): Esta función requiere de 1 parámetro numérico.");
                }
                if (empty($this->tablas) || count($this->tablas) < $params[0]) {
                    throw new MapleException("t(): No hay ninguna tabla con la cual referenciar el valor dado.");
                }
                return str_replace('`', '', $this->tablas[$params[0] - 1]);
            case 'c':
                if (empty($params) || $params[0] == '' || count($params) > 1) {
                    $foundParams = $params[0] == '' ? 0 : count($params);
                    throw new MapleException("c(): Esta función requiere de 1 parámetro. Parámetros encontrados: $foundParams.");
                }
                if (!ctype_digit($params[0])) {
                    throw new MapleException("c(): Esta función requiere de 1 parámetro numérico.");
                }
                if (empty($this->columnas) || count($this->columnas) < $params[0]) {
                    throw new MapleException("c(): No hay ninguna columna con la cual referenciar el valor dado.");
                }
                return str_replace(['*', '^', '`'], '', $this->columnas[$params[0] - 1]);
        }
        return $fullFunction;
    }

    /**
     * Parsea el simbolo de la operación utilizando expresiones regulares. Los simbolos válidos son <br>
     * &#62; = SELECT <br>
     * &#60; = INSERT <br>
     * &#60;&#60; = UPDATE <br>
     * [x] = DELETE
     */
    private function parseOp() {
        if (preg_match($this->modo == 2 ? '/\>|\<\<|\<|\[x\]/' : '/<\d*?>|<<|<|\[x\]|\+/', $this->maple_statement_no_conds, $matches)) {
            $this->op = $matches[0];
        }
    }

    /**
     * Parsea las columnas dadas en una expresión Maple sin tener en cuenta la parte de condicionales.<br>
     * Maple es compatible con funciones nativas de SQL.
     * Ejemplos de columnas válidas:<br>
     * <ul>
     * <li>Nombre</li>
     * <li>^Apellido</li>
     * <li>AVG(Cantidad)</li>
     * <li>*Nombre</li>
     * <li>Personas.Nombre</li>
     * </ul>
     * Maple hace uso de símbolos propios para denotar orden (ORDER BY) y agrupación (GROUP BY). Estos símbolos son:<br>
     * *: GROUP BY <br>
     * ^: ORDER BY
     * @param bool $parseOpBefore
     */
    private function parseColumnas($parseOpBefore = false) {
        if ($parseOpBefore) {
            $this->parseOp();
        }
        $this->columnas = array();
        $noValuesStatement = $this->maple_statement_no_conds;

        //remuevo porción de sentencia con los valores para no confundir al regex con las columnas
        if ($this->op == '<' || $this->op == '<<') {
            $noValuesStatement = substr($this->maple_statement_no_conds, 0, strrpos($this->maple_statement_no_conds, $this->op)) . ' ';
        }
        if (preg_match_all('/[\^|\*|\^\*|\*\^|\s](([A-Za-z]+\.\w+)|(\w*[A-Za-z]\w*)|(\w+\(.*?\)))[,|;|\s]/', $noValuesStatement, $matches)) {
            foreach ($matches[0] as $match) {
                $columna = trim(str_replace(',', '', $match));
                $columna = $this->addQuotToColumnName($columna);
                $this->columnas[] = $columna;
            }
        }
    }

    /**
     * Envuelve los nombres de columnas con `` para impedir que se interpreten como una palabra reservada de SQL.
     *
     * @param $columna string el nombre de la columna a transformar.
     * @return string Por ejemplo, si hay una columna llamada asc, este método la transformará en `asc`.
     */
    private function addQuotToColumnName(string $columna) {
        $hasOrderBy = Utils::str_starts_with($columna, '^') || Utils::str_starts_with($columna, '*^');
        $hasGroupBy = Utils::str_starts_with($columna, '*') || Utils::str_starts_with($columna, '^*');
        $columna = $hasOrderBy ? Utils::replace_first('^', '', $columna) : $columna;
        $columna = $hasGroupBy ? Utils::replace_first('*', '', $columna) : $columna;

        if (preg_match('/^\w+(;)?$/', $columna)) { //columna estilo 'precio'
            $columna = "`$columna`";
        } else if (preg_match('/[A-Za-z]+\.\w+/', $columna)) { //columna estilo 'producto.precio'
            $arrayCol = explode('.', $columna);
            $tableName = '`' . $arrayCol[0] . '`';
            $columnName = '`' . $arrayCol[1] . '`';
            $columna = $tableName . '.' . $columnName;
        }

        $columna = $hasOrderBy ? "^$columna" : $columna;
        $columna = $hasGroupBy ? "*$columna" : $columna;
        return $columna;
    }

    private function parseFunctionFromColumn($column) {
        if (preg_match('/(\w+)\(.*?\)/', $column, $matches)) {
            return $matches[1];
        }
        return '';
    }

    /**
     * Parsea uniones entre tablas en una sentencia Maple. <br><br>Maple da la opción de no indicar qué columnas van a ser
     * utilizadas para hacer la unión entre dos tablas, en ese caso, Maple asume que el usuario utilizó la nomenclatura
     * correcta para nombrar primary keys y foreign keys. La nomenclatura consiste en llamar a primary keys que no sean
     * compuestas como 'id', y a toda foreign key 'id_[nombre de tabla a la que hace relación]'. <br>
     * Por ejemplo: en tabla Personas una foreign key podría ser 'id_Trabajos', que hace referencia a columna 'id' en
     * tabla Trabajos. <br>
     * En cambio si no se desea respetar la nomenclatura, el usuario deberá manualmente indicar qué columnas deberán utilizarse
     * para realizar la unión. <br><br>
     * Aclaración: Maple permite mezclar los dos tipos de INNER JOIN. Es decir, si una sentencia tiene dos INNER JOIN,
     * uno de los dos puede tener los campos explícitos y el otro no.
     */
    private function parseInnerJoin() {
        if (preg_match_all('/\<(.*?)\>|\<\>/', $this->maple_statement_no_conds, $matches)) {
            $this->innerJoinSets = $matches[1];
            //agrega el nombre de la tabla a las columnas a modo "persona.id"
            for ($i = 0; $i < count($this->tablas) - 1; $i++) {
                $nombre1 = str_replace('`', '', $this->tablas[$i]);
                $nombre2 = str_replace('`', '', $this->tablas[$i + 1]);
                $grupo = explode('=', $this->innerJoinSets[$i]);
                //si la persona no indica qué campos deben utilizarse para hacer la unión, miniSQL asume los nombres de las columnas
                //en base a las tablas
                if (count($grupo) == 1) {
                    $nuevoGrupo1 = $nombre1 . "." . "id_" . $nombre2;
                    $nuevoGrupo2 = $nombre2 . ".id";
                } else {
                    $nuevoGrupo1 = preg_match('/[a-zA-Z]+\.[a-zA-Z]+/', trim($grupo[0])) ? trim($grupo[0]) : $nombre1 . "." . trim($grupo[0]);
                    $nuevoGrupo2 = preg_match('/[a-zA-Z]+\.[a-zA-Z]+/', trim($grupo[1])) ? trim($grupo[1]) : $nombre2 . "." . trim($grupo[1]);
                }
//                $nuevoGrupo1 = $this->addQuotToColumnName($nuevoGrupo1);
//                $nuevoGrupo2 = $this->addQuotToColumnName($nuevoGrupo2);
                $this->innerJoinSets[] = $nuevoGrupo1 . " = " . $nuevoGrupo2;
            }
            $this->innerJoinSets = array_splice($this->innerJoinSets, count($this->innerJoinSets) / 2);

            for ($i = 1; $i < count($this->tablas); $i++) {
                $this->innerJoinResultado[] = " INNER JOIN " . $this->tablas[$i];
            }
            for ($i = 0; $i < count($this->innerJoinResultado); $i++) {
                $this->finalInnerJoinStr .= $this->innerJoinResultado[$i] . " ON " . $this->innerJoinSets[$i];
            }
        }
    }

    private function procesar() {
        $this->parseTablas();
        if ($this->modo === 2) {
            $this->parseCondicionales();
            $this->parseColumnas(true);
        } else {
            $this->parseOp();
        }
        $this->parseFunctions();

        if (empty($this->op)) {
            $this->op = '>';
            $this->procesarSelect();
            return;
        }

        switch ($this->op) {
            case '>':
                $this->procesarSelect();
                break;
            case '<':
                $this->modo === 1 ? $this->procesarAddColumn() : $this->procesarInsert();
                break;
            case '<<':
                $this->modo === 1 ? $this->procesarModifyColumn() : $this->procesarUpdate();
                break;
            case '[x]':
                $this->modo === 1 ? $this->procesarDropColumn() : $this->procesarDelete();
                break;
            case '+':
                if ($this->modo === 1) {
                    $this->procesarCreateTable();
                } else {
                    throw new MapleException('El operador no es válido en el contexto dado.');
                }
                break;
            default:
                if (preg_match('/<(\d*?)>/', $this->op, $matches)) {
                    if ($this->modo === 1) {
                        $this->procesarJoinTables($matches[1]);
                    }
                } else {
                    throw new MapleException('El operador no es válido en el contexto dado.');
                }
                break;
        }
    }

    private function filterEnumLikeColumns($columnDefinitions) {
        $newDefinitions = array();
        $enumStarted = false;
        $enumParts = '';
        $addComma = false;
        foreach ($columnDefinitions as $def) {
            if (preg_match("/enum*.\('/i", $def) || preg_match("/set*.\('/i", $def)) {
                $enumStarted = true;
            }

            if ($enumStarted) {
                $enumParts .= ($addComma ? ', ' : '') . trim($def);
                $addComma = true;
            } else {
                $newDefinitions[] = $def;
            }

            if (Utils::str_contains($def, "')")) {
                $enumStarted = false;
                $addComma = false;
                $newDefinitions[] = $enumParts;
                $enumParts = '';
            }
        }
        return $newDefinitions;
    }

    private function procesarCreateTable() {
        $table = $this->tablas[0];
        $tableNameNoQuot = Utils::unquote($table);
        $postOpStmt = preg_split('/\+|(<\d*>)/', $this->maple_statement, -1, PREG_SPLIT_DELIM_CAPTURE);
        $columnSegment = trim($postOpStmt[1]);
        if ($columnSegment == ';') { //la sentencia no tiene columnas ni joins
            $this->finalSql = "CREATE TABLE $table (\n`id` int NOT NULL AUTO_INCREMENT,\nPRIMARY KEY (`id`)\n) ENGINE=InnoDB ROW_FORMAT=COMPACT;";
            return;
        }
        $columnsForCreate = "CREATE TABLE $table (\n`id` int NOT NULL AUTO_INCREMENT,\n";
        $joins = '';
        $defaultValues = [];
        if (!Utils::str_is_empty($columnSegment)) { //la sentencia tiene columnas
            $colDefinitions = $this->filterEnumLikeColumns(explode(',', $columnSegment)); //ex: nombre varchar(255)
            $colNames = [];
            $colDefinitionsOnly = [];
            foreach ($colDefinitions as $colDefinition) {
                $colDefinition = Utils::replace_last(';', '', $colDefinition);
                $definitionsSplit = preg_split('/\s/', trim($colDefinition)); //['nombre', 'varchar(255)']
                $colName = $definitionsSplit[0]; //ex: nombre
                $colNames[] = $colName;
                array_shift($definitionsSplit); //remove first item
                $colIsNullable = Utils::str_ends_with($colName, '?');
                $colName = $colIsNullable ? Utils::replace_last('?', '', $colName) : $colName;
                $colDefinitionOnly = implode(' ', $definitionsSplit) . ($colIsNullable ? '' : ' NOT NULL');
                $colDefinitionsOnly[] = $colDefinitionOnly;
                $columnsForCreate .= "`$colName` $colDefinitionOnly,\n";
            }
        }

        if (count($this->tablas) > 1) { //la sentencia tiene joins
            if (preg_match_all('/<(\s*\d*\s*)>/', $this->maple_statement, $defaultValues)) {
            }
            for ($i = 1; $i < count($this->tablas); $i++) {
                $refTableNameNoQuot = Utils::unquote($this->tablas[$i]);
                $defaultValue = trim($defaultValues[1][$i - 1]);
                $columnsForCreate .= "`id_$refTableNameNoQuot` int(10) NOT NULL " . (Utils::str_is_empty($defaultValue) ? "" : "DEFAULT '$defaultValue'") . ",\n";
                $joins .= "CONSTRAINT `fk_$tableNameNoQuot" . "_$refTableNameNoQuot` FOREIGN KEY (`id_$refTableNameNoQuot`) REFERENCES `$refTableNameNoQuot` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,\n";
            }
            $joins = Utils::replace_last(',', '', $joins);
        }
        $columnsForCreate = $columnsForCreate . "PRIMARY KEY (`id`)" . (Utils::str_is_empty($joins) ? "\n" : ",\n$joins") . ") ENGINE=InnoDB ROW_FORMAT=COMPACT";
        $this->finalSql = $columnsForCreate;
    }

    private function procesarJoinTables($defaultValue) {
        if (count($this->tablas) < 2) {
            throw new MapleException("Debe haber dos tablas para formar una unión.");
        }
        $mainTable = str_replace('`', '', $this->tablas[0]);
        $referencedTable = str_replace('`', '', $this->tablas[1]);
        $this->finalSql = "ALTER TABLE `$mainTable` ADD COLUMN `id_$referencedTable` int NOT NULL" . (Utils::str_is_empty($defaultValue) ? "" : " DEFAULT '$defaultValue'") . ';' . "\n";
        $this->finalSql .= "ALTER TABLE `$mainTable` ADD CONSTRAINT `" . "fk_$mainTable" . "_" . "$referencedTable` FOREIGN KEY (`id_$referencedTable`) REFERENCES `$referencedTable` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
    }

    private function procesarSelect() {
        if (count($this->tablas) > 1) {
            $this->parseInnerJoin();
        }

        //Group by
        $groupBy = '';
        for ($i = 0; $i < count($this->columnas); $i++) {
            $columna = $this->columnas[$i];
            if (Utils::str_contains($columna, '*')) {
                if (($indice = strpos($columna, '*')) < 2) {
                    $this->columnas[$i] = Utils::removerCharEn($columna, $indice);
                    $groupBy = " GROUP BY " . ($i + 1);
                    break;
                }
            }
        }
        //Order by
        $orderBy = '';
        for ($i = 0; $i < count($this->columnas); $i++) {
            $columna = $this->columnas[$i];
            if (Utils::str_contains($columna, '^')) {
                if (($indice = strpos($columna, '^')) < 2) {
                    $this->columnas[$i] = Utils::removerCharEn($columna, $indice);
                    $orderBy = " ORDER BY " . ($i + 1);
                    break;
                }
            }
        }

        //limit
        $limit = '';
        if (preg_match('/(?<!\')\[\s*(\w*|\w*,\s*\w*)\s*\]/', $this->maple_statement, $matches)) {
            $verifyLimit = explode(',', $matches[1]);
            foreach ($verifyLimit as $item) {
                if (!ctype_digit(trim($item))) {
                    throw new MapleException('La funcionalidad LIMIT sólo acepta números como parámetro.');
                }
            }
            $limit = " LIMIT $matches[1]";
        }

        $this->finalSql = "SELECT " . $this->stringifyCols() . " FROM " . $this->tablas[0] . $this->finalInnerJoinStr
            . $this->condicionales . $groupBy . $orderBy . $limit;
    }

    private function procesarInsert() {
        $this->parseValores();
        $this->finalSql = "INSERT INTO " . $this->tablas[0] . (empty($this->columnas) ? "" : "(" . $this->stringifyCols() . ")")
            . " VALUES(" . $this->valores . ")";
    }

    private function procesarUpdate() {
        $valores = $this->parseValores([]);
        $columnaValores = '';
        $colCount = count($this->columnas);
        for ($i = 0; $i < $colCount; $i++) {
            $columnaValores .= $this->columnas[$i] . ' = ' . $valores[$i] . ($i < $colCount - 1 ? ', ' : '');
        }
        $this->finalSql = "UPDATE " . $this->tablas[0] . " SET " . $columnaValores . $this->condicionales;
    }

    private function procesarDelete() {
        if ($this->condicionales == null) {
            $this->finalSql = "TRUNCATE TABLE " . $this->tablas[0];
        } else {
            $this->finalSql = "DELETE FROM " . $this->tablas[0] . $this->condicionales;
        }
    }

    private function procesarAddColumn() {
        if (preg_match('/<\s*?(\w+\?*)/', $this->maple_statement, $matches)) {
            $nullable = Utils::str_ends_with($matches[1], '?');
            $this->maple_statement = preg_replace_callback('/<\s*?(\w+\?*)/', function (array $match) {
                $noQuestionMark = Utils::replace_last('?', '', $match[1]);
                return "< `$noQuestionMark`";
            }, $this->maple_statement); //agregamos `` a la columna nueva que se va a agregar y removemos el '?' si es que está
            $this->maple_statement = Utils::replace_last(';', '', $this->maple_statement);
            $this->finalSql = "ALTER TABLE " . $this->tablas[0] . " ADD COLUMN " . explode($this->op, $this->maple_statement)[1] . (!$nullable ? ' NOT NULL ' : '') . ";";
        }
    }

    private function procesarDropColumn() {
        $column = trim(explode($this->op, $this->maple_statement)[1]);
        if ($column == ';') { //no hay columnas - asumimos que se quiere dropear la tabla
            $this->finalSql = 'DROP TABLE ' . $this->tablas[0];
        } else {
            $this->finalSql = "ALTER TABLE " . $this->tablas[0] . " DROP COLUMN " . $this->addQuotToColumnName(str_replace(';', '', $column));
        }
    }

    private function procesarModifyColumn() {
        if (preg_match('/<\s*?(\w+\?*)/', $this->maple_statement, $matches)) {
            $nullable = Utils::str_ends_with($matches[1], '?');
            $this->maple_statement = preg_replace_callback('/<\s*?(\w+\?*)/', function (array $match) {
                $noQuestionMark = Utils::replace_last('?', '', $match[1]);
                return "< `$noQuestionMark`";
            }, $this->maple_statement); //agregamos `` a la columna nueva que se va a agregar y removemos el '?' si es que está
            $this->maple_statement = Utils::replace_last(';', '', $this->maple_statement);
            $this->finalSql = "ALTER TABLE " . $this->tablas[0] . " MODIFY COLUMN " . explode($this->op, $this->maple_statement)[1] . (!$nullable ? ' NOT NULL ' : '') . ";";
        }
    }

    /**
     * Extrae los valores pertenecientes a una sentencia UPDATE or INSERT. Si $comoArray as dado, los valores serán
     * puestos en este array siguiendo la nomenclatura [Columna = Valor].
     * @param array $comoArray [opcional] un array en el cual serán puestos los valores
     * @return array
     */
    private function parseValores(array $comoArray = null) {
        $this->valores = trim(str_replace(';', '', substr($this->maple_statement_no_conds,
            strrpos($this->maple_statement_no_conds, $this->op) + strlen($this->op))));

        if (!is_null($comoArray)) {
            foreach (explode(',', $this->valores) as $valor) {
                $comoArray[] = trim($valor);
            }
        }
        return $comoArray;
    }

    private function stringifyCols() {
        $columnasStr = '';
        $colCount = count($this->columnas);
        if ($colCount == 0) {
            return '*';
        }
        for ($i = 0; $i < $colCount; $i++) {
            $columnasStr .= $i < $colCount - 1 ? $this->columnas[$i] . ', ' : $this->columnas[$i];
        }
        return trim(str_replace(';', '', $columnasStr));
    }

    public function getOp() {
        return $this->op;
    }

    public function getModo() {
        return $this->modo;
    }

    private function getFinalSql() {
        return $this->finalSql;
    }

    private static function splitStatements($maple_statements) {
        $separator = "\r\n";
        $line = strtok($maple_statements, $separator);

        $statements = [];
        $buffer = '';
        $isSQLStatement = false;

        while ($line !== false) {
            $line = trim($line);
            if (Utils::str_starts_with($line, '<?')) { //comienzo de bloque SQL
                //chequeamos si ya hay una sentencia en el buffer,
                //y si la hay, la mandamos al array de statements y vaciamos el buffer para dar lugar al sql que viene
                if (strlen($buffer) > 0) {
                    $statements[] = rtrim($buffer);
                    $buffer = '';
                }
                $isSQLStatement = true;
            }

            if (Utils::str_starts_with($line, '-- ')) { //remueve lineas que son solo comentarios
                if (strlen($buffer) !== 0) { //si había algo en el buffer, lo agregamos al array de statements y lo vaciamos
                    $statements[] = rtrim($buffer);
                    $buffer = '';
                }
                $line = strtok($separator);
                continue;
            }

            if (Utils::str_contains($line, '-- ')) { //remueve comentarios que sean parte de linea
                $lineWithoutComment = trim(substr($line, 0, strpos($line, '-- ')));
                $mustRestoreClosingTag = Utils::str_contains($line, '?>') && !Utils::str_ends_with($lineWithoutComment, '?>');
                $line = $lineWithoutComment . ($mustRestoreClosingTag ? ' ?>' : '');
            }

            $buffer .= "$line\n";

            if (Utils::str_ends_with($line, '?>')) {
                $line = strtok($separator);
                $isSQLStatement = false;
                $statements[] = rtrim($buffer);
                $buffer = '';
                continue;
            }

            if (!$isSQLStatement && Utils::str_ends_with(rtrim($line), ';')) {
                $statements[] = rtrim($buffer);
                $buffer = '';
            }

            $line = strtok($separator);

            if ($line === false && strlen($buffer) !== 0) { //si se acabaron las lineas y el buffer no está vacío, vaciamos el buffer en statements.
                $statements[] = rtrim($buffer);
            }
        }

        return $statements;
    }

    public static function processMaple(string $incoming, $user, $source) {
        $length = strlen($incoming);
        if ($length == 0) {
            throw new MapleException("La sentencia está vacía.");
        }
        if ($length > 20000) {
            throw new MapleException("La sentencia es demasiado grande.\nEl valor máximo de caracteres permitido es 20000.\nEncontrados: $length");
        }
        $operators = []; //used for statistics
        $hybridQuery = false; //used for statistics

        $start_time = microtime(true);
        $statements = Parser::splitStatements($incoming);
        $converted_sql = '';
        if (count($statements) == 0 || (count($statements) == 1 && $statements[0] == "")) {
            throw new MapleException("La sentencia está vacía."); //es posible que el usuario solo haya mandado comentarios
        }
        foreach ($statements as $statement) {
            if ($statement === ';' || strlen($statement) == 0) {
                continue;
            }
            if (Utils::str_starts_with($statement, '<?') && Utils::str_ends_with($statement, '?>')) {
                $sqlStmt = Utils::replace_first('<?', '', $statement);
                $sqlStmt = Utils::replace_last('?>', '', $sqlStmt);
                $sqlStmt = trim($sqlStmt);
                $sqlStmt = Utils::str_ends_with($sqlStmt, ';') ? $sqlStmt : "$sqlStmt;";
                $converted_sql .= $sqlStmt . "\n";
                $hybridQuery = true;
                continue;
            }
            $parser = new Parser($statement);
            $parser->procesar();
            $operators[] = ['modo' => $parser->getModo(), 'op' => $parser->getOp()];
            $finalSql = Utils::str_ends_with($parser->getFinalSql(), ';') ? $parser->getFinalSql() : $parser->getFinalSql() . ";";
            $converted_sql .= "$finalSql\n";
        }

        //statistics
        if ($source != null) {
            switch ($source) {
                case 'DESKTOP':
                case 'MOBILE':
                    $array_statistics = ['user' => $user, 'query_length' => $length, 'operators_and_modes' => $operators, 'isHybrid' => $hybridQuery, 'multipleQueries' => count($statements) > 1,
                        'parsing_time' => number_format((microtime(true) - $start_time) * 1000, 2), 'source' => $source];
                    Logger::query_by_user($array_statistics);
                    break;
                case 'WEB':
                    $array_statistics = ['query_length' => $length, 'operators_and_modes' => $operators, 'isHybrid' => $hybridQuery, 'multipleQueries' => count($statements) > 1,
                        'parsing_time' => number_format((microtime(true) - $start_time) * 1000, 2), 'source_ip' => $_SERVER['REMOTE_ADDR'], 'query_contents' => $incoming];
                    Logger::query_from_web($array_statistics);
                    break;
            }
        }

        return rtrim($converted_sql);
    }
}