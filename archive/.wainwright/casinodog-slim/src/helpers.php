<?php
if (!function_exists('save_log')) {
    function save_log($type, $message, $extra_data = NULL) {
        $data = [
            'message' => $message
        ];
        return \Wainwright\CasinoDog\Models\DataLogger::save_log($type, $data, $extra_data);
    }
    }

    if (!function_exists('replaceInFile')) {
        function replaceInFile($search, $replace, $path)
        {
            file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
        }
    }

    if (!function_exists('replaceInBetweenInFile')) {
        function replaceInBetweenInFile($a, $b, $replace, $path)
        {
            $file_get_contents = file_get_contents($path);
            $in_between = in_between($a, $b, $file_get_contents);
            if($in_between) {
                $search_string = stripcslashes($a.$in_between.$b);
                $replace_string = stripcslashes($a.$replace.$b);
                file_put_contents($path, str_replace($search_string, $replace_string, file_get_contents($path)));
                return true;
            }
            return true;
        }
    }

    if (!function_exists('in_between')) {
        function in_between($a, $b, $data)
        {
            preg_match('/'.$a.'(.*?)'.$b.'/s', $data, $match);
            if(!isset($match[1])) {
                return false;
            }
            return $match[1];
        }
    }

