<?php

include("simplehtmldom/simple_html_dom.php");
include("FoodItem.php");
include("PopulateDB.php");
include_once "LogFileInfo.php";

$DAY_LIST = array(
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday",
    "Sunday",
);

$MEAL_TYPE = array("Breakfast", "Lunch", "Dinner");

parseHTML($DAY_LIST, $MEAL_TYPE);

function parseHTML($DAY_LIST, $MEAL_TYPE) {
    //make sure directory exist
    checkDir();
    $file = openLogFile(PARSE_LOG_FILE_NAME, "COMMONS MENU PARSE LOG FILE");
    writeToFile($file, "\n===================================\t
        Begin new Log\t===================================");
    writeToFile($file, "ParseHTML() :: parseHTML()");

    $url = "http://dining.kennesawstateauxiliary.com/commonsmenu.htm";
    writeToFile($file, "ParseHTML() :: Parsing $url");
    $document = file_get_html($url);
    $body = $document->getElementByTagName("body");

    prepareDataRetrieval($body, $DAY_LIST, $MEAL_TYPE, $file);

    unset($body);
    unset($document);
}

function prepareDataRetrieval(simple_html_dom_node $body, $DAY_LIST, $MEAL_TYPE, $file) {
    writeToFile($file, "ParseHTML() :: prepareDataRetrieval()");

    /*create array dictionary for food items
        [itemname : FoodItem]
    */
    $foodItemMap = array();
    $currentMeal = "";  //will store the current meal html traversal below
    $currentStation = "";   //will store the current station

    writeToFile($file, "ParseHTML() :: Pulling date of week");
    //get current week
    $weekOf = $body->find("td[class=titlecell]", 0);
    $weekOf = $weekOf->find("span", $weekOf->size-1)->innertext;

    /*pull information for each day of the week*/
    foreach ($DAY_LIST as &$day) {
        /*use $day variable to retrieve html tag with class name of $day*/
        $dayInnerTable = $body->getElementById(strtolower($day))->find("table[class=dayinner]", 0);
        if(!isset($dayInnerTable)) {
            continue;
        }
        //pull information for each meal of the day
        foreach ($MEAL_TYPE as &$mealTime) {
            $meal = strtolower(substr($mealTime,0,3));

            $tableRows = $dayInnerTable->find("tr[class=$meal]");
            if (count($tableRows) < 1) {
                if ($tableRows != null) {
                    unset($tableRows);
                }
                continue;
            }

            foreach ($tableRows as &$row) {

                $firstChild = $row->first_child();
                $className = $firstChild->getAttribute("class");
                $dataStr = removeWhiteSpace($firstChild->text());

                if($className == "mealname") {
                    if (strlen($dataStr) > 0) {
                        $currentMeal = $dataStr;
                    }
                } else if($className == "station") {
                    if (strlen($dataStr) > 0) {
                        $currentStation = $dataStr;
                    }

                    /*check if the row contains the venue station... validate this
                    by removing unwanted characters in the string and checking the
                    remaining string length*/

                    /*the current system KSU uses has a <td class="menuitem">
                    * which holds the food item name, located under a <td> tag
                    * with a station class name. However, only the first instance
                    * of the current station will be listed. Each other subsequent
                    * items associated with this station will be located under an
                    * empty td tag of class name "station" */

                    $fooditem = pullFoodItemNames($row, $currentMeal, $currentStation, $day, $weekOf);
                    //store food item in array dictionary
                    if(isset($fooditem)) {
                        $foodItemMap [string_sanitize(removeWhiteSpace($fooditem->getItem_name()), 2)] = $fooditem;
                    }
                }

                unset($firstChild);    //free memory
                unset($dataStr);
                unset($className);


            }
            unset($row);    //deallocate $row
            unset($tableRows);
        }
        unset($mealTime);   //deallocate $meal
    }
    unset($day);    //deallocate $day

    parseNutritionData($foodItemMap, $body, $file);

    writeToFile($file, "ParseHTML() :: Starting PopulateDB()");
    writeToFile($file, "\n===================================\t
        End Log\t===================================");
    //start DB populating
    startDBAccess($foodItemMap);
    closeFile($file);
}

/*
 * removes white spaces of various types from string */
function removeWhiteSpace($str) {
    $str = str_replace("&nbsp;", "", $str);
    $str = str_replace(" ", "", $str);
    return trim($str);
}

/*creates a FoodItem() object and sets attributes to the data passed in method
* method parameters
* */
function pullFoodItemNames(simple_html_dom_node $row, $mealStr, $stationStr, $dayStr, $weekOfStr) {
    echo "Item Created<br>";
    $foodItem = new FoodItem();
    $element = $row->find("span", 0);
    if(!isset($element)) {
        $element = $row->find("[class=nonuts]", 0);
        if(!isset($element)) {
            return null;
        }
    }

    $foodItem->setItem_name($element->convert_text($element->text()));
    $foodItem->setItem_price(removeWhiteSpace($row->find("[class=price]", 0)->text()));
    $foodItem->setStation(string_sanitize($stationStr, 0));
    $foodItem->setDayname($dayStr);
    $foodItem->setDay(getDayNum($dayStr));
    $foodItem->setMeal($mealStr[0] . strtolower(substr($mealStr, 1)));
    $foodItem->setMenudate($weekOfStr);

    return $foodItem;
}

function parseNutritionData(array $foodItemMap, simple_html_dom_node $body, $file){
    $scripts = $body->find("script");
    $parentClass = "nutrPkey";
    $childClass = "nutrDat";
    $newHtml = "";

    /*iterate through each script and append that html to $newHtml
    ...this html will be parsed and used later
    */
    foreach ($scripts as &$scr) {
        $newHtml .= $scr->innertext();
    }
    unset ($scr);
    unset($scripts);
    /*The following is to get the information from the javascript array*/

    //create closing and opening div tag for parent. text is pkey
    //===========================================================
    /* regex find: \saData\[\'
     * replace: </div>\n<div class="nutPkey">
    */
    $pat = "/\\saData\\[\'/";
    $newHtml = preg_replace($pat, "</div><div class=\"$parentClass\"><div class='firstChild'>", $newHtml);

    /* * create child div tag for data
      * ====================================
      * regex find: \']=[A-z]+ [A-z]+\(
      * replace: \n<div class="nutItemData">
    */
    $pat = "/\']=[A-z]+ [A-z]+\\(/";
    $newHtml = preg_replace($pat, "</div><div class=\"$childClass\">", $newHtml);

    /* close child div
    * ===================================
    * regex find: \);
    * replace: \n</div>
     * note: first pass has (?!.*\\);) added to pattern to replace all matches
     * except the last occurrence
    * */
    $pat = "/\\);(?!.*\\);)/";
    $newHtml = preg_replace($pat, "\n</div>\n", $newHtml);
    /*one more pass to close all relevant div tags*/
    $pat = "/\\);/";
    $newHtml = preg_replace($pat, "\n</div></div>\n", $newHtml);

    /*remove all comments starters..not the entire comment block*/
    $newHtml = str_replace("<!--", "", $newHtml);

    //create new body element from new html
    $body = str_get_html($newHtml);
    unset($newHtml); //free memory

    $parents = $body->find("[class=$parentClass]");

    if(isset($parents)) {
        writeToFile($file, "ParseHTML() :: Begin storing Food Data");

        foreach ($parents as $parent) {
            /*replace quotes used for each data item.. split string*/
            $data = preg_split("/,,,/", str_replace("'", ",", $parent->find("[class=$childClass]", 0)->text()));

            /*remove unwanted characters from first and last index*/
            $data[0] = str_replace(",", "", $data[0]);
            $data[count($data) - 1] = removeWhiteSpace($data[count($data) - 1]);
            $data[count($data) - 1] = removeWhiteSpace(str_replace(",", "", $data[count($data) - 1]));

            $nameKey = string_sanitize(removeWhiteSpace($data[22]), 2);
            try {
                storeFoodData($foodItemMap[$nameKey], $data, $parent->find("[class=firstChild]", 0)->text(), $nameKey, $file);

            } catch (Exception $e){
                $e->getTrace();
            }
            unset($nameKey);
            unset($data);
        }
        unset($parent);
    }
    writeToFile($file, "ParseHTML() :: Parsing Complete");

}

function storeFoodData($foodItem, array $data, $pkey, $name, $file) {
    $nvar = null;

    if(isset($foodItem)) {

//        $foodItem->setPkey(sanitizeInt($pkey));
        //serving size
        $foodItem->setServ_size($data[0]);

        //Calories
        $nvar = intval($data[1], 10);
        $foodItem->setCalories("".(int)(($nvar < 5) ? 0 : (($nvar >= 5 && $nvar <= 50) ? round($nvar/5, 0)*5 : round($nvar/10, 0)*10)));

        //fat calories
        $nvar = intval($data[2]);
        $cf_pct = (!$foodItem->getCalories() == 0) ? round(($nvar/$foodItem->getCalories())*100, 0) : 0;
        $foodItem->setCalfat("".(int)(($nvar < 5) ? 0 : (($nvar >= 5 && $nvar <= 50) ? round($nvar/5, 0)*5 : round($nvar/10, 0)*10))." ($cf_pct %)");

        //fat
        $nvar = round(floatval($data[3]), 2);
        $foodItem->setFat("". (($nvar<.50) ? 0 : (($nvar>=.50 && $nvar<5.00) ? round(round($nvar/.5,0)*.5,1) : round($nvar,0)))."g");

        //fat percent
        $foodItem->setFat_pct_dv("". intval($data[4])."%");

        //sat fat
        $nvar=round(floatval($data[5]),2);
        $foodItem->setSatfat("". (($nvar < .50) ? 0 : (($nvar >= .50 && $nvar < 5.00) ? round(round($nvar / .5, 0) * .5, 1) : round($nvar, 0)))."g");
        //sat fat percent
        $foodItem->setSatfat_pct_dv("".intval($data[6])."%");

        //transfat
        $nvar=round(floatval($data[7]),2);
        $foodItem->setTransfat("". (($nvar<.50) ? 0 : (($nvar>=.50 && $nvar<5.00) ? round(round($nvar/.5,0)*.5,1) : round($nvar,0)))."g");
        //cholesterol
        $nvar = intval($data[8]);
        $foodItem->setChol("". (($nvar<2) ? 0 : (($nvar>=2 && $nvar<5) ? "< 5" : (int)round($nvar/5,0)*5))."mg");

        //chol percent
        $foodItem->setChol_pct_dv("". intval($data[9])."%");

        //sodium
        $nvar= intval($data[10]);
        $foodItem->setSodium("".(int)(($nvar<5) ? 0 : (($nvar>=5 && $nvar<=140) ? round($nvar/5,0)*5 : round($nvar/10,0)*10))."mg");
        //sodium %
        $foodItem->setSodium_pct_dv("".intval($data[11])."%");

        //carbo
        $nvar=round(floatval($data[12]), 2);
        $foodItem->setCarbo("". (($nvar<.50) ? "0" : (($nvar>=.50 && $nvar<1.0) ? "< 1" : (int)round($nvar,0)))."g");
        //carbo %
        $foodItem->setCarbo_pct_dv("". intval($data[13])."%");

        //dietary fiber
        $nvar=round(floatval($data[14]), 2);
        $foodItem->setDfib("". (($nvar<.50) ? "0" : (($nvar>=.50 && $nvar<1.0) ? "< 1" : (int)round($nvar,0)))."g");
        //dietary fib %
        $foodItem->setDfib_pct_dv("". intval($data[15])."%");

        //sugars
        $nvar=round(floatval($data[16]), 2);
        $foodItem->setSugars("". (($nvar<.50) ? "0" : (($nvar>=.50 && $nvar<1.0) ? "< 1" : (int)round($nvar,0)))."g");

        //protein
        $nvar=round(floatval($data[17]), 2);
        $foodItem->setProtein("". (($nvar<.50) ? 0 : (($nvar>=.50 && $nvar<1.00) ? 1  : (int)round($nvar,0)))."g");

        //vit a (compare to $data[18])
        $nvar=round(floatval($data[25])/5000 * 100, 1);
        $foodItem->setVita_pct_dv("". (int)(($nvar<1.0) ? 0 : (($nvar<=2) ? 2 : (($nvar<=10.0) ? round(round($nvar/2,1)*2,0) : (($nvar<=50) ? round(round($nvar/5,1)*5,0) : round($nvar/10,0)*10))))."%");

        //vit c (compare to $data[19])
        $nvar=round(floatval($data[26])/60 * 100, 1);
        $foodItem->setVitc_pct_dv("". (int)(($nvar<1.0) ? 0 : (($nvar<=2) ? 2 : (($nvar<=10.0) ? round(round($nvar/2,1)*2,0) : (($nvar<=50) ? round(round($nvar/5,1)*5,0) : round($nvar/10,0)*10))))."%");

        //calcium (compare to $data[20])
        $nvar=round(floatval($data[27])/1000 * 100, 1);
        $foodItem->setCalcium_pct_dv("". (int)(($nvar<1.0) ? 0 : (($nvar<=2) ? 2 : (($nvar<=10.0) ? round(round($nvar/2,1)*2,0) : (($nvar<=50) ? round(round($nvar/5,1)*5,0) : round($nvar/10,0)*10))))."%");

        //iron (compare to $data[21])
        $nvar=round(floatval($data[28])/18 * 100, 1);
        $foodItem->setIron_pct_dv("".(int)(($nvar<1.0) ? 0 : (($nvar<=2) ? 2 : (($nvar<=10.0) ? round(round($nvar/2,1)*2,0) : (($nvar<=50) ? round(round($nvar/5,1)*5,0) : round($nvar/10,0)*10))))."%");

        //$data[22] is $foodItem named.. already stored...
        $foodItem->setItem_desc($data[23]);
        $allergArr = preg_split("/,/",$data[24]);
        $al = "";
        foreach($allergArr as &$allergen) {
            $temp = string_sanitize($allergen, 2);
            $temp = str_replace("Contains", "", $temp);
            if($temp == "MSG") {
                $al .= $allergen.",";
            } else {
                $al .= strtolower(substr($temp, 0, 3)).",";
            }
        }
        unset($allergen);
        unset($allergArr);
        $foodItem->setAllergens($al);
        $foodItem->setVeg_type($data[29]);

    } else {
        writeToFile($file, "Failed at: $name");
    }
}

function string_sanitize($str, $quoteStyle) {
    $ENT = null;
    if($quoteStyle == 0) {
        $ENT = ENT_NOQUOTES;
    } else if ($quoteStyle == 1) {
        $ENT = ENT_COMPAT;
    } else {
        $ENT = ENT_QUOTES;
    }

    $str = str_replace("&nbsp;", "", $str);
    $str = preg_replace("/[^a-zA-Z0-9]+/", "", html_entity_decode($str, $ENT));
    return $str;
}

function sanitizeInt($str) {
    return preg_replace("/[^0-9]+/", "", html_entity_decode($str, ENT_QUOTES));
}
function getDayNum($dayStr) {
    switch($dayStr) {
        case "Monday":
            return "1";
        case "Tuesday":
            return "2";
        case "Wednesday":
            return "3";
        case "Thursday":
            return "4";
        case "Friday":
            return "5";
        case "Saturday":
            return "6";
        case "Sunday":
            return "7";
        default:
            // we shouldn't get here
            return "1";
    }
}
