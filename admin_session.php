<?php
   include('config.php');
   session_start();
   
   if(!isset($_SESSION['login_user'])){
      header("location:login.php");
   }

   $admin_check = $_SESSION['login_user'];
   $sql = pg_query($db,"select uid, username from users where username = '$admin_check' ");
   $row = pg_fetch_array($sql,0,PGSQL_ASSOC);
   $uid = $row['uid'];
   $login_session = $row['username'];

   if($adminUid != intval($uid)){
      echo '<h3>You don\'t have access to this page<h3>
      		<a href="index.php">back to the Main Page</a>';
      exit();
   }

?>