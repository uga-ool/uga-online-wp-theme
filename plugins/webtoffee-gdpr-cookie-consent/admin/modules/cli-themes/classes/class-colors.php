<?php
/**
 * Color class for HTML scanner
 *
 * @link       http://cookielawinfo.com/
 * @since      2.1.8
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Cookie_Law_Info_Colors
{
	private static $lighter_limit=70;
	private static $lightness_diff_limit_min=40;
	private static $lightness_diff_limit_max=50;
	private static $lightness_buffer=20;
	public static function genDarkColor($hsl,$h='')
	{
		$hsl[2]=$hsl[2]-self::$lightness_diff_limit_min;
		$hsl[0]=$h!="" ? $h : $hsl[0];
		return self::genColor($hsl);
	}
	public static function genColor($hsl)
	{
		$rgb=self::hslToRgb($hsl);
		return self::getHEX($rgb);
	}
	public static function process_colors($tc,$bg="",$pbg="",$bdc="")
	{
		/*
		$tc='';
		$bg='';
		$pbg='';
		$bdc=''; 
		*/
		$lighter_limit=self::$lighter_limit;
		$lightness_diff_limit_min=self::$lightness_diff_limit_min;
		$lightness_diff_limit_max=self::$lightness_diff_limit_max;
		$lightness_buffer=self::$lightness_buffer;

		if($tc!="")
		{
			if($tc!="" && $bg!="") //text color and button bg exists
			{
				if($pbg!="") //parent bg exists
				{
					//generate btn
				}else //parent bg not avaialable
				{
					$bg_rgb=self::getRGB($bg);
					$bg_hsl=self::rgb2hsl($bg_rgb);
					if($bg_hsl[2]>=$lightness_diff_limit_min)
					{
						// darker bg
						$pbg=self::genDarkColor($bg_hsl);
					}else
					{
						$pbg='#ffffff';
					}
				}

			}else
			{
				$tc_rgb=self::getRGB($tc);
				$tc_hsl=self::rgb2hsl($tc_rgb);
				if($bdc=="") //no border color
				{
					if($pbg!="") //parent bg exists
					{
						$pbg_rgb=self::getRGB($pbg);
						$pbg_hsl=self::rgb2hsl($pbg_rgb);

						$lightness_diff=$tc_hsl[2]-$pbg_hsl[2];
						if($pbg_hsl[2]<$lighter_limit && $tc_hsl[2]>=$lighter_limit) //dark parent BG and light text
						{						
							if($lightness_diff>=$lightness_diff_limit_max) //text and parent bg has big lightness diff
							{
								// darker bg from tc
								$bg=self::genDarkColor($tc_hsl);
							}else 
							{
								// darker bg from pbg
								$bg=self::genDarkColor($pbg_hsl);
							}
						}elseif($pbg_hsl[2]>=$lighter_limit && $tc_hsl[2]>=$lighter_limit) //lighter parent bg and text
						{
							// darker bg from pbg
							$bg=self::genDarkColor($pbg_hsl);
						}else
						{
							if($lightness_diff>=$lightness_diff_limit_max) //text and parent bg has big lightness diff
							{
								if($pbg_hsl[2]>$tc_hsl[2]) //parent BG is more lighter
								{
									$bg_hsl=$pbg_hsl;
									$bg_hsl[2]=90; //brighter button bg
									$bg=self::genColor($bg_hsl);

								}else //parent BG is more darker
								{
									// darker bg from pbg
									$bg=self::genDarkColor($pbg_hsl);
								}
							}else //text and parent bg has almost similar lightness
							{
								if($pbg_hsl[2]>=$lightness_diff_limit_max) //both are in lighter side
								{
									// darker bg from tc
									$bg=self::genDarkColor($tc_hsl);
								}else
								{
									//lighter bg
									$bg_hsl=$tc_hsl;
									$bg_hsl[2]=$bg_hsl[2]+$lightness_diff_limit_min;
									$bg=self::genColor($bg_hsl);
								}
							}
						}
					}else //no parent bg
					{
						$pbg='#ffffff';
						if($tc_hsl[2]>=$lighter_limit)
						{
							// darker bg
							$bg_hsl=$tc_hsl;
							$bg_hsl[2]=$lightness_diff_limit_min-(100-$bg_hsl[2]);
							$bg=self::genColor($bg_hsl);
						}else
						{
							if($tc_hsl[2]>$lightness_diff_limit_min)
							{
								// darker bg from tc
								$bg=self::genDarkColor($tc_hsl);
							}else
							{
								// lighter bg
								$bg_hsl=$tc_hsl;
								$bg_hsl[2]=$bg_hsl[2]+$lightness_diff_limit_min+$lightness_buffer;
								$bg=self::genColor($bg_hsl);
							}
						}
					}
				}else
				{
					if($tc_hsl[2]>=$lighter_limit)
					{
						// darker bg
						$bg_hsl=$tc_hsl;
						$bg_hsl[2]=$lightness_diff_limit_min-(100-$bg_hsl[2]);
						$bg=self::genColor($bg_hsl);
					}else
					{
						if($tc_hsl[2]>$lightness_diff_limit_min)
						{
							// darker bg from tc
							$bg=self::genDarkColor($tc_hsl);
						}else
						{
							// lighter bg from tc
							$bg_hsl=$tc_hsl;
							$bg_hsl[2]=$bg_hsl[2]+$lightness_diff_limit_min;
							$bg=self::genColor($bg_hsl);
						}
					}
					$pbg=$bg;
				}
			}


			//parent text color
			$pbg_rgb=getRGB($pbg);
			$pbg_hsl=rgb2hsl($pbg_rgb);
			if($pbg_hsl[2]>$lighter_limit)
			{
				$ptc="#000000";
			}else
			{
				$ptc="#ffffff";
			}
			//genHTML($tc,$bg,$pbg,$ptc,$bdc);
		}
	}
	/*
	* Generating hex code based on RGB
	* @since 2.1.8	
	*/
	private static function getHEX($rgb)
	{
		$r=$rgb[0];
		$g=$rgb[1];
		$b=$rgb[2];
		$hex_r=dechex($r); $hex_r=strlen($hex_r)==1 ? '0'.$hex_r : $hex_r;
		$hex_g=dechex($g); $hex_g=strlen($hex_g)==1 ? '0'.$hex_g : $hex_g;
		$hex_b=dechex($b); $hex_b=strlen($hex_b)==1 ? '0'.$hex_b : $hex_b;
		return '#'.$hex_r.$hex_g.$hex_b;
	}

	/*
	* Generating RGB from hex code
	* @since 2.1.8	
	*/
	private static function getRGB($colorCode) 
	{
	    if($colorCode[0]=='#')
	    {
	    	$colorCode=substr($colorCode,1,7);
	    }
	    //Turn html color code into RGB
	    $var_R = substr($colorCode, 0, 2);
	    $var_G = substr($colorCode, 2, 2);
	    $var_B = substr($colorCode, 4, 2);  

	    //Get Hex values
	    $val_R = hexdec($var_R);
	    $val_G = hexdec($var_G);
	    $val_B = hexdec($var_B);
	    return array($val_R,$val_G,$val_B);
	}

	/*
	* Converting RGB to HSL
	* @since 2.1.8	
	*/
	private static function rgb2hsl($rgb)
	{
	    $clrR = ($rgb[0]);
	    $clrG = ($rgb[1]);
	    $clrB = ($rgb[2]);
	     
	    $clrMin = min($clrR, $clrG, $clrB);
	    $clrMax = max($clrR, $clrG, $clrB);
	    $deltaMax = $clrMax - $clrMin;
	     
	    $L = ($clrMax + $clrMin) / 510;
	     
	    if (0 == $deltaMax){
	        $H = 0;
	        $S = 0;
	    }
	    else{
	        if (0.5 > $L){
	            $S = $deltaMax / ($clrMax + $clrMin);
	        }
	        else{
	            $S = $deltaMax / (510 - $clrMax - $clrMin);
	        }

	        if ($clrMax == $clrR) {
	            $H = ($clrG - $clrB) / (6.0 * $deltaMax);
	        }
	        else if ($clrMax == $clrG) {
	            $H = 1/3 + ($clrB - $clrR) / (6.0 * $deltaMax);
	        }
	        else {
	            $H = 2 / 3 + ($clrR - $clrG) / (6.0 * $deltaMax);
	        }

	        if (0 > $H) $H += 1;
	        if (1 < $H) $H -= 1;
	    }
	    $L=round($L*100);
	    $S=round($S*100);
	    $H=$H*(360/100);
	    $H=round($H*100);
	    return array($H, $S,$L);
	}

	/*
	* Converting hue to RGB
	* @since 2.1.8	
	*/
	private static function hue2rgb($p,$q,$t)
	{
		if($t < 0) $t += 1;
	    if($t > 1) $t -= 1;
	    if($t < 1/6) return $p + ($q - $p) * 6 * $t;
	    if($t < 1/2) return $q;
	    if($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
	    return $p;
	}

	/*
	* Converting HSL to RGB
	* @since 2.1.8	
	*/
	private static function hslToRgb($hsl)
	{
	    $r='';
	    $g='';
	    $b='';
	    $h=$hsl[0];
	    $s=$hsl[1];
	    $l=$hsl[2];
		
		$h=$h/(360/100);
		$h=$h/100;
		$s=$s/100;
		$l=$l/100;


	    if($s==0)
	    {
	        $r =$g =$b =$l; // achromatic
	    }else{
	        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
	        $p = 2 * $l - $q;
	        $r = self::hue2rgb($p,$q,$h + 1/3);
	        $g = self::hue2rgb($p,$q,$h);
	        $b = self::hue2rgb($p,$q,$h - 1/3);
	    }
	    return array(round($r * 255),round($g * 255),round($b * 255));
	}
}