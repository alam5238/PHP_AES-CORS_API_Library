<?php

    $set_accept = "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7";
    $set_userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';


    function fetch_it($url){

        $cookie = get_value_for_generateCookie($url);
        $results = call_api_to_get_data($url, $cookie);
        return $results;

    }

    function get_value_for_generateCookie($url_path){

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url_path,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => $GLOBALS['set_userAgent'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $html = curl_exec($ch);
        curl_close($ch);

        $start_pos_a = strpos($html, 'a=toNumbers("') + 13; 
        $a = substr($html, $start_pos_a, 32);

        $start_pos_b = strpos($html, 'b=toNumbers("') + 13;
        $b = substr($html, $start_pos_b, 32);

        $start_pos_c = strpos($html, 'c=toNumbers("') + 13;
        $c = substr($html, $start_pos_c, 32);

        if (strlen($a) !== 32 || strlen($b) !== 32 || strlen($c) !== 32) {
            die("Error: Failed to extract values from the response");
        }

        return generate_cookie($a, $b, $c);

    }


    function generate_cookie($a, $b, $c){

                    
            /**
             * Converts a hex string to a raw binary string.
             * Equivalent to how the JS 'toNumbers' prepares data for AES,
             * but directly gives the binary string openssl functions expect.
             *
             * @param string $hexString
             * @return string
             */
            function hex_to_raw_bytes(string $hexString): string
            {
                $binary = hex2bin($hexString);
                if ($binary === false) {
                    throw new InvalidArgumentException("Invalid hex string provided.");
                }
                return $binary;
            }

            /**
             * Converts a raw binary string to a lowercase hex string.
             * Equivalent to the JS 'toHex' function.
             *
             * @param string $rawBytes
             * @return string
             */
            function raw_bytes_to_hex(string $rawBytes): string
            {
                return strtolower(bin2hex($rawBytes));
            }

            /**
             * Generates the cookie value by decrypting ciphertext with given key and IV.
             *
             * @param string $c_hex Ciphertext as a hex string.
             * @param string $a_hex Key as a hex string (AES-128, so 32 hex chars / 16 bytes).
             * @param string $b_hex IV as a hex string (16 bytes for AES-128).
             * @return string|false The decrypted data as a hex string, or false on failure.
             */
            function generate_decrypted_cookie_value(string $c_hex, string $a_hex, string $b_hex)
            {
                try {
                    $ciphertext_raw = hex_to_raw_bytes($c_hex);
                    $key_raw = hex_to_raw_bytes($a_hex);
                    $iv_raw = hex_to_raw_bytes($b_hex);
                } catch (InvalidArgumentException $e) {
                    error_log("Hex conversion error: " . $e->getMessage());
                    return false;
                }

                $cipher_method = 'aes-128-cbc';

                // Using OPENSSL_NO_PADDING tells OpenSSL not to expect or remove any padding.
                // This should make it behave like the JS version which skips unpadding for 16-byte results.
                // OPENSSL_RAW_DATA is still crucial.
                $decrypted_raw = openssl_decrypt(
                    $ciphertext_raw,
                    $cipher_method,
                    $key_raw,
                    OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, // MODIFIED LINE
                    $iv_raw
                );

                if ($decrypted_raw === false) {
                    $errors = [];
                    while ($msg = openssl_error_string()) { // Get all OpenSSL error messages
                        $errors[] = $msg;
                    }
                    error_log("OpenSSL decryption failed: " . implode("; ", $errors));
                    return false;
                }

                return raw_bytes_to_hex($decrypted_raw);
            }

            $default_a_hex = $a;
            $default_b_hex = $b;
            $default_c_hex = $c;

            $input_a_hex = $_GET['a'] ?? $default_a_hex;
            $input_b_hex = $_GET['b'] ?? $default_b_hex;
            $input_c_hex = $_GET['c'] ?? $default_c_hex;

            if (strlen($input_a_hex) !== 32 || strlen($input_b_hex) !== 32 || strlen($input_c_hex) % 2 !== 0) {
                http_response_code(400); // Bad Request
                die("Invalid hex input lengths. Key (a) and IV (b) must be 32 hex characters. Ciphertext (c) must have an even number of hex characters.");
            }

            $cookie_value = generate_decrypted_cookie_value($input_c_hex, $input_a_hex, $input_b_hex);

            $results = "";
            if(!empty($cookie_value)){
                $results = $cookie_value;
            }else{
                $results = "Failed to generate cookie value. Check server error logs for details.";
            }

            return $results;
        }




        function call_api_to_get_data($url, $cookie){
        
         
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
                curl_setopt($ch, CURLOPT_HEADER, false); 

                $headers = [
                    "Accept: ".$GLOBALS['set_accept'],
                    "Cookie: __test=".$cookie, // Your cookie
                    "User-Agent: ".$GLOBALS['set_userAgent'],
                ];
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $response = curl_exec($ch);

                if (curl_errno($ch)) {
                    echo "cURL Error: " . curl_error($ch);
                } else {
                   return $response;
                }

                curl_close($ch);
        }

?>