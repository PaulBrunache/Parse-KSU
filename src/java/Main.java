package java;

import java.io.IOException;
import java.net.URISyntaxException;


public class Main {

    public static void main (String[] args) throws IOException, URISyntaxException {

        String url = "http://dining.kennesawstateauxiliary.com/commonsmenu.htm";

        ParseHtml.parseHtml(url);

    }
}
