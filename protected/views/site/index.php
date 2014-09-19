<?php
$flashes = new Flashes;
$flashes->render();

$start = microtime(true);
$manager = new Manager;
$courses = $manager->load_unique_courses();

?>
<style>
div.browse-archive {
    border-top:2px solid #ccc;
    padding:10px;
    margin: 15px 0px;
}
div.sub-header {
    font-size:14px;
}
button.course-container {
    min-width:150px;
    min-height:85px;
}
div.course-years {
    padding-top:5px;
}
table#browse-archive-table {
    width:100%;
    border-spacing: 15px;
    border-collapse:separate;
    margin: 0 auto;
}
table#browse-archive-table tr td {
    text-align:center;
    vertical-align:middle;
}
</style>
<div class="ui-state-default ui-corner-all" style="padding:5px;margin-bottom:15px;font-size:14px;text-align:center;">
    Welcome to the Syllabus Archive! You may search the archive for past syllabi or browse it by selecting one of the department buttons below.
</div>

<div style="width:100%;text-align:center;margin:25px;">
	<form action="<?=Yii::app()->createUrl('search');?>" method="get">
		<input type="text" name="s" style="width:600px;padding:5px;font-size:13px;" class="ui-corner-all" /> <button>Search</button>
	</form>
</div>

<div class="browse-archive">
    <div class="sub-header" style="text-align:center;font-size:16px;font-weight:bold;">Browse our Archive</div>
    <table id="browse-archive-table">
        <?php 
        $count = 0;
        foreach($courses as $prefix=>$data):
            if($count%6==0) echo "<tr>";
        ?>
            <td>
                <button class="course-container" prefix="<?php echo $prefix;?>">
                    <div style="padding:5px;font-weight:bold;margin-bottom:5px;"><?php echo $prefix; ?></div>
                    <div><?php echo $data["numsyllabi"]; ?> Syllabi</div>
                    <div><?php echo $data["numinstructors"]; ?> Instructors</div>
                    <div class="course-years"><?php echo $data["minyear"]; ?>-<?php echo $data["maxyear"]; ?></div>
                </button>
            </td>
        <?php 
        $count++;
        if(count($courses)==$count or $count%6==0) echo "</tr>";
        endforeach; 
        ?>
    </table>
</div>

<script>
jQuery(document).ready(function($){
    $("button.course-container").click(function(){
        var prefix = $(this).attr("prefix");
        window.location = "<?php echo Yii::app()->createUrl('course'); ?>?prefix="+prefix;
        return false;
    });
});
</script>