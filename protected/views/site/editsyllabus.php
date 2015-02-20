<?php
StdLib::Functions();
Flashes::render();
?>
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap.css" />
<script type="text/javascript" src="<?php echo WEB_LIBRARY_PATH; ?>jquery/modules/bootstrap/bootstrap.min.js"></script>

<div class="well" style="background-color:inherit;">
    <form class="form-horizontal" id="edit-syllabus-form" method="post" enctype="multipart/form-data">
      <input type="hidden" value="<?php echo make_unique_form_id(); ?>" name="uniqueformid" id="uniqueformid" />
      <input type="hidden" value="<?php echo date("Y-m-d H:i:s"); ?>" name="datetime" id="datetime" />
      <input type="hidden" name="id" id="id" value="<?php echo @$_REQUEST["id"]; ?>" />
      <fieldset>
        <legend>Edit Course Syllabus</legend>
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
        <div class="pull-right">
            <a href="#" class="btn btn-primary btn-xs btn-danger" id="reset-form"><span class="icon icon-wand"></span> Clear the Form</a>
        </div>
        <br class="clear" />
        <div class="form-group" id="form-group-0">
          <label for="inputPrefix" class="col-lg-2 control-label">Course ID</label>
          <div class="col-lg-10">
            <input type="text" class="form-control" disabled value="<?php echo @$_GET["id"]; ?>" style="width:210px;display:inline-block;">
          </div>
        </div>
        <div class="form-group" id="form-group-1">
          <label for="inputPrefix" class="col-lg-2 control-label">Course Information</label>
          <div class="col-lg-10">
            <input type="text" class="form-control" name="prefix" id="prefix" placeholder="Course Prefix" maxlength="4" disabled value="<?php echo @$CS->prefix; ?>" style="width:130px;display:inline-block;">
            <input type="text" class="form-control" name="num" id="num" placeholder="Course Number" maxlength="4" disabled value="<?php echo @$CS->num; ?>" style="width:140px;display:inline-block;">
            <input type="text" class="form-control" name="title" id="title" placeholder="Course Title" value="<?php echo @$CS->title; ?>" style="width:450px;display:inline-block;">
            <div class="help-block error-block text-danger" style="display:none;"></div>
          </div>
        </div>
        <div class="form-group" id="form-group-2">
          <label for="inputPrefix" class="col-lg-2 control-label">Class Information</label>
          <div class="col-lg-10">
            <input type="text" class="form-control" name="section" id="section" placeholder="Class Section" value="<?php echo @$CS->section; ?>" style="margin-bottom:3px;width:150px;display:inline-block;">
            <input type="text" class="form-control" name="special_topics_title" id="special_topics_title" placeholder="Special Topics Title (Optional)" value="<?php echo @$CS->special_topics_title; ?>" style="width:450px;display:inline-block;">
            <span class="help-block error-block text-danger" style="display:none;"></span>
          </div>
        </div>
        <div class="form-group" id="form-group-3">
          <label for="term" class="col-lg-2 control-label">Term &amp; Year</label>
          <div class="col-lg-10">
            <select class="form-control" name="term" id="term" disabled style="width:200px;display:inline-block;">
              <option value="1" <?php if(@$CS->term == "Spring") { echo "selected='selected'"; } ?>>Spring</option>
              <option value="4" <?php if(@$CS->term == "Summer") { echo "selected='selected'"; } ?>>Summer</option>
              <option value="7" <?php if(@$CS->term == "Fall") { echo "selected='selected'"; } ?>>Fall</option>
            </select>
            <input type="text" class="form-control" name="year" id="year" placeholder="ex. 2015" maxlength="4" disabled value="<?php echo @$CS->year; ?>" style="width:140px;display:inline-block;">
            <div class="help-block error-block text-danger" style="display:none;"></div>
          </div>
        </div>
        <div class="form-group" id="form-group-4">
          <label for="inputPrefix" class="col-lg-2 control-label">Recitation</label>
          <div class="col-lg-10">
              <div class="radio">
                  <label>
                      <input type="radio" name="recitation" id="recitation-yes" value="yes" <?php if(@$CS->recitation == "yes") { echo "checked"; } ?> > Yes, this class is a recitation.
                  </label>
              </div>
              <div class="radio">
                  <label>
                      <input type="radio" name="recitation" id="recitation-no" value="no" <?php if(@$CS->recitation == "no") { echo "checked"; } ?>> No, this class is not a recitation.
                  </label>
              </div>
          </div>
        </div>
        <div class="form-group" id="form-group-5">
          <label for="inputPrefix" class="col-lg-2 control-label">Restrict to On-Campus</label>
          <div class="col-lg-10">
              <div class="radio">
                  <label>
                      <input type="radio" name="restricted" id="restricted-yes" value="yes"  <?php if(@$CS->restricted == "yes") { echo "checked"; } ?>> Yes
                  </label>
              </div>
              <div class="radio">
                  <label>
                      <input type="radio" name="restricted" id="restricted-no" value="no"  <?php if(@$CS->restricted == "no") { echo "checked"; } ?>> No
                  </label>
              </div>
              <div class="help-block error-block">Restrict syllabus to be viewable only by computers on the campus network.</div>
          </div>
        </div>
        <div class="form-group" id="form-group-6">
          <label for="inputPrefix" class="col-lg-2 control-label">Instructor Name(s)</label>
          <div class="col-lg-10">
              <textarea class="form-control" rows="3" name="instructors" id="instructors" style="width:450px;"><?php echo @$CS->editable_instructors(); ?></textarea>
              <span class="help-block">Separate instructors by new line.</span>
              <span class="help-block text-danger hide"></span>
          </div>
        </div>
        <div class="form-group" id="form-group-7">
            <label for="syllabus" class="col-lg-2 control-label">Syllabus</label>
            <div class="col-lg-10">
                <input type="file" name="syllabus" id="syllabus" accept="application/msword,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                <div class="help-block error-block">
                    Current Syllabus: <br/>
                    <?php 
                    if($CS->has_syllabus_file()) {
                        $syllabi = array();
                        foreach($CS->syllabus_links as $extension => $link) {
                            if(!is_null($link)) {
                                $syllabi[] = "<a href='".$link."' target='_blank'>".$CS->id.".".$extension."</a>";
                            }
                        }
                        echo implode("<br/> ",$syllabi);
                    }
                    else {
                        echo "<i>&lt;none&gt;</i>";
                    }
                    ?>
                </div>
                <div class="help-block error-block text-danger" style="display:none;"></div>
            </div>
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
              <div class="help-block error-block"><i>*Not available yet*</i> Running TextOverlay on a syllabus PDF will make it text searchable and more accessible for screen readers.</div>
          </div>
        </div>
        <div class="form-group" id="form-group-8">
          <div class="col-lg-10 col-lg-offset-2">
            <input type="hidden" name="savetype" id="savetype" value="continue" />
            <button type="reset" id="cancel" class="btn btn-default">Cancel</button>
            <button type="submit" class="btn btn-danger delete-button">Delete</button>
            <button type="submit" class="btn btn-primary save-button" save-type="exit">Save and Exit</button>
            <button type="submit" class="btn btn-primary save-button" save-type="continue">Save and Go Back</button>
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

<div class="modal" id="delete-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title">Delete this Course Syllabus</h4>
      </div>
      <div class="modal-body">
        <p>Deleting this Course Syllabus is not undoable. Continue with deletion?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="delete-confirm">Delete Syllabus</button>
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
    
    $(document).on("click","button.delete-button",function(){
       $(".modal").modal("show");
       return false;
    });
    
    $(document).on("click","#delete-confirm",function(){
        $.ajax({
            "url":      "<?php echo Yii::app()->createUrl('ajax/deleteCourseSyllabus'); ?>",
            "data":     "id=<?php echo $_GET["id"]; ?>",
            "success":  function(response) {
                window.location = "<?php echo Yii::app()->createUrl('course')."?prefix=".$CS->prefix."&num=".$CS->num; ?>";
                return false;
            }
        });
    });
    
    $(document).on("click","#cancel",function(){
        window.history.back();
        return false;
    });
    
    $(document).on("click","#reset-form",function(){
        $("#edit-syllabus-form")[0].reset();
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
    
    $(document).on("click","button.save-button",function(){
        $("#savetype").val($(this).attr("save-type"));
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
        
        $('#edit-syllabus-form input, #edit-syllabus-form select, #edit-syllabus-form textarea').each(function() {
            if($(this).val().length == 0 && ($(this).attr("name") == "title" || $(this).attr("name") == "section" || $(this).attr("name") == "instructors")) {
                has_errors = true;
            }
        });
        
        if(has_errors) {
            show_window_alert("warning", "Some required fields were left empty. All fields are required! <i>Except Special Topics Title</i>");
            return false;
        }
        
        $("#edit-syllabus-form button").attr("disabled",true);
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
    $("html, body").animate({ scrollTop: 0 }, "slow");
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
        $("html, body").animate({ scrollTop: 0 }, "slow");
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
