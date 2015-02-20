<?php
StdLib::Functions();
Flashes::render();

$prefix = $_REQUEST["prefix"];
$num = $_REQUEST["num"];

$title = Yii::app()->db->createCommand()
    ->select("title")
    ->from("course_syllabi")
    ->where("prefix = :prefix AND num = :num", array(":prefix"=>$prefix,":num"=>$num))
    ->queryScalar();

$classes = Yii::app()->db->createCommand()
    ->select("id")
    ->from("course_syllabi")
    ->where("prefix = :prefix AND num = :num", array(":prefix"=>$prefix,":num"=>$num))
    ->order("year DESC, (term = 'Fall') DESC, (term = 'Summer') DESC, (term = 'Spring') DESC")
    ->queryAll();

$COREUSER = (!Yii::app()->user->isGuest) ? new UserObj(Yii::app()->user->name) : new UserObj();
?>
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap.css" />
<script type="text/javascript" src="<?php echo WEB_LIBRARY_PATH; ?>jquery/modules/bootstrap/bootstrap.min.js"></script>
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
table tr td a:not(.btn) {
    text-decoration:none;
}
</style>

<ul class="breadcrumb">
    <li><a href="<?php echo Yii::app()->homeUrl; ?>">Home</a></li>
    <li><a href="<?php echo Yii::app()->createUrl('course');?>?prefix=<?php echo $prefix; ?>">Explore Course Archive [<?php echo $prefix; ?>]</a></li>
    <li><a href="<?php echo Yii::app()->createUrl('course');?>?prefix=<?php echo $prefix; ?>&num=<?php echo $num; ?>"><?php echo $prefix." ".$num." ".$title; ?></a></li>
</ul>

<h3>[<?php echo $prefix." ".$num; ?>] <?php echo $title; ?></h3>

<table>
    <thead>
        <tr>
            <th width="120px" class="calign">Term/Year</th>
            <th width="200px" class="calign">Instructors</th>
            <th width="100px" class="calign">Section</th>
            <th>Class Title</th>
            <th width="100px" class="calign">View</th>
            <?php if($COREUSER->atleast_permission("manager")): ?>
            <th class="calign">Edit</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php 
        $count=0; 
        foreach($classes as $row): 
            $count++; 
            $class = new CourseSyllabusObj($row["id"]);
            $class->find_syllabus_links();
        ?>
        <tr <?php if($count%2==0): ?>class="odd"<?php endif; ?>>
            <td class="ralign"><?php echo $class->term." ".$class->year; ?></td>
            <td class="calign"><?php echo $class->print_instructors(); ?></td>
            <td class="calign"><?php echo $class->section; ?></td>
            <td class="lalign"><?php echo ($class->special_topics_title == "") ? $title : $class->special_topics_title; ?></td>
            <td class="calign">
                <?php 
                foreach($class->syllabus_links as $extension => $link):
                    if(is_null($link)) continue;
                ?>
                <a href="<?php echo $link; ?>">
                    <span class="icon icon-file"> </span> <?php echo $extension; ?>
                </a>
                <?php
                endforeach;
                ?>
            </td>
            <?php if($COREUSER->atleast_permission("manager")): ?>
            <td class="calign"><a href="<?php echo Yii::app()->createUrl('edit'); ?>?id=<?php echo $class->id; ?>">edit</a></td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>