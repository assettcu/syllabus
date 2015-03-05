<?php
StdLib::Functions();
Flashes::render();
?>

<!-- Load Queue widget CSS -->
<style type="text/css">@import url(<?php echo WEB_LIBRARY_PATH; ?>jquery/modules/plupload/js/jquery.plupload.queue/css/jquery.plupload.queue.css);</style>
<style>.plupload_start { display:none; }</style>

<!-- Load plupload and all it's runtimes and finally the jQuery queue widget -->
<script type="text/javascript" src="<?php echo WEB_LIBRARY_PATH; ?>jquery/modules/plupload/js/plupload.full.js"></script>
<script type="text/javascript" src="<?php echo WEB_LIBRARY_PATH; ?>jquery/modules/plupload/js/jquery.plupload.queue/jquery.plupload.queue.js"></script>

<div class="well" style="background-color:inherit;">
    <form class="form-horizontal" id="add-syllabus-form" method="post" enctype="multipart/form-data">
      <input type="hidden" value="<?php echo make_unique_form_id(); ?>" name="uniqueformid" id="uniqueformid" />
      <input type="hidden" value="<?php echo date("Y-m-d H:i:s"); ?>" name="datetime" id="datetime" />
      <input type="hidden" value="false" name="overwrite" id="overwrite" />
      <fieldset>
        <legend>Add Course Syllabus</legend>
        <div class="pull-right">
            <a href="#" class="btn btn-primary btn-xs btn-danger" id="reset-form"><span class="icon icon-wand"></span> Clear the Form</a>
        </div>
        <span class="help-block">All fields are required, except for the Special Topics Title.</span>
        <div class="bs-component" id="alert-field">
            <div class="alert dismissible alert-warning" style="display:none;">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <p></p>
            </div>
            <div class="alert dismissible alert-success" style="display:none;">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <p></p>
            </div>
            <div class="alert dismissible alert-danger" style="display:none;">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <p></p>
            </div>
        </div>
        <div class="form-group" id="form-group-1">
          <label for="inputPrefix" class="col-lg-2 control-label">Course Information</label>
          <div class="col-lg-10">
            <input type="text" class="form-control" name="prefix" id="prefix" placeholder="Course Prefix" maxlength="4" value="<?php echo @$_POST["prefix"]; ?>" style="width:130px;display:inline-block;">
            <input type="text" class="form-control" name="num" id="num" placeholder="Course Number" maxlength="4" value="<?php echo @$_POST["num"]; ?>" style="width:140px;display:inline-block;">
            <input type="text" class="form-control" name="title" id="title" placeholder="Course Title" value="<?php echo @$_POST["title"]; ?>" style="width:450px;display:inline-block;">
            <div class="help-block error-block text-danger" style="display:none;"></div>
          </div>
        </div>
        <div class="form-group" id="form-group-2">
          <label for="inputPrefix" class="col-lg-2 control-label">Class Information</label>
          <div class="col-lg-10">
            <input type="text" class="form-control" name="section" id="section" placeholder="Class Section" value="<?php echo @$_POST["section"]; ?>" style="margin-bottom:3px;width:150px;display:inline-block;">
            <input type="text" class="form-control" name="special_topics_title" id="special_topics_title" placeholder="Special Topics Title (Optional)" value="<?php echo @$_POST["special_topics_title"]; ?>" style="width:450px;display:inline-block;">
            <span class="help-block error-block text-danger" style="display:none;"></span>
          </div>
        </div>
        <div class="form-group" id="form-group-3">
          <label for="term" class="col-lg-2 control-label">Term &amp; Year</label>
          <div class="col-lg-10">
            <select class="form-control" name="term" id="term" style="width:200px;display:inline-block;">
              <option value="1" <?php if(@$_POST["term"] == "Spring") { echo "selected='selected'"; } ?>>Spring</option>
              <option value="4" <?php if(@$_POST["term"] == "Summer") { echo "selected='selected'"; } ?>>Summer</option>
              <option value="7" <?php if(@$_POST["term"] == "Fall") { echo "selected='selected'"; } ?>>Fall</option>
            </select>
            <input type="text" class="form-control" name="year" id="year" placeholder="ex. 2015" maxlength="4" value="<?php echo @$_POST["year"]; ?>" style="width:140px;display:inline-block;">
            <div class="help-block error-block text-danger" style="display:none;"></div>
          </div>
        </div>
        <div class="form-group" id="form-group-4">
          <label for="inputPrefix" class="col-lg-2 control-label">Recitation</label>
          <div class="col-lg-10">
              <div class="radio">
                  <label>
                      <input type="radio" name="recitation" id="recitation-yes" value="yes" <?php if(@$_POST["recitation"] == "yes") { echo "checked"; } ?> > Yes, this class is a recitation.
                  </label>
              </div>
              <div class="radio">
                  <label>
                      <input type="radio" name="recitation" id="recitation-no" value="no" <?php if(!isset($_POST["recitation"]) or $_POST["recitation"] == "no") { echo "checked"; } ?>> No, this class is not a recitation.
                  </label>
              </div>
          </div>
        </div>
        <div class="form-group" id="form-group-5">
          <label for="inputPrefix" class="col-lg-2 control-label">Restrict to On-Campus</label>
          <div class="col-lg-10">
              <div class="radio">
                  <label>
                      <input type="radio" name="restricted" id="restricted-yes" value="yes"  <?php if(@$_POST["restricted"] == "yes") { echo "checked"; } ?>> Yes
                  </label>
              </div>
              <div class="radio">
                  <label>
                      <input type="radio" name="restricted" id="restricted-no" value="no"  <?php if(!isset($_POST["restricted"]) or $_POST["restricted"] == "no") { echo "checked"; } ?>> No
                  </label>
              </div>
              <div class="help-block error-block">Restrict syllabus to be viewable only by computers on the campus network.</div>
          </div>
        </div>
        <div class="form-group" id="form-group-6">
          <label for="inputPrefix" class="col-lg-2 control-label">Instructor Name(s)</label>
          <div class="col-lg-10">
              <textarea class="form-control" rows="3" name="instructors" id="instructors" style="width:450px;"><?php echo @$_POST["instructors"]; ?></textarea>
              <span class="help-block">Separate instructors by new line.</span>
              <span class="help-block text-danger hide"></span>
          </div>
        </div>
        <div class="form-group" id="form-group-7">
            <label for="syllabus" class="col-lg-2 control-label">Syllabus</label>
            <div class="col-lg-10 control-label">
                <input type="file" name="syllabus" id="syllabus" value="<?php echo @$_POST["syllabus"]; ?>" accept="application/msword,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
            </div>
            <div class="help-block error-block text-danger" style="display:none;"></div>
        </div>
        <div class="form-group" id="form-group-5">
          <label for="inputPrefix" class="col-lg-2 control-label">Run TextOverlay on Syllabus</label>
          <div class="col-lg-10">
              <div class="radio">
                  <label>
                      <input type="radio" name="ocr" id="ocr-yes" value="yes" disabled  <?php if(!isset($_POST["ocr"]) or $_POST["ocr"] == "no") { echo "checked"; } ?>> Yes
                  </label>
              </div>
              <div class="radio">
                  <label>
                      <input type="radio" name="ocr" id="ocr-no" value="no" disabled <?php if(@$_POST["restriction"] == "yes") { echo "checked"; } ?> checked> No
                  </label>
              </div>
              <div class="help-block error-block"><i>*Not available yet*</i> Running TextOverlay on the syllabus will make it text searchable and more accessible for screen readers.</div>
          </div>
        </div>
        <div class="form-group" id="form-group-8">
          <div class="col-lg-10 col-lg-offset-2">
            <input type="hidden" name="savetype" id="savetype" value="<?php echo (isset($_POST["savetype"])) ? $_POST["savetype"] : "exit"; ?>" />
            <button type="reset" class="btn btn-default">Cancel</button>
            <button type="submit" class="btn btn-primary save-button" save-type="exit">Save and Exit</button>
            <button type="submit" class="btn btn-primary save-button" save-type="continue">Save and Add Another</button>
          </div>
        </div>
        <div class="form-group" id="form-group-9" style="display:none;">
          <div class="col-lg-10 col-lg-offset-2">
             Submitting syllabus to archive...
            <div class="progress progress-striped active">
                <div class="progress-bar progress-bar-info" style="width: 100%"></div>
            </div>
          </div>
        </div>
      </fieldset>
    </form>
</div>

<div class="modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title">Duplicate Course Syllabus Found</h4>
      </div>
      <div class="modal-body">
        <p>This course syllabus will overwrite any existing course syllabus. Do you wish to overwrite?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="overwrite-existing-confirm">Overwrite Existing</button>
      </div>
    </div>
  </div>
</div>

<script>
var errors = {
    "Course"        : false,
    "Class"         : false,
    "Term"          : false,
    "Recitation"    : false,
    "Restriction"   : false,
    "Instructors"   : false,
    "Syllabus"      : false
}
var savetype;
$(".modal").modal({
    show: false
});
jQuery(document).ready(function($){
    
    $(document).on("click","#overwrite-existing-confirm",function(){
        $(".modal").modal('toggle');
        $("#overwrite").val("true");
        $("#add-syllabus-form").submit();
    });
    
    $(document).on("click","#reset-form",function(){
        $("#add-syllabus-form")[0].reset();
        hide_window_alerts();
        hide_form_alerts();
        return false;
    });
    
    $(document).on("click","button.close",function(){
        $(this).parent().hide("fade");   
    });
    
    $(document).on("blur","#form-group-1 input",function(){
        var patterns = {
            "prefix" : /[A-Z]{4}/,
            "num"    : /[0-9]{4}/,
        };
        var local_errors = false;
        var error_message = "";
        
        // Test Prefix first
        if(!patterns["prefix"].test($("#prefix").val()) && $("#prefix").val().length != 0) {
            local_errors = true;
            error_message = "Course Prefix must be four letters. Example: \"COMM\"";
        }
        // Test Course Number next
        else if(!patterns["num"].test($("#num").val()) && $("#num").val().length != 0) {
            local_errors = true;
            error_message = "Course Number must be four numbers. Example: \"1300\"";
        }
        errors["Course"] = local_errors;
        show_form_error("form-group-1",error_message,local_errors);
    });
    
    $(document).on("blur","#form-group-2 input",function(){
        var patterns = {
            "section" : /([0-9]{3},?)+/,
        };
        var local_errors = false;
        var error_message = "";
        
        // Test Prefix first
        if(!patterns["section"].test($("#section").val()) && $("#section").val().length != 0) {
            local_errors = true;
            error_message = "Class sections are three numbers long, separated by commas. Example: \"001,003\"";
        }
        errors["Class"] = local_errors;
        show_form_error("form-group-2",error_message,local_errors);
    });
    
    $(document).on("blur","#form-group-3 input",function(){
        var patterns = {
            "year" : /((21|20|19)[0-9]{2})/,
        };
        var local_errors = false;
        var error_message = "";
        
        // Test Prefix first
        if(!patterns["year"].test($("#year").val()) && $("#year").val().length != 0) {
            local_errors = true;
            error_message = "Year must be 4 digits and be within a reasonable amount of time. No 19th century syllabi allowed.";
        }
        errors["Term"] = local_errors;
        show_form_error("form-group-3",error_message,local_errors);
    });
    
    $(document).on("click","button",function(){
        $("#savetype").val($(this).attr("save-type"));
        $("#add-syllabus-form").submit();
    });
    
    $(document).on("submit",function(){
        var has_errors = false;
        hide_window_alerts();
        
        // Loop through each area and see if there are any form errors
        for(var key in errors) {
            if(errors[key]) {
                has_errors = true;
            }
        }
        // If there are any form syntax errors
        if(has_errors) {
            show_window_alert("warning", "There were errors processing this form. Make sure you filled out all the fields correctly.");
            return false;
        }
        
        $('#add-syllabus-form input, #add-syllabus-form select, #add-syllabus-form textarea').each(function() {
            if($(this).val().length == 0 && $(this).attr("name") != "special_topics_title") {
                has_errors = true;
            }
        });
        
        if(has_errors) {
            show_window_alert("warning", "Some required fields were left empty. All fields are required! <i>Except Special Topics Title</i>");
            return false;
        }
        
        if($("#overwrite").val() == "false") {
            var overwrite_necessary = false;
            $.ajax({
                "url":  "<?php echo Yii::app()->createUrl('ajax/CourseSyllabusExists'); ?>",
                "data": $("#add-syllabus-form").serialize(),
                "type": "post",
                "dataType": "JSON",
                "async": false,
                "success": function(response) {
                    // Response will be an array with each section if it exists
                    // Example { "001" : false, "002" : true, "003" : false }
                    for(var section in response) {
                        overwrite_necessary = response[section] | overwrite_necessary;
                    }
                }
            });
            if(overwrite_necessary) {
                $(".modal").modal("show");
                return false;
            }
        }
        
        $("#add-syllabus-form button").attr("disabled",true);
        $("#form-group-9").show("fade");
        return true;
    });
});

function hide_window_alerts() {
    $("#alert-field .alert").hide();
}

function hide_form_alerts() {
    $(".form-group").removeClass("has-error");
    $(".form-group .error-block").text("").hide();
}

function show_window_alert(type,message) {
    $("#alert-field .alert").hide();
    if(type == "warning") {
        $("#alert-field .alert-warning p").html("<span class=\"icon icon-warning\"> </span> "+message);
        $("#alert-field .alert-warning").show("fade");
    }
    else if(type == "success") {
        $("#alert-field .alert-success p").html("<span class=\"icon icon-checkmark\"> </span> "+message);
        $("#alert-field .alert-success").show("fade");
    }
    else if(type == "error") {
        $("#alert-field .alert-danger p").html("<span class=\"icon icon-spam\"> </span> "+message);
        $("#alert-field .alert-danger").show("fade");
    }
}

function show_form_error(target,message,has_error) {
    if(has_error) {
        $("#"+target).addClass("has-error");
        $("#"+target+" .error-block").text(message);
        $("#"+target+" .error-block:hidden").show('clip');
    }
    else {
        $("#"+target).removeClass("has-error");
        $("#"+target+" .error-block").text("");
        $("#"+target+" .error-block:visible").hide('clip');
    }
}
</script>
