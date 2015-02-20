<?php

class FileSystem {
    
    public $FILES = array();
    public $CURRENT_FILE = null;
    public $ERROR = false;
    public $ERROR_MSG = "";
    public $STATUS;
    
    
    private $VALID_EXTENSIONS = array(
        "pdf",
        "doc",
        "docx"
    );
    
    const UNCHECKED = 0;
    const ERROR     = 1;
    const READY     = 2;
    const UPLOADED  = 3;
    
    public function __construct() {
        $this->STATUS = Self::UNCHECKED;
    }
    
    protected function set_error($message) {
        $this->ERROR = true;
        $this->STATUS = self::ERROR;
        $this->ERROR_MSG = $message;
        return true;
    }
    
    public function get_error() {
        return $this->ERROR_MSG;
    }
    
    public function check_valid_extension($extension) {
        return in_array($extension,$this->VALID_EXTENSIONS);
    }
    
    public function process_file_upload($file) {
        $this->FILES[] = $file;
        $this->STATUS = ($this->check_file($file)) ? self::READY : self::ERROR;
    }
    
    protected function check_file($file) {
        # Check the size of the file
        if($file["size"] == 0) {
            return !$this->set_error("File size is empty.");
        }
        # Check the error flag
        switch($file["error"]) {
            case UPLOAD_ERR_OK: return true;
            case UPLOAD_ERR_INI_SIZE:
                return !$this->set_error("The uploaded file exceeds the maximum filesize upload limit.");
            break;
            case UPLOAD_ERR_FORM_SIZE:
                return !$this->set_error("The uploaded file exceeds the maximum filesize upload limit defined in the form.");
            break;
            case UPLOAD_ERR_PARTIAL:
                return !$this->set_error("The uploaded file was only partially uploaded.");
            break;
            case UPLOAD_ERR_NO_FILE:
                return !$this->set_error("No file was uploaded");
            break;
            case UPLOAD_ERR_NO_TMP_DIR:
                return !$this->set_error("Missing a temporary folder.");
            break;
            case UPLOAD_ERR_CANT_WRITE:
                return !$this->set_error("Failed to write file to disk.");
            break;
            case UPLOAD_ERR_EXTENSION:
                return !$this->set_error("A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop. Please check enabled extensions.");
            break;
            default:
                return !$this->set_error("Unknown error has occured: ".$file["error"]);
            break;
        }
        # Check temp file exists
        if(!is_file($file["tmp_name"])) {
            return !$this->set_error("Temp file cannot be found.");
        }
        return true;
    }

    public function is_ready() {
        return $this->STATUS == self::READY;
    }
    
    public function is_uploaded() {
        return $this->STATUS == self::UPLOADED;
    }

    public function upload_to($location) {
        if(!$this->is_ready()) {
            return false;
        }
        if(substr($location,-1,1) != "/") {
            $location .= "/";
        }
        try {
            foreach($this->FILES as $index => $file) {
                if(!move_uploaded_file($file["tmp_name"], $location.$file["name"])) {
                    throw new Exception("Could not upload file. ".$file["tmp_name"]." to location: ".$location.$file["name"]);
                }
                $this->FILES[$index]["file_location"] = $location.$file["name"];
            }
            $this->STATUS = self::UPLOADED;
        }
        catch (Exception $e) {
            return !$this->set_error($e);
        }
        return true;
    }
    
    public function get_files_uploaded_location() {
        $locations = array();
        foreach($this->FILES as $index => $file) {
            $locations[] = @$file["file_location"];
        }
        return $locations;
    }
}
