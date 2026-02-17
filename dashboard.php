<?php
  session_start();
  
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if($_POST['log_out_btn']){
      session_unset();
      session_destroy();
      
      // Redirect to the login page
      header("Location: login.php");
      exit();
    }
  }
?>


<!DOCTYPE html>
<html>
<head>
  <title>CSCI 6040</title>
  <link rel="stylesheet" href="custom_style.css">
</head>
<body>
  <div id="content_div">
    <h1>Welcome to CSCI 6040</h1>
    <h2>Dashboard Under-contstruction</h2>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <h3>User Information</h3>
   <h4>User name:<?php echo htmlspecialchars($_SESSION['user_name']); ?></h4>
  <h4>Email: <?php echo htmlspecialchars($_SESSION['user_email']); ?></h4>
      <input type="submit" id="submit_btn" name="log_out_btn" value="Log Out">
    </form>
  </div>
</body>
</html>
