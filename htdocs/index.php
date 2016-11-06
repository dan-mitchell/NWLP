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
        $amt[$row['TYPE']] = $row['AMOUNT'];
        while($row = mysqli_fetch_array($win))  // Fetch win amounts only from games we have current tickets for
        {
		    if (mysqli_num_rows( mysqli_query($con,"SELECT * FROM LotteryNumbers WHERE GAMETYPE = '" . $row['TYPE'] . "'  AND STARTDATE <= '" . $row['DATE'] . "' AND ENDDATE >= '" . $row['DATE'] . "'")) > 0)
            {
			    $amt[$row['TYPE']] = $row['AMOUNT'];  //Store Winnings info
				$disp .= $row['TYPE'] . "," . $gameNames[$row['TYPE']] . "%";  //Load string with results info
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
        <!-- Include meta tag to ensure proper rendering and touch zooming -->
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Include jQuery Mobile stylesheets -->
        <link rel="stylesheet" href="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css">
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">

        <!-- Include the jQuery library -->
        <script src="http://code.jquery.com/jquery-1.11.2.min.js"></script>

        <!-- Include the jQuery Mobile library -->
        <script src="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>

        <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css'>
        <link href="style/wipStyle.css" rel="stylesheet" type="text/css">
        <script>
            function swapTitle(x)
            {
                var temp_title = x.title;
                var temp_text = x.innerHTML;
                x.title = temp_text;
                x.innerHTML = temp_title;
            }
            function loadPlayerFields(x)
            {
                if ($("ul").is("#newRoster"))// note:#newRoster only exisits during sign ups
                    $("#player").attr("value", $(x).attr("data-nwplayer"));
                else
                    $("#player").attr("value", $(x).attr("data-nwplayer")).addClass("ui-state-disabled");
                $("#nickname").attr("value", $(x).attr("data-nwnickname"));
                $("#email").attr("value", $(x).attr("data-nwemail"));
                document.getElementById("playerInfoForm").reset();
            }
            function processSignup()
            {
                var curPlayer = $("[data-nwplayer='" + $('#player').val() + "']");
                var pName = $('#player').val();
                var pNick = $('#nickname').val();
                var pMail = $('#email').val();
                if (pNick.trim() == "") pNick = pName;

                $("#player").removeClass("ui-state-disabled");  //Re-enable player input field
                $(curPlayer).attr("data-nwplayer", pName).attr("data-nwnickname", pNick);   //Update name & nickname attributes of all elements for this player
                $(curPlayer).filter("a").not(".ui-icon-edit").addClass("signed ui-icon-check ui-btn-icon-left");    //Add check mark & signed status to sign up button only
                $(curPlayer).filter("a").attr("data-nwemail", pMail);   //Update email attribute for this player
                $(curPlayer).find(".nwnickname").text(pNick);   //Update nickname text in the change name listing
                $(curPlayer).find(".nwemail").text(pMail);  //Update email text in the change name listing
                $(curPlayer).filter("li").not(".owes").text(pNick);  //Update paid name displayed in the player lists 
                $(curPlayer).filter("li.owes").text("$$-"+pNick);  //Update owes name displayed in the player lists 
                if ($("ul").is("#newRoster"))// note:#newRoster only exisits during sign ups
                {
                    if (!$("#newRoster li").is("[data-nwplayer='" + pName + "']"))  //if player is NOT on the list yet
                    {
                        //Add new player to sign up roster
                        var colPos = "ui-block-" + "abcd".charAt($("#newRoster li").length % 4);    //Calculate position in roster
                        $("#newRoster").append("<li onmouseenter='swapTitle(this)' onmouseleave='swapTitle(this)'"+
                                                     "title='" + pName + "' data-nwplayer='" + pName + "' data-nwnickname='" + pNick +
                                                     "' class='players owes " + colPos + "'>$$-" + pNick + "</li>");
                        //All new player to button list
                        colPos = "ui-block-" + "abcd".charAt($("#signupButtons a").length % 4); //Calculate position in button list
                        $("#signupButtons").append('<a href="#playersInfo" onclick="loadPlayerFields(this)" data-transition="slidefade" data-rel="popup" '+
                                                        'data-nwplayer="'+pName+'" data-nwnickname="'+pNick+'" data-nwemail="'+pMail+'"'+
                                                        ' class="ui-btn ui-btn-inline ui-corner-all ui-shadow players signed ui-icon-check ui-btn-icon-left '+ colPos +'">'+
                                                        '<p class="nwnickname">'+pNick+'</p></a>');
                    }
                }
                /* Save the form input to the Database */
                $.post("PlayerSignup.php",
                {   player:     pName,
                nickname:   pNick,
                email:      pMail
                });
            }
            $(document).on("pagecreate", "#playersPage", function ()
            {
                $(".playerList li").on("tap", function ()
                {
                    swapTitle(this);
                });
            });

            $(document).ready(function ()
            {
                $("#displayArea").html("<p>Beginning POST Call...</p>");
                $.post("DisplayLotteryResults.php",
                            { "games": $("#gamesToDisplay").text() },
                            function (data, status) { $("#displayArea").html(data) });
            });
        </script>

    </head>

    <body>
        <!--==================== HOME Page ====================-->
        <div data-role="page" data-theme="c" id="homePage">
            <div data-role="panel" id="lottoLinks">
                <div data-role="controlgroup">
                	<a href="http://www.calottery.com/play/draw-games/mega-millions" class="ui-btn">
		   			    <img src="../images/megamillions-logo.png" alt="mega"/></a>
		            <a href="http://www.calottery.com/play/draw-games/superlotto-plus" class="ui-btn">
		   			    <img src="../images//superlotto-plus-logo.png" alt="lotto"/></a>
		            <a href="http://www.calottery.com/play/draw-games/powerball" class="ui-btn">
		   			    <img src="../images//powerball-logo.png" alt="powerball"/></a>
                </div>
            </div>
            <div data-role="header" data-position="fixed" data-theme="c">
                <img  src="http://www.nwlotterypool.byethost11.com/images/NW%20-%20Lottery%20Pool.png" alt="NW - Lottery Pool (147K)" style="width: 100%;" /> 
            </div>

            <div data-role="main" class="ui-content contentSpacer">
                <div id="homepageButtons">
                    <a href="#lottoLinks" class="ui-btn ui-btn-inline ui-icon-carat-l ui-btn-icon-left ui-btn-b ui-mini">Lottery Links</a>
                    <a href='#playerDialog' class='ui-btn ui-btn-inline ui-btn-b' id='signupButton'>
                        <?php
                            //Display the correct popup dialog: regular or SignUps
                            if($SUP)
                                echo "S I G N&nbsp;&nbsp;U P";
                            else
                                echo "Change Name";
                        ?>
                    </a>
                    
                </div>


                <div class="Earnings ui-grid-a">
		
                    <div class="ui-block-a">
                        <h2>Current Winnings</h2>
                    </div>
                    <div class="ui-block-b">
                        <h2>Current Drawings</h2>
                    </div>

                    <div class="ui-block-a">
                        <p id="current"> <b>$<?php echo $amt['current'] ?><sup><small><u>00</u></small></sup></b></p>
                    </div>
                    <div class="ui-block-b">
                        <?php
		                        //Only display results for current games
		                        if($amt['super'] != NULL) echo "<p>SuperLotto Plus: $" .  $amt['super'] . "</p>";
		                        if($amt['mega'] != NULL) echo "<p>MegaMillions: $" .  $amt['mega'] . "</p>";
		                        if($amt['power'] != NULL) echo "<p>Powerball: $" .  $amt['power'] . "</p>";
		                        if($amt['fan5'] != NULL) echo "<p>Fantasy 5: $" .  $amt['fan5'] . "</p>"; 
                        ?>
                    </div>

                </div>
            </div>

            <div data-role="footer" data-position="fixed" data-fullscreen="true" data-theme="b">
                <div data-role="navbar" data-iconpos="top">
                    <ul>
                        <li><a href="#playersPage" title="List of Current Players" data-icon="user" data-transition="flip">Players</a></li>
                        <li><a href="#rulesPage" title="Our Lottery Pool Rules" data-icon="bullets" data-transition="flip">Rules</a></li>
                        <li><a href="#ticketsPage" title="Copy of Our Current Tickets" data-icon="tag" data-transition="flip">Tickets</a></li>
                        <li><a href="#resultsPage" title="See How We Are Doing So Far" data-icon="star" data-transition="flip">Results</a></li>
                    </ul>
                </div>
            </div>

        </div><!--End of Home Page-->

        <!--==================== Player Name Dialog ====================-->
        <div data-role="page" data-theme ="c" id="playerDialog">

            <div data-role="header" data-position="fixed" data-theme ="b">
                 <?php
                     //Show the correct heading
                     if($SUP)
                        echo "<h1>PLAYER SIGN UP</h1>";
                     else
                        echo "<h1>Edit Player</h1>";
                ?>
            </div>

            <div data-role="main" class="ui-content signUp">

                <?php
                    
                    if($SUP)
                    {   //Show the SignUP Intro paragraph
                        echo '<p class="note">The current lottery pool is about to end. If you want
                         to keep on playing you can sign up on the line by selecting your name below or
                         filling in the sign up form at work. Please have your money in by 
                          <span class="standOut">Saturday, June 13</span>.
                         The cost is still $20 and can be given to Dan, Ed, or Ben.</p>
                                <p class="lastLine"><span>HAPPY FATHERS DAY!</span> --Dan</p>';
                        //Show Player Names
                        echo '<div class="ui-grid-c ui-responsive" id="signupButtons">';
                        $playerCnt = 0;
                        for($indx=0; $indx<count($players); $indx++)
                        {
                            if ($players[$indx]['CURRENT'] or $players[$indx]['SIGNEDUP'])
                            {
                                echo '<a href="#playersInfo" onclick="loadPlayerFields(this)" data-transition="slidefade" data-rel="popup" ';
                                echo    'data-nwplayer="'.$players[$indx]["PLAYER"].'" data-nwnickname="'.$players[$indx]['NICKNAME'].'" data-nwemail="'.$players[$indx]['EMAIL'].'"';
                                echo ' class="ui-btn ui-btn-inline ui-corner-all ui-shadow players ';
                                if($players[$indx]['SIGNEDUP'] != 0)
                                    echo 'signed ui-icon-check ui-btn-icon-left '. $playerCol[$playerCnt++ % 4] . '">';
                                else
                                    echo $playerCol[$playerCnt++ % 4] . '">';
                                echo    '<p class="nwnickname">'.$players[$indx]["NICKNAME"].'</p>';
                                echo '</a>';
                            }
                        }
                        echo '</div>';
                        echo '<a href="#playersInfo" onclick="loadPlayerFields(this)" data-transition="slidefade" data-rel="popup" ';
                        echo    'data-nwplayer="" class="ui-btn ui-corner-all ui-shadow players" id="newPlayerButton">';
                        echo     'NEW PLAYER</a>';
                    }
                    else
                    {   //Show the Change Name Intro paragraph
                        echo '<p class="note">Use this form to sign up for the lottery mail list or if
                                you would like to change the way your name is displayed on the website.
                                <i>(note: this is for current players only.)</i></p>';
                                //Display Players List 
                                echo '<ul data-role="listview" id="curPlayerList">';

                                for($indx=0; $indx<count($players); $indx++)
                                {
                                    if ($players[$indx]['CURRENT'])
                                    {
                                        echo '<li data-icon="edit" class="players">';
                                        echo    '<a href="#playersInfo" onclick="loadPlayerFields(this)" data-transition="slidefade" data-rel="popup" ';
                                        echo       'data-nwplayer="'.$players[$indx]["PLAYER"].'" data-nwnickname="'.$players[$indx]['NICKNAME'].'" data-nwemail="'.$players[$indx]['EMAIL'].'">';
                                        echo        '<div class="ui-grid-a ui-responsive">';
                                        echo            '<p class="ui-block-a nwplayer">'.$players[$indx]["PLAYER"].'</p>';
                                        echo            '<p class="ui-block-b nwnickname">'.$players[$indx]["NICKNAME"].'</p>';
                                        echo        '</div>';
                                        echo        '<div class="ui-grid-solo">';
                                        echo            '<p class="ui-block-a nwemail">'.$players[$indx]["EMAIL"].'</p>';
                                        echo        '</div>';
                                        echo    '</a>';
                                        echo '</li>';
                                    }
                                }
                                echo '</ul>';
                    }
                ?>
                

                <!--++++++++++++++ Player Name Popup Form +++++++++++++++-->
                <div data-role="popup" data-theme="c" data-position-to="window" class="signUp" id="playersInfo">
                    <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn ui-icon-delete ui-btn-icon-notext ui-btn-bottom">Close</a>
                    <div data-role="header" data-theme="b" class="ui-grid-solo ui-responsive">
                        <div class="ui-block-a">
                            <h2 style="text-align: center;">Player Information</h2>
                        </div>
                    </div>
                    <div data-role="main" class="ui-content" data-theme="a">
                
                        <form action="" method="post" onsubmit="processSignup()" id="playerInfoForm">
                            <div class="ui-field-contain">
                                <label for="player">Player:</label>
                                <input type="text" size = 50 name="player" id="player" required  placeholder="Name for your check"/>
                            </div>
                            <div class="ui-field-contain">
                                <label for="nickname">Nickname:</label>
    	                        <input type="text"  size = 50  name="nickname" id="nickname" placeholder="Nickname for our website" data-clear-btn="true"/>
                            </div>
                            <div class="ui-field-contain">
                                <label for="email">Email:</label>
                                <input type="email"  size = 50  name="email" id="email" placeholder="Get on our mailing list" data-clear-btn="true"/>
                            </div>
                            <?php
                                if($SUP)
                                    echo '<input type="submit" value="SIGN UP" data-icon="check" data-iconpos="right" data-inline="true"/>';
                                else
                                    echo '<input type="submit" value="CHANGE" data-icon="check" data-iconpos="right" data-inline="true"/>';
                            ?>
                            
                        </form>
                    </div>
                </div>
                <!--++++++++++++++ End of Popup +++++++++++++++-->

            </div>

            <div data-role="footer" data-fullscreen="true"  data-position="fixed" data-theme="b">
                <h1>Go Back</h1>
                <a href="#homePage" class="ui-btn ui-corner-all ui-shadow ui-btn ui-icon-back ui-btn-icon-notext ui-btn-left">Close</a>
            </div>
        </div>
       <!--==================== End of Player Name Dialog ====================-->

       <!--==================== Players Page ====================-->
       <div data-role="page" data-theme="c" id="playersPage">
            <div data-role="header" data-position="fixed" data-theme="c">
                <img  src="http://www.nwlotterypool.byethost11.com/images/NW%20-%20Lottery%20Pool.png" alt="NW - Lottery Pool (147K)" style="width: 100%;" /> 
            </div>

            <section class="contentSpacer">
                <div data-role="main" class="ui-content" data-theme="c">

                <div class="ui-grid-solo">
                    <!--Heading Row-->
                    <div class="ui-block-a sectionTitle"><h1><u>Current Players</u></h1></div>
                </div>
                <!--Players List-->
                <div class="playerList">
                    <ul class="ui-grid-c ui-responsive" id="curRoster">
                    <?php
                        $playerCnt = 0;
                        for($indx=0; $indx<count($players); $indx++)
                        {
	   	 		            if ($players[$indx]['CURRENT'])
                            {
  			   		            echo "<li onmouseenter='swapTitle(this)' onmouseleave='swapTitle(this)' title='".$players[$indx]['PLAYER']."'";
                                echo " data-nwplayer='".$players[$indx]['PLAYER']."'"; 
                                if($SUP)
                                     echo "class='players " . $playerCol[$playerCnt++ % 4] . "'>";
                                else
                                {
                                    if ( $players[$indx]['PAID'])
                                    {  
							            echo " class='players " . $playerCol[$playerCnt++ % 4] . "'>";
						            }else
                                    {
						                echo " class='players owes " . $playerCol[$playerCnt++ % 4] . "'>$$-";
						            }
                                }
						        echo $players[$indx]['NICKNAME']."</li>";
					        }
				        }
		            ?>
                    </ul>
                </div>
                <!-- **********Only Display this section during signups **********-->
                <?php
                    $signupHTML = '';
                    if($SUP)
                    {
                        $signupHTML  = "<div class='ui-grid-solo'>";
                        $signupHTML .= "    <div class='ui-block-a sectionTitle'><h1><u>Signed Up</u></h1></div>";
                        $signupHTML .= "</div>";
                        $signupHTML .= "<div class='playerList'>";
                        $signupHTML .= "    <ul class='ui-grid-c ui-responsive' id='newRoster'>";
                        $playerCnt = 0;
                        for($indx=0; $indx<count($players); $indx++)
                        {
	   	 				    if ($players[$indx]['SIGNEDUP'])
                            {
  			   				    $signupHTML .= "    <li onmouseenter='swapTitle(this)' onmouseleave='swapTitle(this)' title='".$players[$indx]['PLAYER']."'";
                                $signupHTML .= " data-nwplayer='".$players[$indx]['PLAYER']."' data-nwnickname='".$players[$indx]['NICKNAME']."'";
							    if ( $players[$indx]['PAID'])
                                {
								    $signupHTML .= " class='players paid " . $playerCol[$playerCnt++ % 4] . "'>";
							    }else
                                {
								    $signupHTML .= " class='players owes " . $playerCol[$playerCnt++ % 4] . "'>$$-";
							    }
							    $signupHTML .= $players[$indx]['NICKNAME']."</li>";
                            }
					    }
                        $signupHTML .= "        </ul>";
                        $signupHTML .= "    </div>";
                    }
                    //Output the code for the section
                    echo $signupHTML;
                ?>

            </div>
        </section>

            <div data-role="footer" data-position="fixed" data-fullscreen="true" data-theme="b">
                <div data-role="navbar" data-iconpos="top">
                    <ul>
                        <li class="active"><a href="#homePage" title="Our Lottery Pool Home Page" data-icon="home" data-transition="flip">Home</a></li>
                        <li><a href="#rulesPage" title="Our Lottery Pool Rules" data-icon="bullets" data-transition="flip">Rules</a></li>
                        <li><a href="#ticketsPage" title="Copy of Our Current Tickets" data-icon="tag" data-transition="flip">Tickets</a></li>
                        <li><a href="#resultsPage" title="See How We Are Doing So Far" data-icon="star" data-transition="flip">Results</a></li>
                    </ul>
                </div>
            </div>
        </div><!--End of PLayers Page-->

       <!--==================== Rules Page ====================-->
        <div data-role="page" data-theme="c" id="rulesPage">
            
            <div data-role="header" data-position="fixed" data-theme="c">
                <img  src="http://www.nwlotterypool.byethost11.com/images/NW%20-%20Lottery%20Pool.png" alt="NW - Lottery Pool (147K)" style="width: 100%;" /> 
            </div>

            <section class="contentSpacer">
                <div data-role="main" class="ui-content" data-theme="c">

                    <div class="ui-grid-solo">
                        <!--Heading Row-->
                        <div class="ui-block-a sectionTitle"><h1><u>LOTTERY RULES</u></h1></div>
                    </div>

                    <section class="ui-block-a">
                        <ol id="rules">
                            <li>The entry fee is $20 per player.</li>
                            <li>We will play 5 quick picks for each of the lottery drawings (Super Lotto Plus, Mega Millions, and Power Ball)</li>
                            <li>We will use the advance play feature to play the same numbers for up to 20 consecutive drawings depending on the amount of cash available and the rules for the different drawings (i.e.  Power Ball only allows for 10 advance draws).</li>
                            <li>The pool will last approximately 2 to 4 months depending on  the number of players in the pool and the amount of the winnings.</li>
                            <li>We will take the cash value of the jackpot. Major prizes will be shared equally between all players. Minor prizes will be used to purchase more picks.</li>
                            <li>To be included in the pool a player must have their money in, be at least 18 years of age,  and sign agreeing to these rules.</li>
                            <li>A copy of the tickets we are playing and the people playing in this pool will be posted, additional copies will be provided on request.</li>
                            <li>All players are free to play the lottery on their own with no obligation to share with the group.</li>
                            <li>A charge of $20 will be deducted from the pool’s winnings to cover expenses.</li>
                            <li>Money can be turned into Dan Mitchell, Ed Curry, or Ben Esseling.</li>
                        </ol>
                        <p id="rulesRevised"><small><b><i>Rules revised 2/28/2014</i> DPM</b></small></p>
                    </section>
                </div>
            </section>

            <div data-role="footer" data-position="fixed" data-fullscreen="true" data-theme="b">
                <div data-role="navbar" data-iconpos="top">
                    <ul>
                        <li class="active"><a href="#homePage" title="Our Lottery Pool Home Page" data-icon="home" data-transition="flip">Home</a></li>
                        <li><a href="#playersPage" title="List of Current Players" data-icon="user" data-transition="flip">Players</a></li>
                        <li><a href="#ticketsPage" title="Copy of Our Current Tickets" data-icon="tag" data-transition="flip">Tickets</a></li>
                        <li><a href="#resultsPage" title="See How We Are Doing So Far" data-icon="star" data-transition="flip">Results</a></li>
                    </ul>
                </div>
            </div>
        </div><!--End of Rules Page-->
                
        <!--==================== Tickets Page ====================-->
        <div data-role="page" data-theme="c" id="ticketsPage">
            <div data-role="header" data-position="fixed" data-theme="c">
                <img  src="http://www.nwlotterypool.byethost11.com/images/NW%20-%20Lottery%20Pool.png" alt="NW - Lottery Pool (147K)" style="width: 100%;" /> 
            </div>


            <section class="contentSpacer">
                <div data-role="main" class="ui-content" data-theme="c">

                    <div class="ui-grid-solo">
                        <!--Heading Row-->
                        <div class="ui-block-a sectionTitle"><h1><span>OUR TICKETS</span><br>We start our second year with these tickets.<br>GOOD LUCK!<br></h1></div>
                    </div>

                        <div class="tickets">
                            <p><small><b>Click on the image to enlarge it.</b></small></p>
                            <a href="#ticketPopup" data-rel="popup" data-position-to="window">
                                <img src="../images/NW - Current Tickets.png" alt="Ticket Thumbnail View"></a>
                        </div>

                        <div data-role="popup" id="ticketPopup">
                            <a href="#ticketPage" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
                            <img src="../images/NW - Current Tickets.png" style="width:fill-available;" alt="Ticket Full View">
                        </div>

                </div>
            </section>

            <div data-role="footer" data-position="fixed" data-fullscreen="true" data-theme="b">
                <div data-role="navbar" data-iconpos="top">
                    <ul>
                        <li class="active"><a href="#homePage" title="Our Lottery Pool Home Page" data-icon="home" data-transition="flip">Home</a></li>
                        <li><a href="#playersPage" title="List of Current Players" data-icon="user" data-transition="flip">Players</a></li>
                        <li><a href="#rulesPage" title="Our Lottery Pool Rules" data-icon="bullets" data-transition="flip">Rules</a></li>
                        <li><a href="#resultsPage" title="See How We Are Doing So Far" data-icon="star" data-transition="flip">Results</a></li>
                    </ul>
                </div>
            </div>
        </div><!--End of Tickets Page-->

       <!--==================== Results Page ====================-->
       <div data-role="page" data-theme="c" id="resultsPage">
            <div data-role="header" data-position="fixed" data-theme="c">
                <img  src="http://www.nwlotterypool.byethost11.com/images/NW%20-%20Lottery%20Pool.png" alt="NW - Lottery Pool (147K)" style="width: 100%;" /> 
            </div>

            <section class="contentSpacer">
                <div data-role="main" class="ui-content" data-theme="c">
                    <?php 
                        echo "<p id='gamesToDisplay' style='display:none;'>$disp</p>";  //Store results info in an invisible area
                    ?>
                    <!--Display Lotto Results-->
                    <div id="displayArea"></div>

                </div>
            </section>

            <div data-role="footer" data-position="fixed" data-fullscreen="true" data-theme="b">
                <div data-role="navbar" data-iconpos="top">
                    <ul>
                        <li class="active"><a href="#homePage" title="Our Lottery Pool Home Page" data-icon="home" data-transition="flip">Home</a></li>
                        <li><a href="#playersPage" title="List of Current Players" data-icon="user" data-transition="flip">Players</a></li>
                        <li><a href="#rulesPage" title="Our Lottery Pool Rules" data-icon="bullets" data-transition="flip">Rules</a></li>
                        <li><a href="#ticketsPage" title="Copy of Our Current Tickets" data-icon="tag">Tickets</a></li>
                    </ul>
                </div>
            </div>
        </div><!--End of Results Page-->

    </body>
</html>
