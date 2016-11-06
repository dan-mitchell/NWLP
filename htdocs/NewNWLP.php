<?php
	//Create an array with the display names of the lottery games 
	$gameNames = array( "super" => "SUPERLOTTO PLUS", "mega" => "MEGAMILLIONS", "power" => "POWERBALL", "fan5" => "FANASTY 5");
	$disp = '';

    //Connect with Winnings Database    
    $con=mysqli_connect("sql206.byethost11.com","b11_15675702","S3rver3O8","b11_15675702_NWLotto");
    // Check connection
    if (mysqli_connect_errno())
    {
	    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    else
    {
        //WINNINGS
        $win = mysqli_query($con,"SELECT  * FROM Winnings");  //Fetch all the entries from the winnings table 
        $row = mysqli_fetch_array($win);  //Process the first entry (current) seperately from the rest
        $amt['current'] = $row['AMOUNT'];
        while($row = mysqli_fetch_array($win))  // Fetch win amounts only from games we have current tickets for
        {
            $type = $row['TYPE'];
            $date = $row['DATE'];
		    if (mysqli_num_rows( mysqli_query($con,"SELECT * FROM LotteryNumbers WHERE GAMETYPE = '$type' AND STARTDATE <= '$date' AND ENDDATE >= '$date'")) > 0)
            {
			    $amt[$type] = $row['AMOUNT'];  //Store Winnings info
				$disp .= "$type,$gameNames[$type]%";  //Load string with results info
		    }
            else
            {
				$amt[$row['TYPE']] = NULL;
		    }
        }
        #$amt = array_column($result, 'AMOUNT', 'TYPE'); not avaible in PHP ver 5.2

        //PLAYERS Fetch Special Signup row (this will determine if we are in regular play or sign ups)
        $playList=mysqli_query($con,"SELECT * FROM Players WHERE PLAYER='SiGnUp!3O8'");
        $SUP = mysqli_fetch_array($playList, MYSQLI_ASSOC)['SIGNEDUP'];

        // Fetch all the rest
        $playList=mysqli_query($con,"SELECT * FROM Players WHERE PLAYER != 'SiGnUp!3O8'");
        $indx=0;
        while($row=mysqli_fetch_array($playList,MYSQLI_ASSOC))
        {
            $players[$indx]=$row;
            $indx++;
        }

        //Color Harmonies. Fetch the current theme and its harmonies
        $harmonies = mysqli_query($con,"SELECT * FROM ColorHarmonies WHERE HARMID = 1");
        $currentTheme = mysqli_fetch_array($harmonies, MYSQLI_ASSOC)['THEME'];   //Current theme name is contained in THEME field of special record
        $currentTheme = "w3-theme-light-green"; //Hardcoded for development purposes
        // Fetch the Current theme harmonies
        $harmonies = mysqli_query($con,"SELECT * FROM ColorHarmonies WHERE THEME = '$currentTheme' AND HARMID != 1");
        $row = mysqli_fetch_array($harmonies);
        $theme = array( "color" => $row['COLOR'],
                        "light" => $row['LIGHT'],
                        "dark"  => $row['DARK'],
                        "comp"  => $row['COMP'],
                        "adjr"  => $row['ADJR'],
                        "adjl"  => $row['ADJL'],
                        "trir"  => $row['TRIR'],
                        "tril"  => $row['TRIL'],
                        "compr" => $row['COMPR'],
                        "compl" => $row['COMPL']);

    }
    mysqli_close($con);
    //Set up button position array
    $playerCol = array("ui-block-a", "ui-block-b", "ui-block-c", "ui-block-d");

?>

<!DOCTYPE html>

<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Northwoods Lottery Pool</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="../style/w3.css">
        <link rel="stylesheet" href="../style/<?php echo $currentTheme; ?>.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css">
        <!-- Include the jQuery library -->
        <script src="http://code.jquery.com/jquery-1.11.3.min.js"></script>

        <style>
            <?php
                echo ".w3-theme-comp {color: #fff !important; background-color: #" . $theme['comp'] ."!important}
                    .w3-text-comp {color: #" . $theme['comp'] . "!important}
                    .w3-theme-adjr {color: #fff !important; background-color: # " . $theme['adjr'] . "!important}
                    .w3-text-adjr {color: #" . $theme['adjr'] . "!important}
                    .w3-theme-adjl {color: #fff !important; background-color: #" . $theme['adjl'] . "!important}
                    .w3-text-adjl {color: #" . $theme['adjl'] . "!important}
                    .w3-theme-trir {color: #fff !important; background-color: #" . $theme['trir'] . "!important}
                    .w3-text-trir {color: #" . $theme['trir'] . "!important}
                    .w3-theme-tril {color: #fff !important; background-color: #" . $theme['tril'] . "!important}
                    .w3-text-tril {color: #" . $theme['tril'] . "!important}
                    .w3-theme-compr {color: #fff !important; background-color: #" . $theme['compr'] . "!important}
                    .w3-text-compr {color: #" . $theme['compr'] . "!important}
                    .w3-theme-compl {color: #fff !important; background-color: #" . $theme['compl'] . "!important}
                    .w3-text-compl {color: #" . $theme['compl'] . "!important}";
            ?>

            /*
              ========================================
              Results
              ========================================
            */
            .winning_number_sm{
                width: 198px;
                margin: 0 auto;
                padding: 0;
                list-style: none;
            }
            .winning_number_sm li  {
                width:29px;
                line-height:30px;
                background:url("../../images/white-ball-sm.png") no-repeat center;
                padding:2px 2px 2px 2px;
                margin:0 5px 0px 0;
                text-align:center;
                margin-right:0;
                font-weight:bold;
                color:#de6a1b;
                font-family:Arial, Geneva, sans-serif;
                font-size:16px;
                float: left;
            }

            .winning_number_sm li.mega  {
                color:#09c;
                font-weight:bold;
            }

            .winning_number_sm li.noMega  {
	            visibility:hidden;
            }
		
            hr.thick  {
                border-color: #<?php echo $theme['color']; ?>;
                border-style:ridge;
                border-width:10px;
            }

            .draw_games {
	            color: #<?php echo $theme['dark']; ?>;
        
            }
		   		
            .results {
	            color: #<?php echo $theme['dark']; ?>;
	            font-family:Verdana,sans-serif;
	            font-size:20px; 
            }
            .results h1 {
                font-size: 4em;
            }
            #resultsPage div  h2 {
                text-align:left;
                margin-left:10%;
          
            }
		  
            #resultsCaption {
                font-weight:bold;
                color: #<?php echo $theme['light']; ?>;
                padding: 20px 0 10px 0; 
          
            }
	
            .game_win_amt {
                padding:20px 0px 20px 100px;
                font-weight:600;
                font-size:125%;
          
            }
		
            /*.picks  {
                font-family:"Courier New", Courier, monospace;
                font-weight:600;
            }*/
		
            .match, .match_amt  {
                padding:0px 10px 10px 20px;
                font-weight:400;
          
            }
		
            span.match_pick  {
                font-weight: bold;
                color: #<?php echo $theme['trir']; ?>; 
          
            }

            span.mega_pick  {
                font-weight: bold;
                color: #<?php echo $theme['light']; ?>;
            }

            #resultsSummary {
                width: 100%;
                text-align: center;
                color: #<?php echo $theme['color']; ?>;
            }
            #resultsSummary hr {
                border:10px solid #<?php echo $theme['dark']; ?>;
            }
            #resultsSummary h1 {
                font-size: 250%;
                font-weight: bold;
                margin-top: 20px;
                color: #<?php echo $theme['compl']; ?>;
                text-shadow: 2px 2px 8px #<?php echo $theme['dark']; ?>;
            }

        </style>

        <script>
            $(document).ready(function ()
            {
                $("#displayArea").html("<p>Beginning POST Call...</p>");
                $.post("DisplayLotteryResults.php",
                            { "games": $("#gamesToDisplay").text() },
                            function (data, status) { $("#displayArea").html(data) });
            });

        </script>
    </head>
    <body class="w3-theme-l2" onload="getMessages()">
        <!--******************** Navigation Page *************************-->
        <div class="w3-container">
            <!-- Nav for large & Medium screens -->
            <div class="w3-card-8 w3-round-large w3-hide-small">
                <ul class="w3-navbar w3-theme-d3 w3-center w3-round-large ">
                    <li><a href="#" title="Lottery Pool Home Page" onclick="openPage('homePage')">Home</a></li>
                    <li><a href="#" title="List of Current Players" onclick="openPage('playersPage')">Players</a></li>
                    <li><a href="#" title="Our Lottery Pool Rules" onclick="openPage('rulesPage')">Rules</a></li>
                    <li><a href="#" title="Copy of Our Current Tickets" onclick="openPage('ticketsPage')">Tickets</a></li>
                    <li><a href="#" title="See How We Are Doing So Far" onclick="openPage('resultsPage')">Results</a></li>
                    <li><a href="javascript:void(0)" title="Change Player info" onclick="openPage('editPage')">Edit</a></li>
                    <li class="w3-right"><a href="#" title="Signup for our lottery pool"><b>Signup</b></a></li>
                    <li class="w3-dropdown-hover"><a href="#" title="Links to Offical Lottery pages">Lotto</a>
                        <div class="w3-dropdown-content w3-theme-d1 w3-card-4 w3-large">
                            <a href="http://www.calottery.com/play/draw-games/superlotto-plus"><b>SuperLotto</b></a>
                            <a href="http://www.calottery.com/play/draw-games/mega-millions"><b>MegaMillions</b></a>
                            <a href="http://www.calottery.com/play/draw-games/powerball"><b>Powerball</b></a>
                        </div>
                    </li>
                </ul>
            </div>
            <!-- Nav for small screens -->
            <div class="w3-card-8 w3-theme-d3 w3-round-large w3-hide-medium w3-hide-large">
                <ul class="w3-theme-d3 w3-round-large w3-padding-large">
                    <li class="w3-dropdown-hover">
                        <a href="#"><i class="fa fa-bars"> Menu</i></a>
                        <ul class="w3-dropdown-content w3-card-4 w3-theme-l5 w3-large" style="list-style-type: none;">
                            <li><a href="#" onclick="openPage('homePage')">Home</a></li>
                            <li><a href="#" onclick="openPage('playersPage')">Players</a></li>
                            <li><a href="#" onclick="openPage('rulesPage')">Rules</a></li>
                            <li><a href="#" onclick="openPage('ticketsPage')">Tickets</a></li>
                            <li><a href="#" onclick="openPage('resultsPage')">Results</a></li>
                            <li><a href="#" onclick="openPage('editPage')">Edit Nickname</a></li>
                            <li><a href="javascript:void(0)"><b>Signup</b></a></li>
                        </ul>
                    </li>
                    <li class="w3-dropdown-hover">
                        <div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Lotto <i class="fa fa-caret-down"></i></div>
                        <ul class="w3-dropdown-content w3-card-4 w3-theme-l5 w3-large" style="list-style-type: none;">
                            <li><a href="http://www.calottery.com/play/draw-games/superlotto-plus"><b>SuperLotto</b></a></li>
                            <li><a href="http://www.calottery.com/play/draw-games/mega-millions"><b>MegaMillions</b></a></li>
                            <li><a href="http://www.calottery.com/play/draw-games/powerball"><b>Powerball</b></a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div> <!--******************End of Navigation Page******************-->

        <!--******************** Home Page *************************-->
        <div class="w3-container page" id="homePage">

            <!-- Header Graphics -->
            <header  class="w3-display-container w3-padding w3-hide-medium w3-hide-small">
                <img src="../images/Woods-Exterior.gif" alt="Northwoods Exterior" class="w3-round-large w3-opacity" style="width: 100%; opacity: 0.7">
                <div class="w3-display-topleft w3-container w3-padding" style="margin-top: 5%;">
                    <img  src="../images/NW%20-%20Lottery%20Pool.png" alt="NW - Lottery Pool (147K)" style="width: 100%;" />
                </div>
                <?php
                    //Display the Signup Notice
                    if($SUP)
                        echo "<div class='w3-display-bottomleft w3-spin' style='margin-left: 43%; margin-bottom: 7%;'>
                                <img src='../images/NewPool.png' alt='Signup Notice' style='width: 200px; ' />
                            </div>";
                ?>
                 
            </header>
            <header class="w3-container w3-theme-l4 w3-round w3-padding-small w3-margin w3-hide-large">
                <img  src="../images/NW%20-%20Lottery%20Pool.png" alt="NW - Lottery Pool (147K)" style="width: 100%;" />
                <?php
                    //Display the Signup Notice icon only during sign-ups
                    if($SUP)
                        echo "<div class='w3-display-bottomleft w3-spin' style='margin-left: 23%; margin-bottom: 7%;'>
                                <img src='../images/NewPool.png' alt='Signup Notice' style='width: 200px; ' />
                            </div>";
                ?>
            </header>

            <!-- Current Stats -->
            <div class="w3-row-padding">
                <div class="w3-card-8 w3-half w3-center">
                    <header class="w3-theme-dark w3-padding w3-round-large" style="border: 2px solid #<?php echo $theme['trir']; ?>;">
                        <h2 class="w3-text-comp w3-text-shadow"><b>Current Winnings</b></h2>
                    </header>
                    <div class="w3-container w3-card-4 w3-round-large w3-theme-l3" style="height: 240px; border: 2px solid #<?php echo $theme['trir']; ?>;">
                        <p class="w3-jumbo" id="current"> 
                            <span class="w3-badge w3-theme-d3"><sup><i>&nbsp;$</i></sup><b><?php echo $amt['current'] ?>&nbsp;</b></span>
                        </p>
                    </div>
                </div>

                <div class="w3-card-8 w3-half">
                    <header class="w3-theme-dark w3-center w3-padding w3-round-large" style="border: 2px solid #<?php echo $theme['trir']; ?>;">
                        <h2 class="w3-text-comp w3-text-shadow"><b>Current Drawings</b></h2>
                    </header>
                    <div class="w3-theme-l3 w3-container w3-card-4 w3-center w3-xxlarge w3-text-theme w3-text-shadow w3-round-large w3-hide-medium w3-hide-small"
                          style="height: 240px; line-height: 0.75em; border: 2px solid #<?php echo $theme['trir']; ?>;">
                        <?php
		                        //Only display results for current games
		                        if($amt['super'] != NULL) echo "<p><b>SuperLotto Plus: $" .  $amt['super'] . "</b></p>";
		                        if($amt['mega'] != NULL) echo "<p><b>MegaMillions: $" .  $amt['mega'] . "</b></p>";
		                        if($amt['power'] != NULL) echo "<p><b>Powerball: $" .  $amt['power'] . "</b></p>";
		                        if($amt['fan5'] != NULL) echo "<p><b>Fantasy 5: $" .  $amt['fan5'] . "</b></p>"; 
                        ?>
                    </div>
                    <div class="w3-theme-l3 w3-container w3-card-4 w3-center w3-xlarge w3-text-theme w3-text-shadow w3-round-large w3-hide-large"
                          style="height: 240px; border: 2px solid #<?php echo $theme['trir']; ?>;">
                        <?php
		                        //Only display results for current games
		                        if($amt['super'] != NULL) echo "<p><b>SuperLotto Plus: $" .  $amt['super'] . "</b></p>";
		                        if($amt['mega'] != NULL) echo "<p><b>MegaMillions: $" .  $amt['mega'] . "</b></p>";
		                        if($amt['power'] != NULL) echo "<p><b>Powerball: $" .  $amt['power'] . "</b></p>";
		                        if($amt['fan5'] != NULL) echo "<p><b>Fantasy 5: $" .  $amt['fan5'] . "</b></p>"; 
                        ?>
                    </div>
                </div>
            </div>
        </div> <!--******************End of Home Page******************-->

        <!--******************** Players Page *************************-->
        <div class="w3-container page" id="playersPage">

            <header class="w3-container w3-theme-l4 w3-round w3-padding-small w3-margin">
                <img  src="../images/NW%20-%20Lottery%20Pool.png" alt="NW - Lottery Pool (147K)" style="width: 100%;" />
            </header>

            <div class="w3-card-8 w3-round-xlarge">
                <!--Heading Row-->
                <div class="w3-container w3-round-xlarge w3-theme-dark w3-center"><h1>Current Players</h1></div>
            </div>
            <!--Players List-->
            <div class="w3-container w3-card-4 w3-xxlarge w3-center w3-text-shadow w3-round-xlarge w3-text-theme w3-theme-l3">
                <ul class="w3-ul" id="curRoster">
                <?php
                    $playerCnt = 0;
                    for($indx=0; $indx<count($players); $indx++)
                    {
	   	 		        if ($players[$indx]['CURRENT'])
                        {
  			   		        echo "<li data-nwplayer='".$players[$indx]['PLAYER']."' onmouseenter='swapTitle(this)' onmouseleave='swapTitle(this)' title='".$players[$indx]['PLAYER']."'";
                            echo " class='w3-third w3-border-0 players ";
                            if($SUP)
                                    echo $playerCol[$playerCnt++ % 4] . "'>";
                            else
                            {
                                if ( $players[$indx]['PAID'])
                                {  
							        echo $playerCol[$playerCnt++ % 4] . "'>";
						        }else
                                {
						            echo " owes " . $playerCol[$playerCnt++ % 4] . "'>$$-";
						        }
                            }
						    echo "<b>" . $players[$indx]['NICKNAME']."</b></li>";
					    }
				    }
		        ?>
                </ul>
                <!-- **********Only Display this section during signups **********-->
                <?php
                    if($SUP)
                    {
                        echo "<div class='w3-card-8 w3-round-xlarge'>";
                        echo    "<div class='w3-container w3-round-xlarge w3-theme-dark w3-center'><h1>Signed Up</h1></div>";
                        echo "</div>";
                        echo "<ul class='w3-ul w3-xlarge w3-center w3-text-shadow w3-text-theme' id='newRoster'>";
                        $playerCnt = 0;
                        for($indx=0; $indx<count($players); $indx++)
                        {
	   	 				    if ($players[$indx]['SIGNEDUP'])
                            {
  			   				    echo "    <li onmouseenter='swapTitle(this)' onmouseleave='swapTitle(this)' title='".$players[$indx]['PLAYER']."'";
                                echo        " data-nwplayer='".$players[$indx]['PLAYER']."' data-nwnickname='".$players[$indx]['NICKNAME']."'";
                                echo        " class='w3-quarter w3-border-0 players ";
							    if ( $players[$indx]['PAID'])
                                {
								    echo "paid " . $playerCol[$playerCnt++ % 4] . "'>";
							    }else
                                {
								    echo "owes " . $playerCol[$playerCnt++ % 4] . "'>$$-";
							    }
							    echo $players[$indx]['NICKNAME']."</li>";
                            }
					    }
                        echo "</ul>";
                    }
                ?>
            </div>
        </div> <!--******************End of Players Page******************-->

        <!--******************** Rules Page *************************-->
        <div class="w3-container page" id="rulesPage">

            <header class="w3-container w3-theme-l4 w3-round w3-padding-small w3-margin">
                <img  src="../images/NW%20-%20Lottery%20Pool.png" alt="NW - Lottery Pool (147K)" style="width: 100%;" />
            </header>

            <div class="w3-card-8 w3-round-xlarge">
                <!--Heading Row-->
                <div class="w3-container w3-round-xlarge w3-theme-dark w3-center"><h1>Lottery Pool Rules</h1></div>
            </div>
            <div class="w3-row">
                <div class="w3-col m2">&nbsp;</div>
                <ol class="w3-col m8 w3-ul w3-theme-l1 w3-xlarge w3-card-8 w3-round-large" style="list-style-type: disc; border: 2px solid #<?php echo $theme['compr']; ?>;" id ="rulesList"></ol>
                <div class="w3-col m2"></div>
            </div>
        </div> <!--******************End of Rules Page******************-->

        <!--******************** Tickets Page *************************-->
        <div class="w3-container page" id="ticketsPage">

            <header class="w3-container w3-theme-l4 w3-round w3-padding-small w3-margin">
                <img  src="../images/NW%20-%20Lottery%20Pool.png" alt="NW - Lottery Pool (147K)" style="width: 100%;" />
            </header>

            <div class="w3-card-8 w3-round-xlarge">
                <!--Heading Row-->
                <div class="w3-container w3-round-xlarge w3-theme-dark w3-center"><h1>OUR TICKETS</h1></div>
            </div>

            <div class="w3-row">
                <div class="w3-col m1">&nbsp;</div>
                <div class="w3-col m10 w3-xxlarge w3-text-tril w3-shadow w3-center" id="ticketMsg"></div>
                <div class="w3-col w3-rest">&nbsp;</div>
            </div>
            <!-- Display ticket thumbnail -->
            <div class="w3-container">
                <div class="w3-quarter">&nbsp;</div>
                <div class="w3-half w3-card-8 w3-round-xlarge w3-hover-opacity" style="border: 4px solid #<?php echo $theme['compr']; ?>;">
                    <p class="w3-theme-dark w3-round-large w3-center w3-large"><small><b>Click on the image to enlarge it.</b></small></p>
                    <img src="../images/NW - Current Tickets.png" alt="Ticket Thumbnail View"
                          onclick="document.getElementById('ticketFull').style.display='block'" style="width: 100%;">
                </div>
            </div>
            <!-- Display ticket full size -->
            <div id="ticketFull" class="w3-modal w3-animate-zoom" onclick="this.style.display='none'">
                <img class="w3-modal-content" src="../images/NW - Current Tickets.png" alt="Ticket normal View" style="width: 100%;">
            </div>
        </div><!--******************End of Tickets Page******************-->

        <!--******************** Results Page *************************-->
        <div class="w3-container page" id="resultsPage">

            <header class="w3-container w3-theme-l4 w3-round w3-padding-small w3-margin">
                <img  src="../images/NW%20-%20Lottery%20Pool.png" alt="NW - Lottery Pool (147K)" style="width: 100%;" />
            </header>

            <div class="w3-card-8 w3-round-xlarge">
                <!--Heading Row-->
                <div class="w3-container w3-round-xlarge w3-theme-dark w3-center"><h1>RESULTS</h1></div>
            </div>
            <!---->
                <section class="w3-container w3-theme-l2">
                    <div>
                        <?php 
                            echo "<p id='gamesToDisplay' style='display:none;'>$disp</p>";  //Store results info in an invisible area
                        ?>
                        <!--Display Lotto Results-->
                        <div class="w3-center" id="displayArea"></div>

                    </div>
                </section>
            </div> <!--******************End of Results Page ******************-->

        <!--******************** Edit Page *************************-->
        <div id="editPage" class="w3-container page">
            <header class="w3-container w3-theme-l4 w3-round w3-padding-small w3-margin">
                <img  src="../images/NW%20-%20Lottery%20Pool.png" alt="NW - Lottery Pool (147K)" style="width: 100%;" />
            </header>

            <div class="w3-card-8 w3-round-xlarge">
                <!--Heading Row-->
                <div class="w3-container w3-round-xlarge w3-theme-dark w3-center"><h1>Edit Player</h1></div>
            </div>
            <!--==================== Player Name Dialog ====================-->
            <!--<div id="playerDialog" class="w3-container w3-card-8 w3-theme-l3">
                <div class="w3-container w3-xlarge w3-center" id="editMsg"></div> Show the Change Name Intro paragraph-->
                    <?php
                        /*Display Players List 
                        echo '<ul id="curPlayerList" class="w3-ul w3-center w3-xlarge">';

                        for($indx=0; $indx<count($players); $indx++)
                        {
                            if ($players[$indx]['CURRENT'])
                            {
                                echo '<li class="w3-third players">';
                                echo    '<a href="#playersInfo" onclick="loadPlayerFields(this)" data-transition="slidefade" data-rel="popup" ';
                                echo       'data-nwplayer="'.$players[$indx]["PLAYER"].'" data-nwnickname="'.$players[$indx]['NICKNAME'].'" data-nwemail="'.$players[$indx]['EMAIL'].'">';
                                echo        '<div class="ui-grid-a ui-responsive">';
                                echo            '<p class="ui-block-a nwplayer">'.$players[$indx]["PLAYER"].'</p>';
                                echo            '<p class="ui-block-b nwnickname">'.$players[$indx]["NICKNAME"].'</p>';
                                echo        '</div>';
                                echo        '<div class="ui-grid-solo">';
                                echo            '<p class="ui-block-a nwemail">&nbsp;'.$players[$indx]["EMAIL"].'</p>';
                                echo        '</div>';
                                echo    '</a>';
                                echo '</li>';
                            }
                        }
                        echo '</ul>';*/
                    ?>
            <div class="w3-col" style="width:16.67%">&nbsp;</div>
            <div class="w3-col w3-card-4 w3-round-xlarge w3-twothird">
                        <h1 class="w3-center">Select Player Information to Change</h1>

                        <select class="w3-select w3-btn w3-theme-l3 w3-large"  onclick="clearPlayerFields()" id="playerOption">
                          <?php
                              foreach($players as $x) {
                                  $opt  = "<option value='" . $x['PLAYER'] . "' ";
                                  $opt .= "data-nwplayer='" . $x["PLAYER"] . "' ";
                                  $opt .= "data-nwnickname='" . $x['NICKNAME']. "' ";
                                  $opt .= "data-nwemail='" .$x['EMAIL'] ."' ";
                                  $opt .= ">" . $x['PLAYER'] . "</option>";
                                  echo  $opt;
                              }
                          ?>
                        </select>
                        <p class="w3-btn w3-tiny w3-wide w3-theme-trir" onclick="loadPlayerFields()">LOAD</p>
                        <br />
                        <br />
                    <form id="playerInfoForm" action="">
                        <label class="w3-label">Player:</label>
                        <input class="w3-input" type="text" size = 50 name="player" id="player" disabled  placeholder="Name for your check"/>
                        <label class="w3-label">Nickname:</label>
    	                <input class="w3-input" type="text"  size = 50  name="nickname" id="nickname" placeholder="Nickname for use on the website"/>
                        <label class="w3-label">Email:</label>
                        <input class="w3-input" type="email"  size = 50  name="email" id="email" placeholder="Get on our mailing list"/>
                        <p class="w3-center"><button class="w3-btn w3-round w3-theme-dark">Change</button></p>
                            
                </form>
            </div>
                    <!--++++++++++++++ End of Popup +++++++++++++++-->

                </div>
        </div> <!--******************End of Edit Page ******************-->

            <!--<a href="sms:+9098519404">Send SMS to us </a>-->
            <?php
                // Call Me
                //mail("9098519404@messaging.sprintpcs.com", "", "Your packaged has arrived!", "From: Northwoods Lottery Pool <david@davidwalsh.name>\r\n");
            ?>
        <script>
            function accordionAction(id) {
                var x = getElementById(id);
                if (x.className.indexOf("w3-show") == -1) {
                    x.className += " w3-show";
                } else {
                    x.className = x.className.replace(" w3-show", "");
                }
            }
            function openPage(pageName) {
                var i;
                var x = document.getElementsByClassName("page");
                for (i = 0; i < x.length; i++) {
                    x[i].style.display = "none";
                }
                document.getElementById(pageName).style.display = "block";
            }
            function getMessages() {
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function () {
                    if (this.readyState == 4 && this.status == 200) {
                        processNotes(this);
                    }
                };
                xmlhttp.open("POST", "NWmessages.xml", true);
                xmlhttp.send();
                openPage("homePage");
            }

            function processNotes(xml) {
                var xmlDoc = xml.responseXML;
                //var x = xmlDoc.getElementsByTagName("note");
                document.getElementById("rulesList").innerHTML = xmlDoc.getElementById("rules").innerHTML;
                document.getElementById("ticketMsg").innerHTML = xmlDoc.getElementById("tickets").innerHTML;
                document.getElementById("editMsg").innerHTML = xmlDoc.getElementById("changeName").innerHTML;
            }

            function loadPlayerFields() {
                $("#player").attr("value", $(":selected").attr("data-nwplayer"));
                $("#nickname").attr("value", $(":selected").attr("data-nwnickname"));
                $("#email").attr("value", $(":selected").attr("data-nwemail"));
            }
            function clearPlayerFields() {
                $("#player").attr("value", "");
                $("#nickname").attr("value", "");
                $("#email").attr("value", "");
                $("#player").text("");
                $("#nickname").text("");
                $("#email").text("");
                document.getElementById("playerInfoForm").reset();

            }
        </script>
    </body>
</html>
