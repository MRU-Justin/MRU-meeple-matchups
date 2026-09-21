<?php

/**

 * Admin login controller.
 *
 * Handles login display and validation.
 *
 * Requirements: A1-A8
 *  -I want to get to this page by going to http://somedomain/admin [A1]
 *  -I want to log in to the administrative portal with an email and a password. [A2]
 *  -If this is the first time I've attempted to log in for this session, the login form should be empty. [A3]
 *  -When I type my password, it should be obfuscated. [A4]
 *  -When I submit the login form, I should be notified if the login wasn't successful, but not told what specifically (email, password, or both) was incorrect, since that's a security risk. [A]
 *  -When I submit the login form, if the login wasn't successful, I want my email to be pre-filled to make logging in again easier for me, but I don't want my password to be pre-filled, because that's a security no-no. [A6]
 *  -When I submit the login form, if my login is successful, I want to be taken to the Dashboard Page. [A7]
 *  -I want only authorized administrators to be able to log in to the portal. [A8]
 *
 * All form validation is done using PHP: there is no built-in HTML form validation or JS form validation present.
 * A database table must be created to hold records for authorized administrators; the table needs email and digest fields. You may add other fields to the table if you like - with the exception of a raw password field, obviously! A small hint: the "previous login" requirement for the Dashboard will be more easily implemented if you add certain additional fields to your administrator table….
 * You must make user jpratt@mtroyal.ca with password comp3512 an authorized administrator.
 * The hashed passwords stored in the administrator table's digest field must be created using PHP's password_hash() function, using the PASSWORD_BCRYPT algorithm with a cost of 15.
 * To verify that a login attempt is valid, you must use PHP's password_verify() function.
 */

view('admin/members', []);
