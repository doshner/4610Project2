<!--
    Andrew Riebow
    4610
    Project 1
    February 25, 2020
 -->

<!DOCTYPE html>
<html lang="en">
   <head>
      <title>Project 1</title>
      <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
      <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" >
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   </head>
   <body>
      <div class="container">
        <div class="row text-center">
            <div class=" col">
                <button class="btn btn-outline-primary" onclick="location.href = 'index.php?tableNumber=0';" name="students" value="students">Students</button>
            </div>
            <div class="col">
            <button class="btn btn-outline-primary" onclick="location.href = 'index.php?tableNumber=1';">Course</button>
            </div>
            <div class="col">
            <button class="btn btn-outline-primary" onclick="location.href = 'index.php?tableNumber=2';">Section</button>
            </div>
            <div class="col">
            <button class="btn btn-outline-primary" onclick="location.href = 'index.php?tableNumber=3';">Grade Report</button>
            </div>
            <div class="col">
            <button class="btn btn-outline-primary" onclick="location.href = 'index.php?tableNumber=4';">Prerequisite</button>
            </div>
        </div>
      </div>
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
   </body>
</html>


<?php
//These variables are used to connect to the database and can be changed to whatever is necessary
$servername = "localhost";
$username = "root";
$password = "";
$dbName = "university";

//Create connection to database
$conn = mysqli_connect($servername, $username, $password, $dbName);

//Get variables from the URl
$tableNumber = intval(filter_input(INPUT_GET, "tableNumber"));
$columnNumber = intval(filter_input(INPUT_GET, "col"));
$sortOrder = strval(filter_input(INPUT_GET, "sortOrder"));
if($sortOrder == '0') $sortOrder = 'DESC';

//Create an array of the tables
$tableArray = array();
$tableArray[] = "student";
$tableArray[] = "course";
$tableArray[] = "section";
$tableArray[] = "grade_report";
$tableArray[] = "prerequisite";

$tableName = $tableArray[$tableNumber];

//Echo an error if unable to connect to database
if(!$conn){
    echo "Error connecting to database";
}


//If the form for creating a new row is sent to the server
if(isset($_POST['submitRow'])){
    $numOfColumns = count($_POST) - 1; //Ignore the last variable in $_POST which is the submit button value
    $dbColumnNames = getColumnNames($tableName, $conn);

    $sqlColumns = ""; //Create an empty string to store our concatenated sql values
    for ($i = 0; $i < $numOfColumns; $i++){
        if($i == ($numOfColumns - 1)){
            $sqlColumns .= $dbColumnNames[$i];
        }
        else{
            $sqlColumns .= $dbColumnNames[$i] . ",";
        }
    }

    $sqlValues = "";
    $count = 0;
    foreach($_POST as $key => $value){
        if($count < $numOfColumns){
            if($count == ($numOfColumns - 1)){
                $sqlValues .= "'".$value."'";
            }
            else{
                $sqlValues .= "'".$value."'" .",";
            }     
        }
        $count++;
    }

    $sqlQuery = "INSERT INTO $tableName ($sqlColumns) VALUES ($sqlValues)"; //Construct the SQL query to insert a row based off of the columns in the databse and the values from the $_POST
    $conn->query($sqlQuery);
}

//If the form for updating an old row is sent to the server
if(isset($_POST['updateRow'])){
    $numOfColumns = count($_POST) - 3;//Ignore last 3 from $_POST. Last 3 include hidden form elemnts for the value and column of a row so we can find it in the table to modify it
    $dbColumnNames = getColumnNames($tableName, $conn);

    $sqlColumns = "";
    for ($i = 0; $i < $numOfColumns; $i++){
        if($i == ($numOfColumns - 1)){
            $sqlColumns .= $dbColumnNames[$i];
        }
        else{
            $sqlColumns .= $dbColumnNames[$i] . ",";
        }
    }

    $sqlValues = "";
    $count = 0;
    $sqlValueArray = array();
    foreach($_POST as $key => $value){
        if($count < $numOfColumns){
            if($count == ($numOfColumns - 1)){
                $sqlValues .= "'".$value."'";
            }
            else{
                $sqlValues .= "'".$value."'" .",";
            }
            $sqlValueArray[] = $value;     
        }
        $count++;
    }

    $sqlSet = "";
    for ($i = 0; $i < $numOfColumns; $i++){
        if($i == $numOfColumns - 1){
            $sqlSet .= $dbColumnNames[$i] . " = '" . $sqlValueArray[$i]."' ";
        }
        else{          
            $sqlSet .= $dbColumnNames[$i] . " = '" . $sqlValueArray[$i]."', ";
        }
    }

    $idColumn = $_POST['columnName'];
    $idValue = $_POST['columnValue'];

    $sqlQuery = "UPDATE $tableName SET $sqlSet WHERE $idColumn = '$idValue'";//Construct SQL Query from the columns and new row values

    $conn->query($sqlQuery);
}

//$tableNumber is used to decide what table to display on the page
if ($tableNumber == 0){
    $tableName = $tableArray[$tableNumber];
    $columnNames = getColumnNames($tableName, $conn);
    
    if(isset($_POST['deleteItem'])){ //If the delete button was clicked, delete the row from the table before dispalying it
        $id = $_POST['deleteItem'];
        $conn->query("DELETE FROM $tableArray[$tableNumber] WHERE student_number = $id");
    }

    

    $sqlQuery = "SELECT * FROM $tableName ORDER BY $columnNames[$columnNumber] $sortOrder";
    //echo "sqlQuery: $sqlQuery";
    $result = $conn->query($sqlQuery);
    if($result->num_rows > 0){
        //Create table
        echo "<div class='container'>";
        
        echo "<table class='table' id='mainTable'>";
        echo "<thead class='thead-dark'>";
        echo "<tr>";
        echo "<th scope='col'>Student Number</th>";
        echo "<th scope='col'>Name</th>";
        echo "<th scope='col'>Class</th>";
        echo "<th scope='col'>Major</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        while($row = $result->fetch_assoc()){ //While there are rows to fetch
            echo "<tr>";
            echo "<td>" . $row["student_number"] .  "</td>"; //Construct a row with cells for each column
            echo "<td>" . $row["name"] . "</td>";
            echo "<td>" . $row["class"] . "</td>";
            echo "<td>" . $row["major"] . "</td>";
            echo "<form action='' method='post'>";
            echo "<td style='width:10%; padding-bottom:0; margin-bottom:0; border-bottom:0'>
            <button type='button' class='btn btn-outline-dark name='editItem' onclick='createEditRowFields(". count($columnNames) .", \"student_number\", ". $row['student_number'] ."); this.firstElementChild.classList.remove(\"fa-pencil\"); this.firstElementChild.classList.add(\"fa-check\")'><span class='fa fa-pencil'></span></button>
            <button class='btn btn-outline-dark' type='submit' name='deleteItem' value='" .$row["student_number"]. "'><span class='fa fa-trash'></span></button>       
            </td>"; //Echo out the Edit and delete buttons for the row
            echo "</form>";
            echo "</tr>";
        } 

        //Call functions to create the sort buttons for each column, as well as the button to insert a new row
        createSortButtons(count($columnNames), $tableNumber, $columnNumber);
        createInsertButton($tableName, $conn);
        echo "</tbody>";
        echo "</table>";       
        echo "</div>";
    }

} //This is repeated for the other 4 tables

if($tableNumber == 1){
    $tableName = $tableArray[$tableNumber];
    $columnNames = getColumnNames($tableName, $conn);
    
    if(isset($_POST['deleteItem'])){
        $id = $_POST['deleteItem'];
        $query = "DELETE FROM $tableArray[$tableNumber] WHERE course_number = \"$id\"";
        $conn->query($query);
    }

    $sqlQuery = "SELECT * FROM $tableName ORDER BY $columnNames[$columnNumber] $sortOrder";
    $result = $conn->query($sqlQuery);
    if($result->num_rows > 0){
        echo "<div class='container'>";
        echo "<table class='table'>";
        echo "<thead class='thead-dark'>";
        echo "<tr>";
        echo "<th scope='col'>Course Number</th>";
        echo "<th scope='col'>Course Name</th>";
        echo "<th scope='col'>Credit Hours</th>";
        echo "<th scope='col'>Department</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
    }
    while($row = $result->fetch_assoc()){
        echo "<tr>";
        echo "<td>" . $row["course_number"] .  "</td>";
        echo "<td>" . $row["course_name"] . "</td>";
        echo "<td>" . $row["credit_hours"] . "</td>";
        echo "<td>" . $row["department"] . "</td>";
        echo "<form action='' method='post'>";
        echo "<td style='width:10%; padding-bottom:0; margin-bottom:0; border-bottom:0'>
        <button type='button' class='btn btn-outline-dark name='editItem' onclick='createEditRowFields(". count($columnNames) .", \"course_number\", \"". $row['course_number'] ."\"); this.firstElementChild.classList.remove(\"fa-pencil\"); this.firstElementChild.classList.add(\"fa-check\")'><span class='fa fa-pencil'></span></button>
        <button class='btn btn-outline-dark' type='submit' name='deleteItem' value='" .$row["course_number"]. "'><span class='fa fa-trash'></span></button>       
        </td>";
        echo "</form>";
        echo "</tr>";
    }
    $columnNames = getColumnNames($tableName, $conn); 
    echo "</tbody>";
    createSortButtons(count($columnNames), $tableNumber, $columnNumber);
    createInsertButton($tableName, $conn);
    echo "</table>";
    echo "</div>";
}

if ($tableNumber == 2){
    $tableName = $tableArray[$tableNumber];
    $columnNames = getColumnNames($tableName, $conn);
    
    if(isset($_POST['deleteItem'])){
        $id = $_POST['deleteItem'];
        $conn->query("DELETE FROM $tableArray[$tableNumber] WHERE section_identifier = $id");
    }

    

    $sqlQuery = "SELECT * FROM $tableName ORDER BY $columnNames[$columnNumber] $sortOrder";
    
    $result = $conn->query($sqlQuery);
    if($result->num_rows > 0){
        echo "<div class='container'>";
        
        echo "<table class='table' id='mainTable'>";
        echo "<thead class='thead-dark'>";
        echo "<tr>";
        echo "<th scope='col'>Section Identifier</th>";
        echo "<th scope='col'>Course Number</th>";
        echo "<th scope='col'>Semester</th>";
        echo "<th scope='col'>Year</th>";
        echo "<th scope='col'>Instructor</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        while($row = $result->fetch_assoc()){
            echo "<tr>";
            echo "<td>" . $row["section_identifier"] .  "</td>";
            echo "<td>" . $row["course_number"] . "</td>";
            echo "<td>" . $row["semester"] . "</td>";
            echo "<td>" . $row["year"] . "</td>";
            echo "<td>" . $row["instructor"] . "</td>";
            echo "<form action='' method='post'>";
            echo "<td style='width:10%; padding-bottom:0; margin-bottom:0; border-bottom:0'>
            <button type='button' class='btn btn-outline-dark name='editItem' onclick='createEditRowFields(". count($columnNames) .", \"section_identifier\", ". $row['section_identifier'] ."); this.firstElementChild.classList.remove(\"fa-pencil\"); this.firstElementChild.classList.add(\"fa-check\")'><span class='fa fa-pencil'></span></button>
            <button class='btn btn-outline-dark' type='submit' name='deleteItem' value='" .$row["section_identifier"]. "'><span class='fa fa-trash'></span></button>       
            </td>";
            echo "</form>";
            echo "</tr>";
        } 

        createSortButtons(count($columnNames), $tableNumber, $columnNumber);
        createInsertButton($tableName, $conn);
        echo "</tbody>";
        echo "</table>";       
        echo "</div>";
    }

}

if ($tableNumber == 3){
    $tableName = $tableArray[$tableNumber];
    $columnNames = getColumnNames($tableName, $conn);
    
    if(isset($_POST['deleteItem'])){
        $id = $_POST['deleteItem'];
        $conn->query("DELETE FROM $tableArray[$tableNumber] WHERE section_identifier = $id");
    }

    

    $sqlQuery = "SELECT * FROM $tableName ORDER BY $columnNames[$columnNumber] $sortOrder";
    
    $result = $conn->query($sqlQuery);
    if($result->num_rows > 0){
        echo "<div class='container'>";
        
        echo "<table class='table' id='mainTable'>";
        echo "<thead class='thead-dark'>";
        echo "<tr>";
        echo "<th scope='col'>Student Number</th>";
        echo "<th scope='col'>Section Identifier</th>";
        echo "<th scope='col'>Grade</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        while($row = $result->fetch_assoc()){
            echo "<tr>";
            echo "<td>" . $row["student_number"] .  "</td>";
            echo "<td>" . $row["section_identifier"] . "</td>";
            echo "<td>" . $row["grade"] . "</td>";
            echo "<form action='' method='post'>";
            echo "<td style='width:10%; padding-bottom:0; margin-bottom:0; border-bottom:0'>
            <button type='button' class='btn btn-outline-dark name='editItem' onclick='createEditRowFields(". count($columnNames) .", \"section_identifier\", ". $row['section_identifier'] ."); this.firstElementChild.classList.remove(\"fa-pencil\"); this.firstElementChild.classList.add(\"fa-check\")'><span class='fa fa-pencil'></span></button>
            <button class='btn btn-outline-dark' type='submit' name='deleteItem' value='" .$row["section_identifier"]. "'><span class='fa fa-trash'></span></button>       
            </td>";
            echo "</form>";
            echo "</tr>";
        } 

        createSortButtons(count($columnNames), $tableNumber, $columnNumber);
        createInsertButton($tableName, $conn);
        echo "</tbody>";
        echo "</table>";       
        echo "</div>";
    }
}

if ($tableNumber == 4){
    $tableName = $tableArray[$tableNumber];
    $columnNames = getColumnNames($tableName, $conn);
    
    if(isset($_POST['deleteItem'])){
        $id = $_POST['deleteItem'];
        $conn->query("DELETE FROM $tableArray[$tableNumber] WHERE prerequisite_number = '$id'");
    }

    

    $sqlQuery = "SELECT * FROM $tableName ORDER BY $columnNames[$columnNumber] $sortOrder";
    
    $result = $conn->query($sqlQuery);
    if($result->num_rows > 0){
        echo "<div class='container'>";
        
        echo "<table class='table' id='mainTable'>";
        echo "<thead class='thead-dark'>";
        echo "<tr>";
        echo "<th scope='col'>Course Number</th>";
        echo "<th scope='col'>Prerequisite Number</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        while($row = $result->fetch_assoc()){
            echo "<tr>";
            echo "<td>" . $row["course_number"] .  "</td>";
            echo "<td>" . $row["prerequisite_number"] . "</td>";
            echo "<form action='' method='post'>";
            echo "<td style='width:10%; padding-bottom:0; margin-bottom:0; border-bottom:0'>
            <button type='button' class='btn btn-outline-dark name='editItem' onclick='createEditRowFields(". count($columnNames) .", \"prerequisite_number\", \"". $row['prerequisite_number'] ."\"); this.firstElementChild.classList.remove(\"fa-pencil\"); this.firstElementChild.classList.add(\"fa-check\")'><span class='fa fa-pencil'></span></button>
            <button class='btn btn-outline-dark' type='submit' name='deleteItem' value='" .$row["prerequisite_number"]. "'><span class='fa fa-trash'></span></button>       
            </td>";
            echo "</form>";
            echo "</tr>";
        } 

        createSortButtons(count($columnNames), $tableNumber, $columnNumber);
        createInsertButton($tableName, $conn);
        echo "</tbody>";
        echo "</table>";       
        echo "</div>";
    }
}

//Function to create the sort buttons with the correct URL parameters
function createSortButtons($numToCreate, $tableNumber){
    echo "<tr>";
    for ($i = 0; $i < $numToCreate; $i++){
        echo "<td style='width: 8em'>";
        echo "<button class='btn btn-outline-dark' onclick=\"location.href = 'index.php?tableNumber=$tableNumber&col=$i&sortOrder=ASC'\"><span class='fa fa-arrow-up'></span></button>";
        echo "<button class='btn btn-outline-dark' onclick=\"location.href = 'index.php?tableNumber=$tableNumber&col=$i&sortOrder=DESC'\"><span class='fa fa-arrow-down'/></button>";
        echo "</td>";
    }
    echo "</tr>";
}

//Function to get the columnNames from the currently selected table
function getColumnNames($tableName, $conn){
    $result = $conn->query("SHOW COLUMNS FROM $tableName");
    $columnNames = array();
    while($row = $result->fetch_array()){
        $columnNames[] = $row['0'];
    }
    return $columnNames;
}

//Function to create the Insert new row button, which creates fields via javascript for the user to input into
function createInsertButton($tableName, $conn){
    $columns = getColumnNames($tableName, $conn);
    $numColumns = count($columns);
    echo "<tr id='insertRow'>";

    
    echo "<button style='margin-top: 10px' class='btn btn-default btn-outline-secondary' onclick=\"createNewRowFields($numColumns); this.remove();\">New Row</button>";
    echo "</tr>";
}



$conn->close();

?>

<script>
//Debounce for edit and add buttons so you can't do both at the same time
var isEditing = false;
var isAdding = false;

//Function to create the fields that allow you to enter values for a new row
function createNewRowFields(numOfColumns){
    if(isEditing == true) return;
    isAdding = true;
    var f = document.createElement("form");
    f.setAttribute('method',"post");
    f.setAttribute('id', 'insertForm');


    var insertTable = document.getElementById("insertRow")

    for (j = 0; j < numOfColumns; j++){
        var cell = document.createElement("td");
        var i = document.createElement("input"); 
        i.setAttribute('type',"text");
        i.setAttribute('name',"insertField" + j);
        i.setAttribute('form', 'insertForm');
        cell.appendChild(i);
        insertTable.appendChild(cell);

    }


    var s = document.createElement("input"); 
    s.setAttribute('type',"submit");
    s.setAttribute('name','submitRow');
    s.setAttribute('value',"Submit");
    s.setAttribute('form','insertForm');
    s.setAttribute
    s.setAttribute('class', 'bnt btn-default btn-outline-secondary')
    var cell = document.createElement("td");
    cell.appendChild(s);
    insertTable.appendChild(cell);
    


    document.getElementById("insertRow").appendChild(f);

}


//Function to do the same as above but for editing an existing row. Additionally, Adds hidden inputs that contain values that are sent to the server in $_POST in order to identify the row to edit
function createEditRowFields(numOfColumns, sqlColumnName, sqlColumnValue){
    if(isEditing == true || isAdding == true) return;
    isEditing = true;
    var f = document.createElement("form");
    f.setAttribute('method',"post");
    f.setAttribute('id', 'editForm');


    var insertTable = document.getElementById("insertRow")

    for (j = 0; j < numOfColumns; j++){
        var cell = document.createElement("td");
        var i = document.createElement("input"); 
        i.setAttribute('type',"text");
        i.setAttribute('name',"editField" + j);
        i.setAttribute('form', 'editForm');
        cell.appendChild(i);
        insertTable.appendChild(cell);

    }


    var hiddenInputColumnName = document.createElement("input");
    hiddenInputColumnName.setAttribute("type", "hidden");
    hiddenInputColumnName.setAttribute("name","columnName")
    hiddenInputColumnName.setAttribute("value", sqlColumnName);
    hiddenInputColumnName.setAttribute("form", "editForm");

    var hiddenInputColumnValue = document.createElement("input");
    hiddenInputColumnValue.setAttribute("type", "hidden");
    hiddenInputColumnValue.setAttribute("name","columnValue")
    hiddenInputColumnValue.setAttribute("value", sqlColumnValue);
    hiddenInputColumnValue.setAttribute("form", "editForm");

    insertTable.appendChild(hiddenInputColumnName);
    insertTable.appendChild(hiddenInputColumnValue);

    var s = document.createElement("input");
    s.setAttribute('type',"submit");
    s.setAttribute('name','updateRow');
    s.setAttribute('value',"Update");
    s.setAttribute('form','editForm');
    s.setAttribute
    s.setAttribute('class', 'bnt btn-default btn-outline-secondary')
    var cell = document.createElement("td");
    cell.appendChild(s);
    insertTable.appendChild(cell);
    




    document.getElementById("insertRow").appendChild(f);

}
</script>