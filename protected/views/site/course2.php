<?php

$course = new CourseObj();
$course->prefix = $_GET["prefix"];
$course->num = $_GET["num"];
$course->load();

$classes = $course->get_classes();

if(!$course->loaded) {
    Yii::app()->user->setFlash("warning","Could not find course with prefix ".$_GET["prefix"]." and number ".$_GET["num"]);
    $this->redirect(Yii::app()->homeUrl);
    exit;
}

?>
<style>
table {
    width:100%;
    border-spacing:3px;
    border-collapse:separate;
}
table tbody tr td {
    padding:5px;
}
table thead tr th {
    border:2px solid #ccc;
    background-color:#f0f0f0;
    padding:5px;
}
table tbody tr:hover td {
    background-color:#f0fff0;
    cursor:default;
}
div.breadcrumbs {
    font-size:12px;
}
span.spacer {
    padding-left:5px;
    padding-right:5px;
}
</style>

<div class="breadcrumbs">
    <a href="<?php echo Yii::app()->homeUrl; ?>">Home</a>  <span class="spacer">&gt;</span>
    <a href="<?php echo Yii::app()->createUrl('course');?>?prefix=<?php echo $course->prefix; ?>">Explore Course Archive [<?php echo $course->prefix; ?>]</a> <span class="spacer">&gt;</span>
    <a href="<?php echo Yii::app()->createUrl('course');?>?prefix=<?php echo $course->prefix; ?>&num=<?php echo $course->num; ?>"><?php echo $course->prefix." ".$course->num." ".$course->title; ?></a>
</div>

<h1>[<?php echo $course->prefix." ".$course->num; ?>] <?php echo $course->title; ?></h1>

<table>
    <thead>
        <tr>
            <th width="120px" class="calign">Term/Year</th>
            <th width="200px" class="calign">Instructors</th>
            <th width="100px" class="calign">Section(s)</th>
            <th>Class Title</th>
            <th width="100px" class="lalign">Page Link</th>
            <th width="100px">Website</th>
            <th width="100px" class="calign">Syllabus</th>
        </tr>
    </thead>
    <tbody>
        <?php $count=0; foreach($classes as $class): $count++; ?>
        <tr <?php if($count%2==0): ?>class="odd"<?php endif; ?>>
            <td class="ralign"><?php echo $class->term." ".$class->year; ?></td>
            <td class="calign"><?php echo $class->print_instructors(); ?></td>
            <td class="calign"><?php echo $class->section; ?></td>
            <td class="lalign"><?php echo ($class->subtitle=="")?$course->title:@$class->subtitle; ?></td>
            <td><a href="<?php echo Yii::app()->createUrl('permalink');?>?cid=<?php echo $class->classid; ?>">Syllabus Page</a></td>
            <td>
                <?php if($class->website!=""): ?>
                    (<a href="<?php echo $class->website; ?>">website</a>)
                <?php endif; ?>
            </td>
            <td class="lalign">
                <?php foreach($class->syllabi as $syllabus): ?>
                    <a href="<?php echo Yii::app()->createUrl('_download'); ?>?sid=<?php echo $syllabus->syllabusid; ?>">
                        <?php echo StdLib::load_image($syllabus->type,"16px","16px")." ".$syllabus->type; ?>
                    </a>
                <?php endforeach; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>