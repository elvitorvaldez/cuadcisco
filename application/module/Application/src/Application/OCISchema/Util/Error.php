<?php

namespace Application\OCISchema\Util;

/**
 * Description of Error
 *
 * @author Roman
 */
class Error {
    
    public static function getMessageError($numberError){
        $result = "";
        $messages = array(
            "4000" => "Error de sistema.",
            "4001" => "Transacción no autorizada.",
            "4003" => "Petición no autorizada.",
            "4008" => "Usuario no encontrado.",
            "4042" => "Lenguaje no encontrado.",
            "4012" => "TimeZone inválido",
            "4200" => "El usuario ya existe.",
            "4202" => "La extensión  ya se encuentra en uso.",
            "4410" => "El servicio no está asignado a este usuario.",
            "4511" => "La dirección MAC esta en uso por otro dispositivo",
            "4495" => "Se ha alcanzado el número máximo de dispositivos permitidos.",
            "4807" => "Número telefónico no válido.",
            "4813" => "La contraseña no debe ser una de las 10 contraseñas usadas anteriormente.",
            "4901" => "Identificador de inicio de sesión no válido.",
            "4902" => 'El nombre de usuario no puede contener los caracteres %, +, \, " o caracteres del código ASCII extendido.',
            "4909" => 'Extensión no válida. No cumple con las propiedades del grupo actual.',
            "4962" => 'Contraseña no válida.',
            "5202" => 'Cuenta deshabilitada.',
            "5401" => 'Usuario no válido.',
            "5668" => "Otro perfil de dispositivo tiene el mismo nombre de usuario de autenticación.",
            "6004" => "Error en la petición de validación OCI XML."
        );
        if (!empty($messages[$numberError])) {
            $result = $messages[$numberError];
        }
        return $result;
    }
    
}
