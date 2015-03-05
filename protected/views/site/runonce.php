<?php
/**
 * Run Once
 * 
 * The purpose of the "run once" is to run PHP functions to alter and modify
 * the archive in conjunction with the system itself. This means it loads up
 * functions and users and objects just as the system would and run functionality
 * against them.
 * 
 * For example, say we modify the namespace for syllabi. We would create functions in
 * Run Once to change the names for all the syllabi.
 * 
 * Only PROGRAMMERS are allowed here. Restricted in the SiteController using StdLib::is_programmer.
 * Will be ignored by the GitHub repository.
 */
StdLib::pre();
StdLib::set_debug_state("DEVELOPMENT");

set_time_limit(0);

return false;

?>
