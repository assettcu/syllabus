<?php

class CourseSyllabusObj extends FactoryObj
{
    public $syllabus_links = array(
        "pdf"  => null,
        "doc"  => null,
        "docx" => null
    );

    public function __construct($courseid=null) {
        parent::__construct("id","course_syllabi",$courseid);
    }

    public function pre_save() {
        if(!$this->is_valid_id()) {
            $this->id = $this->generate_id();
        }
        if(!isset($this->date_created)) {
            $this->date_created = date("Y-m-d H:i:s");
        }
        if(isset($this->term)) {
            $this->term = $this->convertto_numeric_term($this->term);
            $this->term = $this->convertto_string_term($this->term);
        }
    }

    public function post_save() {
        if(isset($this->instructors) and !empty($this->instructors)) {
            # Get the current instructors of this course (if exist)
            $result = Yii::app()->db->createCommand()
                ->select("*")
                ->from("course_instructors")
                ->where("courseid = :courseid",
                    array(
                        "courseid" => $this->id
                    )
                )
                ->queryAll();

            # Pull out the instructor ids into proper array
            $dbinstructors = array();
            foreach($result as $row) {
                $dbinstructors[$row["instrid"]] = $row["instrid"];
            }
            # Loop through each instructor attached to this course
            foreach($this->instructors as $instrid) {
                if(!in_array($instrid,$dbinstructors)) {
                    $instructor = new InstructorObj($instrid);
                    $this->insert_course_instructor($this->id, $instrid, $instructor->name);
                }
                unset($dbinstructors[$instrid]);
            }
            # Remove any instructors found that are not attached anymore
            if(!empty($dbinstructors)) {
                foreach($dbinstructors as $instrid) {
                    $this->remove_course_instructor($this->id, $instrid);
                }
            }
        }
    }

    public function remove_course_instructor($courseid, $instrid) {
        if(empty($courseid) or empty($instrid)) {
            return false;
        }
        if(!$this->has_course_instructor($courseid,$instrid)) {
            return true;
        }
        return Yii::app()->db->createCommand()
            ->delete("course_instructors",
                "courseid = :courseid AND instrid = :instrid",
                array(
                    "courseid"  => $courseid,
                    "instrid"   => $instrid
                )
            );
    }

    public function insert_course_instructor($courseid, $instrid, $fullname) {
        if(empty($courseid) or empty($instrid) or empty($fullname)) {
            return false;
        }
        if($this->has_course_instructor($courseid,$instrid)) {
            return true;
        }
        return Yii::app()->db->createCommand()
            ->insert("course_instructors",
                array(
                    "courseid"  => $courseid,
                    "instrid"   => $instrid,
                    "fullname"  => $fullname
                )
            );
    }

    public function has_course_instructor($courseid,$instrid) {
        return (Yii::app()->db->createCommand()
            ->select("COUNT(*)")
            ->from("course_instructors")
            ->where("courseid = :courseid AND instrid = :instrid",array(":courseid"=>$courseid,":instrid"=>$instrid))
            ->queryScalar() == 1);
    }

    public function generate_id() {
        $this->yearterm = $this->year.$this->convertto_numeric_term($this->term);
        if(isset($this->prefix,$this->num,$this->year,$this->term,$this->section)) {
            $idlist = array(
                $this->prefix,
                $this->num,
                $this->yearterm,
                $this->section
            );
            return implode("-",$idlist);
        }
        else {
            return null;
        }
    }

    public function break_id() {
        list($this->prefix,$this->num,$this->yearterm,$this->section) = explode(",",$this->id);
    }

    protected function convertto_numeric_term($term) {
        switch(str_replace(" ","",strtolower($term))) {
            case "spring":      return 1;
            case "summer":
            case "summerm":
            case "summera":
            case "summerb":
            case "summerc":
            case "summerd":    return 4;
            case "fall":       return 7;
            default:           return $term;
        }
    }

    protected function convertto_string_term($term) {
        switch($term) {
            case 1:  return "Spring";
            case 4:  return "Summer";
            case 7:  return "Fall";
            default: return $term;
        }
    }

    public function run_check() {
        $required = array(
            "prefix",
            "num",
            "year",
            "term",
            "section",
            "title"
        );
        foreach($required as $var) {
            if(!isset($this->$var) or empty($this->$var)) {
                return !$this->set_error("Class variable <i>".$var."</i> is not set so CourseSyllabusObj could not save.");
            }
        }

        # Prefix must be exactly four alphabetical letters
        if(!preg_match("/[A-Z]{4}/i",$this->prefix)) {
            return !$this->set_error("Class variable <i>\$prefix</i> is not formatted properly. Value: {$this->prefix}");
        }

        # Number must be exactly four numbers
        if(!preg_match("/[0-9]{4}/",$this->num)) {
            return !$this->set_error("Class variable <i>\$num</i> is not formatted properly. Value: {$this->num}");
        }

        # Year must be 19** or 20** (hopefully replaced by the time we get to year 2100!)
        if(!preg_match("/(19|20)[0-9]{2}/",$this->year)) {
            return !$this->set_error("Class variable <i>\$year</i> is not formatted properly. Value: {$this->year}");
        }

        # Term must have a numeric equivalent
        $term = $this->convertto_numeric_term($this->term);
        if($term != 1 and $term != 4 and $term != 7) {
            return !$this->set_error("Class variable <i>\$term</i> was not the expected value. Value: {$this->term}");
        }

        # No errors, then return success
        return true;
    }

    public function num_syllabi()
    {
        return Yii::app()->db->createCommand()
            ->select("COUNT(*)")
            ->from("course_syllabi")
            ->where("prefix = :prefix AND num = :num", array(":prefix"=>$this->prefix, "num"=>$this->num))
            ->queryScalar();
    }

    public function year_range()
    {

        # Load the earliest year
        $return["minyear"] = Yii::app()->db->createCommand()
            ->select("year")
            ->from("course_syllabi")
            ->where("prefix = :prefix AND num = :num", array(":prefix"=>$this->prefix, "num"=>$this->num))
            ->order("year ASC")
            ->queryScalar();

        # Load the latest year
        $return["maxyear"] = Yii::app()->db->createCommand()
            ->select("year")
            ->from("course_syllabi")
            ->where("prefix = :prefix AND num = :num", array(":prefix"=>$this->prefix, "num"=>$this->num))
            ->order("year DESC")
            ->queryScalar();

        return $return;
    }

    public function print_span_years()
    {
        $years = $this->year_range();
        echo $years["minyear"]." - ".$years["maxyear"];
    }

    public function print_instructors()
    {
        $instructors = $this->get_instructors();
        $instructors_array = array();
        foreach($instructors as $row) {
            $instructors_array[] = $row["fullname"];
        }

        echo implode(", ",$instructors_array);
    }

    public function get_instructors() {
        return Yii::app()->db->createCommand()
            ->select("fullname")
            ->from("course_instructors")
            ->where("courseid = :courseid",array("courseid"=>$this->id))
            ->queryAll();
    }

    public function get_instrids() {
        return Yii::app()->db->createCommand()
            ->select("instrid")
            ->from("course_instructors")
            ->where("courseid = :courseid",array("courseid"=>$this->id))
            ->queryAll();
    }

    public function editable_instructors() {
        $instructors = $this->get_instructors();
        $instructors_array = array();
        foreach($instructors as $row) {
            $instructors_array[] = $row["fullname"];
        }

        return implode("\n",$instructors_array);
    }

    public function find_syllabus_links()
    {
        foreach($this->syllabus_links as $extension => $file) {
            if(is_file(LOCAL_ARCHIVE.$this->id.".".$extension)) {
                $this->syllabus_links[$extension] = WEB_ARCHIVE.$this->id.".".$extension;
            }
        }
    }

    public function has_syllabus_file()
    {
        # Double check we called this function
        $this->find_syllabus_links();
        if(is_null($this->syllabus_links)) {
            return false;
        }
        foreach($this->syllabus_links as $extension => $link) {
            if(!is_null($link)) {
                return true;
            }
        }
        return false;

    }

    public function pre_delete() {
        $this->find_syllabus_links();
        foreach($this->syllabus_links as $ext => $link) {
            if(is_file(LOCAL_ARCHIVE.$this->id.".".$ext)) {
                unlink(LOCAL_ARCHIVE.$this->id.".".$ext);
            }
        }
    }
}
