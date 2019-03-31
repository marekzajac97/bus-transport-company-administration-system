<?php
   include("config.php");
   include('print_errors.php');
   session_start();
   $log_errors = array();
   if($_SERVER["REQUEST_METHOD"] == "POST") {
      
      $myusername = pg_escape_string($db,$_POST['username']);
      $mypassword = pg_escape_string($db,$_POST['password']);
      if( empty($myusername) and empty($mypassword) ){
         array_push($log_errors, "Username and Password fields are empty");
      } elseif (empty($myusername)) {
         array_push($log_errors, "Username field is empty");
      } elseif(empty($mypassword)){
         array_push($log_errors, "Password field is empty");
      } else{
         $mypassword = md5($mypassword);
         
         $sql = "SELECT uid FROM users WHERE username = '$myusername' and password = '$mypassword'";
         $result = pg_query($db,$sql);
         //$row = pg_fetch_array($result,0,PGSQL_ASSOC);
         //$active = $row['active'];
         
         $count = pg_num_rows($result);
         
         if($count == 1) {
            $_SESSION['login_user'] = $myusername;
            $row = pg_fetch_array($result,0,PGSQL_ASSOC);
            $uid = $row['uid'];
            
            if(intval($uid) == $adminUid) {
               header("location: admin_home.php");
            } else {
               header("location: index.php");
            }
         }else {
            array_push($log_errors, "Username or Password is invalid");
         }
      }
   }

   pg_close($db);
?>
<html>
   <head>
      <title>Login Page</title>
      <meta charset="UTF-8">
      <link rel="stylesheet" href="styles/layout2.css" type="text/css">
   </head>
   
   <body>
   
      <div>
         <div class = "LoginBoxContainer">
            <div class = "loginContainer"><b>Login</b></div>
            
            <div class = "modal-content-container">
               
               <form action = "" method = "post">
                  <label>UserName  :</label><input type = "text" name = "username" class = "box"/><br /><br />
                  <label>Password  :</label><input type = "password" name = "password" class = "box" /><br/><br />
                  <input type = "submit" value = " Submit "/><br />
               </form>
               
               <div class = "errorContainer"><?php print_errors($log_errors); ?></div>
               
            </div>
            
         </div>
         
      </div>

   </body>
</html>