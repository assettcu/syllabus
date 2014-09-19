<?php

$cs = Yii::app()->getClientScript();
$cs->registerScriptFile(HTTPS."://".LIBRARY_DIRECTORY."/javascript/jquery/modules/jsTree/jquery.jstree.js");
$cs->registerCssFile(HTTPS."://".LIBRARY_DIRECTORY."/javascript/jquery/modules/jsTree/themes/classic/style.css");

$user = Yii::app()->user->getState("_user");
$flashes = new Flashes;
$flashes->render();
$dir = "C:/archive/";
?>
<h1>Files of the Archive</h1>
<div id="tree-container" style="width:300px;height:532px;border:2px solid #ccc;padding:5px;float:left;margin-bottom:15px;">
	<input type="text" name="search" value="" id="search" /> Search
	<span id="ajax-loading" class="hide" style="padding-left:10px;padding-top:3px;">
		<img src="<?=Yii::app()->baseUrl;?>/images/ajax-loader2.gif" />
	</span>
	<div id="demo" style="overflow-y:auto;background:transparent;padding:5px;height:490px;">
		<ul style="display:none;" class="folders">
		<?php
		if ($handle = opendir($dir)) {
		
		    /* This is the correct way to loop over the directory. */
		    while (false !== ($entry = readdir($handle))) {
				if($entry != "." && $entry != "..") {
					if(is_file($dir.$entry)) continue;
					if($user->has_permission($entry)) {
					?>
					<li class="jstree-closed" rel="dir" id="<?php echo $entry; ?>">
						<a href="#"><?php echo $entry;?></a>
						<ul class="files">
						<?php
						$handle2 = opendir($dir.$entry);
					    while (false !== ($file = readdir($handle2))) {
							if($file != "." && $file != "..") {
								$path_parts = pathinfo($dir.$entry.$file);
								$ext = $path_parts["extension"];
								if($ext=="docx") {
									$ext = "doc";
								}
							?>
							<li rel="<?php echo $ext;?>" filename="<?php echo $file; ?>"><a href="#"><?php echo $file; ?></a></li>
							<?php
							}
						}
						?>
						</ul>
					</li>
					<?php
					} else {
						echo "User does not have permission: ".$entry."<br/>";
					}
				}
			}
		} else {
			print "Could not open directory.";
		}
		?>
		</ul>
	</div>
</div>
<div id="syllabus-panel" style="float:right;width:775px;border:2px solid #ccc;text-align:center;margin-bottom:15px;min-height:25px;">
	<div id="syllabus-title" style="padding:10px;text-align:left;font-size:15px;font-weight:bold;color:#fff;background-color:#059;display:none;">
		<span id="dept"></span> / <span id="filename"></span>
	</div>
	<div id="syllabus">
		<div style="padding:10px;text-align:left;">Select a syllabus to view</div>
	</div>
</div>


<script>
var myTree = 0;
var timer = 0;
jQuery(document).ready(function($){
	
	$("#search").keyup(function(){
		if(timer) {
			clearTimeout(timer);
		}
		var $value = $(this).val();
		$value = $.trim($value);
		if($value == "") {
			$("ul.files li").show();
		} else {
			$("#ajax-loading").show();
			timer = setTimeout(function(){search($value)}, 1000);
		}
	});
	
	myTree = $("#demo").jstree({ 
        "plugins" : ["themes","html_data","dnd","ui","types","contextmenu"],
        "types" : {
            "valid_children" : [ "web" ],
            "types" : {
                "pdf" : {
                    "icon" : { 
                        "image" : "//compass.colorado.edu/libraries/javascript/jquery/modules/jsTree/icons/pdf.png" 
                    },
                },
                "doc" : {
                    "icon" : { 
                        "image" : "//compass.colorado.edu/libraries/javascript/jquery/modules/jsTree/icons/doc.png" 
                    },
                },
                "dir" : {
                    "icon" : { 
                        "image" : "//compass.colorado.edu/libraries/javascript/jquery/modules/jsTree/icons/folder.png" 
                    },
                },
                "default" : {
                    "icon" : { 
                        "image" : "//compass.colorado.edu/libraries/javascript/jquery/modules/jsTree/icons/file.png" 
                    },
                }
            }
        },
        "contextmenu": { 
        	"items" : customMenu
        }
	});
	
	function customMenu(node) {
	    // The default set of all items
	    var items = {
	        download: { // The "rename" menu item
	            label: "Download",
	            action: function (obj) { 
                	var dept = obj.parent().parent().attr("id");
                	var filename = obj.attr("filename");
                	window.location = "<?=Yii::app()->createUrl('_download');?>?dept="+escape(dept)+"&filename="+escape(filename);
		        }
	        },
            <?php if($user->permission_level >= 10) { ?>
	        deleteItem: { // The "delete" menu item
	            label: "Delete",
	            action: function (obj) {
                	var dept = obj.parent().parent().attr("id");
                	var filename = obj.attr("filename");
            		var v = confirm("Are you sure you wish to delete this syllabus?\n "+dept+" / "+filename);
            		if(v) {
            			obj.hide("blind").remove();
            		}
	            }
	        }
	        <?php } ?>
	    };
	
	    if ($(node).attr("rel")=="dir") {
	        // Delete the "delete" menu item
	        delete items.deleteItem;
	        delete items.download;
	    }
	
	    return items;
	}
	
	myTree.bind("select_node.jstree",function(event, data) {
		var filename = data.inst.get_text(data.rslt.obj);
		var dept = data.inst.get_text(data.inst._get_parent(data.rslt.obj));
		if(filename.length == 4) {
			return false;
		}
		$("span#dept").html(dept);
		$("span#filename").html(filename);
		$("#syllabus-title").show('blind');
		$("#syllabus").load("<?=Yii::app()->createUrl('_preview_syllabus');?>?dept="+escape(dept)+"&filename="+escape(filename)+"&w=775&h=500");
	});
});

	
function search($value) {
	$("ul.files li").each(function(index,obj){
		var terms = $value.split(' ');
		var filename = $(obj).attr("filename");
		var $flag = true;
		for(a in terms) {
			var re = new RegExp(".*"+terms[a]+".*","g");
			if(!filename.match(re,filename)) {
				$flag = false;
			}
		}
		if($flag===true) {
			$(obj).show();
		} else {
			$(obj).hide();
		}
	});
	$("#ajax-loading").hide();
}
</script>