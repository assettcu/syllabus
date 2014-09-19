<?php $flashes = new Flashes; $flashes->render(); ?>

<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/table.css" />

<h1>Manage Archive</h1>
<br/>
<div id="navigation">
	<div class="row" id="nav-top">
		<label>In Department:</label>
		<div class="selector" id="uniform-department_filter">
			<span>Any department</span>
			<select name="d" id="department_filter" style="opacity: 0;">
				<option value="all">Any department</option>
				<?php foreach($prefixes as $prefix): ?>
					<option value="<?=$prefix["prefix"];?>"><?=$prefix["prefix"];?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="triangle"></div>
		<div class="filtered_text">
			<input autocomplete="off" class="example hint" id="qf" name="q" title="Filter classes..." type="text" value="Filter classes..." />
		</div>
		<div class="selector" id="uniform-syllabus_filter">
			<span>Any Classes</span>
			<select name="sf" id="syllabus_filter" style="opacity: 0;">
				<option value="all">Any Classes</option>
				<option value="1">Missing Syllabi</option>
				<option value="2">Attached Syllabi</option>
			</select>
		</div>
		<div class="triangle"></div>
	</div>
	<div class="row" id="nav-bottom">
		<div class="select">
			<label>Select:</label>
			<a class="action" href="#" id="select_all_button" title="Select all the classes on this page">These</a>
			<a class="action" href="#" id="select_none_button" title="Clear the selection">None</a>
		</div>
		<div class="bulk disabled_bulk">
			<label>Actions:</label>
			<a class="action" href="#" id="bulk_tag" title="Add or remove tags from classes">Tag</a>
			<a class="action" href="#" id="bulk_merge" title="Display a screen for combining records that are the same class">Merge</a>
			<a class="action" href="#" id="bulk_delete" title="Remove class from most views">Delete</a>
			<a class="action" href="#" id="bulk_restore" title="Restore class to move views">Restore</a>
			<a class="action" href="#" id="bulk_export" title="Export classes into a CSV file">Export</a>
		</div>
	</div>
</div>
<div class="clear"></div>
<div id="loading-panel" class="ui-state-highlight" style="padding:5px;margin-top:5px;margin-bottom:5px;">
	Loading table... <img src="<?=Yii::app()->baseUrl;?>/images/ajax-loader.gif" />
</div>
<div id="classes-panel"></div>
<div id="navigation-bottom" style="display:block;">
	<div class="row">
		<div class="alpha">
			<div class="alpha pagination">
				<a href="#" class="current" title="All" alpha="">All</a>
				<?php
					$letters = str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZ");
					ob_start();
					foreach($letters as $letter):
					?>
						<a href="#" alpha="<?=$letter?>"><?=$letter?></a>
					<?php
					endforeach;
					$contents = ob_get_contents();
					ob_end_clean();
					print $contents;
				?>
			</div>
		</div>
		<div class="classes-count">
			<select id="classes-count-menu" name="classes-count-menu" style="display:inline-block;">
				<option value="10">Show 10 classes</option>
				<option value="25">Show 25 classes</option>
				<option value="50">Show 50 classes</option>
				<option value="75">Show 75 classes</option>
				<option value="100">Show 100 classes</option>
			</select>
		</div>
		<div class="paging">
			<div class="pagination page">
				<span class="disabled first_page">First</span>
				<span class="disabled prev_page">Prev</span>
				<span class="counts current">1-10 of 0</span>
				<a href="#" class="next_page">Next</a>
				<a href="#" class="last_page">Last</a>
			</div>
		</div>
	</div>
</div>

<script>
var ajaxloadtable;
jQuery(document).ready(function($){
	
	loadTable();
	
	if(parseAnchor('f')!="")
		$("#qf").val(parseAnchor('f')).removeClass('hint');
		
	if(parseAnchor('a')!="")
	{
		$(".alpha.pagination").find(".current").removeClass("current");
		$(".alpha.pagination a[alpha='"+parseAnchor('a')+"']").addClass('current');
	}
	
	$("a[href='#']").live('click',function(){
		return false;
	});
	
	if(parseAnchor('d')!="")
	{
		$('#department_filter option[value="'+parseAnchor("d")+'"]').attr('selected','selected');
		$('#department_filter').parent().find('span').text($("#department_filter option:selected").text());
	}
	
	if(parseAnchor('sf')!="")
	{
		$('#syllabus_filter option[value="'+parseAnchor("sf")+'"]').attr('selected','selected');
		$('#syllabus_filter').parent().find('span').text($("#syllabus_filter option:selected").text());
	}
				
	$("#select_all_button").click(function(){
		$("input:checkbox").attr("checked","checked");
		$("#nav-bottom .bulk.disabled_bulk").removeClass("disabled_bulk");
		return false;
	});
	$("#select_none_button").click(function(){
		$("input:checkbox").removeAttr("checked");
		$("#nav-bottom .bulk").addClass("disabled_bulk");
		return false;
	});
	
	$("a[href='#']").live('click',function(){
		return false;
	});
	
	$(".classes-table table thead tr th a.sort").live('click',function(){
		var sort = $(this).attr('sort');
		updateAnchor("sort",sort);
		loadTable();
	});
	
	$("a.next_page").live('click',function(){
		var currentPage = parseAnchor("page");
		if(currentPage=="") currentPage = 1;
		currentPage = parseInt(currentPage);
		var nextpage = currentPage+1;
		updateAnchor("page",nextpage);
		loadTable();
		return false;
	});
	
	$("a.prev_page").live('click',function(){
		var currentPage = parseAnchor("page");
		if(currentPage=="") currentPage = 2;
		currentPage = parseInt(currentPage);
		var prevpage = currentPage-1;
		updateAnchor("page",prevpage);
		loadTable();
		return false;
	});
	
	$("#classes-count-menu").change(function(){
		updateAnchor("pl",$(this).val());
		loadTable();
		return false;
	});
	
	$("#uniform-c > select").change(function(){
		var $text = $(this).val();
		$("#uniform-c > span").text($text);
	});
	
	$("#uniform-tagging_filter > select").change(function(){
		var $text = $(this).val();
		if($text!="")
			$("#uniform-tagging_filter > span").text($text);
		else
			$("#uniform-tagging_filter > span").text("Any tag");
		updateAnchor("page",1);
		updateAnchor("tag",escape($(this).val()));
		loadTable();
		return false;
	});
	
	$("#uniform-department_filter select").change(function(){
		var $val = $(this).val();
		var $text = $(this).find("option:selected").text();
		if($val!="")
			$("#uniform-department_filter > span").text($text);
		else
			$("#uniform-department_filter > span").text("Any department");
		updateAnchor("page",1);
		updateAnchor("d",escape($val));
		loadTable();
		return false;
	});
	
	$("#uniform-syllabus_filter select").change(function(){
		var $val = $(this).val();
		var $text = $(this).find("option:selected").text();
		if($val!="")
			$("#uniform-syllabus_filter > span").text($text);
		else
			$("#uniform-syllabus_filter > span").text("Any classes");
		updateAnchor("page",1);
		updateAnchor("sf",escape($val));
		loadTable();
		return false;
	});
	
	$("#qf").live('keyup',function(){
		updateAnchor("f",$(this).val());
		updateAnchor("page",1);
		updateAnchor("sort","name");
		loadTable();
		return false;
	});
	
	$("#search").focus(function(){
		if($(this).val()=="Search...")
		{
			$(this).val("");
		}
		$(this).removeClass("hint");
	}).blur(function(){
		if($(this).val()==""){
			$(this).val("Search...");
			$(this).addClass("hint");
		}
	});
	
	$("#qf").focus(function(){
		if($(this).val()=="Filter classes...")
		{
			$(this).val("");
		}
		$(this).removeClass("hint");
	}).blur(function(){
		if($(this).val()==""){
			$(this).val("Filter classes...");
			$(this).addClass("hint");
		}
	});
});
function loadTable()
{
	$("#loading-panel").show();
	var currentPage = parseAnchor("page");
	if(currentPage=="") currentPage = 1;
	currentPage = parseInt(currentPage);
	updateAnchor("page",currentPage);
	
	var pagelength = parseAnchor("pl");
	if(pagelength=="") pagelength = 10;
	updateAnchor("pl",pagelength);
	$("#classes-count-menu option[value='"+pagelength+"']").attr('selected','selected');
	
	var sort = parseAnchor("sort");
	if(sort=="") sort = "name";
	updateAnchor("sort",sort);
	
	var filter = parseAnchor("f");
	var tag = parseAnchor("tag");
	var letter = parseAnchor("a");
	var dept = parseAnchor("d");
	var sf = parseAnchor("sf");
	
	ajaxloadtable = $.ajax({
		"url":				"<?=Yii::app()->createUrl('_load_syllabus_table');?>",
		"data":				"page="+currentPage+"&pl="+pagelength+"&sort="+sort+"&f="+filter+"&tag="+tag+"&letter="+letter+"&dept="+dept+"&sf="+sf,
		"dataType":			"JSON",
		"success":			function(data){
			$("#classes-panel").html(data.contents);
			$(".paging .pagination").html(data.paging);
			$("#loading-panel").hide();
		}
	});
	return false;
}

function parseAnchor(akey)
{
	var hash = location.hash.substring(1);
	if(hash=="") return "";
	var $params = hash.split("&");
	var $return = "";
	$.each($params,function(key,value){
		var key = value.split("=")[0];
		var value = value.split("=")[1];
		if(key==akey) $return = value;
	});
	return unescape($return);
}
function updateAnchor(akey,avalue)
{
	var hash = location.hash.substring(1);
	var $params = hash.split("&");
	var updated = false;
	var hashstring = "";
	if(hash.length!=0)
	{
		$.each($params,function(key,value){
			var key = value.split("=")[0];
			var value = value.split("=")[1];
			if(key==akey)
			{
				updated = true;
				hashstring = hashstring + akey + "=" + avalue + "&";
			} else {
				hashstring = hashstring + key + "=" + value + "&";
			}
		});
	}
	if(!updated) hashstring = hashstring + akey + "=" + avalue;
	else hashstring = hashstring.substring(0,hashstring.length-1);
	window.location.hash = hashstring;
}
</script>