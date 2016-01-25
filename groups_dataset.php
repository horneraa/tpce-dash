<?php
//Open access control to allow the data to be passed beyond the scope of this page, and set the data type to JSON.
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
setlocale(LC_MONETARY, 'en_US.UTF-8');

//Set variables for the SQL query.
$currentTime = time(); //Define the current Unix time.
$currentDay = date('Y-m-d', time());
//$currentDay = date('Y-m-d', strtotime($Date. ' - 1 days')); //TEST LINE minus one day
$roundedMinutes = floor($currentTime / (30 * 60)) * (30 * 60); //Round currentTime down to the nearest 30 minutes.
$slotTime = date('H:i:s', $roundedMinutes);
//Start and End dates for SQL query.
$dateStart = date('Y-m-d 00:00:00', time());
$dateEnd = date('Y-m-d 23:59:59', strtotime('this Sunday'));
//  //Counter for slot details.
$count = 0;
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
$tsql = "DECLARE @startToday datetime, @endWeek datetime;
SET @startToday = '" . $dateStart . "';
SET @endWeek = '" . $dateEnd . "';
SELECT ga.GrpStatusNo, ga.Description, CONVERT(varchar, ga.StartDateTime, 120) AS StartDateTime, CONVERT(varchar, ga.EndDateTime, 120) AS EndDateTime, ga.GroupSize, ga.TotalSaleAmount, ga.TotalDepositAmount, ga.BookingDate, ga.BirthdayEvent, ga.ContactFirstName, ga.ContactLastName, ga.ContactPhoneNumber, ga.ContactEmailAddress, ga.Confirmed_DateTime, em.FirstName AS Confirmed_EmpFirstName, em.LastName AS Confirmed_EmpLastName, ga.Notes, ga.SalesPerson_EmpNo, ga.TimeCreated, ga.CreatedBy_EmpNo, ga.PrivateNotes, ga.BookedFromWeb, ga.WebReviewDate, ga.WebReviewEmpNo, ga.CancelDate
FROM CenterEdge.dbo.GroupArrivals AS ga LEFT OUTER JOIN CenterEdge.dbo.Employees AS em ON ga.Confirmed_EmpNo = em.EmpNo
WHERE (ga.StartDateTime >= @startToday AND ga.StartDateTime < @endWeek)
ORDER BY ga.StartDateTime";

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
		$output .= '"group' . $count . '":{"GrpStatusNo":"' . $row["GrpStatusNo"] . '",';
		$output .= '"Description":"' . $row["Description"] . '",';
		$output .= '"StartDateTime":"' . date('Y-m-d H:i:s', strtotime($row["StartDateTime"])) . '",';
		$output .= '"EndDateTime":"' . date('Y-m-d H:i:s', strtotime($row["EndDateTime"])) . '",';
		$output .= '"GroupSize":"' . $row["GroupSize"] . '",';
		$output .= '"TotalSaleAmount":"' . number_format($row["TotalSaleAmount"], 2, ".", ",") . '",';
		$output .= '"TotalDepositAmount":"' . number_format($row["TotalDepositAmount"], 2, ".", ",") . '",';
		$output .= '"BookingDate":"' . date('Y-m-d H:i:s', strtotime($row["BookingDate"])) . '",';
		$output .= '"BirthdayEvent":"' . $row["BirthdayEvent"] . '",';
		$output .= '"ContactFirstName":"' . $row["ContactFirstName"] . '",';
		$output .= '"ContactLastName":"' . $row["ContactLastName"] . '",';
		$output .= '"ContactPhoneNumber":"' . $row["ContactPhoneNumber"] . '",';
		$output .= '"ContactEmailAddress":"' . $row["ContactEmailAddress"] . '",';
		$output .= '"Confirmed_DateTime":"' . date('Y-m-d H:i:s', strtotime($row["Confirmed_DateTime"])) . '",';
		$output .= '"Confirmed_EmpFirstName":"' . $row["Confirmed_EmpFirstName"] . '",';
		$output .= '"Confirmed_EmpLastName":"' . $row["Confirmed_EmpLastName"] . '",';
		$output .= '"Notes":' . json_encode($row["Notes"]) . ',';
		$output .= '"SalesPerson_EmpNo":"' . $row["SalesPerson_EmpNo"] . '",';
		$output .= '"TimeCreated":"' . date('Y-m-d H:i:s', strtotime($row["TimeCreated"])) . '",';
		$output .= '"CreatedBy_EmpNo":"' . $row["CreatedBy_EmpNo"] . '",';
		$output .= '"PrivateNotes":' . json_encode($row["PrivateNotes"]) . ',';
		$output .= '"BookedFromWeb":"' . $row["BookedFromWeb"] . '",';
		$output .= '"WebReviewDate":"' . date('Y-m-d H:i:s', strtotime($row["WebReviewDate"])) . '",';
		$output .= '"WebReviewEmpNo":"' . $row["WebReviewEmpNo"] . '",';
		$output .= '"CancelDate":"' . date('Y-m-d H:i:s', strtotime($row["CancelDate"])) . '"';
		$output .= '}';
		$count++;
	}
	$output = '{ "groups":{'.$output.'}}';
}

//Output stored data in JSON format.
echo $output;

//Close Database Connection.
//Free statement resources and close the connection.
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

?>