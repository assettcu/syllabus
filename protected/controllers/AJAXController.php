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
        
        # Must be logged in
        if(Yii::app()->user->isGuest) {
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

}
