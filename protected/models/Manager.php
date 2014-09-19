<?php

class Manager
{
    
    public function load_courses($prefix)
    {
        $conn = Yii::app()->db;
        $query = "
            SELECT              courseid
            FROM                {{courses}}
            WHERE               prefix = :prefix
            ORDER BY            num ASC, title ASC; 
        ";
        $command = $conn->createCommand($query);
        $command->bindParam(":prefix",$prefix);
        $result = $command->queryAll();
        
        if(!$result or empty($result)) {
            return array();
        }
        
        $courses = array();
        foreach($result as $row) {
            $courses[] = new CourseObj($row["courseid"]);
        }
        
        return $courses;
    }
    
    public function load_unique_courses()
    {
        $conn = Yii::app()->db;
        $query = "
            SELECT DISTINCT     prefix
            FROM                {{courses}}
            WHERE               1=1
            ORDER BY            prefix ASC; 
        ";
        $result = $conn->createCommand($query)->queryAll();
        
        if(!$result or empty($result)) {
            return array();
        }
        
        $return = array();
        foreach($result as $row) {
            
            $prefix = $row["prefix"];
            # Init array position for Course
            $return[$prefix] = array();
            
            # Load the number of classes per course
            $query = "
                SELECT          COUNT(*)
                FROM            {{classes}} AS CL
                INNER JOIN      {{courses}} AS CO
                ON              CL.courseid = CO.courseid
                WHERE           CO.prefix = :prefix;           
            ";
            $command = $conn->createCommand($query);
            $command->bindParam(":prefix",$prefix);
            $return[$prefix]["numclasses"] = $command->queryScalar();
            
            # Load the number of syllabi per course
            $query = "
                SELECT          COUNT(*)
                FROM            {{syllabi}} AS S
                INNER JOIN      {{classes}} AS CL
                ON              S.classid = CL.classid
                INNER JOIN      {{courses}} AS CO
                ON              CL.courseid = CO.courseid
                WHERE           CO.prefix = :prefix;           
            ";
            $command = $conn->createCommand($query);
            $command->bindParam(":prefix",$prefix);
            $return[$prefix]["numsyllabi"] = $command->queryScalar();
            
            # Load the number of instructors per course
            $query = "
                SELECT          instructors
                FROM            {{classes}} AS CL
                INNER JOIN      {{courses}} AS CO
                ON              CL.courseid = CO.courseid
                WHERE           CO.prefix = :prefix;
            ";
            $command = $conn->createCommand($query);
            $command->bindParam(":prefix",$prefix);
            $result = $command->queryAll();
            
            # Parse out unique instructors only
            $LOI = array();
            foreach($result as $row) {
                $instructors = $row["instructors"];
                $instructors = explode(",",$instructors);
                foreach($instructors as $instrid) {
                    if(!in_array($instrid,$LOI)) {
                        $LOI[] = $instrid;
                    }
                }
            }
            
            # Add to return statement
            $return[$prefix]["numinstructors"] = count($LOI);
            
            # Load the earliest year
            $query = "
                SELECT          year
                FROM            {{classes}} AS CL
                INNER JOIN      {{courses}} AS CO
                ON              CL.courseid = CO.courseid
                WHERE           CO.prefix = :prefix
                ORDER BY        year ASC;
            ";
            $command = $conn->createCommand($query);
            $command->bindParam(":prefix",$prefix);
            $return[$prefix]["minyear"] = $command->queryScalar();
            
            # Load the latest year
            $query = "
                SELECT          year
                FROM            {{classes}} AS CL
                INNER JOIN      {{courses}} AS CO
                ON              CL.courseid = CO.courseid
                WHERE           CO.prefix = :prefix
                ORDER BY        year DESC;
            ";
            $command = $conn->createCommand($query);
            $command->bindParam(":prefix",$prefix);
            $return[$prefix]["maxyear"] = $command->queryScalar();
        }
        
        return $return;
    }
    
}
