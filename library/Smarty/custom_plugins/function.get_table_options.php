<?php
function smarty_function_get_table_options($params, $template) {
    if (!isset($params["column"])) {
        trigger_error("no column supplied");
    }
    $column = $params["column"];

    if ($column['type'] != 'foreign_key') {
        throw new CoreException("Incorrect column type: ".$column['type']);
    }

    $method = isset($column['method']) ? $column['method'] : "findAllSelect";
    $table = $column['table'];

    $value = Table::factory($table)->$method();

    if (!isset($params["assign"])) {
        return $value;
    } else {
        $template->assign($params["assign"], $value);
    }
}
