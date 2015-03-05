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

$department = Yii::app()->db->createCommand()
    ->select("label")
    ->from("departments")
    ->where("id = :prefix", array(":prefix"=>$prefix))
    ->queryScalar();

$classes = Yii::app()->db->createCommand()
    ->select("id")
    ->from("course_syllabi")
    ->where("prefix = :prefix AND num = :num", array(":prefix"=>$prefix,":num"=>$num))
    ->order("year DESC, (term = 'Fall') DESC, (term = 'Summer') DESC, (term = 'Spring') DESC")
    ->queryAll();

$COREUSER = (!Yii::app()->user->isGuest) ? new UserObj(Yii::app()->user->name) : new UserObj();
?>

<ul class="breadcrumb">
    <li><a href="<?php echo Yii::app()->homeUrl; ?>">Home</a></li>
    <li><a href="<?php echo Yii::app()->homeUrl;?>?prefix=<?php echo $prefix; ?>"><?php echo $department; ?></a></li>
    <li class="active"><?php echo $prefix." ".$num." - ".$title; ?></li>
</ul>

<div class="courses-header">
<h2><b><?php echo $prefix." ".$num; ?></b> - <?php echo $title; ?></h2>
</div>


    <table class="table table-striped table-hover">
        <thead>
            <tr class="active">
                <th width="120px">Term/Year</th>
                <th width="200px">Instructors</th>
                <th width="100px" class="calign">Section</th>
                <th>Class Title</th>
                <th width="100px">View</th>
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
            <tr>
                <td><?php echo $class->term." ".$class->year; ?></td>
                <td><?php echo $class->print_instructors(); ?></td>
                <td class="calign"><?php echo $class->section; ?></td>
                <td class="lalign"><?php echo ($class->special_topics_title == "") ? $title : $class->special_topics_title; ?></td>
                <td>
                    <?php 
                    foreach($class->syllabus_links as $extension => $link):
                        if(is_null($link)) continue;
                    ?>
                    <a href="<?php echo $link; ?>">
                        <?php if($extension === "docx" || $extension === "doc"): ?>
                            <i class="fa fa-file-word-o"></i>
                        <?php elseif ($extension === "pdf"): ?>
                            <i class="fa fa-file-pdf-o"></i>
                        <?php endif; ?><?php echo $extension; ?>
                    </a><br>
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
