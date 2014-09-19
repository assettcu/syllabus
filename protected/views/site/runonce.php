<?php
ini_set("display_errors",1);
error_reporting(E_ALL);

set_time_limit(0);

$dir = "C:/archive/IPHY/";

$query = "
	SELECT		syllabusid
	FROM		{{syllabi}}
	WHERE		filename LIKE '%_sect000';
";


if ($handle = opendir($dir)) {
    echo "Directory handle: $handle\n";
    echo "Entries:\n";

    /* This is the correct way to loop over the directory. */
    while (false !== ($entry = readdir($handle))) {
		if($entry != "." && $entry != "..") {
			
			$query = "
				SELECT		syllabusid
				FROM		{{syllabi}}
				WHERE		filename = :filename;
			";
			
			$conn = Yii::app()->db;
			$command = $conn->createCommand($query);
			$command->bindParam(":filename",$entry);
			$result = $command->queryScalar();
			$syllabus = new SyllabusObj($result);
			$class = new ClassObj($syllabus->classid);
			var_dump($class);
			die();
		}
    }

    closedir($handle);
}

function add_syllabus_dynamically($dir,$entry) {
	$query = "
		SELECT		syllabusid
		FROM		{{syllabi}}
		WHERE		filename LIKE '%_sect000';
	";
	
	$conn = Yii::app()->db;
	$command = $conn->createCommand($query);
	$command->bindParam(":filename",$entry);
	$result = $command->queryScalar();
	$syllabus = new SyllabusObj($result);
	if(!$syllabus->loaded){
		$syllabus->filename = $entry;
	}
	$syllabus->get_classobj();
	if(!isset($syllabus->class)) {
		$course = new CourseObj();
		$course->prefix = "IPHY";
		$syllabus->parse_syllabus_name();
		$course->num = $syllabus->num;
		$course->load();
		if($course->loaded) {
			$class = new ClassObj();
			$class->courseid = $course->courseid;
			$class->section = $syllabus->section;
			$class->term = $syllabus->term;
			$class->year = $syllabus->year;
			if(!$class->save()) {
				print $class->get_error();
				die();
			}
			$syllabus->classid = $class->classid;
			$filename = $syllabus->generate_syllabus_name();
			$syllabus->filename = $filename.".".$syllabus->type;
			rename($dir.$entry,$dir.$filename) or die("Renaming failed: ".$dir."/".$filename);
			if(!$syllabus->save()) {
				print $syllabus->get_error();
				die();
			}
			print "Successfully saved new classid:".$class->classid." with syllabusid:".$syllabus->syllabusid." in courseid:".$course->courseid."<br/>";
		} else {
			print "Course not found: ".$course->prefix." ".$course->num."<br/>";
		}
	} else if($syllabus->class->course->prefix == "IPHY" and $syllabus->section != "000"){
		print "IPHY!<br/>";
	} else {
		if($syllabus->class->section == "000") {
			$syllabus->filename = $syllabus->filename.".".$syllabus->type;
			$syllabus->save();
			print "Changed filetype to ".$syllabus->type."<br/>";
		}
	}
}

ENDOFTHEWORLD:

?>
Done.
