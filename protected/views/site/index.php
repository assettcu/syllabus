<?php
StdLib::Functions();

$flashes = new Flashes;
$flashes->render();

$courses = load_unique_courses();
?>

<div id="welcome-text-well" class="well well-sm">
    Welcome to the Syllabus Archive! You may search the archive for past syllabi or browse it by selecting one of the department buttons below.
</div>

<div class="browse-archive">
<div class="row">
    <div class="col-md-4">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Departments</h3>
            </div>
            <div id="departmentsListGroup" class="list-group">
                <?php foreach($courses as $prefix=>$data): ?>
                <a href="#" class="list-group-item">
                    <p class="list-group-item-heading" data-prefix="<?php echo $prefix; ?>">
                        <?php echo $data["department"] . " <span class='text-muted'>(" . $prefix . ")</span>"; ?>
                    </p>
                    <p class="list-group-item-text"><?php echo $data["numsyllabi"]. ngettext(' Syllabus', ' Syllabi', $data["numsyllabi"]); ?> from <?php echo $data["numinstructors"] . ngettext(' Instructor', ' Instructors', $data["numinstructors"]);?></p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Courses</h3>
            </div>
            <div id="courses-help-text" class="panel-body">
                <span class="text-muted">Please select a Department on the left to view its courses.</span>
            </div>
            <div id="coursesListGroup" class="list-group">
            </div>
        </div>
    </div>
</div>
</div> <!-- /browse-archive -->

<script>
jQuery(document).ready(function($){

    var prefix = getParameterByName("prefix");
    if(prefix) {
        selectDepartment($("#departmentsListGroup a p[data-prefix='" + prefix + "']").parent());
    }

    function getParameterByName(name) {
        var match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
        return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
    }

    function pluralizeSyllabus(num) {
        if(num > 1)
            return num + " Syllabi";
        else
            return num + " Syllabus";
    }

    $("#departmentsListGroup a").click(function(){
        selectDepartment($(this));
        return false;
    });

    function selectDepartment(element) {
        $("#departmentsListGroup a").removeClass("active");
        $(element).addClass("active");
        var prefix = $(element).children(".list-group-item-heading").attr("data-prefix");
        $.getJSON("ajax/LoadCourses", { prefix: prefix}, function(data) {
            $('#coursesListGroup').html("");
            $('#courses-help-text').hide();
            $.each(data, function(i, course) {
                var courseRow = $('<a>').addClass('list-group-item').prop("href", "course?prefix=" + course.prefix + "&num=" + course.num)
                .append("<b>" + course.prefix + " " + course.num + "</b> - " + course.title + 
                    "<small class='pull-right text-muted'>" + pluralizeSyllabus(course.num_syllabi) + "</small>");
                $('#coursesListGroup').append(courseRow);
            });
        });

        return false;
    }
});
</script>
