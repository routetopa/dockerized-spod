<?php

class ODE_CLASS_InputFilter
{

    private static $classInstance;

    public static function getInstance()
    {
        if(self::$classInstance === null)
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function is_json($string)
    {
        return !empty($string) && is_string($string) && preg_match('/^("(\\.|[^"\\\n\r])*?"|[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t])+?$/',$string);
    }

    private function validateTextInputVsSqlInjection($key, $input)
    {
        //todo 'Improve this security check !!'
        /*
        if(preg_match("#^{#",$input) || preg_match("#^\[#",$input) ){
            if(json_decode($input) == FALSE)
                return true;
            else
                return false;
        }

        return preg_match("/(script)|(&lt;)|(&gt;)|(%3c)|(%3e)" .
            "|(SELECT)|(UPDATE)|(INSERT)|(DELETE)|(GRANT)|(REVOKE)|(UNION)" .
            "|(select)|(update)|(insert)|(delete)|(grant)|(revoke)|(union)|(database)" .
            "|(--)" .
            "|(&amp;lt;)|(&amp;gt;)/", $input);
        */
        return false;
    }

    private function filterParam($param){
        $cleanParam = "";
        if(filter_var($param, FILTER_VALIDATE_URL))
        {
            $cleanParam = filter_var($param, FILTER_SANITIZE_URL);
        }else if(filter_var($param, FILTER_VALIDATE_EMAIL))
        {
            $cleanParam = filter_var($param, FILTER_SANITIZE_EMAIL);
        }else if(filter_var($param, FILTER_VALIDATE_INT)){
            $cleanParam = filter_var($param, FILTER_SANITIZE_NUMBER_INT);
        }else if(filter_var($param,
                            FILTER_VALIDATE_REGEXP,
                            array(
                                "options"=>array(
                                    "regexp"=>"#^(((?:0?[1-9]|1[012])|(?:0?[1-9]|[12][0-9]|3[01])|([a-zA-Z]+))([.,]?[-.\\\/\s]))?(((?:0?[1-9]|1[012])|(?:0?[1-9]|[12][0-9]|3[01])|([a-zA-Z]+))([.,]?[-.\\\/\s]))?((?:20|19)[0-9]{2})$#")))){
            $cleanParam = date("Y-m-d", strtotime($param));
        }else{
            if( preg_match("#^\\{#",$param) || preg_match("#^\\[#",$param) || preg_match('#^"#',$param)){
                $cleanParam = $param;
            }else{
                $cleanParam = $param;
                //$cleanParam = filter_var($param, FILTER_SANITIZE_STRING);
                //$cleanParam = filter_var($cleanParam, FILTER_SANITIZE_SPECIAL_CHARS);
                //$cleanParam = filter_var($cleanParam, FILTER_SANITIZE_MAGIC_QUOTES);
            }
        }
        return $cleanParam;
    }

    private function sanitizeFlatParam(array &$clean, $key, $value){
        if ($this->validateTextInputVsSqlInjection($key, $value)) {
            return null;
        } else {
            $arrayParam = array_filter(explode("#######", $value));
            if (count($arrayParam) <= 1 && !preg_match('/#######/',$value)) {
                $clean[$key] = $this->filterParam($value);
            } else {
                $cleanArrayParam = array();
                foreach ($arrayParam as $param)
                    array_push($cleanArrayParam, $this->filterParam($param));
                $clean[$key] = $cleanArrayParam;
            }
        }
    }

    private function sanitizeArrayParam(array &$clean, $value){
        foreach($value as $k => $v) {
            $this->sanitizeFlatParam($clean, $k, $v);
        }
    }

    public function sanitizeInputs(array $params){
        $clean = array();
        foreach($params as $key => $value)
        {
            if(is_array($value)){
                $clean[$key] = [];
                $this->sanitizeArrayParam($clean[$key], $value);
            }else {
                /*if ($this->validateTextInputVsSqlInjection($value)) {
                    return null;
                } else {
                    $arrayParam = array_filter(explode("    ", $value));
                    if (count($arrayParam) == 1) {
                        $clean[$key] = $this->filterParam($value);
                    } else {
                        $cleanArrayParam = array();
                        foreach ($arrayParam as $param)
                            array_push($cleanArrayParam, $this->filterParam($param));
                        $clean[$key] = $cleanArrayParam;
                    }
                }*/
                $this->sanitizeFlatParam($clean, $key, $value);
            }
        }
        return $clean;
    }
}