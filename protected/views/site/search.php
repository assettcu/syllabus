<?php

function sort_by_weight($a,$b){
	if(!isset($a->weight,$b->weight)) return 0;
	if($a->weight == $b->weight) {
		if($a->prefix == $b->prefix) {
			if($a->num == $b->num) {
				if($a->year == $b->year) {
					if($a->term == $b->term) {
						return ($a->section < $b->section) ? 1 : -1;
					}
					return ($a->term < $b->term) ? -1 : 1;
				}
				return ($a->year < $b->year) ? 1 : -1;
			}
			return ($a->num < $b->num) ? -1 : 1;
		}
		return ($a->prefix > $b->prefix) ? 1 : -1;
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
		{{course_syllabi}}.section = :term_".$count." OR
		{{course_syllabi}}.prefix = :term_".$count." OR
		{{course_syllabi}}.num = :term_".$count." OR
		{{course_syllabi}}.title LIKE :term2_".$count." OR
		{{course_instructors}}.fullname LIKE :term2_".$count." OR
		{{course_syllabi}}.term = :term_".$count." OR
		{{course_syllabi}}.year = :term_".$count."
	)";
}

$where[] = "({{course_syllabi}}.id = {{course_instructors}}.courseid)";

if(empty($where)) {
	$where = "WHERE 1=0";
} else {
	$where = "WHERE ".implode(" AND ",$where);
}

$conn = Yii::app()->db;
$query = "
	SELECT 		{{course_syllabi}}.id
	FROM		{{course_syllabi}}, {{course_instructors}}
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
	$class = new CourseSyllabusObj($row["id"]);
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
		
		if($class->prefix == $term) {
			$class->weight += ($term_weight * 10);
		}
		if($class->num == $term) {
			$class->weight += ($term_weight * 10);
		}
		if(preg_match("/".$term."/i",$class->title)) {
			$class->weight += ($term_weight * 5);
		}
		if($class->section == $term) {
			$class->weight += ($term_weight * 7);
		}
		$instructors = $class->get_instructors();
		foreach($instructors as $instructor) {
			if(preg_match("/".$term."/i",$instructor["fullname"])) {
				$class->weight += ($term_weight * 8);
			}
		}
		if($class->term == $term) {
			$class->weight += ($term_weight * 15);
		}
		if($class->year == $term) {
			$class->weight += ($term_weight * 15);
		}
	}
	if($class->weight >= 5) {
		$classes[$class->id] = $class;
	}
}

$total = $time-microtime(true);

usort($classes,"sort_by_weight");

ob_start();

$count=0;
if(!empty($classes)) {
	$limiter = 100;
	foreach($classes as $class) {
		$count++;
		if($count >= $limiter) {
			break;
		}
		if($class->special_topics_title!="") {
			$title = $class->title."<br/>".$class->special_topics_title;
		} else {
			$title = $class->title;
		}
		?>
		<tr>
			<td>
			    <a href="<?php echo Yii::app()->createUrl('course'); ?>?prefix=<?php echo $class->prefix; ?>&num=<?php echo $class->num; ?>">
			        <?php echo $class->prefix." ".$class->num." - ".$class->title; ?>
			    </a>
			</td>
			<td class="calign"><?=$class->section;?></td>
			<td class=""><?=$class->year." ".$class->term;?></td>
			<td><?=$class->print_instructors();?></td>
			<td>
			    <?php 
			    $class->find_syllabus_links();
                foreach($class->syllabus_links as $extension => $link):
                    if(is_null($link)) continue;
                ?>
                <a class="doc-link" href="<?php echo $link; ?>">
                    <?php if($extension === "docx" || $extension === "doc"): ?>
                        <i class="fa fa-file-word-o"></i>
                    <?php elseif ($extension === "pdf"): ?>
                        <i class="fa fa-file-pdf-o"></i>
                    <?php endif; ?><?php echo $extension; ?>
                </a>
                <?php
                endforeach;
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

<h2 style="margin-bottom:10px;padding-top:10px;">Search Results for <span style="color:#09f;">"<?=$search;?>"</span></h2>
<div class="text-primary pull-right">Search yields <?=count($result);?> results<?=(count($result) > @$limiter)?" but only showing top ".@$limiter." results":"";?>.</div>
<table class="table table-striped table-hover" style="width:100%;">
	<thead>
		<tr class="active">
			<th id="prefix">Course</th>
			<th class="calign" id="section" width="120px">Section</th>
			<th id="term" width="140px">Term</th>
			<th id="instructor" width="250px">Instructor</th>
			<th width="70px">Action</th>
		</tr>
	</thead>
	<?=$results_table;?>
</table>
