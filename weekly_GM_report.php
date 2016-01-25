<?php
//Open access control to allow the data to be passed beyond the scope of this page, and set the data type to JSON.
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

//Set variables for the SQL query.
$currentTime = time(); //Define the current Unix time.
$currentDate = date('Y-m-d', time());
$lastWeekBegin = date('Y-m-d', strtotime("last week Monday")) . " 00:00:00";
$lastWeekBeginNoTime = date('Y-m-d', strtotime("last week Monday"));
$lastWeekEnd = date('Y-m-d', strtotime("this week Monday")) . " 00:00:00";
$lastWeekEndNoTime = date('Y-m-d', strtotime("last week Sunday"));
$lastWeekFri = date('Y-m-d', strtotime("last week Friday"));
$lastWeekSat = date('Y-m-d', strtotime("last week Saturday"));
$lastWeekSun = date('Y-m-d', strtotime("last week Sunday"));
$clubAirBegin = "21:00:00";
$clubAirEnd = "00:00:00"; //12:00AM the following day.
$count = 1;
//  //Create variables to store the output.
$headCount = "";
$totalRevenue = "";
$numBirthdayParties = "";
$attendAirFit = '"attendAirFit":0,';
$attendClubAir = "";
$organizationList = "";
$output = "";

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
$tsql = "SELECT SUM(HeadCounts.HeadCount) AS HeadCount FROM CenterEdge.dbo.HeadCounts 
		WHERE (HeadCounts.ShiftDate >= {ts '" . $lastWeekBegin . "'} AND HeadCounts.ShiftDate < {ts '" . $lastWeekEnd . "'});";
		
$tsql2 = "SELECT SUM(Sales.AmtSold - Sales.Amtreturned) AS Sales FROM CenterEdge.dbo.Sales
		WHERE (Sales.ShiftDate >= {ts '" . $lastWeekBegin . "'} AND Sales.ShiftDate < {ts '" . $lastWeekEnd . "'});";
		
$tsql3 = "SELECT COUNT(*) AS NumberOfParties FROM CenterEdge.dbo.GroupArrivals
		WHERE (GroupArrivals.StartDateTime >= {ts '" . $lastWeekBegin . "'} AND GroupArrivals.EndDateTime < {ts '" . $lastWeekEnd . "'}) 
		AND GroupArrivals.BirthdayEvent = 1 AND GroupArrivals.GrpStatusNo = 2;";
		
$tsql4 = "SELECT (CEILING(SUM(CAST(Areas_Sold.ActQuantity AS float)) / 2)) AS Quantity FROM CenterEdge.dbo.Areas_Sold
WHERE (Areas_Sold.StartDateTime >= {ts '" . $lastWeekFri . " " . $clubAirBegin . "'} AND Areas_Sold.EndDateTime < {ts '" . $lastWeekSat . " " . $clubAirEnd . "'})
OR (Areas_Sold.StartDateTime >= {ts '" . $lastWeekSat . " " . $clubAirBegin . "'} AND Areas_Sold.EndDateTime < {ts '" . $lastWeekSun . " " . $clubAirEnd . "'});";

$tsql5 = "SELECT Customers.OrganizationName
FROM CenterEdge.dbo.GroupArrivals LEFT OUTER JOIN CenterEdge.dbo.Customers ON GroupArrivals.CustomerID = Customers.CustomerID
WHERE (GroupArrivals.StartDateTime >= {ts '" . $lastWeekBegin . "'} AND GroupArrivals.EndDateTime < {ts '" . $lastWeekEnd . "'}) AND GroupArrivals.BirthdayEvent = 0 AND GrpStatusNo = 2 AND Customers.CustTypeID != 5;";

//Prepare sqlsrv_query().
$stmt = sqlsrv_query($conn, $tsql);
$stmt2 = sqlsrv_query($conn, $tsql2);
$stmt3 = sqlsrv_query($conn, $tsql3);
$stmt4 = sqlsrv_query($conn, $tsql4);
$stmt5 = sqlsrv_query($conn, $tsql5);

//Statement 1
if( $stmt === false ) {
	echo "Statement could not be executed.\n";
	die( print_r(sqlsrv_errors(), true));
}
else {
	while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
		$headCount .= '"headCount":' . $row['HeadCount'] . ',';
	}
}
//Statement 2
if( $stmt2 === false ) {
	echo "Statement could not be executed.\n";
	die( print_r(sqlsrv_errors(), true));
}
else {
	while($row2 = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {
		$totalRevenue .= '"totalRevenue":' . number_format($row2['Sales'], 2, ".", "") . ',';
	}
}
//Statement 3
if( $stmt3 === false ) {
	echo "Statement could not be executed.\n";
	die( print_r(sqlsrv_errors(), true));
}
else {
	while($row3 = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC)) {
		$numBirthdayParties .= '"numParties":' . $row3['NumberOfParties'] . ',';
	}
}
//Statement 4
if( $stmt4 === false ) {
	echo "Statement could not be executed.\n";
	die( print_r(sqlsrv_errors(), true));
}
else {
	while($row4 = sqlsrv_fetch_array($stmt4, SQLSRV_FETCH_ASSOC)) {
		$attendClubAir .= '"attendClubAir":' . $row4['Quantity'] . ',';
	}
}
//Statement 5
if( $stmt5 === false ) {
	echo "Statement could not be executed.\n";
	die( print_r(sqlsrv_errors(), true));
}
else {
	while($row5 = sqlsrv_fetch_array($stmt5, SQLSRV_FETCH_ASSOC)) {
		if ($organizationList != "") {$organizationList .= ",";}
		$organizationList .= '"name' . $count . '":"' . $row5['OrganizationName'] . '"';
		$count++;
	}
	$organizationList = '"organizationList":{' . $organizationList . '}';
}

//Create JSON output.
$output = '{ "weeklyGmReport":{ "0":{"lastWeekBegin":"' . $lastWeekBeginNoTime . '","lastWeekEnd":"' . $lastWeekEndNoTime . '",' . $headCount . $totalRevenue . $numBirthdayParties . $attendAirFit . $attendClubAir . $organizationList . '}}}';

//Output stored data in JSON format.
//echo $headCount . $totalRevenue . "\n" . $numBirthdayParties . "\n" . $attendAirFit . "\n" . $attendClubAir . "\n" . $organizationList;
echo $output;

//Close Database Connection.
//Free statement resources and close the connection.
sqlsrv_free_stmt($stmt);
sqlsrv_free_stmt($stmt2);
sqlsrv_free_stmt($stmt3);
sqlsrv_free_stmt($stmt4);
sqlsrv_free_stmt($stmt5);
sqlsrv_close($conn);

?>