<?php 
ini_set("display_errors",1);
error_reporting(E_ALL);

$flashes = new Flashes;
$flashes->render();

$group = new GroupObj(new InstructorObj);
$instructors = $group->instructors;

usort($instructors,"sort_by_name");

function sort_by_name($a,$b) {
	if(!isset($a->name,$b->name)) return 0;
	if($a->name == $b->name) return 0;
	return ($a->name < $b->name) ? -1 : 1;
}
?>

<style>
	table.usertable thead tr th {
		padding:5px;
		background-color:#900;
		color:#fff;
		border-right:1px solid #fff;
	}
	#tabs a.action {
		color: #09f;
	}
	#tabs a.action:hover {
		color:#f90;
	}
	div.disabled {
		margin-left:10px;
	}
	div.disabled a {
		color:#999;
		cursor:default;
	}
	div.nav-top {
		margin-bottom:7px;
	}
</style>

<div class="title">Manage Users</div>

<div id="tabs" class="hide">
	<ul>
		<li><a href="#tab-1">System Users</a></li>
		<li><a href="#tab-2">Instructors</a></li>
	</ul>
	<div id="tab-1">
		<div class="nav-top">
			<div style="display:inline-block;">
				<a href="<?=Yii::app()->createUrl('adduser');?>" class="action">Add New User</a>
			</div> |  
			<div style="display:inline-block;">
				Actions: 
				<div class="disabled" style="display:inline-block;">
					<a href="#" class="bulk-email">Email</a>
				</div>
				<div class="disabled" style="display:inline-block;">
					<a href="#" class="bulk-delete">Delete</a>
				</div>
			</div>
		</div>
		<table id="sysusers" class="usertable">
			<thead>
				<tr>
					<th width="35px">
						<input type="checkbox" />
					</th>
					<th width="120px">Username</th>
					<th>Email</th>
					<th width="50px" class="calign">Attempts</th>
					<th width="50px" class="calign">Active</th>
					<th class="calign">Permission Level</th>
					<th class="calign" style="min-width:200px;">Permissions</th>
					<th class="calign">Action</th>
				</tr>
			</thead>
			<tbody>
				<?php $count=0; foreach($users as $user): $count++; 
					$user_ = Yii::app()->user->getState("_user");
					if($user_->permission_level <= $user->permission_level and $user_->permission_level < 10) continue;
					if(!$user_->shares_permissions_with($user)) continue;
					?>
					<tr class="<?=($count%2==0)?'odd':'even';?>">
						<td class="calign" style="padding-left:0px;">
							<?php if($user->username != Yii::app()->user->name): ?>
							<input type="checkbox" />
							<?php else: ?>
								<img src="<?=StdLib::load_image_href("object-locked.png");?>" class="lock" width="24px" height="24px" title="Cannot modify this user, currently logged in as this user." />
							<?php endif; ?>
						</td>
						<td><?=$user->username;?></td>
						<td style="padding-right:10px;"><?=$user->email;?></td>
						<td class="calign"><?=$user->attempts;?></td>
						<td class="calign"><?=($user->active)?"Yes":"No";?></td>
						<td class="calign"><?=$user->permission_level;?></td>
						<td style="font-size:10px;">
						<?php 
						if(isset($user->permissions)) {
							$restrictflag = false;
							$allowflag = false;
							$allowed = array();
							$restricted = array();
							foreach($user->permissions as $perm):
								if($perm->level == 2) {
									$allowed = array("All Courses");
									$allowflag = true;
								} else if($perm->level == 0) {
									if($perm->permission=="") {
										$restricted = array("All Courses");
										$restrictflag = true;
									} else if($restrictflag){
										$restricted[] = $perm->permission;
									}
								} else if($perm->level == 1 and !$allowflag) {
									$allowed[] = $perm->permission;
								}
							endforeach;
							$allowed = implode(",",$allowed);
							$restricted = implode(",",$restricted);
						}
						?>
							<table style="width:100%;">
								<tbody>
									<?php if($allowed!=""): ?>
									<tr>
										<td style="background-color:#dfd;" width="66px">Allowed: </td>
										<td style="background-color:#dfd;"><?=$allowed;?></td>
									</tr>
									<?php endif; ?>
									<?php if($restricted!=""): ?>
									<tr>
										<td style="background-color:#fdd;" width="66px">Restricted: </td>
										<td style="background-color:#fdd;" class="lalign"><?=$restricted;?></td>
									</tr>
									<?php endif; ?>
								</tbody>
							</table>
						</td>
						<td class="calign" style="padding-top:5px;">
						    <div class="admin-button ui-widget-header active edit" title="Edit User" username="<?=$user->username;?>">
						        <div class="icon"><?=StdLib::load_image("pencil_edit.png","13px");?></div>
						    </div>
							<?php if($user->username != Yii::app()->user->name): ?>
						    <div class="admin-button ui-widget-header active delete" title="Delete User" username="<?=$user->username;?>">
						        <div class="icon"><?=StdLib::load_image("close_delete_2.png","13px");?></div>
						    </div>
						    <?php else: ?>
						    <div class="admin-button ui-widget-header active" title="Cannot delete this user, currently logged in as this user.">
						        <div class="icon"><?=StdLib::load_image("object-locked.png","13px");?></div>
						    </div>
						    <?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<div id="tab-2">
		<table id="instructors" class="usertable">
			<thead>
				<tr>
					<th width="35px"></th>
					<th width="200px">Name</th>
					<th class="calign">Action</th>
				</tr>
			</thead>
			<tbody>
				<?php $count=0; foreach($instructors as $instr): $count++; ?>
					<tr class="<?=($count%2==0)?'odd':'even';?>">
						<td class="calign" style="padding-left:0px;"><?=$instr->instrid;?></td>
						<td><?=@$instr->name;?></td>
						<td class="calign" style="padding-top:5px;">
						    <div class="admin-button ui-widget-header active edit" title="Edit Instructor" instrid="<?=$instr->instrid;?>">
						        <div class="icon"><?=StdLib::load_image("pencil_edit.png","13px");?></div>
						    </div>
						    <div class="admin-button ui-widget-header active delete" title="Delete Instructor" instrid="<?=$instr->instrid;?>">
						        <div class="icon"><?=StdLib::load_image("close_delete_2.png","13px");?></div>
						    </div>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>

<script>
jQuery(document).ready(function($){
	$(".add-user").click(function(){
		window.location = "<?=Yii::app()->createUrl('adduser');?>";
		return false;
	});
	$("#tabs").tabs({
	    activate: function(event, ui){
	        $.cookie($(this).prop('id'), ui.newTab.index(), { expires: 365 });
	    }
	}).show();
	$(".admin-button.edit").click(function(){
		window.location = "<?=Yii::app()->createUrl('edituser');?>?id="+$(this).attr("username");
		return false;
	});
	$(".admin-button, .lock").tipTip();
	$("div.admin-button").hover(
		function() {
			$(this).stop().fadeTo("fast",0.7);
		},
		function() {
			$(this).stop().fadeTo("fast",1);
		}
	);
});
</script>