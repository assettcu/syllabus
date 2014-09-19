<?php

function sort_by_weight($a,$b){
	if(!isset($a->weight,$b->weight)) return 0;
	if($a->weight == $b->weight) {
		if($a->course->prefix == $b->course->prefix) {
			if($a->course->num == $b->course->num) {
				if($a->year == $b->year) {
					if($a->term == $b->term) {
						return ($a->section < $b->section) ? 1 : -1;
					}
					return ($a->term < $b->term) ? -1 : 1;
				}
				return ($a->year < $b->year) ? 1 : -1;
			}
			return ($a->course->num < $b->course->num) ? -1 : 1;
		}
		return ($a->course->prefix > $b->course->prefix) ? 1 : -1;
	}
	return ($a->weight < $b->weight) ? 1 : -1;
}

$time = microtime(true);

$search = $_REQUEST["s"];
$terms = explode(" ",$search);

$count = 0;
foreach($terms as $term) {
	$count++;
	$where[] = "(
		{{classes}}.section = :term_".$count." OR
		{{courses}}.prefix = :term_".$count." OR
		{{courses}}.num = :term_".$count." OR
		{{courses}}.title LIKE :term2_".$count." OR
		{{instructors}}.name LIKE :term2_".$count." OR
		{{classes}}.term = :term_".$count." OR
		{{classes}}.year = :term_".$count." OR
		{{classes}}.flag = :term_".$count."
	)";
}

$where[] = "({{courses}}.courseid = {{classes}}.courseid AND {{instructors}}.instrid = {{classes}}.instructors)";

if(empty($where)) {
	$where = "WHERE 1=0";
} else {
	$where = "WHERE ".implode(" AND ",$where);
}

$conn = Yii::app()->db;
$query = "
	SELECT 		{{classes}}.classid
	FROM		{{classes}}, {{courses}}, {{instructors}}
	$where;
";
$command = $conn->createCommand($query);
$count = 0;
foreach($terms as $term) {
	$count++;
	$command->bindParam(":term_".$count,$term);
	$command->bindValue(":term2_".$count,"%".$term."%");
}
$result = $command->queryAll();

$classes = array();

foreach($result as $row) {
	$class = new ClassObj($row["classid"]);
	$class->weight = 0;
	$termcount = 0;
	foreach($terms as $term) {
		
		$termcount += 1;
		$div = (count($terms) - $termcount - 1);
		if($div==0) {
			$div = 1;
		}
		$term_weight = (2 - (count($terms) - $termcount) / ($div));
		if($term_weight<=0) $term_weight = 1;
		
		if($class->course->prefix == $term) {
			$class->weight += ($term_weight * 10);
		}
		if($class->course->num == $term) {
			$class->weight += ($term_weight * 10);
		}
		if(preg_match("/".$term."/i",$class->course->title)) {
			$class->weight += ($term_weight * 5);
		}
		if($class->section == $term) {
			$class->weight += ($term_weight * 7);
		}
		foreach($class->instructors as $instructor) {
			if(!$instructor->loaded) continue;
			if(preg_match("/".$term."/i",$instructor->name)) {
				$class->weight += ($term_weight * 8);
			}
		}
		if($class->term == $term) {
			$class->weight += ($term_weight * 15);
		}
		if($class->year == $term) {
			$class->weight += ($term_weight * 15);
		}
		if($class->flag == $term) {
			$class->weight += ($term_weight * 25);
		}
	}
	if($class->weight >= 5) {
		$classes[$class->classid] = $class;
	}
}

$total = $time-microtime(true);

usort($classes,"sort_by_weight");

ob_start();
ini_set("display_errors",1);
error_reporting(E_ALL);
$count=0;
if(!empty($classes)) {
	$limiter = 100;
	foreach($classes as $class) {
		$syllabus = $class->get_primary_syllabus();
		if(Yii::app()->user->isGuest) {
			if((is_null($syllabus) or !$syllabus->valid()) and $class->website=="") continue;
		}
		$count++;
		if($count >= $limiter) {
			break;
		}
		if($class->subtitle!="") {
			$title = $class->course->title."<br/>".$class->subtitle;
		} else {
			$title = $class->course->title;
		}
		?>
		<tr class="<?=($count%2==0)?'even':'odd';?>">
			<td class="calign"><?=$class->classid;?></td>
			<td class="calign"><a href="<?=Yii::app()->createUrl('search');?>?s=<?=$class->course->prefix." ".$class->course->num;?>"><?=$class->course->prefix." ".$class->course->num;?></a></td>
			<td><?=$title;?></td>
			<td class="calign"><?=$class->section;?></td>
			<td class=""><?=$class->year." ".$class->term;?></td>
			<td><?=$class->print_instructors();?></td>
			<td class="calign">
		    	<?php if(!is_null($syllabus) and $syllabus->valid()): ?>
			    <div class="admin-button ui-widget-header active download" title="Download Syllabus" classid="<?=$class->classid;?>">
			        <div class="icon"><?=StdLib::load_image("arrow_down","13px");?></div>
			    </div>
		        <?php elseif($class->website=="" and !Yii::app()->user->isGuest): ?>
			    <div class="admin-button ui-widget-header missing" title="Syllabus Missing">
			        <div class="icon"><?=StdLib::load_image("attention","13px");?></div>
			    </div>
		        <?php endif;?>
			    <?php if($class->website!=""): ?>
			    <div class="admin-button ui-widget-header active website" title="Open class website in a new tab">
			        <div class="icon"><?=StdLib::load_image("gowebsite","13px","13px");?></div>
			        <a href="<?=$class->website;?>"></a>
			    </div>
			    <?php endif; ?>
			    <div class="admin-button ui-widget-header active permalink" title="Permalink" classid="<?=$class->classid;?>">
			        <div class="icon"><?=StdLib::load_image("anchor","13px");?></div>
			    </div>
			    <?php 
		    	if(!Yii::app()->user->isGuest) {
		    		$user = Yii::app()->user->getState("_user");
					if($user->has_permission($class)):
				?>
			    <div class="admin-button ui-widget-header active edit" title="Edit Class" classid="<?=$class->classid;?>">
			        <div class="icon"><?=StdLib::load_image("pencil_edit.png","13px");?></div>
			    </div>
			    <?php
					endif;
		    	}
		    	?>
			</td>
		</tr>
		<?php
	}
}
else {
?>
	<tr>
		<td colspan="7" class="calign" style="padding:15px;font-size:14px;">Search yielded no results.</td>
	</tr>
<?php
}

$results_table = ob_get_contents();
ob_end_clean();

if(Yii::app()->user->isGuest) {
	$count = $count;
} else {
	$count = count($result);
}

?>
<div style="float:right;width:500px;text-align:right;">
	<form action="<?=Yii::app()->createUrl('search');?>" method="get">
		<input type="text" name="s" style="width:400px;" value="<?=@$search;?>" /> <button>Search</button>
	</form>
</div>
<h1 style="margin-bottom:10px;padding-top:10px;">Search Results for <span style="color:#09f;">"<?=$search;?>"</span></h1>
<div class="hint">Search yields <?=$count;?> results<?=(count($result) > @$limiter)?" but only showing top ".@$limiter." results":"";?>.</div>
<table id="courses" style="width:100%;">
	<thead>
		<tr>
			<th class="calign" width="50px">Class ID</th>
			<th class="calign active selected" id="prefix" width="100px">Course Prefix</th>
			<th class="active" id="title">Course Title</th>
			<th class="calign active" id="section" width="70px">Section</th>
			<th class="calign active" id="term" width="110px">Term</th>
			<th class="calign active" id="instructor" width="150px">Instructor</th>
			<th class="calign" width="70px">Action</th>
		</tr>
	</thead>
	<?=$results_table;?>
</table>

<style>
table#courses thead tr th.sorted {
	background-color:#fed;
}
</style>

<script>
var inverse = new Array();
jQuery(document).ready(function(){
	
	$("div.admin-button").hover(
		function() {
			$(this).stop().fadeTo("fast",0.7);
		},
		function() {
			$(this).stop().fadeTo("fast",1);
		}
	);
	
	$("div.admin-button").tipTip({
		defaultPosition: "top"
	});
	
	$(".website").live('click',function(){
		window.open($(this).find('a').attr('href'));
		return false;
	});
	
	$(".download").live('click',function(){
		window.location = '<?=Yii::app()->createUrl("_download");?>?cid='+$(this).attr("classid");
		return false;
	});
	
	$(".permalink").live('click',function(){
		window.location = '<?=Yii::app()->createUrl("permalink");?>?cid='+$(this).attr("classid");
		return false;
	});
	
	$(".edit").live('click',function(){
		window.open('<?=Yii::app()->createUrl("editclass");?>?cid='+$(this).attr("classid"));
		return false;
	});
	
	
	$('#courses th#prefix, #courses th#title, #courses th#term, #courses th#instructor')
    .each(function(){
		
        var th = $(this),
            thIndex = th.index();
		
		
        th.click(function(){
        	$(this).parent().find('th.selected').removeClass('selected');
			$(this).addClass('selected');
            $(this).parent().parent().parent().find('td').filter(function(){
                return $(this).index() === thIndex;
            }).sortElements(function(a, b){
                if( $.text([a]) == $.text([b]) )
                    return 0;
                return $.text([a]) > $.text([b]) ?
                      inverse[th.index()] ? -1 : 1
                    : inverse[th.index()] ? 1 : -1;
            }, function(){
                // parentNode is the element we want to move
                return this.parentNode; 

            });

            inverse[th.index()] = !inverse[th.index()];
            rowHighlight();
        });

    });
});

function rowHighlight() {
    $("table#courses tbody tr").removeClass("odd").removeClass("even").removeClass("selected");
    var $count = 0;
	$("table#courses").find('tbody tr:visible').each(function(){
		$count++;
		if($count%2==0) $(this).addClass("odd");
		else $(this).addClass("even");
	});
}
</script>