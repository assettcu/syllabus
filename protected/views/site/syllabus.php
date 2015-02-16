<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap.css" />

<!-- Load Queue widget CSS -->
<style type="text/css">@import url(<?php echo WEB_LIBRARY_PATH; ?>jquery/modules/plupload/js/jquery.plupload.queue/css/jquery.plupload.queue.css);</style>
<style>.plupload_start { display:none; }</style>

<!-- Load plupload and all it's runtimes and finally the jQuery queue widget -->
<script type="text/javascript" src="<?php echo WEB_LIBRARY_PATH; ?>jquery/modules/plupload/js/plupload.full.js"></script>
<script type="text/javascript" src="<?php echo WEB_LIBRARY_PATH; ?>jquery/modules/plupload/js/jquery.plupload.queue/jquery.plupload.queue.js"></script>

<div class="well" style="background-color:inherit;">
    <form class="form-horizontal" id="add-syllabus-form">
      <fieldset>
        <legend>Add New Syllabus</legend>
        <div class="pull-right">
            <a href="#" class="btn btn-primary btn-xs btn-danger" id="reset-form"><span class="icon icon-wand"></span> Clear the Form</a>
        </div>
        <span class="help-block">All fields are required, except for the Special Topics Title.</span>
        <div class="form-group">
          <label for="inputPrefix" class="col-lg-2 control-label">Course Information</label>
          <div class="col-lg-10">
            <input type="text" class="form-control" id="prefix" placeholder="Course Prefix" maxlength="4" style="width:130px;display:inline-block;">
            <input type="text" class="form-control" id="num" placeholder="Course Number" maxlength="4" style="width:140px;display:inline-block;">
            <input type="text" class="form-control" id="title" placeholder="Course Title" style="width:450px;display:inline-block;">
            <span class="help-block text-danger hide"></span>
          </div>
        </div>
        <div class="form-group">
          <label for="inputPrefix" class="col-lg-2 control-label">Class Information</label>
          <div class="col-lg-10">
            <input type="text" class="form-control" id="section" placeholder="Class Section" style="margin-bottom:3px;width:150px;display:inline-block;">
            <input type="text" class="form-control" id="inputEmail" placeholder="Special Topics Title (Optional)" style="width:450px;display:inline-block;">
            <span class="help-block text-danger invisible"></span>
          </div>
        </div>
        <div class="form-group">
          <label for="term" class="col-lg-2 control-label">Term &amp; Year</label>
          <div class="col-lg-10">
            <select class="form-control" id="term" style="width:200px;display:inline-block;">
              <option value="1">Spring</option>
              <option value="4">Summer</option>
              <option value="7">Fall</option>
            </select>
            <input type="text" class="form-control" id="year" placeholder="ex. 2015" maxlength="4" style="width:140px;display:inline-block;">
            <span class="help-block text-danger hide"></span>
          </div>
        </div>
        <div class="form-group">
          <label for="inputPrefix" class="col-lg-2 control-label">Recitation</label>
          <div class="col-lg-10">
              <div class="radio">
                  <label>
                      <input type="radio" name="recitation" id="recitation-no" value="no" checked> No, this class is not a recitation.
                  </label>
              </div>
              <div class="radio">
                  <label>
                      <input type="radio" name="recitation" id="recitation-yes" value="yes" > Yes, this class is a recitation.
                  </label>
              </div>
          </div>
        </div>
        <div class="form-group">
          <label for="inputPrefix" class="col-lg-2 control-label">Restrict to On-Campus</label>
          <div class="col-lg-10">
              <div class="radio">
                  <label>
                      <input type="radio" name="restriction" id="restriction-no" value="no" checked> No, do not restrict this syllabus.
                  </label>
              </div>
              <div class="radio">
                  <label>
                      <input type="radio" name="restriction" id="restriction-yes" value="yes" > Yes, restrict this syllabus to be viewable by on-campus computers only.
                  </label>
              </div>
          </div>
        </div>
        <div class="form-group">
          <label for="inputPrefix" class="col-lg-2 control-label">Instructor Name(s)</label>
          <div class="col-lg-10">
              <textarea class="form-control" rows="3" id="instructors" style="width:450px;"></textarea>
              <span class="help-block">Separate instructors by new line.</span>
              <span class="help-block text-danger hide"></span>
          </div>
        </div>
        <div class="form-group">
            <label for="syllabus" class="col-lg-2 control-label">Syllabus</label>
          <div class="col-lg-10 control-label">
              <input type="file" accept="application/msword,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
          </div>
        </div>
        <div class="form-group">
          <div class="col-lg-10 col-lg-offset-2">
            <button type="reset" class="btn btn-default">Cancel</button>
            <button type="submit" class="btn btn-primary">Save and Exit</button>
            <button type="submit" class="btn btn-primary">Save and Add Another</button>
          </div>
        </div>
      </fieldset>
    </form>
</div>

<script>
jQuery(document).ready(function($){
    $(document).on("click","#reset-form",function(){
        $("#add-syllabus-form")[0].reset();
        return false;
    });
});
</script>
