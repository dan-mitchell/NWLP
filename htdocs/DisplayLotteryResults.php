<?php 
// define variables and set to empty values
$game = $lotto = $outputString = '';

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
// Expand strings into arrays and set up variables with safe values
   	    $game     = explode("%", clean_input($_POST["games"]), -1);
		//Create blank Payouts table; note: Max size is set to 9 numbers, key format: '0n' or '0nB', wher n=number of matching spots and B means matching bonus number
		$payOuts = array();
		for($i = 0; $i<10; $i++) {
			  $payOuts["0".$i] = NULL;
			  $payOuts["0".$i."B"] = NULL;
		}
		$totalWinnings = 0;
		$drawWinnings = 0;

		//Connect with Northwoods Database    
		$con=mysqli_connect("sql206.byethost11.com","b11_15675702","S3rver3O8","b11_15675702_NWLotto");
		// Check connection
		 if (mysqli_connect_errno())
		 {
		       echo "Failed to connect to MySQL: " . mysqli_connect_error();
		 }else{
		 
			 //Process Lottery Results
			 for($indx=0; $indx<count($game); $indx++) {   //note:array length = the number of results to process
					
					//Fetch Current game to process 
					$current = explode(",", $game[$indx]);
					
					//Access LotteryResults table for the current game 
					$lotto = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM LotteryResults WHERE TYPE='$current[0]'"));
					
			 		//Reset payOuts table 
    			 		foreach($payOuts as &$amt) $amt = NULL;
					$drawWinnings = 0;  
					
					//Extract payout info from html table 
					$sPos = stripos($lotto['DETAIL'], "<tbody");  //Start position in the html string (start of tbody element)
					$lngth = stripos( $lotto['DETAIL'], "<tfoot>") - $sPos +1; //Calculate length of the string to extract
					$pays = substr($lotto['DETAIL'], $sPos , $lngth); //Extract the tbody from the html code
					$pays = strip_tags( $pays, "<td>");  //Eliminate everything but the <td> nodes
					$pays = str_ireplace("</td>", "", $pays);  //Eliminate </td> tags
					$pays = str_ireplace("<td>", "*", $pays);  //Prepare for spliting the string
					$pays = trim($pays);  //Get rid of any whitespace
					$pays = substr($pays, 1);  //Remove the leading "*" 
					$pays = explode("*", $pays);  //Separate the <td> elements into individual array items
					
					//Build PayOuts table 
					for($i=0; $i<count($pays); $i += 3)  {  //List is in groups of 3; 'Matching text'  -- 'Number of winning tickets' -- 'Match payout' 
							$key = "0"; //Build key to index payOuts table 
							if(preg_match("#\d#", $pays[$i], $digit)==0)  //Find single digits in the 'Matching text' ; the first digit is all we care about
                            {
                                $key .= "0B"; //Handle the case where there are no digits in string(ie. Match mega number)
                            }
							if(preg_match("#\+#", $pays[$i]))  {  //A '+' signifies a matching bonus number payout
									  $key .= $digit[0] . "B";
							}else{
						 			  $key .= $digit[0];
							}
							//Use key to enter info into payOuts table 
							$payOuts[$key] = array( "dispText"=>$pays[$i], "dispAmt"=>$pays[$i+2], "winAmt"=>intval(str_replace(",", '', substr($pays[$i+2],1))));
					}
          			
					//Begin preparation of HTML output 
					$outputString .= "<section class='w3-panel w3-theme-l1 w3-border w3-round-xlarge w3-card-16'>";
					$outputString .= "    <div class='w3-center w3-jumbo'><h1>$current[1]</h1></div>";
                    $outputString .= "    <p><hr class='thick'></p>";
					$outputString .= "    <header>";
					$outputString .= "      <div class='w3-container w3-center'>";
                    $outputString .= "        <ul class='winning_number_sm'>";
					$outputString .= "            <li>" . str_replace(" ", "</li><li>", $lotto['HOTSPOTS']) . "</li>";
					if( $lotto['BONUS'] != "00")
					     $outputString .= "       <li class='mega'>". trim($lotto['BONUS'])."</li>";
					else
					    	$outputString .= "    <li class='noMega'></li>";
					$outputString .= "        </ul>";
					$outputString .= "      </div>";
                    $outputString .= "      <div class='w3-container w3-center' id='resultsCaption'>Results for draw date: " . date_format(date_create($lotto['DATE']), " l F d, Y") . "</div>";
                    $outputString .= "    </header>";
                    $outputString .= "    <div class= 'w3-row'>";
                    $outputString .= "    <div class='w3-col l5  w3-medium results' id='ourResults'>";
					$outputString .= "        <table class='w3-table w3-centered'>";
                    $outputString .= "            <tbody>";
					$outputString .= "                <tr>";
                    $outputString .= "                    <th><u>OUR NUMBERS</u></th>";
                    $outputString .= "                    <th><u>MATCH</u></th>";
                    $outputString .= "                    <th><u>AMOUNT</u></th>";
                    $outputString .= "                </tr>";

					//Retrieve our numbers for the current lottery game
					$ticket =mysqli_query($con,"SELECT * FROM `LotteryNumbers` WHERE GAMETYPE = '$current[0]'  AND STARTDATE <= '".$lotto['DATE']."' AND ENDDATE >= '".$lotto['DATE']."'");
					if ($ticket != NUll) {
							while($ticketNumbers  = mysqli_fetch_assoc($ticket)) {
          					     //output table row by row
								$outputString .= "    <tr>";
                                $outputString .= "        <td class='picks'>";
								//Evaluate pick
								$ticketSpots = explode(" ", $ticketNumbers['HOTSPOTS']);
								$matchHotspots = 0; $matchBonus = false;
								//Check for matching numbers
								for($i=0; $i<count($ticketSpots); $i++)
								     if(substr_count($lotto['HOTSPOTS'], $ticketSpots[$i])>0) {
									     $ticketSpots[$i] = "*".$ticketSpots[$i]."#";
										 $matchHotspots++;
									 }
								$picks  = implode(" ", $ticketSpots);
								$key = "0" . $matchHotspots;
								
								//Check for bonus number match	
								if($lotto['BONUS'] != "00")  {
          								if($lotto['BONUS'] == $ticketNumbers['BONUS']) {
          								     $key .= "B";
          									$picks .= "<span class='mega_pick'> *" . $ticketNumbers['BONUS'] . "#</span></td>";
          								}else{
          									 $picks .= "<span class='mega_pick'> ".  $ticketNumbers['BONUS']  . "</span></td>";
          								}
								}
								//Process picks for display
								$picks = str_replace("*", "<span class='match_pick'>", $picks);
								$picks = str_replace("#", "</span>", $picks);
								$outputString .= $picks;
								//Calculate winnings
								if($payOuts[$key] != NULL) {
										$dispText = $payOuts[$key]["dispText"];
										$dispAmt = $payOuts[$key]["dispAmt"];
										$winpick = $payOuts[$key]["winAmt"];
								}else{
									 	$dispText = "Nothing($matchHotspots)";
										$dispAmt = "$0";
										$winpick = 0;
								}
								$outputString .= "        <td>$dispText</td>";
								$outputString .= "        <td>$dispAmt</td>";
								$drawWinnings += $winpick;
								$outputString .= "    </tr>";
          					}
							//Finish  table
							$outputString .= "    </tbody>";
                            $outputString .= "    <tfoot>";
                            $outputString .= "        <tr>";
                            $outputString .= "            <td class='game_win_amt' colspan=3>WINNINGS: $$drawWinnings</td>";
                            $outputString .= "        </tr>";
                            $outputString .= "    </tfoot>";
                            $outputString .= "</table>";
                            $outputString .= "</div>";
                            $outputString .= "<div class='w3-col l2'>&nbsp;</div>";
							$outputString .= "<div class='w3-col l5 w3-medium w3-centered results' id='officalResults'>".$lotto['DETAIL'] ."</div>";
                            $outputString .= "</div>";
                            $outputString .= "</section>";
					}else{
						 	$outputString .=   "NO MATCHING TICKETS";
					}
					
					$totalWinnings += $drawWinnings;
		}
		//Write results to output stream
		$outputString .= "<div id='resultsSummary'><hr><h1>TOTAL WINNINGS: $$totalWinnings</h1><br> </div>";

		echo $outputString;
	}
}


 function clean_input($data) {
   $data = trim($data);
   $data = stripslashes($data);
   $data = htmlspecialchars($data);
   return $data;
 }
 ?>
