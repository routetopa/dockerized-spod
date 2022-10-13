<?php

class ODE_CLASS_Tools
{
    private static $classInstance;

    public static function getInstance()
    {
        if(self::$classInstance === null)
            self::$classInstance = new self();

        return self::$classInstance;
    }

    public function get_all_datalet_definitions()
    {
        $definitions = '';

        $ch = curl_init($preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_datalet_list')->defaultValue);//1000 limit!
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (200 == $retcode) {
            $data = json_decode($res, true);
            foreach ($data as $datalet)
            {
                $definitions .= '<script type="module" src="'.$datalet['url'].$datalet['name'].'.js"></script>';
            }
        }

        return $definitions;
    }

    public function decodeJwt($jwt)
    {
        //$header = $authorization_header;
        //$jwt_access_token = substr( $header, 7 ); // Remove "Bearer " string
        $jwt_access_token = $jwt;

        $separator = '.';

        if (2 !== substr_count($jwt_access_token, $separator)) {
            throw new \Exception("Incorrect access token format");
        }

        list($header, $payload, $signature) = explode($separator, $jwt_access_token);

        $decoded_signature = base64_decode(str_replace(array('-', '_'), array('+', '/'), $signature));

        // The header and payload are signed together
        $payload_to_verify = utf8_decode($header . $separator . $payload);

        // however you want to load your public key
        $public_key = file_get_contents( OW::getPluginManager()->getPlugin('ode')->getRootDir() . '/cert/pubkey.pem' );

        // default is SHA256
        $verified = openssl_verify($payload_to_verify, $decoded_signature, $public_key, OPENSSL_ALGO_SHA256);

        if ($verified !== 1) {
            throw new Exception("Cannot verify signature");
        }

        return base64_decode($payload);
    }

    public function getUserFromJWT($jwt)
    {
        try {
            if(empty($jwt))
                throw new Exception("No jwt provided");

            $decoded_token = $this->decodeJwt($jwt);

            if (!empty($decoded_token)) {

                $decoded_token = json_decode($decoded_token);

                if(empty($decoded_token->email))
                    throw new Exception("No user mail in decoded message");

                $user = BOL_UserService::getInstance()->findByEmail($decoded_token->email);
                if ($user)
                    return $user->id;
            }else{
                throw new Exception("Empty decoded token");
            }

        }catch (Exception $e)
        {
            throw new Exception($e->getMessage());
        }

        throw new Exception("User " . $decoded_token->email . " not recognized");
    }

}