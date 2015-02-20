<?php
StdLib::Functions();
$courses = load_courses($prefix);
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
</style>

<ul class="breadcrumb">
    <li><a href="<?php echo Yii::app()->homeUrl; ?>">Home</a></li>
    <li><a href="<?php echo Yii::app()->createUrl('course');?>?prefix=<?php echo $prefix; ?>">[<?php echo $prefix; ?>] Explore Course Archive</a></li>
</ul>

<h1>[<?php echo $prefix; ?>] Explore Course Archive</h1>

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