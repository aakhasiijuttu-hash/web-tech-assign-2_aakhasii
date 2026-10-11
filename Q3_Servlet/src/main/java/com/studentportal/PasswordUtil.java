package com.studentportal;

import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;

/**
 * Utility for hashing and verifying passwords securely using SHA-256.
 */
public class PasswordUtil {

    /**
     * Hashes a plaintext password using SHA-256.
     * @param plainPassword Raw password string
     * @return Hex-encoded 64-character SHA-256 hash string
     */
    public static String hashPassword(String plainPassword) {
        if (plainPassword == null) {
            return null;
        }
        try {
            MessageDigest digest = MessageDigest.getInstance("SHA-256");
            byte[] encodedHash = digest.digest(plainPassword.getBytes(StandardCharsets.UTF_8));
            StringBuilder hexString = new StringBuilder();
            for (byte b : encodedHash) {
                String hex = Integer.toHexString(0xff & b);
                if (hex.length() == 1) {
                    hexString.append('0');
                }
                hexString.append(hex);
            }
            return hexString.toString();
        } catch (NoSuchAlgorithmException e) {
            throw new RuntimeException("SHA-256 algorithm not available in standard runtime", e);
        }
    }

    /**
     * Verifies whether a plaintext password matches the stored SHA-256 hash.
     * @param plainPassword Input plaintext password
     * @param storedHash Database stored hash
     * @return true if valid match, false otherwise
     */
    public static boolean verifyPassword(String plainPassword, String storedHash) {
        if (plainPassword == null || storedHash == null) {
            return false;
        }
        String calculated = hashPassword(plainPassword);
        return MessageDigest.isEqual(
                calculated.getBytes(StandardCharsets.UTF_8),
                storedHash.trim().toLowerCase().getBytes(StandardCharsets.UTF_8)
        );
    }
}
