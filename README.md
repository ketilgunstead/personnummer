# personnummer
Kod og dekod norsk personnummer i php

Forklaring på engelsk:

Usage:
Before use u have to parse the birth number trough set_birthnumber(). No other functions besides decode_birthnumber() can be used if set_birtnumber is not initiated. 

If set_birthnumber() is not initiated first the, ERROR_CLASS_NOT_SET will be parsed. All functions will return false on error besides get_gender().

Error message can be fetched by get_error().

No other functions should be initiated before you run check_number(). 

check_number() will validate the birth number, all other functions depends on the birth number beeing correct.

Functions:

set_birth_number()			- parse the BirthNumber to the class

check_number()				- This will validate the birth number and check if it is mathematically correct. Returns true if it is correct

get_gender()				- Check the gender of the BirthNumber holder. Returns false for female and true for male

get_birth_date()			- Get the corresponding date, returns norwegian date like 31-01-1974

getAge()					- Gets the age, returns the age of the person like 38

get_century()				- This functions returns the century which the person is born in, returns 18,19 or 20

code_birthnumber()			- This function codes/crypts the BirthNumber to 6 characters. If the $use_checksum = true it returns 8 characters

decode_birthnumber($code)	- This functions decodes the BirthNumber given from code_birthnumber() function. This function depenss on the code beeing parsed to the function

get_error()					- Returns the error text if any functions returns false

check_h_number($month)		- Checks if the birthnumber is a H number format, parameters is the month in birthnumber eg. 12

check_d_number($day)		- Checks if the birthnumber is a D number format, parameters is the day in birthnumber eg. 01

math_control_numbers($num) 	- Takes a 9 digit number and calculates the control numbers from it
