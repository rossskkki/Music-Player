<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
define("DB_HOST", "mydb");
define("USERNAME", "dummy");
define("PASSWORD", "c3322b");
define("DB_NAME", "db3322");

// --- PHP 逻辑处理区 ---
// 先执行所有逻辑判断，决定要显示什么页面

start(); // 调用核心逻辑

// --- 函数定义区 ---

function start() {
    // 检查 GET error 参数 (这个逻辑保持不变)
    if (isset($_GET['error']) && $_GET['error'] === 'session_expired') {
      display_login_form("Session expired!!");
      return;
    }
    if(isset($_POST['login'])) { //if is a POST request
      $authenticate_value = authenticate();
      if ($authenticate_value==0) {  
        // display main page if user logged in successfully
        display_main_page();
      } elseif ($authenticate_value == 1){
        // display login form again with message
        display_login_form('Incorrect password!!');
      }else{
        display_login_form('No such user!!');
      }
    } else {
      $authenticate_value = authenticate();
      // is a GET request
      if ($authenticate_value==0) {  
        display_main_page();
      } else {
        display_login_form(); //first time access
      }
    }
}

function display_login_form($message = '') {
    ?>
    <!DOCTYPE html> 
    <html>
    <head>
        <title>mymusic - Login</title>
        <link rel="stylesheet" type="text/css" href="look.css">
    </head>
    <body>
        <div class="topinfo">
            <h1>3322 Royalty Free Music</h1>
            <div id="source">(Source: <a href="https://www.chosic.com/free-music/all/">https://www.chosic.com/free-music/all/</a>)</div>
        </div>
        <form action="index.php" method="post" id="loginForm">
          <fieldset name="logininfo">
            <legend>LOG IN</legend>
            <div class="username">
                <label for="username">Username:</label> 
                <input type="text" name="username" id="username" required/><br /> 
            </div>
            <div class="password">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required/><br />
            </div>
            <input type="submit" name="login" value="Log in" id="submitbtn">
          </fieldset>      
          <p class="error"> 
            <?php echo htmlspecialchars($message);?>
          </p>
        </form>
        <script src="handle.js"></script>
    </body>
    </html>
    <?php 
}

function authenticate() {
    // 优先检查：这是否是一个登录尝试？
    if (isset($_POST['username']) && isset($_POST['password'])) {
        // 是登录尝试，进行数据库验证
        $conn = mysqli_connect(DB_HOST, USERNAME, PASSWORD, DB_NAME) or die ('Connection Error!'.mysqli_connect_error());
        $stat= mysqli_prepare($conn, "SELECT * FROM account WHERE username = ? AND password = ?");
        $stat->bind_param("ss", $_POST["username"], $_POST["password"]);
        $stat->execute();
        $result = $stat->get_result();

        if (mysqli_num_rows($result) == 1) { // 登录成功
            $row = mysqli_fetch_array($result);
            // 销毁可能存在的旧 session (以防万一)
            if (session_status() == PHP_SESSION_ACTIVE) {
                session_unset();
                session_destroy();
            }
            // 开启新 session 并设置信息
            session_start(); // 确保 session 重新启动
            $_SESSION['id'] = $row['id'];
            $_SESSION['login_time'] = time();
            mysqli_free_result($result);
            mysqli_close($conn);
            return 0; // 登录成功
        } else { // 登录失败 (密码错误或用户不存在)
            $stat2= mysqli_prepare($conn, "SELECT * FROM account WHERE username = ?");
            $stat2->bind_param("s", $_POST["username"]);
            $stat2->execute();
            $result2 = $stat2->get_result();
            $return_code = (mysqli_num_rows($result2) == 1) ? 1 : 2; // 1: 密码错误, 2: 用户不存在
            mysqli_free_result($result);
            mysqli_free_result($result2);
            mysqli_close($conn);
            return $return_code; // 返回错误码
        }
    } 
    // 如果不是登录尝试，再检查是否存在有效的 session
    else if (isset($_SESSION['id'])) {
        if (time() - $_SESSION['login_time'] > 300) { // Session 超时
            session_unset();
            session_destroy();
            // 对于非登录请求的超时，发送 401 (主要影响 AJAX 请求)
            header("HTTP/1.1 401 Unauthorized");
            exit();
        } else {
            return 0; // Session 有效
        }
    } 
    // 既不是登录尝试，也没有有效 session
    else {
        return -1; // 首次访问或 session 丢失
    }
}

function display_main_page() {
    // 在函数内部输出完整的 HTML 结构
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>mymusic</title>
        <link rel="stylesheet" type="text/css" href="look.css">
    </head>
    <body>
        <div class="topinfo">
            <h1>3322 Royalty Free Music</h1>
            <div id="source">(Source: <a href="https://www.chosic.com/free-music/all/">https://www.chosic.com/free-music/all/</a>)</div>
        </div>
        <div class="musicInfo">
            <div class="searchContainer">
              Search <input type="search" name="Search" placeholder="Search for genre" id="genreSearchLine">
            </div>
            <div class="genreButton">
              <button class="genre">Cinematic</button>
              <button class="genre">Games</button>
              <button class="genre">Romantic</button>
              <button class="genre">Study</button>
              <button class="genre">Popular</button>
            </div>
            <div class="musicContainer">
              <div class="title" id="musicSearchTitle"><?php director()?></div>
              <div class="musicPlayContainer"><?php musicListDirector()?></div>
            </div>
        </div>
        <script src="handle.js"></script>
    </body>
    </html>
  <?php
}

function director() {
   if (isset($_GET['Search'])) {
    if ($_GET['Search'] == "Popular") {
      display_popular();
    }else{
      display_search_title();
    }
   } else{
    display_popular();
   }
}

function display_popular() {
  ?>
  Top Eight Popular Music
 <?php 
}

function display_search_title() {
 $search = $_GET['Search'];
 ?>
  All Music under <?php echo $search;?>
 <?php 
}

function musicListDirector() {
  if (isset($_GET['Search'])) {
      display_search_music();
   } else{
    display_popular_music();
    } 
}

function display_popular_music() {
  $conn = mysqli_connect(DB_HOST, USERNAME, PASSWORD, DB_NAME) or die ('Connection Error!'.mysqli_connect_error());
  $stat= mysqli_prepare($conn, "SELECT * FROM music ORDER BY Pcount DESC LIMIT 8");
  $stat->execute(); 
  $result = $stat->get_result();
  while ($row = mysqli_fetch_array($result)) {
    ?>
    <div class="music">
      <div class="musicLeft">
        <img src="play.png" class="play" title="play"></img>
        <img src="pause.png" class="pause" title="pause"></img>
        <img src="play.png" class="continue" title="continue"></img>
        <div class="musicId"><?php echo $row['_id'];?></div>
        <audio autoplay class="musicAudio" id="audio_<?php echo $row['_id'];?>"></audio>
        <div class="musicTA">
          <div class="musicTitle"><?php echo $row['Title'];?></div>
          <div class="musicArtist"><?php echo $row['Artist'];?></div>
        </div>
        <div class="isPause"> PAUSED </div>
      </div>
      <div class="musicRight">
        <div class="musicLength"><?php echo $row['Length'];?></div>
        <img src="CC4.png" class="CC4"></img>
        <div class="musicPlayCount"><img src="count.png" class="countImg"></img><?php echo $row['Pcount'];?></div>
        <div class="musicTags"><?php echo $row['Tags'];?></div>
      </div>
    </div>
  <?php
  }
  mysqli_free_result($result);
  mysqli_close($conn);
}

function display_search_music(){
  $conn = mysqli_connect(DB_HOST, USERNAME, PASSWORD, DB_NAME) or die ('Connection Error!'.mysqli_connect_error());
  $search_term = $_GET['Search']; // 获取原始搜索词
  $search1 = "% ".$search_term." %";
  $search2 = $search_term." %";
  $search3 = "% ".$search_term;
  $search4 = $search_term; // 完全匹配
  
  $stat= mysqli_prepare($conn, "SELECT * FROM music WHERE Tags LIKE ? OR Tags LIKE ? OR Tags LIKE ? OR Tags = ? ORDER BY Pcount DESC");
  $stat->bind_param("ssss", $search1, $search2, $search3, $search4); // 绑定四个参数
  $stat->execute();
  $result = $stat->get_result();
  if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_array($result)) {
      ?>
      <div class="music">
        <div class="musicLeft">
          <img src="play.png" class="play" title="play"></img>
          <img src="pause.png" class="pause" title="pause"></img>
          <img src="play.png" class="continue" title="continue"></img>
          <div class="musicId"><?php echo $row['_id'];?></div>
          <audio autoplay class="musicAudio" id="audio_<?php echo $row['_id'];?>"></audio>
          <div class="musicTA">
            <div class="musicTitle"><?php echo $row['Title'];?></div>
            <div class="musicArtist"><?php echo $row['Artist'];?></div>
          </div>
          <div class="isPause"> PAUSED </div>
        </div>
        <div class="musicRight">
          <div class="musicLength"><?php echo $row['Length'];?></div>
          <img src="CC4.png" class="CC4"></img>
          <div class="musicPlayCount"><img src="count.png" class="countImg"></img><?php echo $row['Pcount'];?></div>
          <div class="musicTags"><?php echo $row['Tags'];?></div>
        </div>
      </div>
    <?php
    }
  }else{
    echo "<div class='emptyMusic'> No music found under this genre (".$_GET['Search'].") </div>";
  }

  mysqli_free_result($result);
  mysqli_close($conn);
}

?>