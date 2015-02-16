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

function load_unique_courses()
{
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
        
        # Load the number of classes per course
        $return[$prefix]["numclasses"] = count(Yii::app()->db->createCommand()
            ->select('COUNT(*)')
            ->from("course_syllabi")
            ->where("prefix = :prefix",array(":prefix"=>$prefix))
            ->group("prefix")
            ->queryScalar());
            
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
        
        # Load the earliest year
        $return[$prefix]["minyear"] = Yii::app()->db->createCommand()
            ->select("year")
            ->from("course_syllabi")
            ->where("prefix = :prefix", array(":prefix"=>$prefix))
            ->order("year ASC")
            ->queryScalar();
        
        # Load the latest year
        $return[$prefix]["maxyear"] = Yii::app()->db->createCommand()
            ->select("year")
            ->from("course_syllabi")
            ->where("prefix = :prefix", array(":prefix"=>$prefix))
            ->order("year DESC")
            ->queryScalar();
    }
    
    return $return;
}
