<?php

DEFINE("TIMEOUT", 15 * 60);    // Number of seconds to run before timing out and exiting
DEFINE("CHECK_FREQ", 60);      // Number of seconds to wait between each 

// If the file_id is not provided, throw exception
if(!isset($argv[1], $argv[2])) {
	writeToLog("OCRCheck started with invalid or missing arguments.");
	throw new InvalidArgumentException("Incorrect usage. Usage: php OCRCheck.php destination_dir file_id");
}

$destination_dir = $argv[1];
$file_id = $argv[2];
$url = "http://assettdev.colorado.edu/ocr/api/filestatus?file_id=".$file_id;
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
	$downloadUrl = "http://assettdev.colorado.edu/ocr/api/downloadfile?file_id=".$file_id;

	// Change cURL URL and get file path from API
	curl_setopt($ch, CURLOPT_URL, $downloadUrl);
	$response = json_decode(curl_exec($ch));

	// Check if the response contains a valid file path
	if(isset($response->file_path, $response->file_name) && is_file($response->file_path.$response->file_name)) {
		$full_path = $response->file_path.$response->file_name;
		$copied = copy($full_path, $destination_dir.$response->file_name);
		writeToLog("OCRCheck copied file ".$file_id.".");

		// Remove file from OCRout folder
		$removeUrl = "http://assettdev.colorado.edu/ocr/api/removefile?file_id=".$file_id;
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

?>