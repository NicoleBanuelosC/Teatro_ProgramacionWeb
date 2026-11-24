<?php

/** 
 * implementación de un sistema de autenticación basado en Tokens JWT (JSON Web Tokens) en
 * lugar de guardar la sesión del usuario en el servidor, se genera un "ticket" (token) que el navegador guarda y envia en cada petición.
 * esto nos permite autenticar la web como la app de android con el mismo sistema.
*/

class JWT_Helper {
    private static $secret_key = 'nicole22dic2004'; 
    private static $algorithm = 'HS256';

    //aqui se crea el token
    public static function generate($payload) {
        $header = json_encode(['alg' => self::$algorithm, 'typ' => 'JWT']); //Describe el algoritmo y tipo
        $payload['exp'] = time() + (60 * 60 * 2); // expira en 2 horas
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true); //firma todo con una clave secreta usando hash_hmac
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }//generate

    //cuando el usuario hace una peticion al panel, separa el token en sus 3 partes, revisa la expiración, recalcula la firma con la misma
    //clave secreta y compara la furma, si coincide, el token es valido y devuelve los datos del usuaio, de lo contrario devuelve false
    public static function verify($jwt) {
        $tokenParts = explode('.', $jwt);
        if (count($tokenParts) !== 3) return false;

        $header = base64_decode($tokenParts[0]);
        $payload = json_decode(base64_decode($tokenParts[1]), true);
        $signatureProvided = $tokenParts[2];

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false; // Token expirado
        }//if

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $signatureProvided === $base64UrlSignature ? $payload : false;
    }//verif
}