<?php
/*
Written by Ketil Gunstead - ketil@gunstead.no, upheads AS, upheads.no
	
	
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
check_h_number($month)		- Checks if the birthnumber is a H number format, paramteres is the mont in birthnumber eg. 12
check_d_number($day)		- Checks if the birthnumber is a D number format, paramteres is the day in birthnumber eg. 01
math_control_numbers($num) 	- Takes a 9 digit number and calculates the control numbers from it

*/

interface predefined_NO
{
	const ERROR_LENGTH 			= "Fødselsnummeret er skrevet feil";
	const ERROR_ASCII 			= "Fødselsnummeret inneholder mer enn bare tall.";
	const ERROR_MATH 			= "Fødselsnummeret er ikke matematisk korrekt";
	const ERROR_DATE 			= "Datoer stemmer ikke i fødselsnummeret";
	const ERROR_CLASS_NOT_SET	= "Fødselsnummer mangler";
	const ERROR_NOT_VALIDATED	= "Fødselsnummeret er ikke validert";
	const ERROR_WRONG_CODE		= "Fødselskoden er ikke riktig";
	const ERROR_WRONG_CODEC		= "Fødselskoden er ikke riktig kryptert";
	const ERROR_CRYPT_MATH		= "Den krypterte fødselskoden er ikke matematisk korrekt";
}

class BirthNumber implements predefined_NO
{

	private $birth_number;			// Norwegian BirthNumber 11 integers
	private $birth_number_array;		// Birtnumber broken down to categories
	private $error_message		=	"Ingen feil";
	private $validated			=	false;
	private $use_checksum		=	false; 	// Use 2 extra integers at end as checksum in encryption og birthnumber
	private $is_d_number			=	false;
	private $is_h_number			=	false;

	function set_birth_number($bn) {
		$this->birth_number=$bn;
	}

	function check_number()
	{
		$bn=$this->birth_number;
		if(!preg_match('/^[0-9]{1,}$/', $bn)){$this->error_message=self::ERROR_ASCII;return false;} // Is it itegers only
		if(strlen($bn)!=11){$this->error_message=self::ERROR_LENGTH;return false;}
		// lets check if it is mathematically correct
		$numbers=array();
		$k1=substr($bn,9,1); 	// Control number 1
		$k2=substr($bn,10,1); 	// control number 2
		$x=0;
		while($x<12) {
			$numbers[$x]=substr($bn,$x,1);$x++;
		}
		$check1=array(3, 7, 6, 1, 8, 9, 4, 5, 2); 		// Control numbers 1
		$check2=array(5, 4, 3, 2, 7, 6, 5, 4, 3, 2);	// Control numbers 2
		// Get product 1
		$x=0;$res1=(int)0;
		while($x<10){
			$res1+=$check1[$x] * $numbers[$x];$x++;
		}
		$res1=11 - $res1 % 11;
		if($res1==11){$res1=0;}
		if($res1!=$k1 || $res1==10){$this->error_message=self::ERROR_MATH;return false;} // Mathematically wrong
		// Get product 2
		$x=0;$res2=(int)0;
		while($x<11){
			$res2+=$check2[$x] * $numbers[$x];$x++;
		}
		$res2=11 - $res2 % 11;
		if($res2==11){$res2=0;}
		if($res2!=$k2 || $res2==10){$this->error_message=self::ERROR_MATH;return false;} // Mathematically wrong
		// Lets do the last basic checks
		$check_day=(int)substr($this->birth_number,0,2);

		if($check_day>31){
			// The day is wrong lets check if it could be a D number format
			// Lets check if this is a D number
			if($this->check_d_number($check_day)){
				// Lets not put out an error but set $is_d_number to true
				$this->is_d_number=true;
			}else{
				$this->error_message=self::ERROR_DATE;
				return false;
			}
		} // Wrong date format
		$check_month=(int)substr($this->birth_number,2,2);
		if($check_month>12){
			// The month is wrong lets check if it could be a H number format
			// Lets check if this is a H number
		if($this->check_h_number($check_month)){
				// Lets not put out an error but set $is_h_number to true
				$this->is_h_number=true;
			}else{
				$this->error_message=self::ERROR_DATE;
				return false;
			}
		} // Wrong date format
		
		// Everything is ok, lets set $birth_number,  $birth_number_array
		$this->birth_number = $bn;
		$this->birth_number_array = $numbers;
		$this->validated=true;
		return true;
	}
	
	 private function check_d_number($dn){
		$d_check=substr($dn,0,1);
		$d_sub_check=$d_check-4;

		if($d_sub_check<4){
			// This date could be a D number date
			return true;
		}else{
			// This is a bogus date
			return false;
		}
	}
	
	private function check_h_number($hn){
		$h_check=substr($hn,0,1);
		$h_sub_check=$h_check-4;
		if($h_sub_check<2){
			// This date could be a H number date
			return true;
		}else{
			// This is a bogus date
			return false;
		}
	}

	function is_date_h_number(){
		// check_number must have been run to return correkt value, defalt = false
		return $this->is_h_number;	
	}
	

	function is_date_d_number(){
		// check_number must have been run to return correkt value, defalt = false
		return $this->is_d_number;	
	}
	
	function get_individual_number(){
		return (int)substr($this->birth_number,6,3);
	}
	
	function get_gender_number(){
		return (int)substr($this->birth_number,8,1);
	}
	
	function get_gender() {
		if(!isset($this->birth_number)){$this->error_message=self::ERROR_CLASS_NOT_SET;return false;}
		if(($this->birth_number_array[8] % 2) == 0){
			return false; // female
		}else{
			return true; // male
		}
	}

	function get_birth_date(){
		$bn=$this->birth_number;
		$cen=$this->get_century();
		$bd=substr($bn,0,2);
		$bm=substr($bn,2,2);
		if($this->is_d_number){
			$c=substr($bn,0,1);$c=$c-4;$bd=$c . substr($bn,1,1);
		}
		if($this->is_h_number){
			$c=(int)substr($bm,0,1);$c=$c-4;$bm=$c . substr($bm,1,1);
		}
		$bd=$bd . "-" . $bm . "-" . $cen . substr($bn,4,2);
		return $bd;
	}
	

	function get_age(){
		if(!isset($this->birth_number)){$this->error_message=self::ERROR_CLASS_NOT_SET;return false;}
		$bn=$this->birth_number;
		$cen=$this->get_century();
		$bd=substr($bn,2,2) . "-" . substr($bn,0,2) . "-" . $cen . substr($bn,4,2);
        $bd = explode("-", $bd);
        $age = (date("md", date("U", mktime(0, 0, 0, $bd[0], $bd[1], $bd[2]))) > date("md") ? ((date("Y")-$bd[2])-1):(date("Y")-$bd[2]));
        return $age;
	}

	function get_century(){
		if(!isset($this->birth_number)){$this->error_message=self::ERROR_CLASS_NOT_SET;return false;}
		$bn=$this->birth_number;
		$ind=(int)substr($bn,6,3); //500 - 749
		if($ind>498 && $ind<750){return 18;}
		if($ind>0 && $ind<500){return 19;}
		if($ind>399 && $ind<1000){return 20;}
	}

	function code_birthnumber(){
		// Lets make shure we code the right birtnumber
		if(!isset($this->birth_number)){$this->error_message=self::ERROR_CLASS_NOT_SET;return false;}
		if(!$this->validated){$this->error_message=self::ERROR_NOT_VALIDATED;return false;}
		if($this->is_d_number){$d=$this->birth_number_array[0]-4;$this->birth_number_array[0]=$d;}
		if($this->is_h_number){$d=$this->birth_number_array[2]-4;$this->birth_number_array[2]=$d;}
		if($this->is_d_number || $this->is_h_number){
			$bnum="";
			foreach($this->birth_number_array as $n){
				$bnum=$bnum . $n;
			}
			 $bnum=(int)substr($bnum,0,9);
			 $cn=$this->math_control_numbers($bnum);
			 $this->birth_number_array[9] = substr($cn,0,1);
			 $this->birth_number_array[10] = substr($cn,1,1);
		}
		$flags=array(0,0,0,0);$decode=array();
		$matrix=array(0,1,2,3,4,5,6,7,8,9,"A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
		$hex=array(0,1,2,3,4,5,6,7,8,"A","B","C","D","E","F");
		$bin=array("0000","0001","0010","0011","0100","0101","0110","0111","1000","1001","1010","1011","1100","1101","1110","1111");
		$day=(int)ltrim($this->birth_number_array[0] . $this->birth_number_array[1],"0");
		$mon=(int)ltrim($this->birth_number_array[2] . $this->birth_number_array[3],"0");
		$year=(int)$this->birth_number_array[4] . $this->birth_number_array[5];
		$ind=(int)$this->birth_number_array[6] . $this->birth_number_array[7];
		$gend=(int)$this->birth_number_array[8];
		if($year>35){$year=$year-35;$flags[0]=1;}
		if($year>35){$year=$year-35;$flags[1]=1;}
		if($ind>35){$ind=$ind-35;$flags[2]=1;}
		if($ind>35){$ind=$ind-35;$flags[3]=1;}
		$flags_c=$flags[0] . $flags[1] . $flags[2] . $flags[3];
		$pos=array_search($flags_c,$bin);$bin_c=$matrix[$pos];
		$crypt=$matrix[$day].$matrix[$mon].$matrix[$year].$matrix[$ind].$gend.$bin_c;
		$checksum=substr(sprintf("%u",crc32($crypt)),0,2);
		if($this->use_checksum){$crypt=$crypt.$checksum;}
		return $crypt;
	}

	function decode_birthnumber($coded){
		$this->use_checksum ? $num=8:$num=6;
		if(strlen($coded)!=$num){
			echo "faen";
			$this->error_message=self::ERROR_WRONG_CODE;
			return false;
		}
		$crypt=substr($coded,0,6);
		if($this->use_checksum){
			$check=substr($coded,6,2);
			$checksum=substr(sprintf("%u",crc32($crypt)),0,2);
			if($check!=$checksum){$this->error_message=self::ERROR_WRONG_CODEC;return false;}
		}
		$matrix=array(0,1,2,3,4,5,6,7,8,9,'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		$bin=array('0000','0001','0010','0011','0100','0101','0110','0111','1000','1001','1010','1011','1100','1101','1110','1111');
		$flags=substr($crypt,5,1);
		$i=0;$pos=0;
		foreach($matrix as $k) {if($k === $flags){$pos=$i;}$i++;}
		$binary=$bin[$pos];
		$add1=0;if(substr($binary,0,1)=='1') $add1=$add1+35;if(substr($binary,1,1)=='1') $add1=$add1+35;
		$add2=0;if(substr($binary,2,1)=='1') $add2=$add2+35;if(substr($binary,3,1)=='1') $add2=$add2+35;
		$day=substr($crypt,0,1);$i=0;
		foreach($matrix as $k) {if($k === $day){$day=$i;}$i++;}
		if($day<10){$day="0". $day;}
		$mon=substr($crypt,1,1);$i=0;$pos=0;
		foreach($matrix as $k) {if($k === $mon){$mon=$i;}$i++;}
		if($mon<10){$mon="0". $mon;}
		$year=substr($crypt,2,1);$i=0;$pos=0;
		foreach($matrix as $k) {if($k === $year){$year=$i;}$i++;}
		$year=$year+$add1;
		if($year<10){$year="0". $year;}
		$ind=substr($crypt,3,1);$i=0;$pos=0;
		foreach($matrix as $k) {if($k === $ind){$ind=$i;}$i++;}
		$ind=$ind+$add2;
		if($ind<10){$ind="0". $ind;}
		$gen=substr($crypt,4,1);$i=0;$pos=0;
		foreach($matrix as $k) {if($k === $gen){$gen=$i;}$i++;}
		$decoded_date=$day.$mon.$year.$ind.$gen;
		// q1
		$n1="376189452";$t1=0; // array_search fails
		$t1=$t1+(substr($decoded_date,0,1) * substr($n1,0,1));$t1=$t1+(substr($decoded_date,1,1) * substr($n1,1,1));
		$t1=$t1+(substr($decoded_date,2,1) * substr($n1,2,1));$t1=$t1+(substr($decoded_date,3,1) * substr($n1,3,1));
		$t1=$t1+(substr($decoded_date,4,1) * substr($n1,4,1));$t1=$t1+(substr($decoded_date,5,1) * substr($n1,5,1));
		$t1=$t1+(substr($decoded_date,6,1) * substr($n1,6,1));$t1=$t1+(substr($decoded_date,7,1) * substr($n1,7,1));
		$t1=$t1+(substr($decoded_date,8,1) * substr($n1,8,1));
		$k1=$t1;$t1=$t1/11;$t1_arr=explode(".",$t1);$t1=$t1_arr[0];$q1=11-($k1-(11*$t1));
		$decoded_date=$decoded_date . $q1;
		//q2
		$n1="5432765432";$t1=0;
		$t1=$t1+(substr($decoded_date,0,1) * substr($n1,0,1));$t1=$t1+(substr($decoded_date,1,1) * substr($n1,1,1));
		$t1=$t1+(substr($decoded_date,2,1) * substr($n1,2,1));$t1=$t1+(substr($decoded_date,3,1) * substr($n1,3,1));
		$t1=$t1+(substr($decoded_date,4,1) * substr($n1,4,1));$t1=$t1+(substr($decoded_date,5,1) * substr($n1,5,1));
		$t1=$t1+(substr($decoded_date,6,1) * substr($n1,6,1));$t1=$t1+(substr($decoded_date,7,1) * substr($n1,7,1));
		$t1=$t1+(substr($decoded_date,8,1) * substr($n1,8,1));$t1=$t1+(substr($decoded_date,9,1) * substr($n1,9,1));
		$k1=$t1;$t1=$t1/11;$t1_arr=explode(".",$t1);$t1=$t1_arr[0];$q2=11-($k1-(11*$t1));
		if($q1==11){$q1=0;}if($q2==11){$q2=0;}
		$decoded_date=$decoded_date . $q2;
		if($q1==10){$this->error_message=self::ERROR_CRYPT_MATH;return false;}
		if($q2==10){$this->error_message=self::ERROR_CRYPT_MATH;return false;}
	return $decoded_date;
	}
	
	function math_control_numbers($decoded_date){
		// This function takes out an 9 digit number and gets the control numbers from it
		
		// q1
		$n1="376189452";$t1=0; // array_search fails
		$t1=$t1+(substr($decoded_date,0,1) * substr($n1,0,1));$t1=$t1+(substr($decoded_date,1,1) * substr($n1,1,1));
		$t1=$t1+(substr($decoded_date,2,1) * substr($n1,2,1));$t1=$t1+(substr($decoded_date,3,1) * substr($n1,3,1));
		$t1=$t1+(substr($decoded_date,4,1) * substr($n1,4,1));$t1=$t1+(substr($decoded_date,5,1) * substr($n1,5,1));
		$t1=$t1+(substr($decoded_date,6,1) * substr($n1,6,1));$t1=$t1+(substr($decoded_date,7,1) * substr($n1,7,1));
		$t1=$t1+(substr($decoded_date,8,1) * substr($n1,8,1));
		$k1=$t1;$t1=$t1/11;$t1_arr=explode(".",$t1);$t1=$t1_arr[0];$q1=11-($k1-(11*$t1));
		if($q1==11){$q1=0;}
		$decoded_date=$decoded_date . $q1;
		//q2
		$n2="5432765432";$t2=0;
		$t2=$t2+(substr($decoded_date,0,1) * substr($n2,0,1));$t2=$t2+(substr($decoded_date,1,1) * substr($n2,1,1));
		$t2=$t2+(substr($decoded_date,2,1) * substr($n2,2,1));$t2=$t2+(substr($decoded_date,3,1) * substr($n2,3,1));
		$t2=$t2+(substr($decoded_date,4,1) * substr($n2,4,1));$t2=$t2+(substr($decoded_date,5,1) * substr($n2,5,1));
		$t2=$t2+(substr($decoded_date,6,1) * substr($n2,6,1));$t2=$t2+(substr($decoded_date,7,1) * substr($n2,7,1));
		$t2=$t2+(substr($decoded_date,8,1) * substr($n2,8,1));$t2=$t2+(substr($decoded_date,9,1) * substr($n2,9,1));
		$k2=$t2;$t2=$t2/11;$t2_arr=explode(".",$t2);$t2=$t2_arr[0];$q2=11-($k2-(11*$t2));
		if($q2==11){$q2=0;}
		return((int)$q1.$q2);
	}

	function get_error(){
		return $this->error_message;

	}

}

// HTML example
$f = new BirthNumber();
$f->set_birth_number("41017446159");
if($f->check_number()){
	echo "Kjønn: ";
		if($f->get_gender()){echo "mann<br>";}else{"kvinne<br>";}
		echo $f->get_birth_date();
	echo "<br>Er født i det "; 
		echo $f->get_century(); echo " århundre";
	echo "<br>Alder: "; 
		echo $f->get_age();
	echo "<br>Kryptert: ";
		$coded=$f->code_birthnumber();
		echo $coded;
	echo "<br>Dekodet: ";
		echo $f->decode_birthnumber($coded);
	echo "<br>Er datoen D nummer? : ";
		if($f->is_date_d_number()){echo "Ja";}else{echo "Nei";}
	echo "<br>Er datoen H nummer? : ";
		if($f->is_date_h_number()){echo "Ja";}else{echo "Nei";}
	echo "<br>Individnummer: ";
		echo $f->get_individual_number();
	echo "<br>Kjønn siffer: ";
		echo $f->get_gender_number();
		
		}else{
			
	echo "Feil!";
	echo $f->get_error();
}
?>
