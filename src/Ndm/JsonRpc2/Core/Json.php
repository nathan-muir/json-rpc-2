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
     *
     * @return mixed
     *
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
     * @param bool $pretty
     *
     * @return string
     */
    public static function encode($data, $pretty=false)
    {
        if (!$pretty){
            return json_encode($data);
        } elseif(version_compare(phpversion(), '5.4', '>=')) {
            return json_encode($data, JSON_PRETTY_PRINT);
        } else {
            return self::pretty_fallback(json_encode($data));
        }
    }

    /**
     * @param string $json
     *
     * @return string
     */
    public static function pretty($json){
        if(version_compare(phpversion(), '5.4', '>=')){
            return self::encode(self::decode($json,512),true);
        } else {
            return self::pretty_fallback($json);
        }
    }

    /**
     * From http://stackoverflow.com/a/9776726/494182
     *
     * @param string $json
     *
     * @return string
     */
    private static function pretty_fallback($json){
        $result = '';
        $level = 0;
        $prev_char = '';
        $in_quotes = false;
        $ends_line_level = null;
        $json_length = strlen( $json );

        for( $i = 0; $i < $json_length; $i++ ) {
            $char = $json[$i];
            $new_line_level = null;
            $post = "";
            if( $ends_line_level !== null ) {
                $new_line_level = $ends_line_level;
                $ends_line_level = null;
            }
            if( $char === '"' && $prev_char != '\\' ) {
                $in_quotes = !$in_quotes;
            } else if( ! $in_quotes ) {
                switch( $char ) {
                    case '}': case ']':
                        $level--;
                        $ends_line_level = null;
                        $new_line_level = $level;
                        break;

                    case '{': case '[':
                        $level++;
                    case ',':
                        $ends_line_level = $level;
                        break;

                    case ':':
                        $post = " ";
                        break;

                    case " ": case "\t": case "\n": case "\r":
                        $char = "";
                        $ends_line_level = $new_line_level;
                        $new_line_level = null;
                        break;
                }
            }
            if( $new_line_level !== null ) {
                $result .= "\n".str_repeat( "\t", $new_line_level );
            }
            $result .= $char.$post;
            $prev_char = $char;
        }

        return $result;
    }
}
