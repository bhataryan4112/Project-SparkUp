# SparkUp Match QR Code System

## Overview
SparkUp's QR code matching system facilitates real-world connections between matched users through a simple scan-and-verify process. Users have a list of matches but can only see the details of those matches after completing a timed face-to-face conversation and providing feedback.

## System Flow
1. **User Login**: Users log in with their VIT email address using OTP verification
2. **Dashboard**: Users see their match list (hidden until conversations are completed) and their personal QR code
3. **QR Code Match**: Users scan each other's QR codes to verify they are matched in the system
4. **Timed Conversation**: Upon verification, a 5-minute timer starts for their conversation
5. **Feedback**: After the timer ends, users provide feedback about their conversation
6. **Match Reveal**: After providing feedback, the match's details are revealed on the dashboard

## Security Features
- All user data in QR codes is encrypted using AES-256-CBC
- Session-based authentication protects user data
- Match verification requires bilateral confirmation (both users must be matched to each other)

## Technical Implementation
### Database Tables
- `users`: User profile information
- `2025_01_12_00_35_22_matches`: Original match data with user + 5 potential matches (pre-loaded)
- `completed_matches`: Records of completed conversations and feedback
- `user_sessions`: Authentication session tracking

### Main Files
- **Login & Authentication**
  - `login.php`: User login with OTP verification
  - `logout.php`: User logout functionality
  
- **Core Functionality**
  - `dashboard.php`: Main user interface with QR code display and match list
  - `qr_generator.php`: Generate and download personal QR codes
  - `verify_match.php`: API to verify QR code matches
  - `timer.php`: 5-minute conversation timer
  - `feedback.php`: Post-conversation feedback form

- **Utility Functions**
  - `encrypt.php`: Data encryption functions for QR codes
  - `decrypt.php`: Data decryption functions
  - `config/database.php`: Database connection configuration

## Setup Instructions
1. Import the database schema from `db/schema.sql`
2. Ensure the database credentials in `config/database.php` match your environment
3. Upload all PHP files to a server with PHP 7.4+ support
4. If using in a subdirectory, update all URLs in the code accordingly

## QR Code Verification Process
1. User's email is encrypted using AES-256-CBC and added to a QR code
2. When scanned, the system decrypts the email and checks for a mutual match
3. If a mutual match is found, it creates records in the `completed_matches` table
4. The timer page is loaded for both users

## Revealing Matches
Matches are only revealed on the dashboard after:
- Both users have scanned each other's QR codes
- The 5-minute timer has completed
- The user has submitted feedback

This ensures users interact face-to-face before getting contact information.

## Styling and Animations
The interface uses a dark theme with glowing animations to create a modern tech feel:
- Animated timers with color changes
- Glowing effects for QR codes and buttons
- Animated reveal of matches once feedback is provided
- Shake animations for invalid actions
