<?php

require "BaseController.php";

class SiteController extends BaseController
{
	/** DEFAULT ACTIONS **/
	public function actionIndex()
	{		
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

	/** NORMAL PAGES **/
    public function actionAdd() {
        $this->noGuest();

        # See if a topic/link was submitted
        if(isset($_POST["uniqueformid"],$_POST["datetime"])) {
            StdLib::Functions();
            try {
                if(is_valid_form_id($_POST["uniqueformid"], $_POST["datetime"])) {
                    
                    # Grab the syllabus file and start up the File System
                    $file = $_FILES["syllabus"];
                    $fileparts = pathinfo($file["name"]);
                    $fs = new FileSystem();
                    if(!$fs->check_valid_extension($fileparts["extension"])) {
                        throw new Exception("Extension was invalid: ".$fileparts["extension"]);
                    }
                    $fs->process_file_upload($file);
                    $fs->upload_to(LOCAL_ARCHIVE."temp/");
                    if(!$fs->is_uploaded()) {
                        throw new Exception("Could not upload file. ".$fs->get_error());
                    }
                    $file_locations = $fs->get_files_uploaded_location();
                    $file_location = @$file_locations[0];
                    
                    $sections = explode(",",$_POST["section"]);
                    # Check the User permissions.
                    # For now we are allowing any managers to have access to upload syllabi to the Archive
                    $user = new UserObj(Yii::app()->user->name);
                    if(!$user->atleast_permission("manager")) {
                        throw new Exception("You cannot add syllabi at this time. Your permissions restrict your access.");
                    }
                    # See if we saved a syllabus (maybe multiple sections and one section already exists)
                    $saved_at_least_one = FALSE;
                    
                    # Loop through each section and save each as a separate class
                    foreach($sections as $section) {
                        $section = trim($section);
                        if(!preg_match("/[0-9]{3}/",$section)) {
                            continue;
                        }
                        $CS = new CourseSyllabusObj();
                        $CS->prefix = $_POST["prefix"];
                        $CS->num = $_POST["num"];
                        $CS->title = $_POST["title"];
                        $CS->special_topics_title = $_POST["special_topics_title"];
                        $CS->term = $_POST["term"];
                        $CS->year = $_POST["year"];
                        $CS->recitation = $_POST["recitation"];
                        $CS->restricted = $_POST["restricted"];
                        $CS->section = $section;

                        # See if this Course Syllabus exists and if we have permission to overwrite
                        $CS->id = $CS->generate_id();
                        $CS->load();
                        if($CS->loaded and $_POST["overwrite"] == "false") {
                            Yii::app()->user->setFlash('warning',"One Course Syllabus section already exists. The system skipped overwriting this course syllabus.");
                            continue;
                        }
                        
                        # Add Instructors to Course Syllabus
                        $instructors = explode("\n",$_POST["instructors"]);
                        foreach($instructors as $fullname) {
                            $instructor = new InstructorObj();
                            $instructor->name = $fullname;
                            $instructor->load();
                            if(!$instructor->loaded) {
                                if(!$instructor->save()) {
                                    Yii::app()->user->setFlash("warning","Could not save instructor <i>".$instructor->name."</i> for some reason. ".$instructor->get_error());
                                    continue;
                                }
                            }
                            $CS->instructors[] = $instructor->instrid;
                        }

                        # Save!
                        if(!$CS->save()) {
                            throw new Exception("Could not save Course Syllabus: ".$CS->get_error());
                        }
                        
                        # Move file to permanent home in the archive
                        $fileName = $CS->id.".".$fileparts["extension"];
                        copy($file_location, ROOT."/archive/".$fileName);

                        # If the user selected OCR, then copy the file to the OCR directory
                        if($_POST["ocr"] == "yes" && $fileparts["extension"] == "pdf") {
                            // Define OCR api location based on whether we're on the production or the development server
                            $ocr_api = ($_SERVER["SERVER_NAME"] == "assettdev.colorado.edu" or $_SERVER["SERVER_NAME"] == "assetttest.colorado.edu") ? "http://assettdev.colorado.edu" : "http://compass.colorado.edu";
                            $url = $ocr_api.OCR_API.'uploadfile';
                            $data = array('file_dir' => ROOT."/archive/", 'file_name' => $fileName);
                            $options = array(
                                    'http' => array(
                                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                    'method'  => 'POST',
                                    'content' => http_build_query($data),
                                )
                            );

                            $context  = stream_context_create($options);
                            $result = json_decode(file_get_contents($url, false, $context));
                            if(isset($result->id)) {
                                pclose(popen("start php ".ROOT."/protected/models/system/OCRCheck.php ".ROOT."/archive/ ".$result->id." ".$_SERVER["SERVER_NAME"], 'w'));
                            }
                        }
                        else if($fileparts["extension"] == "docx") {

                            $content = read_zipped_xml(ROOT."/archive/".$fileName,"word/document.xml");

                            Yii::app()->db->createCommand()
                                ->update("course_syllabi",
                                    array(
                                        "content" => $content
                                    ),
                                    "id=:id",
                                    array(":id"=> $CS->id)
                            );
                        }
                        
                        # Made it to here? We must have saved at least one course syllabus!
                        $saved_at_least_one = TRUE;
                    }
                }
                else {
                    throw new Exception("Malformed form ID.");
                }
                
                # Let's set a message that we saved at least one file
                if($saved_at_least_one) {
                    Yii::app()->user->setFlash("success","Successfully saved course syllabus to the archive!");
                    if($_POST["savetype"] == "exit") {
                        $this->redirect("index");
                        exit;
                    }
                }
                else {
                    Yii::app()->user->setFlash("info","Did not save any course syllabi.");
                }
                
                # Remove the temporary file
                if(is_file($file_location)) {
                    unlink($file_location);
                }
            }
            # Exception handling here
            catch(Exception $e) {
                Yii::app()->user->setFlash("warning",$e->getMessage());
            }
        }
        
        $this->render('addsyllabus');
    }

    public function actionEdit() {
        $this->noGuest();
        
        if(!isset($_REQUEST["id"])) {
            Yii::app()->user->setFlash('warning','Cannot edit: Invalid course syllabus ID.');
            $this->redirect('index');
            exit;
        }
        
        $CS = new CourseSyllabusObj($_REQUEST["id"]);
        if(!$CS->loaded) {
            Yii::app()->user->setFlash('warning','Could not load Course Syllabus. Something went really wrong.');
            $this->redirect('index');
            exit;
        }
        $syllabus = LOCAL_ARCHIVE.$CS->id;
        $CS->find_syllabus_links();
        $syllabus_links = $CS->syllabus_links;
        
        # See if a topic/link was submitted
        if(isset($_POST["uniqueformid"],$_POST["datetime"])) {
            StdLib::Functions();
            try {
                if(is_valid_form_id($_POST["uniqueformid"], $_POST["datetime"])) {
                    
                    unset($CS->id);
                    # Grab the syllabus file and start up the File System
                    $file = $_FILES["syllabus"];
                    # If the user added a file, let's continue with upload
                    if($file["size"] != 0) {
                        $fileparts = pathinfo($file["name"]);
                        $fs = new FileSystem();
                        if(!$fs->check_valid_extension($fileparts["extension"])) {
                            throw new Exception("Extension was invalid: ".$fileparts["extension"]);
                        }
                        $fs->process_file_upload($file);
                        $fs->upload_to(LOCAL_ARCHIVE."temp/");
                        if(!$fs->is_uploaded()) {
                            throw new Exception("Could not upload file. ".$fs->get_error());
                        }
                        $file_locations = $fs->get_files_uploaded_location();
                        $file_location = @$file_locations[0];
                    }
                    
                    $sections = explode(",",$_POST["section"]);
                    # Check the User permissions.
                    # For now we are allowing any managers to have access to upload syllabi to the Archive
                    $user = new UserObj(Yii::app()->user->name);
                    if(!$user->atleast_permission("manager")) {
                        throw new Exception("You cannot add syllabi at this time. Your permissions restrict your access.");
                    }
                    # See if we saved a syllabus (maybe multiple sections and one section already exists)
                    $saved_at_least_one = FALSE;
                    
                    # Loop through each section and save each as a separate class
                    foreach($sections as $section) {
                        
                        $section = trim($section);
                        if(!preg_match("/[0-9]{3}/",$section)) {
                            continue;
                        }
                        $CS = new CourseSyllabusObj($_REQUEST["id"]);
                        $CS->section = $section;
                        $CS->id = $CS->generate_id();
                        $CS->load();
                        
                        $CS->title = $_POST["title"];
                        $CS->special_topics_title = $_POST["special_topics_title"];
                        $CS->recitation = $_POST["recitation"];
                        $CS->restricted = $_POST["restricted"];
                        $CS->section = $section;
                        
                        # Add Instructors to Course Syllabus
                        $instructors = explode("\n",$_POST["instructors"]);
                        foreach($instructors as $fullname) {
                            $instructor = new InstructorObj();
                            $instructor->name = $fullname;
                            $instructor->load();
                            if(!$instructor->loaded) {
                                if(!$instructor->save()) {
                                    Yii::app()->user->setFlash("warning","Could not save instructor <i>".$instructor->name."</i> for some reason. ".$instructor->get_error());
                                    continue;
                                }
                            }
                            $CS->instructors[] = $instructor->instrid;
                        }
                        
                        $CS->id = $CS->generate_id();
                        $CS->find_syllabus_links();
                        
                        if(!$CS->has_syllabus_file()) {
                            foreach($syllabus_links as $ext => $link) {
                                if(!is_null($link)) {
                                    copy($syllabus.".".$ext, LOCAL_ARCHIVE.$CS->id.".".$ext);
                                }
                            }
                        }
                        
                        # Save!
                        if(!$CS->save()) {
                            throw new Exception("Could not save Course Syllabus: ".$CS->get_error());
                        }
                        
                        # If the user added a file, let's continue with upload
                        if($file["size"] != 0) {
                            # Move file to permanent home in the archive
                            $fileName = $CS->id.".".$fileparts["extension"];
                            copy($file_location, ROOT."/archive/".$fileName);

                            # If the user selected OCR, then copy the file to the OCR directory
                            if($_POST["ocr"] == "yes" && $fileparts["extension"] == "pdf") {
                                // Define OCR api location based on whether we're on the production or the development server
                                $ocr_api = ($_SERVER["SERVER_NAME"] == "assettdev.colorado.edu" or $_SERVER["SERVER_NAME"] == "assetttest.colorado.edu") ? "http://assettdev.colorado.edu" : "http://compass.colorado.edu";
                                $url = $ocr_api.OCR_API.'uploadfile';
                                $data = array('file_dir' => ROOT."/archive/", 'file_name' => $fileName);
                                $options = array(
                                    'http' => array(
                                        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                        'method'  => 'POST',
                                        'content' => http_build_query($data),
                                    )
                                );

                                $context  = stream_context_create($options);
                                $result = json_decode(file_get_contents($url, false, $context));
                                if(isset($result->id)) {
                                    pclose(popen("start php ".ROOT."/protected/models/system/OCRCheck.php ".ROOT."/archive/ ".$result->id." ".$_SERVER["SERVER_NAME"], 'w'));
                                }
                            }
                            else if($fileparts["extension"] == "docx") {

                                $content = read_zipped_xml(ROOT."/archive/".$fileName,"word/document.xml");

                                Yii::app()->db->createCommand()
                                    ->update("course_syllabi",
                                        array(
                                            "content" => $content
                                        ),
                                        "id=:id",
                                        array(":id"=> $CS->id)
                                    );
                            }
                        }
                        
                        # Made it to here? We must have saved at least one course syllabus!
                        $saved_at_least_one = TRUE;
                    }
                }
                else {
                    throw new Exception("Malformed form ID.");
                }
                
                # Let's set a message that we saved at least one file
                if($saved_at_least_one) {
                    Yii::app()->user->setFlash("success","Successfully saved course syllabus to the archive!");
                    if($_POST["savetype"] == "exit") {
                        $this->redirect("index");
                        exit;
                    }
                    else {
                        $this->redirect(Yii::app()->createUrl('course')."?prefix=".$CS->prefix."&num=".$CS->num);
                        exit;
                    }
                }
                else {
                    Yii::app()->user->setFlash("info","Did not save any course syllabi.");
                }
                
                # Remove the temporary file
                if(isset($file_location) and is_file($file_location)) {
                    unlink($file_location);
                }
            }
            # Exception handling here
            catch(Exception $e) {
                Yii::app()->user->setFlash("warning",$e->getMessage());
            }
        }
        
        $this->render('editsyllabus', array("CS"=>$CS));
    }
    
	public function actionCourse()
    {
        if(isset($_GET["prefix"],$_GET["num"])) {
            $params["prefix"] = $_GET["prefix"];
            $params["num"] = $_GET["num"];
            $this->render("course", $params);
        } else {
            Yii::app()->user->setFlash("warning","You must select a course in order to view its syllabi.");
            $this->redirect(Yii::app()->homeUrl);
        }
    }
	
	public function actionSearch()
	{
		$this->render("search");
	}

	public function actionAboutUs()
	{
		$this->render("aboutus");
	}
	
	public function actionRunOnce()
	{
	    if(!StdLib::is_programmer()) {
	        Yii::app()->user->setFlash("error","You do not have access to this page.");
	        $this->redirect('index');
            exit;
	    }
		$this->render("runonce");
	}

	public function actionMaintenance()
	{
		$this->render("maintenance");
	}
}