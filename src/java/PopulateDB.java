package java;

import java.sql.Connection;
import java.sql.SQLException;
//moving project to PHP from java

public class PopulateDB {

    private static final String tableName = "mammals";
    private static final int DELAY = 5000;

    public static void main(String[] args) {
        if (!DBConnect.loadDriver()) {
            System.out.println("Failed to load driver");
            System.exit(0);
        }

    }

}
