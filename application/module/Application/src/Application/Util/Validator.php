<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Util;

/**
 * Description of Validator
 *
 * @author Roman
 */
class Validator {

    private static $instance;

    /**
     * Returns Instance of Validator
     * 
     * @return Validator Return an Instance
     */
    public static function getInstance() {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Sanitizes an Array
     * 
     * @param array $array Array with all data (key-value)
     * @param string $level Kind of level (low, medium, high)
     * @return array
     */
    public function sanitizeArray($array, $level = 'low') {
        $temp = array();
        foreach ($array as $key => $value) {
            $temp[$key] = $this->sanitize($value, $level);
        }
        return $temp;
    }

    /**
     * Sanitizes an input
     * 
     * @param string $value Value to sanitize
     * @param string $level Kind of level (low, medium, high)
     * @return string Value sanitized
     */
    public function sanitize($value, $level = 'low') {
        if (!is_array($value)) {
            switch ($level) {
                case "low":
                    $value = $this->replaceAccents(trim($value));
                    break;
                case "medium":
                    $value = $this->replaceAccents(trim(strip_tags($value)));
                    break;
                case "high":
                    $value = $this->removeExtendedAsciiChars($this->replaceAccents($this->escapeString(trim(htmlentities(strip_tags($value))))));
                    break;
            }
        }
        return $value;
    }

    /**
     * Removes bad chars of string
     * 
     * @param string $string Value for remove bad chars
     * @return string Value cleaned
     */
    private function escapeString($string) {
        $search = array("\\", "\x00", "\n", "\r", "'", '"', "\x1a");
        $replace = array("\\\\", "\\0", "\\n", "\\r", "\'", '\"', "\\Z");
        return str_replace($search, $replace, $string);
    }

    /**
     * Replaces accents in the string
     * 
     * @param String $string
     * @return String
     */
    private function replaceAccents($string) {
        $string = \str_replace(array('á', 'ä'), "a", $string);
        $string = \str_replace(array('Á', 'Ä'), "A", $string);
        $string = \str_replace(array('é', 'ë'), "e", $string);
        $string = \str_replace(array('É', 'Ë'), "E", $string);
        $string = \str_replace(array('í', 'ï'), "I", $string);
        $string = \str_replace(array('Í', 'Ï'), "i", $string);
        $string = \str_replace(array('ó', 'ö'), "o", $string);
        $string = \str_replace(array('Ó', 'Ö'), "O", $string);
        $string = \str_replace(array('ú', 'ü'), "u", $string);
        $string = \str_replace(array('Ú', 'Ü'), "U", $string);
        $string = \str_replace('ñ', "n", $string);
        $string = \str_replace('Ñ', "N", $string);
        $string = \str_replace("&aacute;", "a", $string);
        $string = \str_replace("&Aacute;", "A", $string);
        $string = \str_replace("&eacute;", "e", $string);
        $string = \str_replace("&Eacute;", "E", $string);
        $string = \str_replace("&iacute;", "i", $string);
        $string = \str_replace("&Iacute;", "I", $string);
        $string = \str_replace("&oacute;", "o", $string);
        $string = \str_replace("&Oacute;", "O", $string);
        $string = \str_replace("&uacute;", "u", $string);
        $string = \str_replace("&Uacute;", "U", $string);
        return $string;
    }

    /**
     * Removes extended ascii chars
     * 
     * @param String $string
     * @return String
     */
    private function removeExtendedAsciiChars($string) {
        $str_length = strlen($string);
        $new_string = "";
        for ($x = 0; $x < $str_length; $x++) {
            $character = $string[$x];
            if (ord($character) < 128) {
                $new_string = $new_string . $character;
            }
        }
        $new_string = \str_replace(array("\\", "+", "%", " ", "\"", "'"), "", $new_string);
        return $new_string;
    }

    /**
     * Validates inputs with filters specified
     * 
     * @param Array $inputArray Set of inputs
     * @param ArrayObject $filters Set of filters
     * @return Array|boolean Return false if the validation is ok or a list of array messages
     */
    public function validateArray($inputArray, $filters) {
        $error = array();
        $name = "";
        foreach ($filters as $f) {
            $name = $f["name"];
            $input = (!isset($inputArray[$name]) ? null : $inputArray[$name]);
            $resultValidations = $this->validateInput($input, $f);
            if ($resultValidations !== false) {
                $error[] = $resultValidations;
            }
        }
        if (count($error) === 0) {
            return false;
        } else {
            return $error;
        }
    }

    /**
     * Validates an input with a filters specified
     * 
     * @param string $input Value for validate
     * @param ArrayObject $filter Set of filters
     * @return Array|boolean Return false if the validation is ok or an array messages
     */
    public function validateInput($input, $filter) {
        $error = array();
        $name = $filter["name"];
        foreach ($filter["rules"] as $r) {
            $temp = explode("_", $r);
            if ($temp[0] === "pattern") {
                $tempPattern = \substr($r, 8);
                $temp = array();
                $temp[0] = "pattern";
                $temp[1] = $tempPattern;
            } else if ($temp[0] === "equalTo") {
                $tempEqualTo = \substr($r, 8);
                $temp = array();
                $temp[0] = "equalTo";
                $temp[1] = $tempEqualTo;
            } else if ($temp[0] === "distinctTo") {
                $distinctTo = \substr($r, 11);
                $temp = array();
                $temp[0] = "distinctTo";
                $temp[1] = $distinctTo;
            } else if ($temp[0] === "reverseTo") {
                $reverseTo = \substr($r, 10);
                $temp = array();
                $temp[0] = "reverseTo";
                $temp[1] = $reverseTo;
            } else if ($temp[0] === "equalOldPassword") {
                $equalOldPassword = \substr($r, 17);
                $temp = array();
                $temp[0] = "equalOldPassword";
                $temp[1] = $equalOldPassword;
            } else if ($temp[0] === "equalPassword") {
                $equalPassword = \substr($r, 14);
                $temp = array();
                $temp[0] = "equalPassword";
                $temp[1] = $equalPassword;
            } else if ($temp[0] === "distinctPassword") {
                $distinctPassword = \substr($r, 17);
                $temp = array();
                $temp[0] = "distinctPassword";
                $temp[1] = $distinctPassword;
            } else if ($temp[0] === "reversePassword") {
                $reversePassword = \substr($r, 16);
                $temp = array();
                $temp[0] = "reversePassword";
                $temp[1] = $reversePassword;
            } else if ($temp[0] === "multi" && count($temp) > 1) {
                $tempMulti = \substr($r, 6);
                $temp = array();
                $temp[0] = "multi";
                $temp[1] = $tempMulti;
            }
            if (!is_array($input)) {
                $value = $this->sanitize($input);
            } else {
                $value = $input;
            }
            $totalOptions = count($temp);
            $options = array();
            if ($totalOptions > 1) {
                for ($x = 1; $x < $totalOptions; $x++) {
                    $options[] = $temp[$x];
                }
            }
            $resultValidation = $this->checkValidation($value, $name, $temp[0], $options);
            if ($resultValidation !== false) {
                $error = $resultValidation;
                break;
            }
        }
        if (!empty($error) && $error !== false) {
            return $error;
        } else {
            return false;
        }
    }

    /**
     * Checks validation
     * 
     * @param string $value Value for validate
     * @param string $input Name of input
     * @param string $rule Rule for apply
     * @param Array $options Aditional params
     * @return Array|boolean Return false if the validation is ok or an array messages
     */
    private function checkValidation($value, $input, $rule, $options = array()) {
        $isError = false;
        $paremeters = null;
        switch ($rule) {
            case "required":
                $isError = empty($value);
                break;
            case "email":
                $isError = !\filter_var($value, \FILTER_VALIDATE_EMAIL);
                break;
            case "url":
                $isError = !\filter_var($value, \FILTER_VALIDATE_URL);
                break;
            case "date":
                $date = explode("-", $value);
                $isError = !checkdate($date[1], $date[2], $date[0]);
                break;
            case "number":
                $isError = !is_numeric($value);
                break;
            case "creditcard":
                break;
            case "equalTo":
                $isError = ($options[0] !== $value);
                break;
            case "distinctTo":
                $isError = ($options[0] === $value);
                break;
            case "reverseTo":
                $isError = (\strrev($options[0]) === $value);
                break;
            case "equalOldPassword":
                $isError = ($options[0] !== $value);
                break;
            case "equalPassword":
                $isError = ($options[0] !== $value);
                break;
            case "distinctPassword":
                $isError = ($options[0] === $value);
                break;
            case "reversePassword":
                $isError = (\strrev($options[0]) === $value);
                break;
            case "maxlength":
                $options[0] = (float) $options[0];
                $paremeters = $options[0];
                $isError = (strlen($value) > $options[0]);
                break;
            case "minlength":
                $options[0] = (float) $options[0];
                $paremeters = $options[0];
                $isError = (strlen($value) < $options[0]);
                break;
            case "password":
                $isError = !@preg_match('/(?=^.{8,}$)(?=.*\\d)(?=.*[\\.,\\-_!@#$%^&*]+)(?![.\\n])(?=.*[A-Z])(?=.*[a-z]).*$/', $value);
                break;
            case "rangelength":
                $options[0] = (float) $options[0];
                $options[1] = (float) $options[1];
                $paremeters = array($options[0], $options[1]);
                $isError = !(strlen($value) >= $options[0] && strlen($value) <= $options[1]);
                break;
            case "range":
                $value = (float) $value;
                $options[0] = (float) $options[0];
                $options[1] = (float) $options[1];
                $paremeters = array($options[0], $options[1]);
                $isError = !($value >= $options[0] && $value <= $options[1]);
                break;
            case "max":
                $value = (float) $value;
                $options[0] = (float) $options[0];
                $paremeters = $options[0];
                $isError = ($value > $options[0]);
                break;
            case "min":
                $value = (float) $value;
                $options[0] = (float) $options[0];
                $paremeters = $options[0];
                $isError = ($value < $options[0]);
                break;
            case "pattern":
                $isError = !(@preg_match($options[0], $value));
                break;
            case "multi":
                if (count($options) > 0) {
                    $isError = !(is_array($value) && count($value) == $options[0]);
                    $paremeters = $options[0];
                } else {
                    $isError = !(is_array($value) && count($value) > 0);
                }
                break;
            case "uuid5":
                $isError = !(@preg_match("/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/", $value));
                break;
        }
        if ($isError) {
            return array(
                "name" => $input,
                "message" => $this->getErrorValidationMessage($rule, $paremeters)
            );
        } else {
            return $isError;
        }
    }

    /**
     * Gets error validation message
     * 
     * @param string $key Kind of message
     * @param Array $options Aditional params
     * @return string Test of Error Validation Message
     */
    private function getErrorValidationMessage($key, $options = null) {
        $msg = "";
        $messages = array(
            "required" => "Este campo es obligatorio.",
            "email" => "Por favor, escribe una dirección de correo válida.",
            "url" => "Por favor, escribe una URL válida.",
            "date" => "Por favor, escribe una fecha válida.",
            "number" => "Por favor, escribe un número válido de tarjeta.",
            "creditcard" => "Por favor, escribe los digitos de una tarjeta válida.",
            "equalTo" => "Por favor, escribe el mismo valor de nuevo.",
            "equalOldPassword" => "Por favor, escriba la contraseña actual.",
            "equalPassword" => "Por favor, escriba la misma contraseña.",
            "distinctTo" => "Por favor, no repita el mismo valor.",
            "distinctPassword" => "Por favor, las contraseñas no deben ser las mismas.",
            "reverseTo" => "Por favor, no invierta el mismo valor.",
            "reversePassword" => "Por favor, no trate de invertir la contraseña actual.",
            "maxlength" => "Por favor, no escribas más de {0} carácteres.",
            "minlength" => "Por favor, no escribas menos de {0} carácteres.",
            "rangelength" => "Por favor, escribe un valor entre {0} y {1} carácteres.",
            "range" => "Por favor, escribe un valor entre {0} y {1}.",
            "password" => "Por favor, escriba una contraseña segura.",
            "max" => "Por favor, escribe un valor menor o igual a {0}.",
            "min" => "Por favor, escribe un valor mayor o igual a {0}.",
            "pattern" => "Este campo es incorrecto.",
            "multi" => (count($options) == 1 ?
                    "Debes elegir como máximo {0} elemento(s)." :
                    "Debes seleccionar al menos un elemento."),
            "uuid5" => "Por favor, escribe un token válido"
        );
        if ($messages[$key] != null) {
            if ($key == "rangelength" || $key == "range") {
                $msg = \str_replace(array("{0}", "{1}"), $options, $messages[$key]);
            } else if ($key == "maxlength" || $key == "minlength" ||
                    $key == "max" || $key == "min" || ($key == "multi" && count($options) == 1)
            ) {
                $msg = \str_replace("{0}", $options, $messages[$key]);
            } else {
                $msg = $messages[$key];
            }
        }
        return $msg;
    }

}
