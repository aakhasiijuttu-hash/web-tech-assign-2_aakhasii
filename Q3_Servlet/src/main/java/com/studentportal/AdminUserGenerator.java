package com.studentportal;

import java.util.Scanner;

/**
 * Console utility to generate an administrator account with a secure SHA-256 password hash.
 * Can be run directly via java com.studentportal.AdminUserGenerator
 */
public class AdminUserGenerator {

    public static void main(String[] args) {
        System.out.println("==================================================");
        System.out.println(" Student Admission Portal - Admin Account Creator ");
        System.out.println("==================================================");

        String username;
        String password;
        String fullName;

        if (args.length >= 3) {
            username = args[0];
            password = args[1];
            fullName = args[2];
        } else {
            Scanner scanner = new Scanner(System.in);
            System.out.print("Enter Administrator Username: ");
            username = scanner.nextLine().trim();

            System.out.print("Enter Plaintext Password: ");
            password = scanner.nextLine().trim();

            System.out.print("Enter Full Name: ");
            fullName = scanner.nextLine().trim();
        }

        if (username.isEmpty() || password.isEmpty()) {
            System.err.println("Error: Username and Password cannot be empty.");
            return;
        }

        String hash = PasswordUtil.hashPassword(password);

        System.out.println("\nGenerated Password Hash (SHA-256):");
        System.out.println(hash);

        System.out.println("\nSQL INSERT Statement (Ready to run in MySQL / phpMyAdmin):");
        System.out.println("INSERT INTO `admins` (`username`, `password_hash`, `full_name`) VALUES ('"
                + username.replace("'", "''") + "', '" + hash + "', '" + fullName.replace("'", "''") + "');");

        // Try direct database insertion if MySQL connection is active
        try {
            AdminDAO dao = new AdminDAO();
            boolean success = dao.createAdmin(username, password, fullName);
            if (success) {
                System.out.println("\n[SUCCESS] Administrator '" + username + "' has been inserted directly into the database!");
            } else {
                System.out.println("\n[NOTE] Direct DB insertion did not succeed (check MySQL connection or if username already exists). Use the SQL statement above.");
            }
        } catch (Exception e) {
            System.out.println("\n[NOTE] Could not connect directly to MySQL: " + e.getMessage());
            System.out.println("You can manually execute the SQL statement above in phpMyAdmin.");
        }
    }
}
