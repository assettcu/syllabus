<?php
StdLib::Functions();
$courses = load_courses($prefix);
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
</style>

<div class="breadcrumbs">
    <a href="<?php echo Yii::app()->homeUrl; ?>">Home</a> &gt;
    <a href="<?php echo Yii::app()->createUrl('course');?>?prefix=<?php echo $prefix; ?>">Explore Course Archive [<?php echo $prefix; ?>]</a>
</div>

<h1>Explore Course Archive [<?php echo $prefix; ?>]</h1>

<table>
    <thead>
        <tr>
            <th class="lalign">Course</th>
            <th width="120px" class="calign"># Syllabi</th>
            <th width="120px" class="calign">Years</th>
        </tr>
    </thead>
    <tbody>
        <?php $count=0; foreach($courses as $course): $count++; ?>
        <tr <?php if($count%2==0): ?>class="odd"<?php endif; ?>>
            <td class="lalign">
                <a href="<?php echo Yii::app()->createUrl('course');?>?prefix=<?php echo $course->prefix; ?>&num=<?php echo $course->num; ?>">
                [<?php echo $course->prefix; ?> <?php echo $course->num; ?>] <?php echo $course->title; ?>
                </a>
            </td>
            <td class="calign"><?php echo $course->num_syllabi(); ?></td>
            <td class="calign"><?php echo $course->print_span_years(); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>