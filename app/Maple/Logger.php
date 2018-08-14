<?php

use \RedBeanPHP\R as R;

class Logger {

    public static function login_by_user($email, $data_array) {
        $new_login = R::dispense('logins');
        $new_login->id_googleusers = UserManager::getInstance()->get_user_id($email);
        $new_login->source = $data_array['source'];
        R::store($new_login);
        return true;
    }

    /**
     * Loguea una query desde la web. Esta query no requiere autenticación, es por eso que la inicialización del ORM se hace en este método.
     * @param array $statistics
     */
    public static function query_from_web(array $statistics) {
        if (!R::testConnection()) {
            R::setup("mysql:host=localhost;dbname=sqless", "root", "");
        }
        $new_query = R::dispense('webqueries');
        $new_query->query_size_bytes = $statistics['query_length'];
        $new_query->parsing_time_ms = $statistics['parsing_time'];
        $new_query->source_ip = $statistics['source_ip'];
        $new_query->query_contents = $statistics['query_contents'];

        if (count($statistics['operators_and_modes']) === 0 && $statistics['isHybrid']) { //SQL only
            $new_query->query_type = 'SQL';
        } else if ($statistics['isHybrid']) {
            $new_query->query_type = 'HYBRID';
        } else if ($statistics['multipleQueries']) {
            $new_query->query_type = 'MULTIPLE';
        } else {
            $operation = '';
            $modo = $statistics['operators_and_modes'][0]['modo'];
            $operator = $statistics['operators_and_modes'][0]['op'];
            switch ($modo) {
                case 1:
                    if ($operator == '<') {
                        $operation = 'ALTER TABLE ADD';
                    } else if ($operator == '[x]') {
                        $operation = 'ALTER TABLE DROP';
                    } else if ($operator == '<<') {
                        $operation = 'ALTER TABLE MODIFY';
                    } else if ($operator == '+') {
                        $operation = 'CREATE TABLE';
                    }
                    break;
                case 2:
                    if ($operator == '>') {
                        $operation = 'SELECT';
                    } else if ($operator == '<') {
                        $operation = 'INSERT';
                    } else if ($operator == '<<') {
                        $operation = 'UPDATE';
                    } else if ($operator == '[x]') {
                        $operation = 'DELETE';
                    }
                    break;
            }
            $new_query->query_type = $operation;
        }
        R::store($new_query);
        R::close();
    }

    public static function query_by_user(array $statistics) {
        $new_query = R::dispense('queriesbyuser');
        $new_query->id_googleusers = UserManager::getInstance()->get_user_id($statistics['user']);
        $new_query->query_size_bytes = $statistics['query_length'];
        $new_query->parsing_time_ms = $statistics['parsing_time'];
        $new_query->source = $statistics['source'];

        if (count($statistics['operators_and_modes']) === 0 && $statistics['isHybrid']) { //SQL only
            $new_query->query_type = 'SQL';
        } else if ($statistics['isHybrid']) {
            $new_query->query_type = 'HYBRID';
        } else if ($statistics['multipleQueries']) {
            $new_query->query_type = 'MULTIPLE';
        } else {
            $operation = '';
            $modo = $statistics['operators_and_modes'][0]['modo'];
            $operator = $statistics['operators_and_modes'][0]['op'];
            switch ($modo) {
                case 1:
                    if ($operator == '<') {
                        $operation = 'ALTER TABLE ADD';
                    } else if ($operator == '[x]') {
                        $operation = 'ALTER TABLE DROP';
                    } else if ($operator == '<<') {
                        $operation = 'ALTER TABLE MODIFY';
                    } else if ($operator == '+') {
                        $operation = 'CREATE TABLE';
                    }
                    break;
                case 2:
                    if ($operator == '>') {
                        $operation = 'SELECT';
                    } else if ($operator == '<') {
                        $operation = 'INSERT';
                    } else if ($operator == '<<') {
                        $operation = 'UPDATE';
                    } else if ($operator == '[x]') {
                        $operation = 'DELETE';
                    }
                    break;
            }
            $new_query->query_type = $operation;
        }
        R::store($new_query);
    }
}