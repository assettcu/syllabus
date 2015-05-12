<?php
/**
 * Run Once
 * 
 * The purpose of the "run once" is to run PHP functions to alter and modify
 * the archive in conjunction with the system itself. This means it loads up
 * functions and users and objects just as the system would and run functionality
 * against them.
 * 
 * For example, say we modify the namespace for syllabi. We would create functions in
 * Run Once to change the names for all the syllabi.
 * 
 * Only PROGRAMMERS are allowed here. Restricted in the SiteController using StdLib::is_programmer.
 * Will be ignored by the GitHub repository.
 */
StdLib::pre();
StdLib::set_debug_state("DEVELOPMENT");

set_time_limit(0);

defined('OCR_FILE_OUT') or define('OCR_FILE_OUT',"C:\\web\\OCR\\scanout\\~SyllabusArchive");

//$files = scandir(OCR_FILE_OUT);
$files = glob(OCR_FILE_OUT. '\\*.txt', GLOB_BRACE);

// Loop through files
foreach($files as $file) {
    //var_dump($file);
    $id = substr(basename($file), 0, -4);

    $myfile = fopen($file, "r") or die("Unable to open file!");
    $content = fread($myfile,filesize($file));
    fclose($myfile);

    $content = preg_replace('/[^a-zA-Z0-9\s]/', '', $content);

    $command = Yii::app()->db->createCommand();

    $sql='UPDATE course_syllabi SET content=:content WHERE id=:id';
    $params = array(
        "content" => $content,
        "id" => $id
    );
    $command->setText($sql)->execute($params);
}

echo "done";

return false;

?>
