<?php
if(isset($_POST) and !empty($_POST)) {
	$course = new CourseObj();
	$course->prefix = $_POST["prefix"];
	$course->num = $_POST["num"];
	$course->load();
	if(!Yii::app()->user->getState("_user")->has_permission($course)) {
		Yii::app()->user->setFlash("error","You do not have permissions to add classes to this course.");
		goto ENDOFSAVE;
	}
	if(!$course->loaded) {
		if(!isset($_POST["title"])) {
			Yii::app()->user->setFlash("error","Course does not exist.");
			goto ENDOFSAVE;
		} else {
			$course->title = $_POST["title"];
			if(!$course->save()) {
				Yii::app()->user->setFlash("error",$course->get_error());
				goto ENDOFSAVE;
			}
		}
		$course->load();
		if(!$course->loaded) {
			Yii::app()->user->setFlash("error","Unknown error loading course");
			goto ENDOFSAVE;
		}
	}
	
	$class->courseid = $course->courseid;
	$class->instructors = $_POST["instructors"];
	$class->section = $_POST["section"];
	$class->term = $_POST["term"];
	$class->year = $_POST["year"];
	$class->metadata = $_POST["metadata"];
	$class->subtitle = $_POST["subtitle"];
	$class->website = $_POST["website"];
	if(isset($_POST["allowed"])) {
		$class->offcampus = 1;
	} else {
		$class->offcampus = 0;
	}
	if(!$class->save()) {
		Yii::app()->user->setFlash("error",$class->get_error());
		goto ENDOFSAVE;
	}
	
	if(isset($_FILES["syllabus"]) and $_FILES["syllabus"]["error"] != 4){
		$syllabus = new SyllabusObj();
		$syllabus->classid = $class->classid;
		if(!$syllabus->upload_file($_FILES["syllabus"])){
			Yii::app()->user->setFlash("error",$syllabus->get_error());
			goto ENDOFSAVE;
		}
		else if(!$syllabus->save()) {
			Yii::app()->user->setFlash("error",$syllabus->get_error());
			goto ENDOFSAVE;
		} else {
			$class->flag = "";
			$class->save();
		}
	}
	
	if(isset($_POST["primary"])) {
		foreach($class->syllabi as $syllabus) {
			$syllabus->primary = ($_POST["primary"]==$syllabus->syllabusid) ? 1 : 0;
			if($syllabus->primary == 1) {
				Yii::app()->user->setFlash("warning",var_dump($syllabus));
			}
			if(!$syllabus->save()) {
				Yii::app()->user->setFlash("error",$syllabus->get_error());
				goto ENDOFSAVE;
			} 
		}
	}
	
	$syslog = new SyslogObj();
	if($edit) {
		$syslog->action = "Successfully edited class.";
	} else {
		$syslog->action = "Successfully added class";
	}
	$syslog->notes = "Class ID: ".$class->classid;
	$syslog->save();
	
	Yii::app()->user->setFlash("success","Class has been successfully saved!");
	$this->redirect(Yii::app()->createUrl('permalink')."?cid=".$class->classid);
	exit;
	
	# If we're not supposed to use goto statements, why does PHP have them?
	ENDOFSAVE:
}
if(isset($_GET["prefix"],$_GET["num"]) and !isset($class->course)) {
	$class->course = new CourseObj();
	$class->course->prefix = $_GET["prefix"];
	$class->course->num = $_GET["num"];
	$class->course->load();
}
$flashes = new Flashes;
$flashes->render();

?>

<div class="title"><?=$title;?></div>

<form method="post" name="edit_user_form" enctype="multipart/form-data">
	<table id="class-form" style="width:100%;">
		<tr class="breaker">
			<td colspan="3">Course Information</td>
		</tr>
		<tr class="row-course-prefix">
			<td class="label">
				<label>Course Prefix: <span class="required">*</span></label>
			</td>
			<td class="input">
				<input type="text" name="prefix" id="prefix" autocomplete="off" value="<?=@$class->course->prefix;?>" maxlength="4" style="width:55px;" /> 
			</td>
			<td class="hint">Eg. "COMM" or "PSYC"</td>
		</tr>
		<tr class="row-course-num">
			<td class="label">
				<label>Course Number: <span class="required">*</span></label>
			</td>
			<td class="input">
				<input type="text" name="num" id="num" autocomplete="off" value="<?=@$class->course->num;?>" maxlength="4" style="width:55px;" /> 
			</td>
			<td class="hint">Eg. "1200" or "4350"</td>
		</tr>
		<tr class="row-course-title">
			<td class="label">
				<label>Course Title: <span class="required">*</span></label>
			</td>
			<td class="input" colspan="2">
				<div style="width:400px;display:inline-block;">
					<input type="text" name="title" id="title" autocomplete="off" disabled="disabled" value="<?=@$class->course->title;?>" style="width:385px;padding:4px;vertical-align: bottom;" /> 
				</div>
			</td>
		</tr>
		<tr class="breaker">
			<td colspan="3">Class Details</td>
		</tr>
		<tr class="row-class-title">
			<td class="label">
				<label>Class Title:</label>
			</td>
			<td class="input">
				<div style="width:400px;display:inline-block;margin-bottom:5px;">
					<input type="text" name="subtitle" id="subtitle" autocomplete="off" value="<?=@$class->subtitle;?>" style="width:385px;padding:4px;vertical-align: bottom;" /> 
				</div>
			</td>
			<td class="hint">
				Class Title is usually used for Special Topics courses. If the class has a different title than the course title, enter that title here.
			</td>
		</tr>
		<tr class="row-instructors">
			<td class="label">
				<label>Instructors: <span class="required">*</span></label>
			</td>
			<td class="input" colspan="2">
				<div style="width:400px;display:inline-block;">
					<input type="text" name="instructors" id="instructors" style="width:270px;margin-left:2px;" />
				</div>
			    <div class="admin-button ui-widget-header active add-instructor" title="Add New Instructor" classid="<?=$class->classid;?>">
			        <div class="icon"><?=StdLib::load_image("add.png","20px");?></div>
			    </div>
			</td>
		</tr>
		<tr class="row-section">
			<td class="label">
				<label>Sections: <span class="required">*</span></label>
			</td>
			<td class="input">
				<input type="text" name="section" id="section" autocomplete="off" value="<?=@$class->section;?>" style="width:130px;" /> 
			</td>
			<td class="hint">If multiple sections share the same syllabi, separate the sections by commas. Eg. "001,002,003,500"</td>
		</tr>
		<tr class="row-section">
			<td class="label">
				<label>Term: <span class="required">*</span></label>
			</td>
			<td class="input">
				<select name="term" style="width:auto;margin-left:0px;">
					<option <?=(@$class->term=="Fall")?"selected='selected'":"";?>>Fall</option>
					<option <?=(@$class->term=="Spring")?"selected='selected'":"";?>>Spring</option>
					<option <?=(@$class->term=="Summer Maymester")?"selected='selected'":"";?> value="Summer M">Summer Maymester</option>
					<option <?=(@$class->term=="Summer A")?"selected='selected'":"";?>>Summer A</option>
					<option <?=(@$class->term=="Summer B")?"selected='selected'":"";?>>Summer B</option>
					<option <?=(@$class->term=="Summer C")?"selected='selected'":"";?>>Summer C</option>
					<option <?=(@$class->term=="Summer D")?"selected='selected'":"";?>>Summer D</option>
				</select>
			</td>
			<td class="hint"></td>
		</tr>
		<tr class="row-section">
			<td class="label">
				<label>Year: <span class="required">*</span></label>
			</td>
			<td class="input">
				<input type="text" name="year" id="year" autocomplete="off" value="<?=@$class->year;?>" style="width:55px;" maxlength="4" />
			</td>
			<td class="hint"></td>
		</tr>
		<tr class="row-website">
			<td class="label">
				<label>Website: </label>
			</td>
			<td class="input" colspan="2">
				<input type="text" name="website" id="website" autocomplete="off" value="<?=@$class->website;?>" style="width:388px;" /> 
			</td>
		</tr>
		<tr class="row-addinfo">
			<td class="label">
				<label>Additional Info:</label>
			</td>
			<td class="input" colspan="2">
				<textarea name="metadata" style="width:388px;height:85px;resize:none;" ><?=@$class->metadata;?></textarea>
			</td>
		</tr>
		<tr class="breaker">
			<td colspan="3">Upload a Syllabus</td>
		</tr>
		<tr class="row-section">
			<td class="label">
				<label>Syllabus:</label>
			</td>
			<td class="input" colspan="2">
				<input type="file" name="syllabus" id="syllabus" value="<?=@$class->filename;?>"/>
			</td>
		</tr>
		<tr class="row-section">
			<td class="label">
				<label>Current Syllabi:</label>
			</td>
			<td class="input" style="vertical-align: bottom;" colspan="2">
			<?php if($class->loaded): ?>
					<div class="syllabus">
						<table>
							<tr>
								<td>&lt;primary&gt;</td>
								<td></td>
							</tr>
				<?php $flag = false; foreach($class->syllabi as $syllabus): ?>
					<?php 
						if(!$syllabus->valid()) continue; 
						if($syllabus->primary == 1 and $flag) {
							$syllabus->primary = 0;
							if(!$syllabus->save()) {
								Yii::app()->user->setFlash("warning",$syllabus->get_error());
								$this->redirect(Yii::app()->createUrl('permalink')."?cid=".$class->classid);
								exit;
							}
						}
					?>
							<tr>
								<td class="calign" style="padding-right:10px;">
									<input type="radio" name="primary" <?=($syllabus->primary==1 and !$flag)?'checked="checked"':'';?> value="<?=$syllabus->syllabusid;?>" />
								</td>
								<td>
									<?=$syllabus->filename;?> 
									(<a href="#" class="remove" syllabusid="<?=$syllabus->syllabusid;?>">remove</a>)
									(<a href="#" class="preview" syllabusid="<?=$syllabus->syllabusid;?>">preview</a>) 
									(<a href="#" class="download" syllabusid="<?=$syllabus->syllabusid;?>">download</a>)
								</td>
							</tr>
				<?php if($syllabus->primary==1) $flag = true; endforeach; ?>
						</table>
					</div>
			<?php endif; ?>
			</td>
		</tr>
		<tr class="row-allowed">
			<td class="label" style="vertical-align:middle;">
				<label>Allow Off-Campus: <span class="required">*</span></label>
			</td>
			<td class="input" style="padding-top:10px;">
				<input type="checkbox" name="allowed" class="iphone" <?=(@$class->offcampus==1)?"checked='checked'":"";?>/>
			</td>
			<td class="hint" style="vertical-align:middle;">Allow anyone off campus to view this class and its syllabi?</td>
		</tr>
		<tr class="footer">
			<td colspan="3"><span class="required">*</span> = required fields</td>
		</tr>
	</table>
	<button id="cancel">Cancel</button>
	<?php if($edit): ?>
	<button id="delete">Delete</button>
	<?php endif; ?>
	<button>Save Class</button>
</form>

<div id="add-instructor-dialog" title="Add Instructor">
	<table>
		<tr>
			<td style="padding-right:10px;">Name</td>
			<td><input type="text" id="name" /></td>
		</tr>
	</table>
</div>

<div id="delete-class-dialog" title="Delete Class">
	Are you sure you wish to delete this class?
</div>

<div id="preview" title="Preview Syllabus"></div>

<script>
jQuery(document).ready(function(){
	$(".iphone").iphoneStyle({
		checkedLabel: 'YES',
		uncheckedLabel: 'NO'
	});
	
	$(".remove").click(function(){
		$obj = $(this);
		var syllabusid = $(this).attr("syllabusid");
		var r = confirm("Are you sure you wish to remove this syllabus?");
		if(r) {
			$.ajax({
				"url": 		'<?=Yii::app()->createUrl("_remove_syllabus");?>',
				"data": 	'sid='+syllabusid,
				"success": 	function(data) {
					if(data==1) {
						$obj.parent().parent().fadeOut();
					}
				}
			});
		}
		return false;
	});
	
	$(".download").click(function(){
		var syllabusid = $(this).attr("syllabusid");
		window.location = '<?=Yii::app()->createUrl("_download");?>?sid='+syllabusid;
		return false;
	});
	
	$(".preview").click(function(){
		var syllabusid = $(this).attr("syllabusid");
		$("#preview").dialog("open").load("<?=Yii::app()->createUrl('_preview_syllabus');?>?sid="+syllabusid+"&w="+$(window).width()*0.95+"&h="+$(window).height()*0.95);
		return false;
	});
	
	$("#delete").click(function(){
		$("#delete-class-dialog").dialog("open");
		return false;
	});
	
	$("#delete-class-dialog").dialog({
		"autoOpen": 		false,
		"resizable": 		false,
		"draggable": 		false,
		"modal": 			true,
		"width": 			330,
		"height": 			150,
		"buttons": 			{
			"Positive!": 		function() {
				$(this).dialog("option","title","Deleting class...");
				$(this).html("Deleting class. Please wait. <img src='<?=Yii::app()->baseUrl;?>/images/ajax-loader.gif' />");
				$(this).dialog("option","buttons","");
				$(this).dialog("option","closeOnEscape",false);
				$(".ui-dialog-titlebar-close").hide();
				window.location = "<?=Yii::app()->createUrl('deleteClass');?>?cid=<?=$class->classid;?>";
				return false;
			},
			"Nevermind...": 	function() {
				$("#delete-class-dialog").dialog("close");
			}
		}
	});
	
	$("#preview").dialog({
		"autoOpen": 		false,
		"resizable": 		false,
		"draggable": 		false,
		"modal": 			true,
		"width": 			$(window).width()*0.95,
		"height": 			$(window).height()*0.95
	});
	
	
	$("#prefix, #num").keyup(function(){
		var prefix = $("#prefix").val();
		var num = $("#num").val();
		if(prefix.length != 4 || num.length != 4 || !prefix.match(/[A-Za-z]{4}/g) || !num.match(/[0-9]{4}/g)) {
			$("#title").val("").attr("disabled","disabled");
			return false;
		}
		$.ajax({
			"url": 		"<?=Yii::app()->createUrl('_load_course_title');?>",
			"data": 	"prefix="+prefix+"&num="+num,
			"success": 	function(data) {
				$("#title").val(data);
				if($("#title").val()=="") {
					$("#title").removeAttr("disabled");
				}
			}
		});
		return false;
	});
	
	$(".add-instructor").tipTip({
		defaultPosition: "right"
	}).click(function(){
		$("#add-instructor-dialog").dialog("open");
	});
	
	$("#add-instructor-dialog").dialog({
		"autoOpen": 		false,
		"resizable": 		false,
		"draggable": 		false,
		"buttons": 			{
			"Cancel": 		function(){
				$("#name").val("");
				$("#add-instructor-dialog").dialog("close");
			},
			"Add Instructor": 	function(){
				$.ajax({
					"url": 		"<?=Yii::app()->createUrl('_add_instructor');?>",
					"data": 	"name="+escape($("#name").val()),
					"dataType": "JSON",
					"success": 	function(data) {
						if(data) {
							var $name = data["name"];
							var $id = data["id"];
							itoken.tokenInput("add",{id:""+$id+"",name:""+$name+""});
							$("#name").val("");
							$("#add-instructor-dialog").dialog("close");
						}
					}
				});
			}
		}
	});
	
	// Interaction - names autocomplete
	itoken = $("#instructors").tokenInput("<?=Yii::app()->createUrl('_load_instructors');?>", {
			theme: "facebook",
			hintText: "Start typing to look up instructors",
			noResultsText: "No one found by that name",
			searchingText: "Searching instructors...",
			preventDuplicates: true,
			<?php if(!empty($class->instructors) and is_array($class->instructors)): ?>
			prePopulate: [
				<?php foreach($class->instructors as $instructor): ?>
				{id: <?=$instructor->instrid;?>, name: "<?=$instructor->name;?>"},
				<?php endforeach; ?>
			]
			<?php endif; ?>
	});
	
	$(".iphone").iphoneStyle();
	$("#cancel").click(function(){
		window.location = "<?=Yii::app()->createUrl('index');?>#cl="+$("#prefix").val()+$("#num").val()+"&c="+$("#prefix").val();
		return false;
	});
});
</script>
