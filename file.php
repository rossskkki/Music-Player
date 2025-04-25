<?php
session_start();
define("DB_HOST", "mydb");
define("USERNAME", "dummy");
define("PASSWORD", "c3322b");
define("DB_NAME", "db3322");


if (!isset($_SESSION['id']) || !isset($_SESSION['login_time']) || (time() - $_SESSION['login_time'] > 300)) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

$musicId = $_GET['musid'];
$conn = mysqli_connect(DB_HOST, USERNAME, PASSWORD, DB_NAME) or die("Error".mysqli_connect_error($conn));
$query = "SELECT * FROM music WHERE _id = '$musicId'";
$result = mysqli_query($conn, $query) or die("Error".mysqli_connect_error($conn));
$row = mysqli_fetch_array($result);
$Path = $row['Path'];
$Filename = $row['Filename'];

increment();

echo "<source src=\"$Path/$Filename\" type=\"audio/mp3\">";
mysqli_free_result($result);
mysqli_close($conn);


function increment() {
    global $conn;
    global $musicId;
    $query = "UPDATE music SET Pcount = Pcount + 1 WHERE _id = '$musicId'";
    mysqli_query($conn, $query) or die("Error".mysqli_connect_error($conn));
}
?>