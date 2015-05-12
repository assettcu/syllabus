<?php

if(defined("FUNCTIONS_LOADED")) return;
define("FUNCTIONS_LOADED",true);

function load_courses($prefix)
{    
    $result = Yii::app()->db->createCommand()
        ->select("id")
        ->from("course_syllabi")
        ->where("prefix = :prefix", array(":prefix"=>$prefix))
        ->group("num")
        ->order("num ASC, title ASC")
        ->queryAll();
    
    if(!$result or empty($result)) {
        return array();
    }
    
    $courses = array();
    foreach($result as $row) {
        $courses[] = new CourseSyllabusObj($row["id"]);
    }
    
    return $courses;
}

function load_departments() 
{
    $result = Yii::app()->db->createCommand()
        ->selectDistinct("id, label")
        ->from("departments")
        ->order("id")
        ->queryAll();

    return $result;
}

function load_unique_courses()
{
    $time_start = microtime(true);
    $result = Yii::app()->db->createCommand()
        ->selectDistinct("prefix")
        ->from("course_syllabi")
        ->order("prefix")
        ->queryAll(); 

    
    $return = array();
    foreach($result as $row) {
        
        $prefix = $row["prefix"];
        # Init array position for Course
        $return[$prefix] = array();
        $return[$prefix]["department"] = Yii::app()->db->createCommand()
            ->selectDistinct("label")
            ->from("departments")
            ->where("id = :prefix", array(":prefix"=>$prefix))
            ->order("id")
            ->queryScalar();
            
        # Load the number of syllabi per course
        $return[$prefix]["numsyllabi"] = Yii::app()->db->createCommand()
            ->select("COUNT(*)")
            ->from("course_syllabi")
            ->where("prefix = :prefix",array(":prefix"=>$prefix))
            ->queryScalar();

        # Load the number of instructors per course
        $return[$prefix]["numinstructors"] = count(Yii::app()->db->createCommand()
            ->select("COUNT(*)")
            ->from("course_instructors, course_syllabi")
            ->where("course_syllabi.prefix = :prefix AND course_instructors.courseid = course_syllabi.id",array(":prefix"=>$prefix))
            ->group("instrid")
            ->queryAll());
    }
    
    return $return;
}


function make_unique_form_id($date="",$username="") {
    if($date == "") {
        $date = date("Y-m-d H:i:s");
    }
    if($username == "") {
        $username = Yii::app()->user->name;
    }
    $salt = "heresalittlesalt";
    return substr(md5($username.$salt.$date),3,10);
}

function is_valid_form_id($formid,$date,$username="") {
    if($username == "") {
        $username = Yii::app()->user->name;
    }
    return ($formid == make_unique_form_id($date,$username));
}


function read_zipped_xml($archiveFile, $dataFile) {
    // Create new ZIP archive
    $zip = new ZipArchive;

    // Open received archive file
    if (true === $zip->open($archiveFile)) {
        // If done, search for the data file in the archive
        if (($index = $zip->locateName($dataFile)) !== false) {
            // If found, read it to the string
            $data = $zip->getFromIndex($index);
            // Close archive file
            $zip->close();
            // Load XML from a string
            // Skip errors and warnings
            $xml = new DOMDocument();
            $xml->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
            // Return data without XML formatting tags
            return strip_tags($xml->saveXML());
        }
        $zip->close();
    }

    // In case of failure return empty string
    return "";
}