<?php
   include('config.php');
   session_start();

   if(!isset($_SESSION['login_user'])){
      header("location:login.php");
   }
   
   $user_check = $_SESSION['login_user'];
   
   $ses_sql = pg_query($db,"select username from users where username = '$user_check' ");
   
   $row = pg_fetch_array($ses_sql,0,PGSQL_ASSOC);
   
   $login_session = $row['username'];
?>