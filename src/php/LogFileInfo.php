<?php


define('LOG_DIR', "../../logs/");
define('DB_LOG_FILE_NAME', "dbAccessLog.txt");
define('PARSE_LOG_FILE_NAME', "parseLog.txt");

function checkDir() {
    if(!file_exists(LOG_DIR)) {
        mkdir(LOG_DIR, 0700,true);
    }
}
function openLogFile($fileName, $desc) {
    if(!file_exists(LOG_DIR.$fileName)) {
        $file = fopen(LOG_DIR.$fileName, "a+");
        fwrite($file, $desc."\n========================================================\n\n");
        return $file;
    } else {
        return fopen(LOG_DIR.$fileName, "a+");
    }
}

function writeToFile($file, $data) {
    $time = "" . date("Y-m-d") . " :: " . date("h:i:sa");
    fwrite($file, "\n" .$time. "\t:\t");
    fwrite($file, $data);

        //TODO error handle here
}

function closeFile($file) {
    fclose($file);
}
