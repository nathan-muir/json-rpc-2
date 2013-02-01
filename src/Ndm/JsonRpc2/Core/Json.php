<?php
namespace Ndm\JsonRpc2\Core;

/**
 *
 */
class Json
{

    /**
     * @param string $json
     * @param int $depth
     * @return mixed
     * @throws Exception\JsonParseException
     */
    public static function decode($json, $depth)
    {
        $data = json_decode($json, false, $depth);
        // obtain any errors
        $error = json_last_error();

        if ($error != JSON_ERROR_NONE) {
            // in case of error, return useful message why it failed to parse
            // from http://php.net/manual/en/function.json-last-error.php
            switch ($error) {
                case JSON_ERROR_DEPTH:
                    $error_text = 'Maximum request nesting depth reached';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $error_text = 'Underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $error_text = 'Unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    $error_text = 'Syntax error, malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    $error_text = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                default:
                    $error_text = 'Unknown error';
                    break;
            }
            // throw Parse Error
            //TODO decide whether to show json-parser error data or not
            throw new Exception\JsonParseException($error_text);
        }

        return $data;
    }

    /**
     * @param mixed $data
     * @return string
     */
    public static function encode($data)
    {
        return json_encode($data);
    }
}
