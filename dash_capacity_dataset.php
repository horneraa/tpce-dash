<?php
//Open access control to allow the data to be passed beyond the scope of this page, and set the data type to JSON.
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

//Set variables for the SQL query.
$currentTime = time(); //Define the current Unix time.
$currentDay = date('Y-m-d', time());
//$currentDay = date('Y-m-d', strtotime($Date. ' - 1 days')); //TEST LINE
$roundedMinutes = floor($currentTime / (30 * 60)) * (30 * 60); //Round currentTime down to the nearest 30 minutes.
$slotTime = date('H:i:s', $roundedMinutes);
//	//Determine the day of the week and end the returned slots at regular closing time.
if (date('w', time()) < 5) {
  $endDay = date('Y-m-d H:i:s', strtotime("1929-10-18 21:00:00"));
}
elseif (date('w', time()) > 4) {
  $endDay = date('Y-m-d H:i:s', strtotime("1929-10-19 00:00:00"));
}
else {
  $endDay = date('Y-m-d H:i:s', strtotime("1929-10-19 00:00:00"));
}
//	//Outline the start and end dates for the query.
$dateStart = date('Y-m-d 00:00:00', time());
$dateEnd = date('Y-m-d 00:00:00', strtotime($Date. ' + 1 days'));
//$dateStart = date('Y-m-d 00:00:00', strtotime($Date. ' - 1 days')); //TEST LINE
//$dateEnd = date('Y-m-d 00:00:00', time()); //TEST LINE
//  //Counter for slot details.
$count1 = 0;
$count2 = 1;
//  //Create variables to store the output.
$output = "";
$output2 = "";

//Open Database Connection.
//	//Initialize the connection to the database using SQL Server authentication. Print error if connection fails.
$serverName = "AS-MASTER";
$connectionInfo = array( "Database"=>"CenterEdge", "UID"=>"ahorner", "PWD"=>"Password1");
$conn = sqlsrv_connect( $serverName, $connectionInfo);
if( $conn === false ) {
     echo "Could not connect.\n";
     die( print_r( sqlsrv_errors(), true));
}

//Define the queries.
$tsql = "DECLARE @startDay datetime, @endDay datetime, @MaxCap int;
SET @startDay = '" . $dateStart . "';
  SET @endDay = '" . $dateEnd . "';
  SET @MaxCap = (SELECT Capacity FROM CenterEdge.dbo.Areas WHERE areas.Description = 'Flight Tickets');
SELECT JumpTime, SUM(temp.Tickets_Sold) AS Occupancy, MAX(temp.Max_Capacity) AS Capacity
FROM (
SELECT CONVERT(varchar, Areas_ScheduleSlots.StartTime, 8) AS JumpTime, ' ' AS Tickets_Sold, @MaxCap AS Max_Capacity
FROM CenterEdge.dbo.Areas_ScheduleSlots
WHERE  (Areas_ScheduleSlots.StartTime >= {ts '1929-10-18 09:00:00'} AND Areas_ScheduleSlots.StartTime < {ts '" . $endDay . "'})
GROUP BY Areas_ScheduleSlots.StartTime

UNION

SELECT CONVERT(varchar, Areas_Sold.StartDateTime, 8) AS JumpTime, SUM(Areas_Sold.Quantity) AS Tickets_Sold, SUM(DISTINCT Areas.Capacity) AS Max_Capacity
FROM CenterEdge.dbo.Areas_Sold LEFT OUTER JOIN CenterEdge.dbo.Areas ON Areas_Sold.AreaGUID = Areas.AreaGUID
WHERE  (Areas_Sold.EventDate >= @startday AND Areas_Sold.EventDate < @endDay) AND Areas.AreaType = 2
GROUP BY Areas_Sold.StartDateTime

UNION

SELECT CONVERT(varchar, ga.StartDateTime, 8) AS JumpTime, SUM(ga.NumChildren) AS Tickets_Sold, ' ' AS Max_Capacity
FROM CenterEdge.dbo.GroupArrivals AS ga
WHERE (ga.StartDateTime >= @startDay AND ga.StartDateTime < @endDay) AND ga.GrpStatusNo = 1
GROUP BY ga.StartDateTime

UNION ALL

SELECT CASE WHEN DATEADD(minute, 30, gag.StartDateTime) < gag.EndDateTime THEN CONVERT(varchar, DATEADD(minute, 30, gag.StartDateTime), 8) ELSE '09:00:00' END AS JumpTime, CASE WHEN DATEADD(minute, 30, gag.StartDateTime) < gag.EndDateTime THEN SUM(gag.NumChildren) ELSE 0 END AS Tickets_Sold, ' ' AS Max_Capacity
FROM CenterEdge.dbo.GroupArrivals AS gag LEFT OUTER JOIN CenterEdge.dbo.Customers AS cu ON gag.CustomerID = cu.CustomerID
WHERE (gag.StartDateTime >= @startDay AND gag.StartDateTime < @endDay) AND gag.GrpStatusNo = 1
GROUP BY gag.StartDateTime, gag.EndDateTime

UNION ALL

SELECT CASE WHEN DATEADD(minute, 60, gag.StartDateTime) < gag.EndDateTime THEN CONVERT(varchar, DATEADD(minute, 60, gag.StartDateTime), 8) ELSE '09:00:00' END AS JumpTime, CASE WHEN DATEADD(minute, 60, gag.StartDateTime) < gag.EndDateTime THEN SUM(gag.NumChildren) ELSE 0 END AS Tickets_Sold, ' ' AS Max_Capacity
FROM CenterEdge.dbo.GroupArrivals AS gag LEFT OUTER JOIN CenterEdge.dbo.Customers AS cu ON gag.CustomerID = cu.CustomerID
WHERE (gag.StartDateTime >= @startDay AND gag.StartDateTime < @endDay) AND gag.GrpStatusNo = 1 AND BirthdayEvent = 0
GROUP BY gag.StartDateTime, gag.EndDateTime

UNION ALL

SELECT CASE WHEN DATEADD(minute, 90, gag.StartDateTime) < gag.EndDateTime THEN CONVERT(varchar, DATEADD(minute, 90, gag.StartDateTime), 8) ELSE '09:00:00' END AS JumpTime, CASE WHEN DATEADD(minute, 90, gag.StartDateTime) < gag.EndDateTime THEN SUM(gag.NumChildren) ELSE 0 END AS Tickets_Sold, ' ' AS Max_Capacity
FROM CenterEdge.dbo.GroupArrivals AS gag LEFT OUTER JOIN CenterEdge.dbo.Customers AS cu ON gag.CustomerID = cu.CustomerID
WHERE (gag.StartDateTime >= @startDay AND gag.StartDateTime < @endDay) AND gag.GrpStatusNo = 1 AND BirthdayEvent = 0
GROUP BY gag.StartDateTime, gag.EndDateTime

UNION ALL

SELECT CASE WHEN DATEADD(minute, 120, gag.StartDateTime) < gag.EndDateTime THEN CONVERT(varchar, DATEADD(minute, 120, gag.StartDateTime), 8) ELSE '09:00:00' END AS JumpTime, CASE WHEN DATEADD(minute, 120, gag.StartDateTime) < gag.EndDateTime THEN SUM(gag.NumChildren) ELSE 0 END AS Tickets_Sold, ' ' AS Max_Capacity
FROM CenterEdge.dbo.GroupArrivals AS gag LEFT OUTER JOIN CenterEdge.dbo.Customers AS cu ON gag.CustomerID = cu.CustomerID
WHERE (gag.StartDateTime >= @startDay AND gag.StartDateTime < @endDay) AND gag.GrpStatusNo = 1 AND BirthdayEvent = 0
GROUP BY gag.StartDateTime, gag.EndDateTime
) AS temp

GROUP BY temp.JumpTime
ORDER BY JumpTime
;";

//Prepare sqlsrv_query().
$stmt = sqlsrv_query($conn, $tsql);

//If the statement can be executed, store the value of each row in the result set into the variable $output.
if( $stmt === false ) {
	echo "Statement could not be executed.\n";
	die( print_r(sqlsrv_errors(), true));
}
else {
	while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
		if ($output != "") {$output .= ",";}
		$output .= '"' . date('g:i A', strtotime($row["JumpTime"])) . '":{"slotCount":"' . $count1 . '",';
		$output .= '"slotTime":"' . date('g:i A', strtotime($row["JumpTime"])) . '",';
		$output .= '"slotTimeUnix":"' . date('U000', strtotime($row["JumpTime"])) . '",';
		$output .= '"occupancy":"' . $row["Occupancy"] . '",';
		$output .= '"capacity":"' . $row["Capacity"] . '",';
		$output .= '"jumpers": [';
		$slotTime = date('H:i:00', strtotime($row["JumpTime"]));
		$output .= $output2 . ']}';
		$output2 = "";
		$count1++;
	}
	$output = '{ "data":{'.$output.'}}';
}

//Output stored data in JSON format.
echo $output;

//Close Database Connection.
//Free statement resources and close the connection.
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

?>