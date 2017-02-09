package java;
import org.jsoup.Connection;
import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;
import org.jsoup.select.Elements;

import java.io.*;
import java.text.DecimalFormat;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.regex.Matcher;
import java.util.regex.Pattern;


public class ParseHtml {

    private static final ArrayList<String> DAY_LIST = new ArrayList<String>();

    private static final String[] COURSE_LIST = {"Breakfast", "Lunch", "Dinner"};

    static {
        DAY_LIST.add("Monday");
        DAY_LIST.add("Tuesday");
        DAY_LIST.add("Wednesday");
        DAY_LIST.add("Thursday");
        DAY_LIST.add("Friday");
        DAY_LIST.add("Saturday");
        DAY_LIST.add("Sunday");
    }

    public static void parseHtml(String url) throws IOException {

        /*establish connection to url*/
//        Connection con = Jsoup.connect(url);
        /*get html string from url*/
//        String html = con.get().html();

        String fileName = "Menu.html";
        String dirPath = "html";
        File dir = new File(dirPath);
        if(!dir.exists()) {
            dir.mkdir();
        }

        File file = new File(dir, fileName);

        StringBuilder htmlString = new StringBuilder();
        char[] buf = new char[1024];

        FileInputStream inputStream = new FileInputStream(file);
        InputStreamReader reader = new InputStreamReader(inputStream, "utf-8");

        while(reader.read(buf) > -1) {
            htmlString.append(buf);
        }

        /*parse html string into html document*/
        Document doc = Jsoup.parse(htmlString.toString());
        /*retrieve body tag from document*/
        Element body = doc.body();

        prepareDataRetrieval(body);
    }

    private static void prepareDataRetrieval(Element body) {
        /*create*/
//        ArrayList<FoodItem> foodList = new ArrayList<>();

        HashMap <String, FoodItem> foodItemMap = new HashMap<>();
        String currentMeal = "";
        String currentStation = "";
        String weekOf = body.getElementsByClass("titlecell").first().getElementsByTag("span").last().text();

        System.out.println("Week of: " + weekOf);
        /*pull data for each day*/
        for (String day : DAY_LIST) {
            /*find id in html document as a lowercase string*/
            Element dayTag = body.getElementById(day.toLowerCase());
            try {

                Element dayinnerTable = dayTag.getElementsByClass("dayinner").first();

                for (String mealTime : COURSE_LIST) {
                    String meal = mealTime.substring(0, 3).toLowerCase();

                    Elements tableRows = dayinnerTable.getElementsByClass(meal);

                    for (Element row : tableRows) {
                        Element firstChild = row.child(0);

                        if (firstChild.className().equals("mealname")) {
                    /*if the first child is the meal course name, store
                     * that name in the corresponding variable*/
                            currentMeal = firstChild.text();
                        } else if (firstChild.className().equals("station")) {
                    /*if the first child is the venue station, store
                    * that name in the corresponding variable*/

                        /*get rid of any unwanted space characters*/
                            String tempStation = firstChild.text().replace("\u00a0","");
                            if (tempStation.trim().length() > 0) {
                                currentStation = tempStation;
                            }

                        /*the current system KSU uses has a <td class="menuitem">
                        * which holds the food item name, located under a <td> tag
                        * with a station class name. However, only the first instance
                        * of the current station will be listed. Each other subsequent
                        * items associated with this station will be located under an
                        * empty td tag of class name "station" */

                            FoodItem item = pullFoodItemNames(row, currentMeal, currentStation, day, weekOf);
                            foodItemMap.put(item.getItem_name().replaceAll(" ", ""), item);


                        }

                    }
                }


            } catch (NullPointerException e) {
                e.printStackTrace();
            }

        }
        parseNutritionData(foodItemMap, body);
    }

    private static FoodItem pullFoodItemNames(Element row, String meal, String station, String day, String weekOf) {
        FoodItem item = new FoodItem();
        Element element;
        String price;

        if (row.getAllElements().hasClass("nonuts")) {
            /*for items with no nutritional information on webpage*/
            element = row.getElementsByClass("nonuts").first();
        } else {
            element = row.getElementsByTag("span").first();
        }

        /*pull and set name text from element*/
        String foodItemName = element.ownText();
        price = row.getElementsByClass("price").first().text();

        item.setItem_name(foodItemName);
        item.setItem_price(price);
        item.setStation(station);
        item.setDayname(day);
        item.setDay("" + (DAY_LIST.indexOf(day) + 1));
        item.setMeal(meal.charAt(0) + meal.substring(1).toLowerCase());
        item.setMenudate(weekOf);

        return item;
    }

    private static void parseNutritionData(HashMap<String, FoodItem> foodItemMap, Element body) {
        Elements scripts = body.select("script");
        String parentClass = "nutPkey";
        String childClass = "nutItemData";

        /*get all script text and store in string.*/
        StringBuilder sb = new StringBuilder();
        for (Element s : scripts) {
            sb.append(s.html());
        }

        //create closing and opening div tag for parent. text is pkey
        //===========================================================
        /* regex find: \saData\[\'
         * replace: </div>\n<div class="nutPkey">
        */
        Pattern pat = Pattern.compile("\\saData\\[\'");
        String html = pat.matcher(sb.toString()).replaceAll("</div>\n<div class=\"" + parentClass + "\">");

        /* * create child div tag for data
          * ====================================
          * regex find: \']=[A-z]+ [A-z]+\(
          * replace: \n<div class="nutItemData">
        */
        pat = Pattern.compile("\']=[A-z]+ [A-z]+\\(");
        html = pat.matcher(html).replaceAll("\n<div class=\"" + childClass + "\">");

        /* close child div
         * ===================================
         * regex find: \);
         * replace: \n</div>
         * */
        pat = Pattern.compile("\\);");
        html = pat.matcher(html).replaceAll("\n</div>\n");

        /*remove all comments starters..not the entire comment block*/
        html = html.replaceAll("<!--", "");

        //create new body element from new html
        body = Jsoup.parse(html).body();

        sb.delete(0, sb.length());
        Elements parents = body.getElementsByClass(parentClass);
        System.out.println(parents.size());
        for (Element parent : parents) {
            String pkey;

            /*last element usually has added, unwanted text.. so using a regex
            * pattern, store as the pkey, only the wanted information*/
            if (parent == parents.last()) {
                pat = Pattern.compile("[a-zA-z0-9]+_[a-zA-z0-9]+");
                Matcher matcher = pat.matcher(parent.ownText());

                pkey = (matcher.find()) ? matcher.group(0) : parent.ownText();

//                Turkey Baguette with Chipotle Mayo
            } else {
                pkey = parent.ownText();

            }

            /*replace quotes used for each data item.. split string*/
            String[] data = parent.child(0).text().replace("\'", ",").split(",,,");

            /*remove unwanted characters from first and last index*/
            data[0] = data[0].substring(1);
            data[data.length-1] = data[data.length-1].substring(0, data[data.length-1].length()-1);
            data[data.length-1] = data[data.length-1].trim();

            /*store data in food item from hash map*/
            /*data[22] is item name*/
            String name = data[22].replaceAll(" ", "");
            storeFoodData(foodItemMap.get(name),data, pkey, name);

        }

    }

    private static void storeFoodData(FoodItem item, String[] data, String pkey, String name) {
        double nvar;

        if (item != null) {
            DecimalFormat format = new DecimalFormat();

            item.setPkey(pkey);
            //serving size
            item.setServ_size(data[0]);

            //Calories
            nvar = Integer.parseInt(data[1]);
            item.setCalories(""+(int)((nvar < 5) ? 0 : ((nvar >= 5 && nvar <= 50) ? round(nvar/5, 0)*5 : round(nvar/10, 0)*10)));

            //fat calories
            nvar = Integer.parseInt(data[2]);
            item.setCalfat(""+(int)((nvar < 5) ? 0 : ((nvar >= 5 && nvar <= 50) ? round(nvar/5, 0)*5 : round(nvar/10, 0)*10)));

            //fat
            nvar = round(Float.parseFloat(data[3]), 2);
            item.setFat(""+ format.format((nvar<.50) ? 0 : ((nvar>=.50 && nvar<5.00) ? round(round(nvar/.5,0)*.5,1) : round(nvar,0))));

            //fat percent
            item.setFat_pct_dv("" + Integer.parseInt(data[4]));

            //sat fat
            nvar=round(Float.parseFloat(data[5]),2);
            item.setSatfat(""+ format.format((nvar < .50) ? 0 : ((nvar >= .50 && nvar < 5.00) ? round(round(nvar / .5, 0) * .5, 1) : round(nvar, 0))));
            //sat fat percent
            item.setSatfat_pct_dv(""+Integer.parseInt(data[6]));

            //transfat
            nvar=round(Float.parseFloat(data[7]),2);
            item.setTransfat(""+ format.format((nvar<.50) ? 0 : ((nvar>=.50 && nvar<5.00) ? round(round(nvar/.5,0)*.5,1) : round(nvar,0))));
            //cholesterol
            nvar = Integer.parseInt(data[8]);
            item.setChol("" + ((nvar<2) ? 0 : ((nvar>=2 && nvar<5) ? "< 5" : (int)round(nvar/5,0)*5)));

            //chol percent
            item.setChol_pct_dv("" + Integer.parseInt(data[9]));

            //sodium
            nvar= Integer.parseInt(data[10]);
            item.setSodium(""+(int)((nvar<5) ? 0 : ((nvar>=5 && nvar<=140) ? round(nvar/5,0)*5 : round(nvar/10,0)*10)));
            //sodium %
            item.setSodium_pct_dv(""+Integer.parseInt(data[11]));

            //carbo
            nvar=round(Float.parseFloat(data[12]), 2);
            item.setCarbo("" + ((nvar<.50) ? "0" : ((nvar>=.50 && nvar<1.0) ? "< 1" : (int)round(nvar,0))));
            //carbo %
            item.setCarbo_pct_dv("" + Integer.parseInt(data[13]));

            //dietary fiber
            nvar=round(Float.parseFloat(data[14]), 2);
            item.setDfib("" + ((nvar<.50) ? "0" : ((nvar>=.50 && nvar<1.0) ? "< 1" : (int)round(nvar,0))));
            //dietary fib %
            item.setDfib_pct_dv("" + Integer.parseInt(data[15]));

            //sugars
            nvar=round(Float.parseFloat(data[16]), 2);
            item.setSugars(""+ ((nvar<.50) ? "0" : ((nvar>=.50 && nvar<1.0) ? "< 1" : (int)round(nvar,0))));

            //protein
            nvar=round(Float.parseFloat(data[17]), 2);
            item.setProtein("" + ((nvar<.50) ? 0 : ((nvar>=.50 && nvar<1.00) ? 1  : (int)round(nvar,0))));

            //vit a (compare to data[18])
            nvar=round(Float.parseFloat(data[25])/5000 * 100, 1);
            item.setVita_pct_dv("" + (int)((nvar<1.0) ? 0 : ((nvar<=2) ? 2 : ((nvar<=10.0) ? round(round(nvar/2,1)*2,0) : ((nvar<=50) ? round(round(nvar/5,1)*5,0) : round(nvar/10,0)*10)))));

            //vit c (compare to data[19])
            nvar=round(Float.parseFloat(data[26])/60 * 100, 1);
            item.setVitc_pct_dv("" + (int)((nvar<1.0) ? 0 : ((nvar<=2) ? 2 : ((nvar<=10.0) ? round(round(nvar/2,1)*2,0) : ((nvar<=50) ? round(round(nvar/5,1)*5,0) : round(nvar/10,0)*10)))));

            //calcium (compare to data[20])
            nvar=round(Float.parseFloat(data[27])/1000 * 100, 1);
            item.setCalcium_pct_dv("" + (int)((nvar<1.0) ? 0 : ((nvar<=2) ? 2 : ((nvar<=10.0) ? round(round(nvar/2,1)*2,0) : ((nvar<=50) ? round(round(nvar/5,1)*5,0) : round(nvar/10,0)*10)))));

            //iron (compare to data[21])
            nvar=round(Float.parseFloat(data[28])/18 * 100, 1);
            item.setIron_pct_dv(""+(int)((nvar<1.0) ? 0 : ((nvar<=2) ? 2 : ((nvar<=10.0) ? round(round(nvar/2,1)*2,0) : ((nvar<=50) ? round(round(nvar/5,1)*5,0) : round(nvar/10,0)*10)))));

            //data[22] is item named.. already stored...
            item.setItem_desc(data[23]);
            item.setAllergens(data[24]);
            item.setVeg_type(data[29]);

            System.out.println(item.getItem_name());
            System.out.println(item.getPkey());
            System.out.println(item.getItem_desc());
            System.out.println(item.getCalories());
            System.out.println(item.getCalfat());
            System.out.println(item.getFat());
            System.out.println(item.getFat_pct_dv());
            System.out.println(item.getSatfat());
            System.out.println(item.getSatfat_pct_dv());
            System.out.println(item.getTransfat());
            System.out.println(item.getChol());
            System.out.println(item.getChol_pct_dv());
            System.out.println(item.getSodium());
            System.out.println(item.getSodium_pct_dv());
            System.out.println(item.getCarbo());
            System.out.println(item.getCarbo_pct_dv());
            System.out.println(item.getDfib());
            System.out.println(item.getDfib_pct_dv());
            System.out.println(item.getSugars());
            System.out.println(item.getProtein());
            System.out.println(item.getVita_pct_dv());
            System.out.println(item.getVitc_pct_dv());
            System.out.println(item.getCalcium_pct_dv());
            System.out.println(item.getIron_pct_dv());
            System.out.println(item.getAllergens());

            System.out.println(item.getMeal());
            System.out.println(item.getStation());
            System.out.println(item.getMenudate());
            System.out.println(item.getServ_size());
            System.out.println(item.getVeg_type());
            System.out.println(item.getDay());
            System.out.println(item.getDayname());
            System.out.println("========================================\n\n");
        } else {
            System.out.println("failed at: " + pkey + "\n for:\n" + name);
        }

    }

    public static void outPutHTML (String url) throws IOException {
        Connection con = Jsoup.connect(url);

        /*the following is a test to see if the outer html and inner html methods retreive similar or different results.*/
        String outerHtml = con.get().outerHtml();
        String innerHtml = con.get().html();
        String innerName = "innerHTML.txt";
        String outerName = "outerHTML.txt";

        String innerDesc = "Using Jsoup .connect(url).get().html to retrieve the inner HTML of a web page"
                + "\n============================================================\n\n\n";
        String outerDesc = "Using Jsoup .connect(url).get().html to retrieve the outer HTML of a web page"
                + "\n============================================================\n\n\n";

        outPutToFile(innerName, innerHtml, innerDesc);
        outPutToFile(outerName, outerHtml, outerDesc);
    }

    public static void outPutToFile(String fileName, String data, String desc) throws IOException{
        String methodName = "outPutToFile";

        String dirPath = "output";
        File dir = new File(dirPath);
        if(!dir.exists()) {
            dir.mkdir();
        }

        File file = new File(dir, fileName);

        /*if the file does not already exist then create a new file*/
        if (!file.exists()) {
            printLog(methodName, "Creating File");
            file.createNewFile();
        }

        FileOutputStream fileOutputStream = new FileOutputStream(file);
        OutputStreamWriter outputStreamWriter = new OutputStreamWriter(fileOutputStream, "utf-8");
        printLog(methodName, "Writing File");
        outputStreamWriter.write(desc);
        outputStreamWriter.write(data);

        printLog(methodName, "Closing Output Streams");
        outputStreamWriter.close();
        fileOutputStream.close();
        printLog(methodName, "Output Complete");
    }

    private static void printLog (String method, String text) {
        System.out.println(method + "\t:\t" + text);
    }


    /*x is the number of places to round*/
    private static double round(int nVal, int x) {
        return Math.round(nVal * Math.pow(10, x))/Math.pow(10, x);
    }
    private static double round(double nVal, int x) {
        return Math.round(nVal * Math.pow(10, x))/Math.pow(10, x);
    }
}
