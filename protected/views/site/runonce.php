<?php
StdLib::pre();

ini_set("display_errors",1);
error_reporting(E_ALL);

set_time_limit(0);

$NUM_TO_PROCESS = 3;
$NUM_COPIED = 0;
$dir = "C:\\archive\\Syllabus Archive\\";

$results = Yii::app()->db->createCommand()
    ->select("id")
    ->from("course_syllabi")
    ->where("has_file = 2")
    ->limit($NUM_TO_PROCESS)
    ->queryAll();

StdLib::vdump($results);

foreach($results as $row) {
    $filepath = $dir.$row["id"].".pdf";
    if(is_file($filepath)) {
        $source = $filepath;
        $destination = "C:\\web\\OCR\\scanin\\~SyllabusArchive\\".$row["id"].".pdf";
        if(is_file($destination)) {
            continue;
        }
        if(copy($source, $destination)) {
            $course = new CourseSyllabusObj($row["id"]);
            $course->has_file = 3;
            $course->save();
            $NUM_COPIED++;
        }
    }
}

$starttime = time();
$scanout = "C:\\web\\OCR\\scanout\\~SyllabusArchive\\";
do {
    $numfiles = count(scandir($scanout));
    sleep(1);
    var_dump($numfiles);
} while($numfiles < $NUM_COPIED+2 or (($starttime - time()) > 180));

?>
Done.
