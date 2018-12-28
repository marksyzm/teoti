<?php
/* User Agent Detector
 * Version 1.0.1
 * Copyright 2004-2006, Steve Blinch
 * http://code.blitzaffe.com
 * ============================================================================
 *
 * DESCRIPTION
 *
 * Provides an easy way to determine which OS and browser a visitor is using,
 * complete with version numbers, based on a standard HTTP user agent string.
 *
 *
 * EXAMPLE
 *
 * // User Agent Detector usage example
 * require_once('class_UserAgent.php');
 *
 * $ua = &new UserAgent();
 * // get the OS details
 * list($os_name,$os_version) = $ua->check_os($_SERVER["HTTP_USER_AGENT"]);
 * // get the browser details
 * list($browser_name,$browser_version,$browser_engine) = $ua->check_browser($_SERVER["HTTP_USER_AGENT"]);
 *
 * echo sprintf(
 *	'Using %s v%s (%s engine) under %s v%s.',
 *	$browser_name,$browser_version,$browser_engine,
 *	$os_name,$os_version
 * );
 *
 *
 * HISTORY
 *
 * 1.0.1	- Added "engine" as the third element in the array returned by
 *			  check_browser().  This does a q'n'd identification of the HTML
 *			  rendering engine used by the client browser.
 *
 *
 * LICENSE
 *
 * This script is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * This script is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *	
 * You should have received a copy of the GNU General Public License along
 * with this script; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
class UserAgent {

	function check_os($useragent) {
		$os = "Unknown"; $version = "";
		if (preg_match("/Windows NT 5.1/",$useragent,$match)) {
			$os = "Windows"; $version = "XP";
		} elseif (preg_match("/(?:Windows NT 5.0|Windows 2000)/",$useragent,$match)) {
			$os = "Windows"; $version = "2000";
		} elseif (preg_match("/(?:WinNT|Windows\s?NT)\s?([0-9\.]+)?/",$useragent,$match)) {
			$os = "Windows"; $version = "NT ".$match[1];
		} elseif (preg_match("/Mac OS X/",$useragent,$match)) {
			$os = "Mac OS"; $version = "X";
		} elseif (preg_match("/(Mac_PowerPC|Macintosh)/",$useragent,$match)) {
			$os = "Mac OS"; $version = "";
		} elseif (preg_match("/(?:Windows95|Windows 95|Win95|Win 95)/",$useragent,$match)) {
			$os = "Windows"; $version = "95";
		} elseif (preg_match("/(?:Windows98|Windows 98|Win98|Win 98)/",$useragent,$match)) {
			$os = "Windows"; $version = "98";
		} elseif (preg_match("/(?:WindowsCE|Windows CE|WinCE|Win CE)/",$useragent,$match)) {
			$os = "Windows"; $version = "CE";
		} elseif (preg_match("/PalmOS/",$useragent,$match)) {
			$os = "PalmOS";
		} elseif (preg_match("/\(PDA(?:.*)\)(.*)Zaurus/",$useragent,$match)) {
			$os = "Sharp Zaurus";
		} elseif (preg_match("/Linux\s*((?:i[0-9]{3})?\s*(?:[0-9]\.[0-9]{1,2}\.[0-9]{1,2})?\s*(?:i[0-9]{3})?)?/",$useragent,$match)) {
			$os = "Linux"; $version = $match[1];
		} elseif (preg_match("/SunOS\s*([0-9\.]+)?/",$useragent,$match)) {
			$os = "SunOS"; $version = $match[1];
		} elseif (preg_match("/IRIX\s*([0-9\.]+)?/",$useragent,$match)) {
			$os = "SGI IRIX"; $version = $match[1];
		} elseif (preg_match("/FreeBSD\s*((?:i[0-9]{3})?\s*(?:[0-9]\.[0-9]{1,2})?\s*(?:i[0-9]{3})?)?/",$useragent,$match)) {
			$os = "FreeBSD"; $version = $match[1];
		}
		return array($os,$version);
	}
	
	function check_browser($useragent) {
		$browser = "Unknown";
	
		if (preg_match("/^Mozilla(?:.*)compatible;\sMSIE\s(?:.*)Opera\s([0-9\.]+)/",$useragent,$match)) {
			$browser = "Opera";
		} elseif (preg_match("/^Opera\/([0-9\.]+)/",$useragent,$match)) {
			$browser = "Opera";
		} elseif (preg_match("/^Chrome\/([0-9\.]+)/",$useragent,$match)) {
			$browser = "Chrome";
		} elseif (preg_match("/^Mozilla(?:.*)compatible;\siCab\s([0-9\.]+)/",$useragent,$match)) {
			$browser = "iCab";
		} elseif (preg_match("/^iCab\/([0-9\.]+)/",$useragent,$match)) {
			$browser = "iCab";
		} elseif (preg_match("/^Mozilla(?:.*)compatible;\sMSIE\s([0-9\.]+)/",$useragent,$match)) {
			$browser = "MSIE";
		} elseif (preg_match("/^Mozilla(?:.*)\(Macintosh(?:.*)Safari\/([0-9\.]+)/",$useragent,$match)) {
			$browser = "Safari";
		} elseif (preg_match("/^Mozilla(?:.*)\(Macintosh(?:.*)OmniWeb\/v([0-9\.]+)/",$useragent,$match)) {
			$browser = "Omniweb";
		} elseif (preg_match("/^Mozilla(?:.*)\(compatible;\sOmniWeb\/([0-9\.v-]+)/",$useragent,$match)) {
			$browser = "Omniweb";
		} elseif (preg_match("/^Mozilla(?:.*)Gecko(?:.*?)Netscape\/([0-9\.]+)/",$useragent,$match)) {
			$browser = "Netscape";
		} elseif (preg_match("/^Mozilla(?:.*)Gecko(?:.*?)(?:Fire(?:fox|bird)|Phoenix)\/([0-9\.]+)/",$useragent,$match)) {
			$browser = "Firefox";
		} elseif (preg_match("/^Mozilla(?:.*)Gecko(?:.*?)Epiphany\/([0-9\.]+)/",$useragent,$match)) {
			$browser = "Epiphany";
		} elseif (preg_match("/^Mozilla(?:.*)Galeon\/([0-9\.]+)\s(?:.*)Gecko/",$useragent,$match)) {
			$browser = "Galeon";
		} elseif (preg_match("/^Mozilla(?:.*)Gecko(?:.*?)K-Meleon\/([0-9\.]+)/",$useragent,$match)) {
			$browser = "K-Meleon";
		} elseif (preg_match("/^Mozilla(?:.*)Gecko(?:.*?)(?:Camino|Chimera)\/([0-9\.]+)/",$useragent,$match)) {
			$browser = "Camino";
		} elseif (preg_match("/^Mozilla(?:.*)rv:([0-9\.]+)\)\sGecko/",$useragent,$match)) {
			$browser = "Mozilla";
		} elseif (preg_match("/^Mozilla(?:.*)compatible;\sKonqueror\/([0-9\.]+);/",$useragent,$match)) {
			$browser = "Konqueror";
		} elseif (preg_match("/^Mozilla\/(?:[34]\.[0-9]+)(?:.*)AvantGo\s([0-9\.]+)/",$useragent,$match)) {
			$browser = "AvantGo";
		} elseif (preg_match("/^Mozilla(?:.*)NetFront\/([34]\.[0-9]+)/",$useragent,$match)) {
			$browser = "NetFront";
		} elseif (preg_match("/^Mozilla\/([34]\.[0-9]+)/",$useragent,$match)) {
			$browser = "Netscape";
		} elseif (preg_match("/^curl\/([0-9\.]+)/",$useragent,$match)) {
			$browser = "curl";
		} elseif (preg_match("/^links\/([0-9\.]+)/i",$useragent,$match)) {
			$browser = "Links";
		} elseif (preg_match("/^links\s?\(([0-9\.]+)/i",$useragent,$match)) {
			$browser = "Links";
		} elseif (preg_match("/^lynx\/([0-9a-z\.]+)/i",$useragent,$match)) {
			$browser = "Lynx";
		} elseif (preg_match("/^Wget\/([0-9\.]+)/i",$useragent,$match)) {
			$browser = "Wget";
		} elseif (preg_match("/^Xiino\/([0-9\.]+)/i",$useragent,$match)) {
			$browser = "Xiino";
		} elseif (preg_match("/^W3C_Validator\/([0-9\.]+)/i",$useragent,$match)) {
			$browser = "W3C Validator";
		} elseif (preg_match("/^Jigsaw(?:.*) W3C_CSS_Validator_(?:[A-Z]+)\/([0-9\.]+)/i",$useragent,$match)) {
			$browser = "W3C CSS Validator";
		} elseif (preg_match("/^Dillo\/([0-9\.]+)/i",$useragent,$match)) {
			$browser = "Dillo";
		} elseif (preg_match("/^amaya\/([0-9\.]+)/i",$useragent,$match)) {
			$browser = "Amaya";
		} elseif (preg_match("/^DocZilla\/([0-9\.]+)/i",$useragent,$match)) {
			$browser = "DocZilla";
		} elseif (preg_match("/^fetch\slibfetch\/([0-9\.]+)/i",$useragent,$match)) {
			$browser = "FreeBSD libfetch";
		}
		$version = $match[1];
		
		if (strpos($useragent,'Gecko')!==false) $engine = 'Gecko';
		elseif (strpos($useragent,'MSIE')!==false) $engine = 'MSIE';
		elseif (strpos($useragent,'Opera')!==false) $engine = 'Opera';
		elseif ( ($browser=='Safari') || ($browser=='Konqueror') ) $engine = 'KHTML';
		else $engine = 'Other';
		
		return array($browser,$version,$engine);
	}

}
?>