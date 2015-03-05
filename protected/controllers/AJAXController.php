<?php
header('Access-Control-Allow-Origin: *');

require "BaseController.php";

class AJAXController extends BaseController
{
    
    public function actionCourseSyllabusExists() {
        $rest = new RestServer();
        $request = RestUtils::processRequest();
        $required = array("prefix","num","section","term","year");
        $keys = array_keys($request);
        
        # Must be logged in
        if(Yii::app()->user->isGuest) {
            return RestUtils::sendResponse(310); 
        }
        
        # Not all parameters sent
        if(count(array_intersect($required, $keys)) != count($required)) {
            return RestUtils::sendResponse(308); 
        }
        
        $CS = new CourseSyllabusObj();
        $CS->prefix = $request["prefix"];
        $CS->num = $request["num"];
        $CS->term = $request["term"];
        $CS->year = $request["year"];
        $sections = explode(",",$request["section"]);
        
        $return = array();
        foreach($sections as $section) {
            $section = trim($section);
            $CS->section = $section;
            $CS->id = $CS->generate_id();
            $CS->load();
            $return[$section] = $CS->loaded;
        }
        
        return print json_encode($return);
    }

    public function actionDeleteCourseSyllabus() {
        $rest = new RestServer();
        $request = RestUtils::processRequest();
        $required = array("id");
        $keys = array_keys($request);
        
        $user = new UserObj(Yii::app()->user->name);
        
        # Must be logged in and at least be a manager to delete syllabi
        if(Yii::app()->user->isGuest and $user->loaded and $user->atleast_permission("manager")) {
            return RestUtils::sendResponse(310); 
        }
        
        # Not all parameters sent
        if(count(array_intersect($required, $keys)) != count($required)) {
            return RestUtils::sendResponse(308); 
        }
        
        $CS = new CourseSyllabusObj($request["id"]);
        $CS->delete();
        
        Yii::app()->user->setFlash("success","Successfully deleted course syllabus.");
        
        return true;
        
    }

    // Get Courses by department prefix
    public function actionLoadCourses() {    
        $rest = new RestServer();
        $request = RestUtils::processRequest();
        $required = array("prefix");
        $keys = array_keys($request);

        if(count(array_intersect($required, $keys)) != count($required)) {
            return RestUtils::sendResponse(308); 
        }
        $prefix = $request["prefix"];

        $result = Yii::app()->db->createCommand()
        ->select("id, prefix, num, title, COUNT(*) as num_syllabi, MIN(year) as first_year, MAX(year) as last_year")
        ->from("course_syllabi")
        ->where("prefix = :prefix", array(":prefix"=>$prefix))
        ->group("num")
        ->order("num ASC, title ASC")
        ->queryAll();

        return print json_encode($result);
    }
    
    
    
    public function action_preview_syllabus()
    {
        return false;
        # This needs to be revamped, but the coding for previews is still usable.
        $rest = new RestServer();
        $request = RestUtils::processRequest();
        $required = array("filename","dept");
        $keys = array_keys($request);

        if(count(array_intersect($required, $keys)) != count($required)) {
            return RestUtils::sendResponse(308); 
        }
    
        if(isset($_REQUEST["filename"],$_REQUEST["dept"])) {
            $width = (isset($_REQUEST["w"])) ? $_REQUEST["w"] : 600;
            $height = (isset($_REQUEST["h"])) ? $_REQUEST["h"] : 300;
            $dept = $_REQUEST["dept"];
            $filename = $_REQUEST["filename"];
            $user = Yii::app()->user->getState("_user");
            if(!$user->has_permission($dept)){
                return print "You do not have permission to view this syllabus.";
            }
            $file = "C:/archive/".$dept."/".$filename;
            $pathinfo = pathinfo($file);
            if($pathinfo["extension"]=="pdf"){
                return print '<embed src="http://compass.colorado.edu/archive/'.$dept.'/'.$filename.'#view=FitH" width="'.$width.'px" height="'.$height.'px"/>';
            }
            elseif($pathinfo["extension"]=="doc" or $pathinfo["extension"]=="docx") {
                return print '<iframe src="//docs.google.com/viewer?url=http%3A%2F%2Fcompass.colorado.edu%2Farchive%2F'.$dept.'%2F'.$filename.'&embedded=true" width="'.$width.'px" height="'.$height.'" style="border: none;"></iframe>';
            } elseif($pathinfo["extension"]=="txt" or $pathinfo["extension"]=="html"){
                return print file_get_contents($file);
            }
            return print "";
        }
        $syllabus = new SyllabusObj(@$_REQUEST["sid"]);
        if(!$syllabus->loaded) {
            return print "Could not load syllabus with id: ".$_REQUEST["sid"];
        }
        $class = new ClassObj($syllabus->classid);
        if(!$syllabus->loaded) {
            return print "Could not load class with id: ".$syllabus->classid;
        }
        $width = (isset($_REQUEST["w"])) ? $_REQUEST["w"]-50 : 600;
        $height = (isset($_REQUEST["h"])) ? $_REQUEST["h"]-70 : 300;
        if($syllabus->type=="pdf"){
            return print '<embed src="http://compass.colorado.edu/archive/'.$class->course->prefix.'/'.$syllabus->filename.'#view=FitH" width="'.$width.'px" height="'.$height.'px"/>';
        }
        elseif($syllabus->type=="doc" or $syllabus->type=="docx") {
            return print '<iframe src="//docs.google.com/viewer?url=http%3A%2F%2Fcompass.colorado.edu%2Farchive%2F'.$class->course->prefix.'%2F'.$syllabus->filename.'&embedded=true" width="'.$width.'px" height="'.$height.'" style="border: none;"></iframe>';
        }
        return print "";
    }

}
