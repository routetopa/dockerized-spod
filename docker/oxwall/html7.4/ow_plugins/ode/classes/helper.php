<?php

class ODE_CLASS_Helper
{

    private static $classInstance;

    public function getInstance()
    {
        if(self::$classInstance === null)
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public static function validateDatalet($components, $params, $fields='')
    {
        /*$params = get_defined_vars();

        foreach ($params as $param)
        {
            if(empty($param))
                return false;
        }

        return true;*/

        if(empty($components) || empty($params))
            return false;

        return true;
    }

    // TODO test this function
    public static function sanitizeDataletInput(&$datalet, &$dataset, &$query)
    {
        trim($datalet);
        trim($dataset);
        trim($query);

        /*$params = get_defined_vars();

        foreach ($params as &$param)
        {
            trim($param);
        }*/
    }

}