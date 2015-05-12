<?php

DEFINE("TIMEOUT", 15 * 60);    // Number of seconds to run before timing out and exiting
DEFINE("CHECK_FREQ", 60);      // Number of seconds to wait between each 

// Development and Production OCR Servers
DEFINE("DEV_OCR", "http://assettdev.colorado.edu/ocr/api/");
DEFINE("PROD_OCR", "http://compass.colorado.edu/ocr/api/");

// If the file_id is not provided, throw exception
if(!isset($argv[1], $argv[2], $argv[3])) {
    writeToLog("OCRCheck started with invalid or missing arguments.");
    throw new InvalidArgumentException("Incorrect usage. Usage: php OCRCheck.php destination_dir file_id server_name");
}

// Define OCR api location based on whether we're on the production or the development server
$ocr_api = ($argv[3] == "assettdev.colorado.edu" or $argv[3] == "assetttest.colorado.edu") ? DEV_OCR : PROD_OCR;

$destination_dir = $argv[1];
$file_id = $argv[2];
$url = $ocr_api."filestatus?file_id=".$file_id;
$startTime = microtime(true);
$success = false;

writeToLog("Starting OCRCheck on file ".$file_id.".");

// Instantiate cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// Loop until success breaks loop or loop times out
while(!$success && (microtime(true) - $startTime) < TIMEOUT) {
    // Get and decode file status from API
    $response = json_decode(curl_exec($ch));

    // If the file status is complete, then set $success to true
    if(isset($response->$file_id->status) && $response->$file_id->status == "Completed")
        $success = true;
}

if($success) {
    writeToLog("OCR API returned completed for file ".$file_id.".");

    // If successful, replace the old file with the new one
    $downloadUrl = $ocr_api."downloadfile?file_id=".$file_id;

    // Change cURL URL and get file path from API
    curl_setopt($ch, CURLOPT_URL, $downloadUrl);
    $response = json_decode(curl_exec($ch));

    // Check if the response contains a valid file path
    if(isset($response->file_path, $response->file_name) && is_file($response->file_path.$response->file_name)) {
        $full_path = $response->file_path.$response->file_name;
        $copied = copy($full_path, $destination_dir.$response->file_name);
        writeToLog("OCRCheck copied file ".$file_id.".");

        // Get text if Syllabus is a PDF
        $path_parts  = pathinfo($full_path);
        if(strtolower($path_parts['extension']) == "pdf") {
            $textFile = $response->file_path.$path_parts['filename'] . ".txt";
            writeToLog($textFile);

            // Save text to database
            persistSyllabusContent($textFile);
        }

        // Remove file from OCRout folder
        $removeUrl = $ocr_api."removefile?file_id=".$file_id;
        curl_setopt($ch, CURLOPT_URL, $removeUrl);
        $response = curl_exec($ch);
        writeToLog("Response: " . $response ."\r\n");
        $response = json_decode($response);

        if(isset($response->deleted)) {
            if($response->deleted == TRUE)
                writeToLog("Deleted OCRout file successfully.\r\n");
            else
                writeToLog("There was an error while attempting to delete file from OCRout folder.\r\n");
        }
        else {
            writeToLog("Failure.\r\n");
        }
    }
    else {
        writeToLog("OCR API retuned unexpected output: ".$reponse);
    }
}
else {
    writeToLog("Failed to get OCR file due to timeout.");
}
curl_close($ch);


// Write line to log
// Automatically timestamped
function writeToLog($line) {
    $line = date("Y-m-d H:i:s")." - ".$line."\r\n";
    $log = fopen("c:/web/ocr_log.txt", "a") or die("Unable to open file!");
    fwrite($log, $line);
    fclose($log);
}

// Get syllabus text file and add contents to database
function persistSyllabusContent($file) {
    // MYSQL HARDCODED VALUES 
    $servername = "localhost";
    $username = "c_syllabus";
    $password = "defeudalizing phorrhea emblematized althein";
    $dbname = "c_syllabus";


    writeToLog($file);
    if (is_file($file)) {
        // Connect to MySQL Database
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            writeToLog("Connection failed: " . $conn->connect_error);
            die("Connection failed: " . $conn->connect_error);
        }

        // Prepare SQL statement
        $sth = $conn->prepare("UPDATE course_syllabi SET content = ? WHERE id = ?");
        $sth->bind_param('ss', $content, $id);

        // Get Syllabus ID from filename
        $id = substr(basename($file), 0, -4);

        // Open Text file and read contents
        $myfile = fopen($file, "r") or die("Unable to open file!");
        $content = fread($myfile,filesize($file));
        fclose($myfile);
        $content = preg_replace('/[^a-zA-Z0-9\s]/', '', $content);

        // Update database
        $sth->send_long_data(0, $content);
        $sth->execute();
        $sth->close();
        $conn->close();
    }
}

?>