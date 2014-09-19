<?php
/**
 * Standard Library, a static-method-only class for common functions.
 * 
 * The purpose of this class was to extend some of the basic, common functions found across the sites and collaborate
 * them into one class. They can then be accessed using the scope resolution "::" - eg. StdLib::function_name();
 * 
 * 
 * @author      Ryan Carney-Mogan
 * @category    Core_Classes
 * @version     1.0.4
 * @copyright   Copyright (c) 2013 University of Colorado Boulder (http://colorado.edu)
 * 
 */
 
class StdLib
{
    # Public image cache
    public static $image_cache = array();
    
    /**
     * Format Date
     * 
     * A small function to format dates according to strings or in a custom format.
     * 
     * @param   (string)  $date     Date in text form or numeric form
     * @param   (string)  $format   The format in which to format the date
     * @return  (string)
     */
    public static function format_date($date,$format)
    {
        if(!is_numeric($date))
          $date = strtotime($date);
        if($format=="nice")
          $format = "d M Y H:i a";
        if($format=="normal")
          $format = "F jS, Y H:i a";
        if($format=="normal-notime")
          $format = "F jS, Y";
        if($format=="nice-notime")
          $format = "d M Y";
        
        return date($format,$date);
    }
	
    /**
     * Get Library Path
     * 
     * Function to get the path to the library. Library contains common javascript, prototype, images, css, etc.
     * 
     * @param   (boolean)   $local  Determines whether to get the local path or the URL path.
     * @return  (string)
     */
    public static function get_library_path($local=false) {
        if($local === true) {
            return "c:\\web\\".(($_SERVER["SERVER_NAME"]=='assettdev.colorado.edu')?"assettdev.colorado.edu":"compass.colorado.edu")."\\libraries\\images\\";
        } else {
            return "//".(($_SERVER["SERVER_NAME"]=='assettdev.colorado.edu')?"assettdev":"compass").".colorado.edu/libraries/images/";
        }
    }
	
    public static function make_path_web($path) {
        if(strtolower(substr($path,0,3)) == "c:\\") {
            $path = str_replace("c:\\web\\","//",$path);
            $path = str_replace("\\","/",$path);
        }
        return $path;
    }
    
    public static function make_path_local($path) {
        if(strtolower(substr($path,0,2)) == "//") {
            $path = substr_replace($path,"c:\\web\\",0,2);
        }
        return $path;
    }
    /**
     * Load Image
     * 
     * Function to dynamically find and load images. It searches the library for any images that match a certain name.
     * If there is more than one image with the same name it will grab the first one it finds.
     * 
     * @param   (string)    $image  Image name to load
     * @param   (string)    $width  Width of image
     * @param   (string)    $height Height of image
     * @param   (string)    $type   What type of library image to look for
     * @return  (string)
     */
    public static function load_image($image,$width="auto",$height="auto")
    {
        $image_parts = explode(":",$image);
        $image = array_pop($image_parts);
        $subdir = implode("\\",$image_parts);
        
        # Load cached version of image
        $websrc = self::get_cache($image);
        $websrc = self::make_path_web($websrc);
        
        # If image is not cached, find it then cache it
        if($websrc === false) {
            $websrc = self::load_image_source($image,$subdir);
            $websrc = self::make_path_web($websrc);
            StdLib::$image_cache[$image] = $websrc;
        }
        ob_start();
        ?><img src="<?=$websrc?>" width="<?=$width?>" height="<?=$height?>" /><?php
        $contents = ob_get_contents();
        ob_end_clean();
        
        return $contents;
    }
	
    /**
     * Get Cache
     * 
     * Returns a stored image in the Cookie headers. Used to skip recursively searching for images in
     * the library which can take months (execution-time speaking).
     * 
     * @param   (string)    $image      The name of the image to lookup
     * @return  (string,boolean)        Returns the path of the image or false if it can't find it
     */
    public static function get_cache($image) {
        if(isset(StdLib::$image_cache[$image])) {
            return StdLib::$image_cache[$image];
        }
        return false;
    }
    
    /**
     * Load Image Source
     * 
     * Function sets up and calls a recursive function to find an image in the image directorys. Returns a web
     * link to the image.
     * 
     * @param   (string)    $image      Image name to find.
     * @param   (string)    $subdir     Subdirectory to start looking for matching image.
     * @return  (string,boolean)        Returns web based path if found, false if not found
     */
    public static function load_image_source($image,$subdir)
    {
        # Get the URL target directory
        $target_dir = self::get_library_path(true).$subdir;
        
        # Find the image in the target directory
        $image = self::find_image_path($image, $target_dir);
        
        # Return the web path of the image
        return self::make_path_web($image);
    }

    /**
     * Find Image Path
     * 
     * A function that recursively checks directories to find an image based on the name.
     * If the image being searched has no extension it will look for any matches to the image name.
     * 
     * @param   (string)    $image      Image name to search for
     * @param   (string)    $path       Directory path to search for image
     * @return  (string,boolean)        Returns path of image if found, false if not found
     */
    public static function find_image_path($image,$path,$depth=0)
    {
        # Only go three levels deep
        if($depth==3) { return false; }
        
        # If it's a directory then scan its files
        if(is_dir($path)) {
            $files = scandir($path);
            foreach($files as $file) {
                # Skip the . and .. folders
                if($file == ".." or $file == ".") continue;
                
                if(is_dir($path."\\".$file)) {
                    $return = self::find_image_path($image, $path.$file."\\",$depth+1);
                    if($return !== false) {
                        return $return;
                    }
                }
                if($file == $image and is_file($path.$file)) {
                    return $path.$file;
                } else {
                    # Strip extension off file (extension independent)
                    $fileparts = explode(".",$file);
                    array_pop($fileparts);
                    $file_trim = implode(".",$fileparts);
                    
                    # If the file matches the stripped filename, return
                    if(strtolower($file_trim) == strtolower($image) and is_file($path."\\".$file)) {
                        return $path."\\".$file;
                    }
                }
            }
        }
        # Image not found in this path
        return false;
    }

    /**
     * Vdump
     * 
     * Simple function that outputs a variable dump and quits the program.
     * Note that ajax calls will only return on exit.
     * 
     * @param   (variable)  $var      Variable to var_dump
     * @param   (boolean)   $pre      Output variable with preformatted style or not
     */
    public static function vdump($var,$pre=true)
    {
        if($pre) echo "<pre>";
        var_dump($var);
        exit;
    }
    
    /**
     * On Campus
     * 
     * Verifies if the IP Address loading the page is an on-campus IP address.
     * On-campus includes VPN, Campus Housing, and Wireless IP ranges.
     * 
     * @return  (boolean)
     */
    
    public static function on_campus() {
        $ip = $_SERVER["REMOTE_ADDR"];
        $flag = false;
        $flag = ($flag or StdLib::subnetmask($ip, "128.138.0.0/16"));
        $flag = ($flag or StdLib::subnetmask($ip, "198.11.25.0/24"));
        $flag = ($flag or StdLib::subnetmask($ip, "198.11.26.0/23"));
        $flag = ($flag or StdLib::subnetmask($ip, "198.11.24.0/24"));
        $flag = ($flag or StdLib::subnetmask($ip, "172.21.32.0/22"));
        $flag = ($flag or StdLib::subnetmask($ip, "198.11.26.126/0"));
        $flag = ($flag or StdLib::subnetmask($ip, "198.11.26.127/0"));
        $flag = ($flag or StdLib::subnetmask($ip, "172.23.0.0/16"));
        $flag = ($flag or StdLib::subnetmask($ip, "172.21.0.0/16"));
        $flag = ($flag or StdLib::subnetmask($ip, "10.200.0.0/14"));
        return $flag;
    }
    
    /**
     * Subnet Mask
     * 
     * Checks if IP falls within subnet mask
     * 
     * @param   (string)    $ip     The IP to check
     * @param   (string)    $range  The range with which an IP falls
     * @return  (boolean)
     */
     
    public static function subnetmask($ip, $range)
    {
        list ($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned
        return ($ip & $mask) == $subnet;
    }
}