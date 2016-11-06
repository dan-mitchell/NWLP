<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />        
        <title>Player Signup Processing</title>
        <link href="style/wipStyle.css" rel="stylesheet" type="text/css">


        <?php
        // define variables and set to empty values
        $player = $email = $nickname = $share = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
             $player = cleanup_input($_POST["player"]);
             $email = cleanup_input($_POST["email"]);
             $nickname = cleanup_input($_POST["nickname"]); 
	        if ($nickname == ""){ $nickname = $player; };
             if (empty($_POST["share"]))  {
                 $share = "NO";
             } else {
                 $share = cleanup_input($_POST["share"]);
             }
             $con=mysqli_connect("sql206.byethost11.com","b11_15675702","S3rver3O8","b11_15675702_NWLotto");
              // Check connection
              if (mysqli_connect_errno())
  	         {
                  echo "Failed to connect to MySQL: " . mysqli_connect_error();
              }
  	         else
  	         {
	  	        $query = "SELECT * FROM Players WHERE PLAYER = '".$player."'";
		        $row = mysqli_fetch_array(mysqli_query($con, $query),MYSQLI_ASSOC);
		        if ($row==NULL) {
		        // Player DNE, add to table
		           mysqli_query($con,"INSERT INTO Players (PLAYER, NICKNAME, EMAIL, SHARE, SIGNEDUP, CURRENT) VALUES ('$player', '$nickname', '$email', '$share', '1', '0')");
		        } else {
		        // Change exiting player info
		           mysqli_query($con,"UPDATE Players SET NICKNAME='$nickname', EMAIL='$email', SHARE='$share', SIGNEDUP='1' WHERE PLAYER='$player'");
		        }
	        }
	        mysqli_close($con);

        }

        function cleanup_input($data) {
           $data = trim($data);
           $data = stripslashes($data);
           $data = htmlspecialchars($data);
           return $data;
        }
        ?>

    </head>

    <body>
        <section class="container signUp">
            <h1 class="note">Welcome! You are now signed up with the following information:<br></h1>
            <table class="playerInfo">
                <tr>
                    <td class="formFieldLabel">Player Name:</td>
                    <td class="formField"><?php echo $player; ?></td>
                </tr>
                <tr>
                    <td class="formFieldLabel">Nickname:</td>
                    <td class="formField"><?php echo $nickname; ?></td>
                </tr>
                <tr>
                    <td class="formFieldLabel">Email:</td>
                    <td class="formField"><?php echo $email; ?></td>
                </tr>
                <tr>
                    <td class="formFieldLabel">Email sharing:</td>
                    <td class="formField"><?php echo $share; ?></td>
                </tr>
            </table>
            <a href="wip.php"><button>BACK</button></a>
        </section>
    </body>
</html>
