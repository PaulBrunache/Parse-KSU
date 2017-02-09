<?php


require "DB_Connect.php";

$statement = null;
$result = null;


function startDBAccess(array $foodItemMap) {
    //make sure directory exists
    checkDir();
    //create or open log file
    $file = openLogFile(DB_LOG_FILE_NAME, "DATABASE ACCESS LOG FILE");
    writeToFile($file, "\n===================================\t
        Begin new Log\t===================================");
    writeToFile($file, "PopulateDB() :: startDBAccess()");

    $TABLE = "COMMONS";

    while (!clearDB($TABLE, $file)) {
        writeToFile($file, "PopulateDB() :: startDBAccess Loop");
        sleep(10);
    }

    populateDB($TABLE, $foodItemMap, null, null, $file);
    writeToFile($file, "\n===================================\t
        End Log\t===================================");
    closeFile($file);
}



function clearDB($TABLE, $file) {
    writeToFile($file, "PopulateDB() :: clearDB()");

    $dbConn = new DB_Connect($file);
    $conn = $dbConn->getConn();
    $result = null;

    if(mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE '".$TABLE."'")) == 1) {
        //table exists..
        //clear existing data in table
        $statement = "TRUNCATE TABLE $TABLE";
        $result = mysqli_query($conn, $statement);
        if(!$result) {
            writeToFile($file, "PopulateDB() :: Failed to Truncate table ". mysqli_error($conn));
        } else {
            writeToFile($file,"PopulateDB() :: Successful Truncation");
        }
        return $result;
    } else {
        writeToFile($file,"PopulateDB() :: Table Does Not exist...Creating Table");

        while (($result = createTableCommons($conn, $TABLE, $file)) == false) {
            writeToFile($file, "PopulateDB() :: clearDB Loop");
            sleep(10);
        }
    }

    //close connection
    $dbConn->closeConn();
    return $result;
}

function createTableCommons($conn, $TABLE, $file) {
    writeToFile($file, "PopulateDB() :: createTableCommons()");

    //create table
    $statement = "CREATE TABLE IF NOT EXISTS $TABLE (local_Pkey int(11) NOT NULL AUTO_INCREMENT,
          pkey text COLLATE utf8_unicode_ci,
          menudate text COLLATE utf8_unicode_ci,
          week text COLLATE utf8_unicode_ci,
          day text COLLATE utf8_unicode_ci,
          dayname text COLLATE utf8_unicode_ci,
          meal text COLLATE utf8_unicode_ci,
          sort text COLLATE utf8_unicode_ci,
          station text COLLATE utf8_unicode_ci,
          course text COLLATE utf8_unicode_ci,
          item_name text COLLATE utf8_unicode_ci,
          item_desc text COLLATE utf8_unicode_ci,
          item_price text COLLATE utf8_unicode_ci,
          serv_size text COLLATE utf8_unicode_ci,
          veg_type text COLLATE utf8_unicode_ci,
          bal_type text COLLATE utf8_unicode_ci,
          allergens text COLLATE utf8_unicode_ci,
          calories text COLLATE utf8_unicode_ci,
          fat text COLLATE utf8_unicode_ci,
          fat_pct_dv text COLLATE utf8_unicode_ci,
          calfat text COLLATE utf8_unicode_ci,
          satfat text COLLATE utf8_unicode_ci,
          satfat_pct_dv text COLLATE utf8_unicode_ci,
          pufa text COLLATE utf8_unicode_ci,
          transfat text COLLATE utf8_unicode_ci,
          chol text COLLATE utf8_unicode_ci,
          chol_pct_dv text COLLATE utf8_unicode_ci,
          sodium text COLLATE utf8_unicode_ci,
          sodium_pct_dv text COLLATE utf8_unicode_ci,
          carbo text COLLATE utf8_unicode_ci,
          carbo_pct_dv text COLLATE utf8_unicode_ci,
          dfib text COLLATE utf8_unicode_ci,
          dfib_pct_dv text COLLATE utf8_unicode_ci,
          sugars text COLLATE utf8_unicode_ci,
          protein text COLLATE utf8_unicode_ci,
          vita_pct_dv text COLLATE utf8_unicode_ci,
          vitc_pct_dv text COLLATE utf8_unicode_ci,
          calcium_pct_dv text COLLATE utf8_unicode_ci,
          iron_pct_dv text COLLATE utf8_unicode_ci,
          ingredient text COLLATE utf8_unicode_ci,
          PRIMARY KEY (local_Pkey)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=538";
    $result = mysqli_query($conn, $statement);
    if(!$result){
        writeToFile($file, "PopulateDB() :: Failed to create table " . mysqli_error($conn));
    } else {
        writeToFile($file, "PopulateDB() :: Successful table creation");
    }
    return $result;
}

function populateDB($TABLE, array $foodItemMap, $conn = null, $dbConn = null, $file) {
    writeToFile($file, "PopulateDB() :: populateDB");

    if($conn == null || $dbConn == null) {
        //start connection
        $dbConn = new DB_Connect($file);
        $conn = $dbConn->getConn();
    }

    //check connection
    if(isset($conn)) {
        $foodItemCount = count($foodItemMap);
        $failedItems = array();
        foreach($foodItemMap as &$foodItem) {
            $data = $foodItem->getAllAttributes();
            $statement = "INSERT INTO $TABLE
                (
                    pkey, menudate, week, day, dayname,
                    meal, sort, station, course, item_name, item_desc,
                    item_price, serv_size, veg_type, bal_type, allergens,
                    calories, fat, fat_pct_dv, calfat, satfat, satfat_pct_dv,
                    pufa, transfat, chol, chol_pct_dv, sodium, sodium_pct_dv,
                    carbo, carbo_pct_dv, dfib, dfib_pct_dv, sugars, protein,
                    vita_pct_dv, vitc_pct_dv, calcium_pct_dv, iron_pct_dv, ingredient
                ) VALUES (
                    '".cleanString($data['pkey'])."','".cleanString($data['menudate'])."','".cleanString($data['week'])."',
                    '".cleanString($data['day'])."','".cleanString($data['dayname'])."','".cleanString($data['meal'])."',
                    '".cleanString($data['sort'])."','".cleanString($data['station'])."',
                    '".cleanString($data['course'])."','".cleanString($data['item_name'])."',
                    '".cleanString($data['item_desc'])."','".cleanString($data['item_price'])."',
                    '".cleanString($data['serv_size'])."','".cleanString($data['veg_type'])."',
                    '".cleanString($data['bal_type'])."','".cleanString($data['allergens'])."',
                    '".cleanString($data['calories'])."','".cleanString($data['fat'])."',
                    '".cleanString($data['fat_pct_dv'])."','".cleanString($data['calfat'])."',
                    '".cleanString($data['satfat'])."','".cleanString($data['satfat_pct_dv'])."',
                    '".cleanString($data['pufa'])."','".cleanString($data['transfat'])."',
                    '".cleanString($data['chol'])."','".cleanString($data['chol_pct_dv'])."',
                    '".cleanString($data['sodium'])."','".cleanString($data['sodium_pct_dv'])."',
                    '".cleanString($data['carbo'])."','".cleanString($data['carbo_pct_dv'])."',
                    '".cleanString($data['dfib'])."','".cleanString($data['dfib_pct_dv'])."',
                    '".cleanString($data['sugars'])."','".cleanString($data['protein'])."',
                    '".cleanString($data['vita_pct_dv'])."','".cleanString($data['vitc_pct_dv'])."',
                    '".cleanString($data['calcium_pct_dv'])."','".cleanString($data['iron_pct_dv'])."',
                    '".cleanString($data['ingredient'])."'
                )";

            $result = mysqli_query($conn, $statement);
            if(!$result) {
                writeToFile($file, "PopulateDB() :: Failed to store" .$data['item_name']. " " .mysqli_error($conn));
                array_push($failedItems, $foodItemMap[string_sanitize(removeWhiteSpace($data['item_name']), 2)]);
            } else {
                $foodItemCount--;
            }
        }
        unset($foodItem);

        writeToFile($file, "PopulateDB() :: $foodItemCount failed to be stored in database");

        /*in case all the data wasnt stored... try again with only the data that failed to be stored*/
        if(count($failedItems) > 0) {
            /*recursively call the function and pass in the optional parameters*/
            populateDB($TABLE, $failedItems, $conn, $dbConn, $file);
        } else {
            //close connection
            $dbConn->closeConn();
            writeToFile($file, "PopulateDB() :: Database population complete");
        }
    }
}

function cleanString($str) {
    return htmlspecialchars($str, ENT_COMPAT, "UTF-8");
}
