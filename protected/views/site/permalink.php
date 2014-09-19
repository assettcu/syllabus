<style>
table tr td {
	font-size:13px;
}
table tr td:first-child {
	text-align:right;
	padding:5px;
	padding-right:10px;
	border-right:1px solid #0066CC;
	font-weight:bold;
}
table tr td:last-child {
	text-align:left;
	padding:5px;
	padding-left:10px;
}
ul {
	list-style: none;
}
span.spacer {
    padding-left:5px;
    padding-right:5px;
}
</style>
<?php
$course = $class->get_course();
?>

<div class="breadcrumbs">
    <a href="<?php echo Yii::app()->homeUrl; ?>">Home</a>  <span class="spacer">&gt;</span>
    <a href="<?php echo Yii::app()->createUrl('course');?>?prefix=<?php echo $course->prefix; ?>">Explore Course Archive [<?php echo $course->prefix; ?>]</a> <span class="spacer">&gt;</span>
    <a href="<?php echo Yii::app()->createUrl('course');?>?prefix=<?php echo $course->prefix; ?>&num=<?php echo $course->num; ?>"><?php echo $course->prefix." ".$course->num." ".$course->title; ?></a>  <span class="spacer">&gt;</span>
    <a href="<?php echo Yii::app()->createUrl('permalink');?>?cid=<?php echo $class->classid; ?>"><?php echo $class->year." ".$class->term." - section(s) ".$class->section; ?></a>
</div>
<?php

$flashes = new Flashes();
$flashes->render();

?>
<h1>Class Permalink Information</h1>

<table>
	<tr>
		<td>Course</td>
		<td><?php echo $class->course->prefix." ".$class->course->num;?></td>
	</tr>
	<tr>
		<td>Class Title</td>
		<td><?php echo $class->course->title;?></td>
	</tr>
	<tr>
		<td>Section</td>
		<td><?php echo $class->section;?></td>
	</tr>
	<tr>
		<td>Instructor(s)</td>
		<td><?php echo $class->print_instructors();?></td>
	</tr>
	<tr>
		<td>Term</td>
		<td><?php echo $class->year." ".$class->term;?></td>
	</tr>
	
	<tr>
		<td>Website</td>
		<td>
			<?php if($class->website == ""): ?>
				No website
			<?php else: ?>
				<a href="<?php echo $class->website;?>"><?php echo $class->website;?></a></td>
			<?php endif; ?>
	</tr>
	<tr>
		<td>Syllabus</td>
		<td>
			<ul>
				<?php $flag = false; foreach($class->syllabi as $syllabus): ?>
					<?php if($syllabus->valid()): $flag = true;?>
					<li>
						<?php echo $syllabus->filename;?> 
						(<a href="#" class="preview" syllabusid="<?php echo $syllabus->syllabusid;?>">preview</a>)
		    			<?php if(StdLib::on_campus() or $class->offcampus==1): ?>
						(<a href="#" class="download" syllabusid="<?php echo $syllabus->syllabusid;?>">download</a>)
						<?php elseif(!StdLib::on_campus() and $class->offcampus==0): ?>
						(<span style="text-decoration: line-through;" id="download-no" title="Syllabus may only be downloaded from on-campus or using CU's VPN.">download</span>)
						<?php endif; ?>
						<?php 
						if($syllabus->primary and !@$primaryflag) {
                            
                            $websrc = StdLib::load_image_source("star.png","");
                            $local = StdLib::make_path_local($websrc);
                            $imager = new Imager($local);
                            $imager->resize(18);
                            $imager->add_attribute("style", "position:relative;right:0px;top:4px;");
                            $imager->add_attribute("title", "This syllabus will be the one downloaded when you click the download button on the front page.");
                            $imager->add_attribute("id",    "primary");
                            $imager->render();
						}
						?>
					</li>
					<?php endif; ?>
				<?php endforeach; ?>
				<?php if(!$flag): ?>
					<li>
						No attached syllabus.
					</li>
				<?php endif; ?>
			</ul>
		</td>
	</tr>
	<tr>
		<td>Open to Off-Campus</td>
		<td><?php echo ($class->offcampus==1)?"yes":"no";?></td>
	</tr>
</table>
<br/>
<?php if(!Yii::app()->user->isGuest and Yii::app()->user->getState("_user")->has_permission($class)): ?>
<button id="edit">Edit Class</button>
<?php endif; ?>

<div id="preview" title="Preview Syllabus"></div>

<script>
jQuery(document).ready(function($){
	$("#download-no").tipTip();
	$("#primary").tipTip({
		defaultPosition: "right",
	});
	$("#edit").click(function(){
		window.location = '<?php echo Yii::app()->createUrl('editclass');?>?cid=<?php echo $class->classid;?>';
		return false;
	});
	$(".download").click(function(){
		var syllabusid = $(this).attr("syllabusid");
		window.location = '<?php echo Yii::app()->createUrl("_download");?>?sid='+syllabusid;
		return false;
	});
	$(".preview").click(function(){
		var syllabusid = $(this).attr("syllabusid");
		$("#preview").dialog("option","width",$(window).width()*0.95);
		$("#preview").dialog("option","height",$(window).height()*0.95);
		$("#preview").dialog("open").load("<?php echo Yii::app()->createUrl('_preview_syllabus');?>?sid="+syllabusid+"&w="+$(window).width()*0.95+"&h="+$(window).height()*0.95);
		return false;
	});
	
	$("#preview").dialog({
		"autoOpen": 		false,
		"resizable": 		false,
		"draggable": 		false,
		"modal": 			true,
	});
});
</script>