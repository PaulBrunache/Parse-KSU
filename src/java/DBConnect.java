package java;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;


public class DBConnect {

    private static final String URL = "jdbc:mysql://localhost:3306/javabase";
    private static final String USERNAME = "java";
    private static final String PASSWORD = "jeni42";

    public static Connection connectDB() {
        System.out.println("Connecting to database...");

        try (Connection connection = DriverManager.getConnection(URL, USERNAME, PASSWORD))
        {
            System.out.println("Database connected!");
            return connection;
        } catch (SQLException e) {
            throw new IllegalStateException("Cannot connect to the database!", e);
        }
    }

    public static boolean loadDriver() {
        try {
            Class.forName("com.mysql.jdbc.Driver");
            System.out.println("Driver loaded!");
            return true;
        } catch (ClassNotFoundException e) {
            throw new IllegalStateException("Cannot find driver in classpath!", e);
        }
    }
}
