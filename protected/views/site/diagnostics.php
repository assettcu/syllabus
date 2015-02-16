<?php

$archivedir = 'C:/archive';
$group = new GroupObj(new ClassObj);

$tablecontents = "";
$missing = 0;
$syllabi_count = 0;
if ($handle = opendir($archivedir)) {
    while (false !== ($entry = readdir($handle))) {
        if(is_file($archivedir.'/'.$entry)) {
            continue;
        }
        if ($entry != "." && $entry != "..") {
            $syllabi_count += count(scandir($archivedir.'/'.$entry))-2; # Remove '.' and '..' from results
        }
    }
    closedir($handle);
}

$count=0; foreach($group->classes as $class):

if(!$class->has_syllabus()) $missing++;
else continue;
 $count++;
ob_start();
?>
<tr class="<?=($count%2==0)?"odd":"even";?>">
	<td><?=$class->course->prefix." ".$class->course->num;?></td>
	<td><?=$class->course->title;?></td>
	<td><?=$class->section;?></td>
	<td><?=$class->term." ".$class->year;?></td>
	<td><?=($class->has_syllabus())?"yes":"no";?></td>
</tr>
<?php
$tablecontents .= ob_get_contents();
ob_end_clean();

endforeach;
?>

<style>
table tr.odd td {
	background-color:#edf;
}
</style>

<table width="300px">
	<tr>
		<td>Number of Database Syllabi</td>
		<td><?=count($group->classes);?></td>
	</tr>
	<tr>
		<td>Number of Physical Syllabi</td>
		<td><?=$syllabi_count;?></td>
	</tr>
	<tr>
		<td>Number of Missing Syllabi</td>
		<td><?=$missing;?></td>
	</tr>
</table>

<table>
	<thead>
		<tr>
			<th>Course Num</th>
			<th>Course Name</th>
			<th>Class Sect</th>
			<th>Class Term</th>
			<th>Exists</th>
		</tr>
	</thead>
	<tbody>
		<?=$tablecontents;?>
	</tbody>
</table>
