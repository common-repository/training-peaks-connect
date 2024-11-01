<?php 
/*
Plugin Name: Training Peaks Connect
Plugin URI: http://wordpress.org/extend/plugins/training-peaks-connect/
Author URI: http://wordpress.org/extend/plugins/training-peaks-connect/
Description: This plug-in allows you to publish trainingpeaks.com workouts for one or more accounts on your blog or cms.
Version:     1.0
Author:      tpcguy213  
License:     GPL
*/

if ( ! class_exists('Training_Peaks_Connect_Plugin')){
class Training_Peaks_Connect_Plugin{
    
    var $settings;
    var $plugin_folder = '';
	private $imageCode = 0;
    private $URL = '';	
	public $moreWorkoutsLink = '';
    public $moreWorkoutsText = '';
	public $activitiesTitle = '';
	public $endDate = '';
	public $numberOfDaysAgo;
	public $daysAgo;
	public $startDate;
	public $httpErrorCode = 0;
	
	public function Training_Peaks_Connect_Plugin()
	{
        date_default_timezone_set('EST');
        $this->plugin_folder = get_option('home').'/'.PLUGINDIR.'/training-peaks-connect/';       
		$this->imageCode = 0;//default to 'Custom.PNG' image
		$this->endDate = date("m/d/Y"); //today
		$this->numberOfDaysAgo = 30;
		$this->daysAgo = strtotime($this->endDate.' - '.$this->numberOfDaysAgo.' days');
		$this->startDate = date('m/d/Y', $this->daysAgo);
                  add_filter('the_content', array(&$this, 'insert_workouts'));
                  add_action('admin_menu', array(&$this, 'admin_menu'));
        
    }
     function admin_menu()
	{
           add_options_page('Training Peaks Connect Options', 'Training Peaks Connect',
                             'administrator', __FILE__, 
                              array(&$this, 'plugin_options'));
           add_management_page('Training Peaks Connect Accounts', 'Training Peaks Connect', 
                                'administrator', __FILE__,
                                array(&$this, 'admin_manage'));

	}
  

  function plugin_options() {
  if (get_bloginfo('version') >= '2.7') {
               $manage_page = 'tools.php';
            } else {
               $manage_page = 'edit.php';
            }
            print <<<EOT
            <div class="wrap">
            <h2>Training Peaks Connect</h2>
            <p>This is the Setup page for the Training Peaks Connect plug-in.<br>
            This plug-in is recommended for use on WordPress version 2.7 or higher.<p>
               <ul>
               Create a new User Account by going to <a href="$manage_page?page=training-peaks-connect/training-peaks-connect.php">Manage -> Training Peaks Connect Accounts</a>, where you will also find more information.<p>
               <li>Display a user's Training Peaks data by creating a new User Account and placing the following tag along with the chosen Account Name on any page or post.</li>
               <li><b>-training-peaks-connect#AccountName-</b></li><li><br></li><li><br></li>
               
               <br>Data for workouts is retrieved using the Training Peaks API.<br><br>
               <a href="http://home.trainingpeaks.com/">Training Peaks Home Page</a><p>
               <p><br><br>Structure borrowed from CNN News plug-in.
EOT;
        }



function admin_manage() {
            // Edit/delete links
            $mode = trim($_GET['mode']);
            $id = trim($_GET['id']);

            $alloptions = get_option('training-peaks-connect');

            if ( is_array($_POST) && $_POST['training-peaks-connect-submit'] ) {
				              
				$newoptions = array();
                $id                       = $_POST['training-peaks-connect-id'];

                $newoptions['name']       = $_POST['training-peaks-connect-name'];
                $newoptions['username']      = $_POST['training-peaks-connect-username'];
                $newoptions['password']     = $_POST['training-peaks-connect-password'];
                $newoptions['tplink']     = $_POST['training-peaks-connect-tplink'];
                $newoptions['tplinktext']     = $_POST['training-peaks-connect-tplinktext'];
                $newoptions['title']        = $_POST['training-peaks-connect-title'];
                $newoptions['numworkouts']    = $_POST['training-peaks-connect-numworkouts'];
                $newoptions['numdays']    = $_POST['training-peaks-connect-numdays'];
                

                if ( $alloptions['accounts'][$id] == $newoptions ) {
                    $text = 'No change...';
                    $mode = 'main';
                } else {
                    $alloptions['accounts'][$id] = $newoptions;
                    update_option('training-peaks-connect', $alloptions);
 
                    $mode = 'save';
                }
                } 
                
                else if ( ((is_array($_POST)  && $_POST['training-peaks-connect-options-public-errors-submit'])) || ((is_array($_POST)  && $_POST['training-peaks-connect-options-public-errors-submit']))) {
                 if ($_POST['training-peaks-connect-options-public-comments'] == "yes")
                {
                	$alloptions['showcomments'] = "checked";
                	update_option('training-peaks-connect', $alloptions);
                    $text = 'Public Comments are turned on.<br>';
                }
                 else 
                 {                  
                 	$alloptions['showcomments'] = "";
                 	update_option('training-peaks-connect', $alloptions);
                 	$text = 'Public Comments are turned off.<br>';
                 }
                 if ($_POST['training-peaks-connect-options-public-errors'] == "yes")
                 {
                	$alloptions['showerrors'] = "checked";
                	update_option('training-peaks-connect', $alloptions);
                    $text .= 'Public Errors are turned on.';
                 }
                 else 
                 {                  
                 	$alloptions['showerrors'] = "";
                 	update_option('training-peaks-connect', $alloptions);
                 	$text .= 'Public Errors are turned off.';
                 }
               
                    $mode = 'main';
                }
			
              if ( $mode == 'newaccount' ) {
                
              		$newaccount = 0;
                	    if (empty($alloptions['accounts']))
                	      { 
                	      	//do nothing
                	      }
                	    else
                	      {
                		foreach ($alloptions['accounts'] as $k => $v) 
                		   {
                    	 if ( $k > $newaccount ) 
                    		{
                        		$newaccount = $k;
                    		}
                		   }    
                	      }                        	
                	$newaccount += 1;

                	$text = "Please configure new account and press Save.";
                
                $mode = 'main';
            }

            if ( $mode == 'save' ) {
                $text = "Saved account {$alloptions[accounts][$id][name]} [$id].";
                $mode = 'main';
            }

            if ( $mode == 'edit' ) {
                if ( ! empty($text) ) {
                     echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>';
                }
                $text = "Editing account {$alloptions[accounts][$id][name]} [$id].";

                $edit_id = $id;
                $mode = 'main';
            }

            if ( $mode == 'delete' ) {

                $text = "Deleted account {$alloptions[accounts][$id][name]} [$id].";
                
                unset($alloptions['accounts'][$id]);

                update_option('training-peaks-connect', $alloptions);
 
                $mode = 'main';
            }

            // main
            if ( empty($mode) or ($mode == 'main') ) {

                if ( ! empty($text) ) {
                     echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>';
                }
                print '<div class="wrap">';
                print ' <h2>';
                print _e('Manage Training Peaks Accounts','training-peaks-connect');
                print '</h2>';
                print ' <table id="the-list-x" width="100%" cellspacing="3" cellpadding="3">';
                print '  <thead>';
                print '   <tr>';
                print '    <th scope="col" align="left">';
                print _e('Key','training-peaks-connect');
                print '</th>';
                print '    <th scope="col" align="left">';
                print _e('<center>Name</center>','training-peaks-connect');
                print '</th>';
                print '    <th scope="col" align="left">';
                print _e('<center>User Name</center>','training-peaks-connect');
                print '</th>';
                print '    <th scope="col" align="left">';
                print _e('<center>Password</center>','training-peaks-connect');
                print '</th>';
                print '    <th scope="col" align="left">';
                print _e('<center>Training Peaks Profile URL</center>','training-peaks-connect');
                print '</th>';
                print '    <th scope="col" align="left">';
                print _e('<center>Text for Link to Profile</center>','training-peaks-connect');
                print '</th>';
                print '    <th scope="col" align="left">';
                print _e('<center>Title</center>','training-peaks-connect');
                print '</th>';
                 print '    <th scope="col" align="left">';
                print _e('<center>Maximum Number to Display</center>','training-peaks-connect');
                print '</th>';
                print '    <th scope="col" align="left">';
                print _e('<center>Number of Days Ago</center>','training-peaks-connect');
                print '</th>';
                print '    <th scope="col" colspan="2" align="center">';
                print _e('Action','training-peaks-connect');
                print '</th>';
                print '   </tr>';
                print '  </thead>';

                if (get_bloginfo('version') >= '2.7') {
                    $manage_page = 'tools.php';
                } else {
                    $manage_page = 'edit.php';
                }

                if ( $alloptions['accounts'] || $newaccount ) {
                    $i = 0;
					if (empty($alloptions['accounts']))
					{
						//do nothing
					}
                    else{
					foreach ($alloptions['accounts'] as $key => $val) {
                        if ( $i % 2 == 0 ) {
                            print '<tr class="alternate">';
                        } else {
                            print '<tr>';
                        }
                        if ( isset($edit_id) && $edit_id == $key ) {
                            print "<form name=\"training-peaks-connect_options\" action=\"".
                                  htmlspecialchars($_SERVER['REQUEST_URI']).
                                  "\" method=\"post\" id=\"training-peaks-connect_options\">";
                                    
                            print "<th scope=\"row\">".$key."</th>";
                            print '<td><input size="10" maxlength="40" id="training-peaks-connect-name" name="training-peaks-connect-name" type="text" value="'.$val['name'].'" /></td>';
                            print '<td><input size="20" maxlength="40" id="training-peaks-connect-username" name="training-peaks-connect-username" type="text" value="'.$val['username'].'" /></td>';
                            print '<td><input size="20" maxlength="40" id="training-peaks-connect-password" name="training-peaks-connect-password" type="text" value="'.$val['password'].'" /></td>';
                            print '<td><input size="20" maxlength="50" id="training-peaks-connect-tplink" name="training-peaks-connect-tplink" type="text" value="'.$val['tplink'].'" /></td>';
                            print '<td><input size="20" maxlength="50" id="training-peaks-connect-tplinktext" name="training-peaks-connect-tplinktext" type="text" value="'.$val['tplinktext'].'" /></td>';
                            print '<td><input size="20" maxlength="100" id="training-peaks-connect-title" name="training-peaks-connect-title" type="text" value="'.$val['title'].'" /></td>';
                            
                            print '<td><center><input size="3" maxlength="3" id="training-peaks-connect-numworkouts" name="training-peaks-connect-numworkouts" type="text" value="'.$val['numworkouts'].'" /></center></td>';
                            print '<td><center><input size="3" maxlength="3" id="training-peaks-connect-numdays" name="training-peaks-connect-numdays" type="text" value="'.$val['numdays'].'" /></center></td>';
                            
                            print '<td><center><input type="submit" value="Save  &raquo;"></center>';
                            print "</td>";
                            print "<input type=\"hidden\" id=\"training-peaks-connect-id\" name=\"training-peaks-connect-id\" value=\"$edit_id\" />";
                            print "<input type=\"hidden\" id=\"training-peaks-connect-submit\" name=\"training-peaks-connect-submit\" value=\"1\" />";
                            print "</form>";
                        } else {
                            print "<th scope=\"row\">".$key."</th>";
                            print "<td>".$val['name']."</td>";
                            print "<td>".$val['username']."</td>";                          
                            print "<td>".$val['password']."</td>";
                            print "<td>".$val['tplink']."</td>";
                            print "<td>".$val['tplinktext']."</td>";                            
							print "<td>".$val['title']."</td>";	
                            print "<td><center>".$val['numworkouts']."</center></td>";
                            print "<td><center>".$val['numdays']."</center></td>";
                            print "<td><a href=\"$manage_page?page=training-peaks-connect/training-peaks-connect.php&amp;mode=edit&amp;id=$key\" class=\"edit\">";
                            print __('Edit','training-peaks-connect');
                            print "</a></td>\n";
                            print "<td><a href=\"$manage_page?page=training-peaks-connect/training-peaks-connect.php&amp;mode=delete&amp;id=$key\" class=\"delete\" onclick=\"javascript:check=confirm( '".__("This account entry will be erased. Delete?",'training-peaks-connect')."');if(check==false) return false;\">";
                            print __('Delete', 'training-peaks-connect');
                            print "</a></td>\n";
                        }
                        print '</tr>';

                        $i++;
                    }}
                    
                    if ( $newaccount ) {

                        print "<form name=\"training-peaks-connect_options\" action=\"".
                              htmlspecialchars($_SERVER['REQUEST_URI']).
                              "\" method=\"post\" id=\"training-peaks-connect_options\">";
                                
                        print "<th scope=\"row\">".$newaccount."</th>";
                        print '<td><input size="10" maxlength="40" id="training-peaks-connect-name" name="training-peaks-connect-name" type="text" value="NEW" /></td>';
                        print '<td><input size="20" maxlength="40" id="training-peaks-connect-username" name="training-peaks-connect-username" type="text" value="" /></td>';
                        print '<td><input size="20" maxlength="40" id="training-peaks-connect-password" name="training-peaks-connect-password" type="text" value="" /></td>';
                        print '<td><input size="20" maxlength="80" id="training-peaks-connect-tplink" name="training-peaks-connect-tplink" type="text" value="" /></td>';
                        print '<td><input size="20" maxlength="100" id="training-peaks-connect-tplinktext" name="training-peaks-connect-tplinktext" type="text" value="See more workouts..." /></td>';
                        
                        print '<td><input size="20" maxlength="100" id="training-peaks-connect-title" name="training-peaks-connect-title" type="text" value="Recent Workouts" /></td>';                        
                        print '<td><center><input size="3" maxlength="3" id="training-peaks-connect-numworkouts" name="training-peaks-connect-numworkouts" type="text" value="5" /></center></td>';
                        print '<td><center><input size="3" maxlength="3" id="training-peaks-connect-numdays" name="training-peaks-connect-numdays" type="text" value="30" /></center></td>';
                        
                        print '<td><center><input type="submit" value="Save  &raquo;"></center>';
                        print "</td>";
                        print "<input type=\"hidden\" id=\"training-peaks-connect-id\" name=\"training-peaks-connect-id\" value=\"$newaccount\" />";
                        print "<input type=\"hidden\" id=\"training-peaks-connect-newaccount\" name=\"training-peaks-connect-newaccount\" value=\"1\" />";
                        print "<input type=\"hidden\" id=\"training-peaks-connect-submit\" name=\"training-peaks-connect-submit\" value=\"1\" />";
                        print "</form>";
                    } else {
                        print "</tr><tr><td colspan=\"12\"><a href=\"$manage_page?page=training-peaks-connect/training-peaks-connect.php&amp;mode=newaccount\" class=\"newaccount\">";
                        print __('Add extra account','training-peaks-connect');
                        print "</a></td></tr>";

                    }
                } else {
                    print '<tr><td colspan="12" align="center"><b>';
                    print __('No accounts found(!)','training-peaks-connect');
                    print '</b></td></tr>';
                    print "</tr><tr><td colspan=\"12\"><a href=\"$manage_page?page=training-peaks-connect/training-peaks-connect.php&amp;mode=newaccount\" class=\"newaccount\">";
                    print __('Add account','training-peaks-connect');
                    print "</a></td></tr>";
                }
                print ' </table>';
                print '<h2>';
                print _e('Global configuration parameters','training-peaks-connect');
                print '</h2>';
                print ' <form method="post">';
                print ' <table id="global-configuration" cellspacing="3" cellpadding="3">';
                print '<tr><td><b>Publicly display workout comments:</b></td>';
                
                 print '<td><input id="training-peaks-connect-options-public-comments" name="training-peaks-connect-options-public-comments" type="CHECKBOX" value=yes '.$alloptions['showcomments'].' /></td>';
                
                print '<input type="hidden" id="training-peaks-connect-options-public-comments-submit" name="training-peaks-connect-options-public-comments-submit" value=yes checked/>';  
                print '<tr><td><b>Publicly display plug-in errors:</b></td>';
                print '<td><input id="training-peaks-connect-options-public-errors" name="training-peaks-connect-options-public-errors" type="CHECKBOX" value=yes '.$alloptions['showerrors'].' /></td>';
                
                print '<input type="hidden" id="training-peaks-connect-options-public-errors-submit" name="training-peaks-connect-options-public-errors-submit" value=yes checked/>';                       
                print '<td><input type="submit" value="Save  &raquo;"></td></tr>';
                print ' </table>';
                print '</form>'; 

                print '<h2>';
                print _e('Information','training-peaks-connect');
                print '</h2>';
                print ' <table id="the-list-x" width="100%" cellspacing="3" cellpadding="3">';
                print '<tr><td align="right" valign="top" width=205><b>Key: </b></td><td valign="top">Unique identifier used internally.</td></tr>';
                print '<tr><td align="right" valign="top" width=205><b>Account Name: </b></td><td valign="top">Name used to reference a specific account as ';
                print ' <b>-training-peaks-connect#myname-</b>. ';
                print ' If more than one account shares the same name, a random among these will be picked each time.  This name is not case-sensitive.</td></tr>';
                print '<tr><td align="right" valign="top" width=205><b>User Name: </b></td><td valign="top">This is the name used for logging into Training Peaks </td></tr>';
                print '<tr><td align="right" valign="top" width=205><b>Password: </b></td><td valign="top">This is the password used for logging into Training Peaks.</td></tr>';
                print '<tr><td align="right" valign="top" width=205><b>Training Peaks Profile URL: </b></td><td valign="top">Provide a URL link to the user\'s public Training Peaks Profile, if available.  Example: http://www.trainingpeaks.com/JDoe</td></tr>';
                print '<tr><td align="right" valign="top" width=205><b>Text for Link to Profile: </b></td><td valign="top">Provide text for the link that visitors will click on to view a public Training Peaks Profile.  Example: See more workouts...</td></tr>';
                print '<tr><td align="right" valign="top" width=205><b>Title: </b></td><td valign="top">This is the title that will appear over the user\'s list of activities.';
                print '  For a new line, type \'!NL!\'.  Example: First Line!NL!Second Line</td></tr>';
                
                print '<tr><td align="right" valign="top" width=205><b>Maximum Number to Display: </b></td><td valign="top">This is the maximum number of activities shown in the list for this account. If the list has ';
                print 'fewer activities, only the number of activities in the list will be displayed.  The list begins with the most recent Actvities</td></tr>';
				print '<tr><td align="right" valign="top" width=205><b>Number of Days Ago: </b></td><td valign="top">This indicates from how many days ago you would like to show activities. </td></tr>';
                print '<tr><td align="right" valign="top" width=205><b>Publicly display workout comments: </b></td><td valign="top">Check this box if you would like to be able to see comments for each workout that have been input by the user on Training Peaks. </td></tr>';
				
				print '<tr><td align="right" valign="top" width=205><b>Publicly display plug-in errors: </b></td><td valign="top">Check this box if you would like to be able to see when and where errors occur with this plug-in on your pages and posts.  All users and visitors will be able to see them. </td></tr>';
                print ' </table>';
                print '</div>';
            }
        }


    
    function insert_workouts($data)
    
    {
    $tag = '/-training-peaks-connect(|#.*?)-/';
    $result = preg_replace_callback($tag, array(&$this, 'inline_replace_callback'), $data);
      return $result;
    }
    
    
    
    function inline_replace_callback($matches) {

            if ( ! strlen($matches[1]) ) { // Default
                $accountname = '';
            } else {
                $accountname = substr($matches[1], 1); // Skip #
            }
            return $this->display_workouts($accountname);
        }
    
    function display_workouts($accountname)
    {
    	    global $settings;
            $settings   = get_option('training-peaks-connect');
            $matching_accounts = array();

           
            foreach ($settings['accounts'] as $k => $v) {
                if ( strtolower((string)$v['name']) == strtolower($accountname) ) { 
                    $matching_accounts[] = $k;
                } 
            } 
            
            if ( ! count($matching_accounts) ) {         
                if ($settings['showerrors'] == "checked")
                {    
            	return "<ul>--TRAINING PEAKS CONNECT ERROR: Unknown account name--</ul>";
                }
            }
            $account_id = $matching_accounts[rand(0, count($matching_accounts)-1)];
            $account = $settings['accounts'][$account_id];
    		
            if ( strlen($account['username']) ) {
                $username = $account['username'];
            } else {
               //Username Missing
            }
            if ( strlen($account['password']) ) {
                $password = $account['password'];
            } else {
               //Password Missing
            }
            if ( strlen($account['tplink']) ) {
                $this->moreWorkoutsLink = $account['tplink'];
            } else {
               //Password Missing
            }
            if ( strlen($account['tplinktext']) ) {
                $this->moreWorkoutsText = $account['tplinktext'];
            } else {
               $this->moreWorkoutsText = 'See more workouts...';
            }
            if ( strlen($account['numworkouts']) ) {
                $numworkouts = $account['numworkouts'];
            } else {
               //number of workouts Missing
            }
            if ( strlen($account['numdays']) ) {
                $this->numberOfDaysAgo = $account['numdays'];
                $this->daysAgo = strtotime($this->endDate.' - '.$this->numberOfDaysAgo.' days');
		        $this->startDate = date('m/d/Y', $this->daysAgo);
            } else {
                $this->numberOfDaysAgo = 30;
                $this->daysAgo = strtotime($this->endDate.' - '.$this->numberOfDaysAgo.' days');
		        $this->startDate = date('m/d/Y', $this->daysAgo);
            }
            if ( strlen($account['title']) ) {
                $this->activitiesTitle = str_replace('!NL!', '<br>', $account['title']);
            } else {
                $this->activitiesTitle = 'Recent Workouts';
            }
            
    	    $Workouts = $this->gather($accountname, $username, $password, $numworkouts);
    	    
    	    if ((count($Workouts) < 1) || ($Workouts == NULL))
    	    {
    	    	return '';
    	    }
    	    $result = '<!-- Start Training-Peaks-Connect code -->';
            $result .= '<div id=\"training-peaks-connect-inline\"><h4>'.$this->activitiesTitle.'</h4>';
            if ($this->httpErrorCode != 0){
            	if ($settings['showerrors'] == "checked")
            	{
            	$result .= '--TRAINING PEAKS CONNECT ERROR: Cannot access Training Peaks--';
            	}
            	else
            	{
            	    return '';
            	}
            }
            else{
	            for ($x=0; $x<$numworkouts; $x++)
	             {  
	             $result .= $Workouts[$x];
	             }
	             if ($this->moreWorkoutsLink != '' && $this->moreWorkoutsLink != NULL)
	             {
	             $result .= '<a href = "'.$this->moreWorkoutsLink.'">'.$this->moreWorkoutsText.'</a><br>';
	             }
            }
             $result .= '</div><!-- End Training-Peaks-Connect code -->';
            return $result;
            

    }
   public function gather($accountname, $username, $password, $numworkouts)
	{
		
		$URL = 'http://www.trainingpeaks.com/tpwebservices/service.asmx/GetWorkoutsForAthlete?username=' . $username . '&password='. $password.  '&startDate='. $this->startDate .'&endDate='. $this->endDate; 		
		
		$str = file_get_contents($URL);
		if (($str == '') || ($str == NULL))
		{
			$this->httpErrorCode = 1;
			return array ('ERROR');
		}
		$xml = new SimpleXMLElement($str);
		
		$NumberOfWorkouts = count($xml->Workout);
		if (($NumberOfWorkouts < 1) || ($NumberOfWorkouts == NULL)){
			return '';
		}
		$TP_Activities = array($NumberOfWorkouts);
		
		
			$WorkoutLimit = $numworkouts;
			if ($NumberOfWorkouts < $WorkoutLimit)
				{
			     $WorkoutLimit = $NumberOfWorkouts;
				}
			
			for($x = ($NumberOfWorkouts - 1); $x >= ($NumberOfWorkouts - $WorkoutLimit) ; $x--)
			{
				$type = $xml->Workout[$x]->WorkoutTypeDescription;
				$type = $this->correctVerbTense($type);
				$distance = $xml->Workout[$x]->DistanceInMeters;
				$comments = $xml->Workout[$x]->AthleteComments;
				$day = $xml->Workout[$x]->WorkoutDay;
				$day = str_replace("T", " ", $day);
			
				$TP_Activities[$NumberOfWorkouts - $x - 1] = $this->display($accountname, $type, $distance, $comments, $day, $TP_Activities[$x]);
			    
			}
			return $TP_Activities;
	}
	


           
	function display($accountname, $type, $distance, $comments, $day, $CurrentActivity){
		global $settings;
        $settings   = get_option('training-peaks-connect');
		$UTime = strtotime($day);
		$myDate = date('m/d', $UTime); 
		$distance = round(($distance*0.00062137), 1);
  		
		$images = array(
						'images/custom.PNG',
  		                'images/run.PNG',
  		                'images/walk.PNG',
  		                'images/swim.PNG',
  		                'images/bike.PNG',
  		                'images/brick.PNG',
						'images/mtn_bike.PNG',
  		                'images/crosstrain.PNG',
  		                'images/rowing.PNG',
  		                'images/race.PNG',
						'images/xc-ski.PNG',
  		                'images/strength.PNG',
  		                'images/day_off.PNG'
		);
		$imageHTML = '<IMG SRC = "'.$this->plugin_folder. $images[$this->imageCode] . '" height = "18" align="middle" /> ';
		
             $myDateUnformatted = strtotime($myDate);
             $dayOfWeek = date("l", $myDateUnformatted);
		     $output = $imageHTML.$type.' '. $distance . ' miles on ' . $dayOfWeek .', '. $myDate; 
	         if (($comments != NULL) && ($comments != '') && ($settings['showcomments'] == "checked"))
	         {	
	            $output .= '<br><span style="padding-left:50px"><label>Comments:  '.$comments.'</label></span>';
		     	
	         }
		     $output = str_replace(' 0 miles ', ' ', $output);	
	         $output = str_replace('of on', 'on', $output);
	         $output = str_replace('Covered on', 'Worked out on', $output);	
		$CurrentActivity = $output.'<br>';
		return $CurrentActivity;
	}
	
	public function correctVerbTense($type)
	{
		switch($type)
		{
			case 'Run':
				$PastTenseVerb = 'Ran';
				$this->imageCode = 1;
				break;
			case 'Walk':
				$PastTenseVerb = 'Walked';
				$this->imageCode = 2;
				break;	
			case 'Swim':
				$PastTenseVerb = 'Swam';
				$this->imageCode = 3;
				break;
			case 'Bike':
				$PastTenseVerb = 'Biked';
				$this->imageCode = 4;
				break;		
		    case 'Brick':
				$PastTenseVerb = 'Did a brick of';
				$this->imageCode = 5;
				break;	
		    case 'MTB':
		    	$PastTenseVerb = 'Mountain biked';
		    	$this->imageCode = 6;
				break;	
			case 'X-Train':
		    	$PastTenseVerb = 'Crosstrained';
		    	$this->imageCode = 7;
				break;
			case 'Rowing':
		    	$PastTenseVerb = 'Rowed';
		    	$this->imageCode = 8;
				break;
			case 'Race':
		    	$PastTenseVerb = 'Raced';
		    	$this->imageCode = 9;
				break;
			case 'XC-Ski':
		    	$PastTenseVerb = 'Skiied';
		    	$this->imageCode = 10;
				break;	
			case 'Strength':
		    	$PastTenseVerb = 'Strength trained';
		    	$this->imageCode = 11;
				break;
		    case 'Day Off':
		    	$PastTenseVerb = 'Took a day off';
		    	$this->imageCode = 12;
				break;
			default:
				$PastTenseVerb = 'Covered';
				$this->imageCode = 0;
				break;
			
		}
		return $PastTenseVerb;
	}
	
 }
$TP_instance &= new Training_Peaks_Connect_Plugin();
}
?>