<?php
  include('session.php');
  include('print_errors.php');
  $change_errors = array();
  $del_errors = array();
  if($_SERVER["REQUEST_METHOD"] == "POST") {
    // REGISTER  
    if (isset($_POST['change_user'])) {   
      $change_old_password = pg_escape_string($db,$_POST['change_old_password']);
      $change_new_password = pg_escape_string($db,$_POST['change_new_password']);
      $change_new_passoword2 = pg_escape_string($db,$_POST['change_confirm_new_password']);
      if( empty($change_old_password) and empty($change_new_password)){
         array_push($change_errors, "Pola są puste");
      } elseif (empty($change_old_password)) {
         array_push($change_errors, "Stare hasło jest puste");
      } elseif(empty($change_new_password)){
         array_push($change_errors, "Nowe hasło jest puste");
      } elseif (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/",$change_new_password)) {
        array_push($change_errors, "Nowe hasło musi mieć długość conajmniej 8 znaków i zawierać przynajmniej jedną literę i jedną liczbę"); 
      } elseif($change_new_password != $change_new_passoword2){
         array_push($change_errors, "Hasła są różne");
      } else{
        $sql = "SELECT password FROM users WHERE username = '$login_session'";
        $result = pg_query($db,$sql);
        if($result){
          $count = pg_num_rows($result);
          if($count == 1) {
            $row = pg_fetch_array($result,0,PGSQL_ASSOC);
            $original_password = $row['password'];

            if(md5($change_old_password) == $original_password){
              $change_new_password_md5 = md5($change_new_password);
              $sql = "UPDATE users SET password='$change_new_password_md5' WHERE username='$login_session'";
              $result = pg_query($db,$sql);
              if(!$result) {
                array_push($change_errors, pg_last_error($db));
              } else {
                 $change_notify = "Hasło zmienione pomyślnie";
              }
            } else{
              array_push($change_errors, "Twoje stare hasło jest nieprawidłowe");
            }
          } else {
            array_push($change_errors, "???");
          }
        } else{
          array_push($change_errors, pg_last_error($db));
        }
      }
    }
  }

  pg_close($db);
?>
<html lang="en" dir="ltr">
<head>
<title>Zarządzanie Kontem</title>
<meta charset="UTF-8">
<link rel="stylesheet" href="styles/layout2.css" type="text/css">
</head>
<body>
<div class="wrapper row1">
  <div id="header" class="clear">
    <div id="hgroup">
      <h1><a href="index.php">Witaj <?php echo $login_session ?></a></h1>
      <h2>System zarządzania firmy przewozowej</h2>
    </div>
    <nav>
      <ul>
        <li><a href="index.php">Strona Główna</a></li>
        <li><a class="active" href="user_manage.php">Zarządzanie Kontem</a></li>
        <li><a href="user_bookings.php">Rezerwacje</a></li>
        <li class="last"><a href="logout.php">Wyloguj</a></li>
      </ul>
    </nav>
  </div>
</div>
<!-- content -->
<div class="wrapper row2">
  <div id="container" class="clear">
      <!-- services area -->
      <div class="clear section">
        <div class="two_quarter">
          <div class="modal-content-container">
            <div class="row">
                <div class="col-100">
                  <h2>Zmień hasło</h2>
                </div>
            </div>
              <form action = "" method = "post">
                <div class="row">
                  <div class="col-25">
                    <label>Stare hasło:</label>
                  </div>
                  <div class="col-75">
                    <input type = "password" name = "change_old_password" class = "box"/><br /><br />
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Nowe hasło:</label>
                  </div>
                  <div class="col-75">
                    <input type = "password" name = "change_new_password" class = "box" /><br/><br />
                  </div>
                </div>
                <div class="row">
                  <div class="col-25">
                    <label>Potwierdz hasło:</label>
                  </div>
                  <div class="col-75">
                    <input type = "password" name = "change_confirm_new_password" class = "box" /><br/><br />
                  </div>
                </div>
                <div class="row">
                    <div class="col-100">
                      <input type = "submit" value = " Submit " name="change_user" /><br />
                    </div>
                </div>
              </form>
              <div class = "errorContainer"><?php print_errors($change_errors); ?></div>
              <div class = "notificationContainer"><?php if(isset($change_notify)){ echo $change_notify; } ?></div>
          </div>
      </div>
  </div>
</div>
<!-- Footer -->
<div class="wrapper row3">
  <div id="footer" class="clear">
    <p class="fl_left">Created by Marek Zajac</p>
    <!--<p class="fl_right">Template by <a target="_blank" href="https://www.os-templates.com/" title="Free Website Templates">OS Templates</a></p>-->
  </div>
</div>

</body>
</html>
