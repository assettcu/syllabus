<?php

class SiteController extends Controller
{
	/** DEFAULT ACTIONS **/
	public function actionIndex()
	{
		$cs = Yii::app()->getClientScript();
		$cs->registerScriptFile("//".WEB_LIBRARY_PATH."jquery/modules/tipTip/jquery.tipTip.js");
		$cs->registerScriptFile("//".WEB_LIBRARY_PATH."jquery/modules/sortElements/jquery.sortElements.js");
		$cs->registerCssFile("//".WEB_LIBRARY_PATH."jquery/modules/tipTip/tipTip.css");
		
		$this->render('index');
	}

	public function actionError()
	{
	    if($error=Yii::app()->errorHandler->error) {
	    	if(Yii::app()->request->isAjaxRequest) {
	    		echo $error['message'];
	    	} else {
	        	$this->render('error', $error);
			}
	    }
	}
    
    public function actionSyllabus() {
        
        
        $this->render('syllabus');
    }

    public function actionDiagnostics() {
        if(!StdLib::is_programmer()) {
            $this->redirect('index');
            exit;
        }
        
        $this->render("diagnostics");
    }

	public function actionLogin()
	{
		// Force log out
		if(!Yii::app()->user->isGuest) Yii::app()->user->logout();
		
		$this->makeSSL();
		$params = array();
		$model = new LoginForm;
		$redirect = (isset($_REQUEST["redirect"])) ? $_REQUEST["redirect"] : "index";
		
		// collect user input data
		if (isset($_POST['username']) and isset($_POST["password"])) {
			
			$model->username = $_POST["username"];
			$model->password = $_POST["password"];
			// validate user input and redirect to the previous page if valid
			if ($model->validate() && $model->login())
			{
				$this->redirect($redirect);
			}
		}
		
		$params["model"] = $model;
		
		// display the login form
		$this->render('login',$params);
	}


	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
	
	public function beforeAction($action)
	{
		if(!Yii::app()->user->isGuest) {
			$user = Yii::app()->user->getState("_user");
			if(is_null($user) or !isset($user->loaded)) {
			}
			else {
				$user->load();
				Yii::app()->user->setState("_user",$user);
			}
		}
		return $action;
	}

	/** NORMAL PAGES **/
	
	public function actionCourse()
    {
        if(isset($_GET["prefix"]) and !isset($_GET["num"])) {
            $params["prefix"] = $_GET["prefix"];
            $this->render("course1",$params);
        } else if(isset($_GET["prefix"],$_GET["num"])) {
            $params["prefix"] = $_GET["prefix"];
            $params["num"] = $_GET["num"];
            $this->render("course2",$params);
        } else {
            Yii::app()->user->setFlash("warning","You must select a course in order to view its syllabi.");
            $this->redirect(Yii::app()->homeUrl);
        }
    }
	
	public function actionSearch()
	{
		$cs = Yii::app()->getClientScript();
		$cs->registerScriptFile("//".WEB_LIBRARY_PATH."jquery/modules/tipTip/jquery.tipTip.js");
		$cs->registerScriptFile("//".WEB_LIBRARY_PATH."jquery/modules/sortElements/jquery.sortElements.js");
		$cs->registerCssFile("//".WEB_LIBRARY_PATH."jquery/modules/tipTip/tipTip.css");
		
		$this->render("search");
	}

	public function actionPermalink()
	{
		if(isset($_REQUEST["p"],$_REQUEST["n"],$_REQUEST["s"]) and !isset($_REQUEST["cid"])) {
			$course = new CourseObj();
			$course->prefix = $_REQUEST["p"];
			$course->num = $_REQUEST["n"];
			$course->load();
			if(!($course->loaded)) {
				Yii::app()->user->setFlash('error','The permalink is malformed or the class does not exist.');
				$this->redirect(Yii::app()->createUrl('index')."#cl=".$course->prefix.$course->num."&c=".$course->prefix);
				exit;
			}
			$class = new ClassObj();
			$class->course = $course;
			$class->section = $_REQUEST["s"];
			if(strlen($class->section)!=3) {
				Yii::app()->user->setFlash('error','The permalink is malformed or the class does not exist.');
				$this->redirect(Yii::app()->createUrl('index')."#cl=".$course->prefix.$course->num."&c=".$course->prefix);
				exit;
			}
			$class->load();
		} else if(isset($_REQUEST["cid"])) {
			$class = new ClassObj(@$_REQUEST["cid"]);
		}
		if(!$class->loaded) {
			Yii::app()->user->setFlash('error','The permalink is malformed or the class does not exist.');
			$this->redirect(Yii::app()->createUrl('index'));
			exit;
		}
		$params["class"] = $class;
		$cs = Yii::app()->getClientScript();
		$cs->registerScriptFile("//".WEB_LIBRARY_PATH."jquery/modules/tipTip/jquery.tipTip.js");
		$cs->registerCssFile("//".WEB_LIBRARY_PATH."jquery/modules/tipTip/tipTip.css");
		
		$this->render("permalink",$params);
	}
	
	public function actionAboutUs()
	{
		$this->render("aboutus");
	}

	public function actionAddClass()
	{
		if(Yii::app()->user->isGuest) {
			$this->loginRedirect();
		}
        $user = new UserObj(Yii::app()->user->name);
        if($user->atleast_permission("manager") === false) {
            Yii::app()->user->setFlash("error","You do not have permission to access this part of the application.");
            $this->redirect(Yii::app()->createUrl('index'));
            exit;
        }
		
		$cs = Yii::app()->getClientScript();
		$cs->registerScriptFile("//".WEB_LIBRARY_PATH."jquery/modules/tokenInput/src/jquery.tokeninput.js");
		$cs->registerCssFile("//".WEB_LIBRARY_PATH."jquery/modules/tokenInput/styles/token-input-facebook.css");
        $cs->registerCssFile("//".WEB_LIBRARY_PATH."jquery/modules/toggler/style.css");
		$cs->registerScriptFile("//".WEB_LIBRARY_PATH."jquery/modules/toggler/jquery/iphone-style-checkboxes.js");
		$cs->registerScriptFile("//".WEB_LIBRARY_PATH."jquery/modules/tipTip/jquery.tipTip.js");
		$cs->registerScriptFile("//".WEB_LIBRARY_PATH."jquery/modules/sortElements/jquery.sortElements.js");
		$cs->registerCssFile("//".WEB_LIBRARY_PATH."jquery/modules/tipTip/tipTip.css");
		
		$params = array();
		
		$class = new ClassObj();
		$params["class"] = $class;
		$params["title"] = "Create a New Class";
		$params["edit"] = false;
		
		$this->render("editclass",$params);
		
	}

	public function actionEditClass()
	{
		if(Yii::app()->user->isGuest) {
			$this->loginRedirect();
		}
		if(!$this->authLevel(Yii::app()->user->name,2)) {
			Yii::app()->user->setFlash("error","You do not have permission to access this part of the application.");
			$this->redirect(Yii::app()->createUrl('index'));
		}
		
		$cs = Yii::app()->getClientScript();
		$cs->registerScriptFile("//".WEB_LIBRARY_PATH."jquery/modules/tokenInput/src/jquery.tokeninput.js");
		$cs->registerCssFile("//".WEB_LIBRARY_PATH."jquery/modules/tokenInput/styles/token-input-facebook.css");
		$cs->registerScriptFile("//".WEB_LIBRARY_PATH."jquery/modules/toggler/jquery/iphone-style-checkboxes.js");
		$cs->registerCssFile("//".WEB_LIBRARY_PATH."jquery/modules/toggler/style.css");
		$cs->registerScriptFile("//".WEB_LIBRARY_PATH."jquery/modules/tipTip/jquery.tipTip.js");
		$cs->registerScriptFile("//".WEB_LIBRARY_PATH."jquery/modules/sortElements/jquery.sortElements.js");
		$cs->registerCssFile("//".WEB_LIBRARY_PATH."jquery/modules/tipTip/tipTip.css");
		
		$params = array();
		if(!isset($_REQUEST["cid"])) {
			Yii::app()->user->setFlash("error","Can't edit nothing!");
			$this->redirect(Yii::app()->createUrl('index'));
			exit;
		}
		
		$class = new ClassObj($_REQUEST["cid"]);
		$user = Yii::app()->user->getState("_user");
		if(!$user->has_permission($class))
		{
			Yii::app()->user->setFlash("error","You do not have permission to edit this class.");
			$this->redirect(Yii::app()->createUrl('index')."#cl=".@$class->course->prefix.@$class->course->num);
		}
		$params["class"] = $class;
		$params["title"] = "Edit Class";
		$params["edit"] = true;
		
		$this->render("editclass",$params);
	}
	
	public function actionManage()
	{
		if(Yii::app()->user->isGuest) {
			$this->loginRedirect();
		}
        $user = new UserObj(Yii::app()->user->name);
		if($user->atleast_permission("manager") === false) {
			Yii::app()->user->setFlash("error","You do not have permission to access this part of the application.");
			$this->redirect(Yii::app()->createUrl('index'));
            exit;
		}
		
		$params = array();
		$conn = Yii::app()->db;
		$query = "
			SELECT DISTINCT		prefix
			FROM				{{courses}}
			WHERE				1=1
			ORDER BY			prefix ASC;
		";
		$result = $conn->createCommand($query)->queryAll();
		$params["prefixes"] = $result;
		
		$this->render("manage",$params);
	}
	
	public function actionUsers()
	{
		if(Yii::app()->user->isGuest) {
			$this->loginRedirect();
		}
		if(!$this->authLevel(Yii::app()->user->name,10)) {
			Yii::app()->user->setFlash("error","You do not have permission to access this part of the application.");
			$this->redirect(Yii::app()->createUrl('index'));
		}
		
		// Logic for rendering users here
		$usergroup = new GroupObj(new UserObj);
		$params["users"] = $usergroup->users;
		
		$cs = Yii::app()->getClientScript();
		$cs->registerScriptFile("//".WEB_LIBRARY_PATH."jquery/modules/cookie/jquery.cookie.js");
		$cs->registerScriptFile("//".WEB_LIBRARY_PATH."jquery/modules/tipTip/jquery.tipTip.js");
		$cs->registerCssFile("//".WEB_LIBRARY_PATH."jquery/modules/tipTip/tipTip.css");
		
		$this->render("users",$params);
	}
	
	public function actionAddUser()
	{
	    ini_set("display_errors",1);
        error_reporting(E_ALL);
		if(Yii::app()->user->isGuest) {
			$this->loginRedirect();
		}
		if(!$this->authLevel(Yii::app()->user->name,10)) {
			Yii::app()->user->setFlash("error","You do not have permission to access this part of the application.");
			$this->redirect(Yii::app()->createUrl('index'));
		}
		
		$params = array();
		$params["title"] = "Add New User";
		$params["editmode"] = false;  // Using the same view, indicate new user form
		
		if(isset($_POST) and !empty($_POST)) {
			$user = new UserObj($_POST["username"]);
			$user->set_state("new");
			foreach($_POST as $index=>$key) {
				$user->$index = $key;
			}
			
			if(!isset($user->adsync) and $user->password1 != "" and $user->password2 != "") {
				$user->password_hash = $user->create_hash($user->password1);
			}
			
			$user->permissions = array();
			if(isset($_POST["allowall"]) or isset($_POST["restrictall"])) {
				if(isset($_POST["allowall"])) {
					$perm = new PermissionObj();
					$perm->username = $user->username;
					$perm->permission = "";
					$perm->level = 2;
					$user->permissions[] = $perm;
				}
				if(isset($_POST["restrictall"])) {
					$user->permssions = array();
					$perm = new PermissionObj();
					$perm->username = $user->username;
					$perm->permission = "";
					$perm->level = 2;
					$user->permissions[] = $perm;
				}
			} else {
				$user->permissions = array();
				if(isset($_POST["allowed"])) {
					$perm = new PermissionObj();
					$perm->username = $user->username;
					$perm->permission = $_POST["allowed"];
					$perm->level = 1;
					$user->permissions[] = $perm;
				}
				if(isset($_POST["restricted"])) {
					$perm = new PermissionObj();
					$perm->username = $user->username;
					$perm->permission = $_POST["restricted"];
					$perm->level = 1;
					$user->permissions[] = $perm;
				}
			}
			if(!$user->save()) {
				Yii::app()->user->setFlash('error',$user->get_error());
			} else {
				Yii::app()->user->setFlash('success',"Successfully saved user <i>".$user->username."</i>");
				$this->redirect(Yii::app()->createUrl('users'));
				exit;
			}
			$params["user"] = $user;
		}
	
		$cs = Yii::app()->getClientScript();
		$cs->registerScriptFile("//".WEB_LIBRARY_PATH."jquery/modules/spass/jquery.spass.js");
		$cs->registerScriptFile("//".WEB_LIBRARY_PATH."jquery/modules/toggler/jquery/iphone-style-checkboxes.js");
		$cs->registerCssFile("//".WEB_LIBRARY_PATH."jquery/modules/toggler/style.css");
		
		$this->render('edituser',$params);
	}

	public function actionEditUser()
	{
		if(Yii::app()->user->isGuest) {
			$this->loginRedirect();
		}
		if(!$this->authLevel(Yii::app()->user->name,10)) {
			Yii::app()->user->setFlash("error","You do not have permission to access this part of the application.");
			$this->redirect(Yii::app()->createUrl('index'));
		}
		
		$params = array();
		$params["title"] = "Edit User";
		$params["editmode"] = true;  // Using the same view, indicate new user form
		
		$user = new UserObj($_REQUEST["id"]);
		
		if(isset($_POST) and !empty($_POST)) {
			foreach($_POST as $index=>$key) {
				$user->$index = $key;
			}
			
			if(!isset($user->adsync)) {
				$user->password_hash = $user->create_hash($user->password1);
			}
			
			$user->permissions = array();
			if(isset($_POST["allowall"]) or isset($_POST["restrictall"])) {
				if(isset($_POST["allowall"])) {
					$perm = new PermissionObj();
					$perm->username = $user->username;
					$perm->permission = "";
					$perm->level = 2;
					$user->permissions[] = $perm;
				}
				if(isset($_POST["restrictall"])) {
					$user->permssions = array();
					$perm = new PermissionObj();
					$perm->username = $user->username;
					$perm->permission = "";
					$perm->level = 2;
					$user->permissions[] = $perm;
				}
			} else {
				$user->permissions = array();
				if(isset($_POST["allowed"])) {
					$perm = new PermissionObj();
					$perm->username = $user->username;
					$perm->permission = $_POST["allowed"];
					$perm->level = 1;
					$user->permissions[] = $perm;
				}
				if(isset($_POST["restricted"])) {
					$perm = new PermissionObj();
					$perm->username = $user->username;
					$perm->permission = $_POST["restricted"];
					$perm->level = 1;
					$user->permissions[] = $perm;
				}
			}
			if(!$user->save()) {
				Yii::app()->user->setFlash('error',$user->get_error());
			} else {
				Yii::app()->user->setFlash('success',"Successfully saved user <i>".$user->username."</i>");
				$this->redirect(Yii::app()->createUrl('users'));
				exit;
			}
		}
		
		$params["user"] = $user;
	
		$cs = Yii::app()->getClientScript();
		$cs->registerScriptFile("//".WEB_LIBRARY_PATH."jquery/modules/spass/jquery.spass.js");
		$cs->registerScriptFile("//".WEB_LIBRARY_PATH."jquery/modules/toggler/jquery/iphone-style-checkboxes.js");
		$cs->registerCssFile("//".WEB_LIBRARY_PATH."jquery/modules/toggler/style.css");
		
		$this->render('edituser',$params);
	}

	public function actionRunOnce()
	{
		$this->render("runonce");
	}

	public function actionMaintenance()
	{
		$this->render("maintenance");
	}

	public function actionDeleteClass()
	{
		if(Yii::app()->user->isGuest) {
			$this->loginRedirect();
		}
		if(!$this->authLevel(Yii::app()->user->name,1)){
			Yii::app()->user->setFlash("error","You are not allowed to do that action with your permissions.");
			$this->redirect(Yii::app()->createUrl('index'));
			exit;
		}
		
		$class = new ClassObj($_REQUEST["cid"]);
		$user = Yii::app()->user->getState("_user");
		if($user->has_permission($class)) {
			if(!$class->loaded) {
				Yii::app()->user->setFlash("error","This class could not be found and is probably already deleted.");
			}
			if(!$class->delete()) {
				Yii::app()->user->setFlash("error",$class->get_error());
			} else {
				Yii::app()->user->setFlash("success","Class was successfully removed.");
			}
		} else {
			Yii::app()->user->setFlash("error","This class could not be found and is probably already deleted.");
		}
		$this->redirect(Yii::app()->createUrl('index'));
		exit;
	}

	/** AJAX FUNCTIONS **/
	
	public function action_remove_syllabus()
	{
		if(!$this->authLevel(Yii::app()->user->name,1)){
			Yii::app()->user->setFlash("error","You are not allowed to do that action with your permissions.");
			$this->redirect(Yii::app()->createUrl('index'));
			exit;
		}
		
		$syllabus = new SyllabusObj(@$_REQUEST["sid"]);
		if(!$syllabus->loaded) {
			return print "Could not load syllabus with id: ".$_REQUEST["sid"];
		}
		$syllabus->delete();
		
		return print 1;
	}
	
	public function action_preview_syllabus()
	{
		if(isset($_REQUEST["filename"],$_REQUEST["dept"])) {
			$width = (isset($_REQUEST["w"])) ? $_REQUEST["w"] : 600;
			$height = (isset($_REQUEST["h"])) ? $_REQUEST["h"] : 300;
			$dept = $_REQUEST["dept"];
			$filename = $_REQUEST["filename"];
			$user = Yii::app()->user->getState("_user");
			if(!$user->has_permission($dept)){
				return print "You do not have permission to view this syllabus.";
			}
			$file = "C:/archive/".$dept."/".$filename;
			$pathinfo = pathinfo($file);
			if($pathinfo["extension"]=="pdf"){
				return print '<embed src="http://compass.colorado.edu/archive/'.$dept.'/'.$filename.'#view=FitH" width="'.$width.'px" height="'.$height.'px"/>';
			}
			elseif($pathinfo["extension"]=="doc" or $pathinfo["extension"]=="docx") {
				return print '<iframe src="//docs.google.com/viewer?url=http%3A%2F%2Fcompass.colorado.edu%2Farchive%2F'.$dept.'%2F'.$filename.'&embedded=true" width="'.$width.'px" height="'.$height.'" style="border: none;"></iframe>';
			} elseif($pathinfo["extension"]=="txt" or $pathinfo["extension"]=="html"){
				return print file_get_contents($file);
			}
			return print "";
		}
		$syllabus = new SyllabusObj(@$_REQUEST["sid"]);
		if(!$syllabus->loaded) {
			return print "Could not load syllabus with id: ".$_REQUEST["sid"];
		}
		$class = new ClassObj($syllabus->classid);
		if(!$syllabus->loaded) {
			return print "Could not load class with id: ".$syllabus->classid;
		}
		$width = (isset($_REQUEST["w"])) ? $_REQUEST["w"]-50 : 600;
		$height = (isset($_REQUEST["h"])) ? $_REQUEST["h"]-70 : 300;
		if($syllabus->type=="pdf"){
			return print '<embed src="http://compass.colorado.edu/archive/'.$class->course->prefix.'/'.$syllabus->filename.'#view=FitH" width="'.$width.'px" height="'.$height.'px"/>';
		}
		elseif($syllabus->type=="doc" or $syllabus->type=="docx") {
			return print '<iframe src="//docs.google.com/viewer?url=http%3A%2F%2Fcompass.colorado.edu%2Farchive%2F'.$class->course->prefix.'%2F'.$syllabus->filename.'&embedded=true" width="'.$width.'px" height="'.$height.'" style="border: none;"></iframe>';
		}
		return print "";
	}
	
	public function action_load_course_title()
	{
		if(!$this->authLevel(Yii::app()->user->name,1)){
			Yii::app()->user->setFlash("error","You are not allowed to do that action with your permissions.");
			$this->redirect(Yii::app()->createUrl('index'));
			exit;
		}
		
		$course = new CourseObj();
		$course->prefix = $_REQUEST["prefix"];
		$course->num = $_REQUEST["num"];
		$course->load();
		if(!$course->loaded) return print "";
		
		return print $course->title;
	}
	
	public function action_add_instructor()
	{
		if(!$this->authLevel(Yii::app()->user->name,1)){
			Yii::app()->user->setFlash("error","You are not allowed to do that action with your permissions.");
			$this->redirect(Yii::app()->createUrl('index'));
			exit;
		}
		
		$instructor = new InstructorObj();
		$instructor->name = $_REQUEST["name"];
		if(!$instructor->save()) {
			return print $instructor->get_error();
		}
		return print json_encode(array("id"=>$instructor->instrid,"name"=>$instructor->name));
	}
	
	public function action_load_instructors()
	{
		
		$conn = Yii::app()->db;
		$query = "
			SELECT		instrid, name
			FROM		{{instructors}}
			WHERE		name LIKE :name
			ORDER BY	lastname ASC
			LIMIT		0,10;
		";
		$command = $conn->createCommand($query);
		$command->bindValue(":name","%".@$_REQUEST["q"]."%");
		$result = $command->queryAll();
		
		$names = array();
		foreach($result as $row) {
			$names[] = array(
				"name"		=> $row["name"],
				"id"		=> $row["instrid"],
			);
		}
		
		return print json_encode($names);
	}
	
	public function action_load_class_table()
	{
		$cl = $_REQUEST["cl"];
		if(is_null($cl) or empty($cl) or $cl == "") return print "<td colspan='4'>Unknown class lookup</td>";
		
		$course = new CourseObj();
		list($course->prefix,$course->num) = str_split($cl,4);
		$course->load();
		if(!$course->loaded) {
			 return print "<td colspan='4'>Unknown class lookup</td>";
		}
		
		$classes = $course->get_classes();
		
		ob_start();
    	if(!Yii::app()->user->isGuest) {
    		$user = Yii::app()->user->getState("_user");
			if($user->has_permission($course) and $user->permission_level > 1) {
		?>
		<tr>
			<td colspan="4" style="padding:3px;padding-left:10px;background-color:#efefef;">
				<a href="<?=Yii::app()->createUrl('addclass');?>?prefix=<?=$course->prefix;?>&num=<?=$course->num;?>">
					<img src="<?=StdLib::load_image_href("plus.png");?>" width="13px" height="13px" style="position:relative;top:2px;left:0px;" />
					Add Class to Course</a>
			</td>
		</tr>
		<?php
			}
		}
		
		$count=0; 
		foreach($classes as $class):
			if(!$class->has_syllabus() and $class->website=="" and Yii::app()->user->isGuest) continue;
			$count++;
			if($class->subtitle!=""):
			?>
			<tr class="<?=($count%2==0)?"odd":"even";?>">
				<td colspan="4" style="height:20px;font-size:11px;font-style: italic;"><span style="font-style:normal;">Class Title:</span> <?=$class->subtitle;?></td>
			</tr>
			<?php endif; ?>
		<tr class="<?=($count%2==0)?"odd":"even";?>">
			<td><?=$class->year." ".$class->term;?></td>
			<td class="calign"><?=$class->section;?></td>
			<td><?=$class->print_instructors();?></td>
			<td class="calign">
		    	<?php if($class->has_syllabus()): ?>
			    	<?php if(StdLib::on_campus() or $class->offcampus==1): ?>
					    <div class="admin-button ui-widget-header active download" title="Download Syllabus" classid="<?=$class->classid;?>">
					        <div class="icon"><?=StdLib::load_image("arrow_down.png","13px","13px");?></div>
					    </div>
					<?php elseif(!StdLib::on_campus() and $class->offcampus==0): ?>
					    <div class="admin-button ui-widget-header" title="Syllabus may only be downloaded from on-campus or using CU's VPN." classid="<?=$class->classid;?>">
					        <div class="icon"><?=StdLib::load_image("lock.png","13px","13px");?></div>
					    </div>
				    <?php endif; ?>
		        <?php elseif($class->website=="" and !Yii::app()->user->isGuest): ?>
			    <div class="admin-button ui-widget-header missing" title="Syllabus Missing">
			        <div class="icon"><?=StdLib::load_image("attention.png","13px","13px");?></div>
			    </div>
		        <?php endif;?>
			    <?php if($class->website!=""): ?>
			    <div class="admin-button ui-widget-header active website" title="Open class website in a new tab">
			        <div class="icon"><?=StdLib::load_image("gowebsite.png","13px","13px");?></div>
			        <a href="<?=$class->website;?>"></a>
			    </div>
			    <?php endif; ?>
			    <div class="admin-button ui-widget-header active permalink" title="Permalink" classid="<?=$class->classid;?>">
			        <div class="icon"><?=StdLib::load_image("anchor.png","13px");?></div>
			    </div>
			    <?php 
		    	if(!Yii::app()->user->isGuest) {
		    		$user = Yii::app()->user->getState("_user");
					if($user->has_permission($class) and $user->permission_level > 1):
				?>
			    <div class="admin-button ui-widget-header active edit" title="Edit Class" classid="<?=$class->classid;?>">
			        <div class="icon"><?=StdLib::load_image("pencil_edit.png","13px","13px");?></div>
			    </div>
			    <?php
					endif;
		    	}
		    	?>
			</td>
		</tr>
		<?php endforeach;
		if($count==0) :
		?>
		<tr>
			<td colspan="4" style="padding:10px;">
				No classes under this course.
			</td>
		</tr>
		<?php
		endif;
		$contents = ob_get_contents();
		ob_end_clean();
		
		return print $contents;
	}

	public function action_download()
	{
		if(isset($_REQUEST["cid"]) or isset($_REQUEST["sid"]))
		{
			if(isset($_REQUEST["sid"])) {
				$syllabus = new SyllabusObj($_REQUEST["sid"]);
				$class = new ClassObj($syllabus->classid);
			} else {
				$class = new ClassObj($_REQUEST["cid"]);
				if(!$class->has_primary_syllabus()) {
					$syllabus = $class->get_first_syllabus();
				} else {
					$syllabus = $class->get_primary_syllabus();
				}
			}
			$file = Yii::app()->params["syllabus_dir"].$class->course->prefix."/".$syllabus->filename;
			if(substr($file,-4,4)=="PDF ")
			{
				$class->filename = substr($class->filename,0,-4).".pdf";
				$class->save();
				$file = Yii::app()->params["syllabus_dir"].$class->course->prefix."/".$syllabus->filename;
			}
			if(!$syllabus->valid()) {
				Yii::app()->user->setFlash("error","Could not find syllabus.");
				echo "  <script type='text/javascript'>self.history.go(-1);</script>";
				exit;
			}
			
			$dl = new DownloadObj();
			$dl->classid = $class->classid;
			$dl->syllabusid = $syllabus->syllabusid;
			$dl->ip_address = $_SERVER["REMOTE_ADDR"];
			$dl->logged_in = (Yii::app()->user->isGuest) ? 0 : 1;
			$dl->username = Yii::app()->user->name;
			if(!$dl->save()) {
				Yii::app()->user->setFlash("error",$dl->get_error());
				exit;
			}
		} else if(isset($_REQUEST["dept"],$_REQUEST["filename"])) {
			$file = Yii::app()->params["syllabus_dir"].$_REQUEST["dept"]."/".$_REQUEST["filename"];	
			if(!(is_file($file) and filesize($file)>0 and is_readable($file))) {
				Yii::app()->user->setFlash("error","Could not find syllabus: ".$file);
				echo "  <script type='text/javascript'>self.history.go(-1);</script>";
				exit;
			}
		}
    		
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename='.basename($file));
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . filesize($file));
		ob_clean();
		ob_flush();
		flush();
		readfile($file);
		exit;
	}
	
	public function action_load_syllabus_table()
	{
		
		$user = Yii::app()->user->getState("_user");
		if(!$user->loaded) { return json_encode("User not defined."); }
		
		$page = @$_REQUEST["page"];
		$pagelength = isset($_REQUEST["pl"])?$_REQUEST["pl"]:10;
		$sort = @$_REQUEST["sort"];
		$query_ = @$_REQUEST["f"];
		$tag = @$_REQUEST["tag"];
		$letter = @$_REQUEST["letter"];
		$dept = @$_REQUEST["dept"];
		$sf = @$_REQUEST["sf"];
		if($dept=="all") {
			$dept = "";
		}
		
		$start = ($page-1)*$pagelength;
		$finish = $start + $pagelength;
		
		if($query_!="") {
			$sterms = explode(" ",$query_);
			$where = array();
			foreach($sterms as $index=>$term) {
				$where[] = "
					{{courses}}.title 			LIKE :term_$index OR 
					{{instructors}}.firstname 	LIKE :term_$index OR 
					{{instructors}}.lastname 	LIKE :term_$index OR
					{{courses}}.prefix			LIKE :term_$index OR
					{{classes}}.term			LIKE :term_$index OR
					{{classes}}.year			LIKE :term_$index OR
					{{courses}}.num				=	 :term2_$index OR
					{{classes}}.section 		= 	 :term2_$index";
			}
			$where = " AND (".implode(" AND ",$where).")";
		}
		
		if($dept!="") {
			$where .= " AND {{courses}}.prefix = :dept_prefix";
		}
		
		$limit = "LIMIT		$start,$pagelength;";
		if($sf == 1) {
			$limit = "";
		}
		
		if($sort=="")
		{
			$sort = "{{courses}}.prefix ASC, {{courses}}.num ASC";
		} 
		else {
			if($sort=="course") {
				$sort = "{{courses}}.title ASC";
			} else if($sort=="course_r") {
				$sort = "{{courses}}.title DESC";
			}
		}
		
		
		$conn = Yii::app()->db;
		$query = "
			SELECT		classid
			FROM		{{classes}} AS classes, {{courses}} AS courses, {{instructors}} AS instructors
			WHERE		courses.courseid = classes.courseid
			AND			instructors.instrid = classes.instrid
			$where
			$limit
			ORDER BY	{{courses}}.prefix ASC, {{courses}}.num ASC
		";
		$command = $conn->createCommand($query);
		foreach($sterms as $index=>$term) {
			$command->bindValue(":term_$index","%".$term."%");
			$command->bindParam(":term2_$index",$term);
		}
		if($dept!="") {
			$command->bindParam(":dept_prefix",$dept);
		}
		$result = $command->queryAll();
		
		$classes = array();
		foreach($result as $row) {
			$classes[] = new ClassObj($row["classid"]);
		}
		
		if($sf==1) {
			$totalcount = "all"; 
		}
		else {
			$query = "
				SELECT		COUNT(*) as classcount
				FROM		{{classes}} AS classes, {{courses}} AS courses, {{instructors}} AS instructors
				WHERE		courses.courseid = classes.courseid
				AND			instructors.instrid = classes.instrid
				$where;
			";
			$command = $conn->createCommand($query);
			foreach($sterms as $index=>$term) {
				$command->bindValue(":term_$index","%".$term."%");
				$command->bindParam(":term2_$index",$term);
			}
			if($dept!="") {
				$command->bindParam(":dept_prefix",$dept);
			}
			$totalcount = $command->queryScalar();
		}
		
		$classes_count = $totalcount;
		
		ob_start();
		?>
			<div class="contacts-table">
			<table cellpadding="0" cellspacing="0">
				<thead>
					<tr>
						<th class="first check"></th>
						<th class="course <?=($sort=="course" or $sort=="course_r")?"current":"";?>">
							<a href="#" sort="course<?=($sort=="course")?"_r":""?>" class="sort<?=($sort=="name" or $sort=="name_r")?" current":"";?><?=($sort=="course")?" descending":" ascending"?>" title="Course Designation">Course</a>
						</th>
						<th class="section">
							<a href="#" sort="section" title="Class Section">Section</a>
						</th>
						<th class="coursename">
							<a href="#" sort="coursename" title="Course name">Course Name</a>
						</th>
						<th class="term">
							<a href="#" sort="term" title="Term">Term</a>
						</th>
						<th class="instructor">
							<a href="#" sort="instructor" title="Instructor">Instructor</a>
						</th>
						<th class="last edit"></th>
					</tr>
				</thead>
				<tbody>
					<?php if(count($classes)>0): ?>
					<?php 
						$flag = false;
						$count = 0;
						if($sf==1 or $sf==2) {
							$totalcount = 0;
						}
						foreach($classes as $class): 
							$count++;
							if($class->has_syllabus() and $sf == 1) continue;
							if(!$class->has_syllabus() and $sf == 2) continue;
							if($count<=($start*$pagelength)) continue;
							if($count>(($start+1)*$pagelength)) break;
							
							$flag = true;
							
							if($sf == 1) {
								$totalcount++;
							}
							$subtitle = "";
							if($class->subtitle != "") {
								$subtitle = ": ".$class->subtitle;
							}
					?>
					<tr classid="<?=$class->classid;?>" ctype="class">
						<td class="check"><input class="class_checked" type="checkbox" value="<?=$class->classid;?>" /></td>
						<td class="course" classid="<?=$class->classid;?>">
							<div class="box">
								<?=$class->course->prefix." ".$class->course->num;?>
							</div>
						</td>
						<td class="section">
							<div class="box">
								<?=$class->section;?>
							</div>
						</td>
						<td class="coursename">
							<div class="box">
								<a href="#"><?=$class->course->title.$subtitle;?></a>
							</div>
						</td>
						<td class="term">
							<div class="box">
								<?=$class->term." ".$class->year;?>
							</div>
						</td>
						<td class="professor">
							<div class="box">
								<?=$class->instructor->firstname." ".$class->instructor->lastname;?>
							</div>
						</td>
						<td class="edit">
							<a class="delete" href="#delete" title="Delete this class">Delete</a>
						</td>
					</tr>
					<?php endforeach; ?>
					<?php if(!$flag): ?>
					<tr>
						<td colspan="5">
							<div class="empty-table">
								No classes found.
							</div>
						</td>
					</tr>
					<?php endif; ?>
					<?php else: ?>
					<tr>
						<td colspan="5">
							<div class="empty-table">
								No classes found.
							</div>
						</td>
					</tr>
					
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();
		
		
		ob_start();
		?>
			<?php if($start==0): ?>
				<span class="disabled first_page">First</span>
				<span class="disabled prev_page">Prev</span>
			<?php else: ?>
				<a href="#" class="first_page">First</a>
				<a href="#" class="prev_page">Prev</a>
			<?php endif; ?>
			
			<span class="counts current"><?=$start?>-<?=$finish?> of <?=$totalcount;?></span>
			
			<?php if($finish==$classes_count): ?>
				<span class="disabled next_page">Next</span>
				<span class="disabled last_page">Last</span>
			<?php else: ?>
				<a href="#" class="next_page">Next</a>
				<a href="#" class="last_page">Last</a>
			<?php endif; ?>
		
		<?php
		$paging = ob_get_contents();
		ob_end_clean();
		
		$return = array();
		$return["contents"] = $contents;
		$return["paging"] = $paging;
		$return["start"] = $start;
		$return["finish"] = $finish;
		$return["total"] = $classes_count;
		$return["ended"] = ($finish==$classes_count);
		
		print json_encode($return);
		return;
	}
	
	/** INTERNAL FUNCTIONS **/
	
	private function loginRedirect()
	{
		$redirect = "http".((isset($_SERVER["HTTPS"]) and $_SERVER["HTTPS"]=="on")?"s":"")."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$login = Yii::app()->createUrl('login');
		$url = $login."?redirect=".urlencode($redirect);
		$this->redirect($url);
		exit;
	}
	
	private function authLevel($username,$min_level=1)
	{
		$user = new UserObj($username);
		if(!$user->loaded or $user->permission_level < $min_level) {
			return false;
		}
		
		return true;
	}
	
	private function makeSSL()
	{
		if($_SERVER['SERVER_PORT'] != 443) {
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
			exit();
		}
	}

	private function makeNonSSL()
	{
		if($_SERVER['SERVER_PORT'] == 443) {
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
			exit();
		}
	}
}