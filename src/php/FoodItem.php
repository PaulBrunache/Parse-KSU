<?php


class FoodItem
{
    private static $count = 0;
    private $pkey = 0;
    private $menudate = "";
    private $week = "";
    private $day = "";
    private $dayname = "";
    private $meal = "";
    private $sort = "";
    private $station = "";
    private $course = "";
    private $item_name = "";
    private $item_desc = "";
    private $item_price = "";
    private $serv_size = "";
    private $veg_type = "";
    private $bal_type = "";
    private $allergens = "";
    private $calories = "";
    private $fat = "";
    private $fat_pct_dv = "";
    private $calfat = "";
    private $satfat = "";
    private $satfat_pct_dv = "";
    private $pufa = "";
    private $transfat = "";
    private $chol = "";
    private $chol_pct_dv = "";
    private $sodium = "";
    private $sodium_pct_dv = "";
    private $carbo = "";
    private $carbo_pct_dv = "";
    private $dfib = "";
    private $dfib_pct_dv = "";
    private $sugars = "";
    private $protein = "";
    private $vita_pct_dv = "";
    private $vitc_pct_dv = "";
    private $calcium_pct_dv = "";
    private $iron_pct_dv = "";
    private $ingredient = "";

    function __construct() {
        FoodItem::$count++;
        $this->pkey = FoodItem::$count;
        echo "Count : ".FoodItem::$count;
    }

    public function getAllAttributes() {
        $attributeSet  = array();
        foreach($this as $var => &$val) {
            $attributeSet[$var] = $val;
        }
        return $attributeSet;
    }
    public function getPkey()
    {
        return $this->pkey;
    }

    public function setPkey($pkey)
    {
        $this->pkey = $pkey;
    }

    public function getMenudate()
    {
        return $this->menudate;
    }

    public function setMenudate($menudate)
    {
        $this->menudate = $menudate;
    }

    public function getWeek()
    {
        return $this->week;
    }

    public function setWeek($week)
    {
        $this->week = $week;
    }

    public function getDay()
    {
        return $this->day;
    }

    public function setDay($day)
    {
        $this->day = $day;
    }

    public function getDayname()
    {
        return $this->dayname;
    }

    public function setDayname($dayname)
    {
        $this->dayname = $dayname;
    }

    public function getMeal()
    {
        return $this->meal;
    }

    public function setMeal($meal)
    {
        $this->meal = $meal;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    public function getStation()
    {
        return $this->station;
    }

    public function setStation($station)
    {
        $this->station = $station;
    }

    public function getCourse()
    {
        return $this->course;
    }

    public function setCourse($course)
    {
        $this->course = $course;
    }

    public function getItem_name()
    {
        return $this->item_name;
    }

    public function setItem_name($item_name)
    {
        $this->item_name = $item_name;
    }

    public function getItem_desc()
    {
        return $this->item_desc;
    }

    public function setItem_desc($item_desc)
    {
        $this->item_desc = $item_desc;
    }

    public function getItem_price()
    {
        return $this->item_price;
    }

    public function setItem_price($item_price)
    {
        $this->item_price = $item_price;
    }

    public function getServ_size()
    {
        return $this->serv_size;
    }

    public function setServ_size($serv_size)
    {
        $this->serv_size = $serv_size;
    }

    public function getVeg_type()
    {
        return $this->veg_type;
    }

    public function setVeg_type($veg_type)
    {
        $this->veg_type = $veg_type;
    }

    public function getBal_type()
    {
        return $this->bal_type;
    }

    public function setBal_type($bal_type)
    {
        $this->bal_type = $bal_type;
    }

    public function getAllergens()
    {
        return $this->allergens;
    }

    public function setAllergens($allergens)
    {
        $this->allergens = $allergens;
    }

    public function getCalories()
    {
        return $this->calories;
    }

    public function setCalories($calories)
    {
        $this->calories = $calories;
    }

    public function getFat()
    {
        return $this->fat;
    }

    public function setFat($fat)
    {
        $this->fat = $fat;
    }

    public function getFat_pct_dv()
    {
        return $this->fat_pct_dv;
    }

    public function setFat_pct_dv($fat_pct_dv)
    {
        $this->fat_pct_dv = $fat_pct_dv;
    }

    public function getCalfat()
    {
        return $this->calfat;
    }

    public function setCalfat($calfat)
    {
        $this->calfat = $calfat;
    }

    public function getSatfat()
    {
        return $this->satfat;
    }

    public function setSatfat($satfat)
    {
        $this->satfat = $satfat;
    }

    public function getSatfat_pct_dv()
    {
        return $this->satfat_pct_dv;
    }

    public function setSatfat_pct_dv($satfat_pct_dv)
    {
        $this->satfat_pct_dv = $satfat_pct_dv;
    }

    public function getPufa()
    {
        return $this->pufa;
    }

    public function setPufa($pufa)
    {
        $this->pufa = $pufa;
    }

    public function getTransfat()
    {
        return $this->transfat;
    }

    public function setTransfat($transfat)
    {
        $this->transfat = $transfat;
    }

    public function getChol()
    {
        return $this->chol;
    }

    public function setChol($chol)
    {
        $this->chol = $chol;
    }

    public function getChol_pct_dv()
    {
        return $this->chol_pct_dv;
    }

    public function setChol_pct_dv($chol_pct_dv)
    {
        $this->chol_pct_dv = $chol_pct_dv;
    }

    public function getSodium()
    {
        return $this->sodium;
    }

    public function setSodium($sodium)
    {
        $this->sodium = $sodium;
    }

    public function getSodium_pct_dv()
    {
        return $this->sodium_pct_dv;
    }

    public function setSodium_pct_dv($sodium_pct_dv)
    {
        $this->sodium_pct_dv = $sodium_pct_dv;
    }

    public function getDfib()
    {
        return $this->dfib;
    }

    public function setDfib($dfib)
    {
        $this->dfib = $dfib;
    }

    public function getDfib_pct_dv()
    {
        return $this->dfib_pct_dv;
    }

    public function setDfib_pct_dv($dfib_pct_dv)
    {
        $this->dfib_pct_dv = $dfib_pct_dv;
    }

    public function getSugars()
    {
        return $this->sugars;
    }

    public function setSugars($sugars)
    {
        $this->sugars = $sugars;
    }

    public function getProtein()
    {
        return $this->protein;
    }

    public function setProtein($protein)
    {
        $this->protein = $protein;
    }

    public function getVita_pct_dv()
    {
        return $this->vita_pct_dv;
    }

    public function setVita_pct_dv($vita_pct_dv)
    {
        $this->vita_pct_dv = $vita_pct_dv;
    }

    public function getVitc_pct_dv()
    {
        return $this->vitc_pct_dv;
    }

    public function setVitc_pct_dv($vitc_pct_dv)
    {
        $this->vitc_pct_dv = $vitc_pct_dv;
    }

    public function getCalcium_pct_dv()
    {
        return $this->calcium_pct_dv;
    }

    public function setCalcium_pct_dv($calcium_pct_dv)
    {
        $this->calcium_pct_dv = $calcium_pct_dv;
    }

    public function getIron_pct_dv()
    {
        return $this->iron_pct_dv;
    }

    public function setIron_pct_dv($iron_pct_dv)
    {
        $this->iron_pct_dv = $iron_pct_dv;
    }

    public function getIngredient()
    {
        return $this->ingredient;
    }

    public function setIngredient($ingredient)
    {
        $this->ingredient = $ingredient;
    }

    public function getCarbo()
    {
        return $this->carbo;
    }

    public function setCarbo($carbo)
    {
        $this->carbo = $carbo;
    }

    public function getCarbo_pct_dv()
    {
        return $this->carbo_pct_dv;
    }

    public function setCarbo_pct_dv($carbo_pct_dv)
    {
        $this->carbo_pct_dv = $carbo_pct_dv;
    }
}
