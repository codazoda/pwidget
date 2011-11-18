<?php

	/**
	 * ===== pWidget =====
	 *
	 * A widget that lets you embed Pivotal Tracker search 
	 * results into any web page, dashboard, Google Gadget, 
	 * or anywhere else you can put an iframe.
	 */
	 
	/**
	 * ===== Security =====
	 *
	 * pWidget is hosted on a server that uses SSL.  Data,
	 * however, is passed to pWidget
	 * via the query string.  Web browsers encrypt that
	 * data in transit.  Any user who can see the page with
	 * pWidget embedded in it, however, can see the full
	 * query string.  That includes your username and 
	 * password combination or your API key.
	 */
	 
	// Set the default to 3 columns wide
	$cols = 3;
	
	// Add another column if we have priority turned on
	if (substr(strtolower($_GET['priority']), 0, 1) == 'y') {
		$cols = $cols + 1;
	}
	
	// Setup the possible locations for the library
	$libraryPath = array (
		'../pivotal_class/',
		'../../pivotal_class/'
	); 
	
	// Include the Pivotal library from possible locations
	foreach($libraryPath as $path) {
		$file = $path . 'pivotal.php';
		if (file_exists($file)) {
			include_once $file;
			break;
		}
	}
	
	// If we are loading data
	if ($_GET['load'] != '') {
		load($_GET['load']);
	}

	// Verify all of the fields
	validate();
	
	// If the save flag was set
	if (substr(strtolower($_GET['save']), 0, 1) == 'y') {
		save();
	}
	
	// Setup an array of items that are done
	$done = array('finished', 'delivered', 'accepted');

	// Create an instance of the class
	$pivotal = new pivotal;

	/**
	 * ===== Authentication =====
	 *
	 * Either an API token or a Username / Password
	 * combination are required.
	 *
	 * token=abcdefghijklmnopqrstuvwxyz
	 * username=johndoe
	 * password=foobar
	 */
	 
	// If an API token wasn't included, grab via API
	if (!$_GET['token']) {
		$pivotal->token = $pivotal->getToken($_GET['username'], $_GET['password']);
	} else {
		$pivotal->token = $_GET['token'];
	}

	/**
	 * ===== Link Target =====
	 *
	 * You can specify a target for any links on the page.
	 * By default the target is 'pivotal'.  You can
	 * change this to the name of another iframe or
	 * to something like '_new' to open a new window.
	 *
	 * target=pivotal
	 */
	 
	// Set the link target variable
	if ($_GET['target'] == '') {
		$target = 'pivotal';
	} else {
		$target = $_GET['target'];
	}

	/**
	 * ===== Refresh Rate =====
	 *
	 * You can specify a refresh rate for the page.
	 * By default the refresh is 0.  The refresh rate
	 * is in seconds with a minimum of 15 seconds.
	 *
	 * refresh=30
	 */
	 
	// Set the link target variable
	if ($_GET['refresh'] >= 15) {
		$refresh = intval($_GET['refresh']);
	} else {
		$refresh = 0;
	}

	/**
	 * ===== Project(s) =====
	 *
	 * You can specify a single project or a comma 
	 * seperated list of projects.  To specify all projects,
	 * leave the value off.  By default the widget will pull 
	 * stories from all of a users projects.
	 *
	 * project=123456,987654
	 */
	
	// Grab all projects by default or the specified list
	if ($_GET['project'] == '') {
		// Get a list of all the users projects
		$projectObject = $pivotal->getProjects();
		foreach($projectObject->project as $pro) {
			$projects[] = array(
				'id' => $pro->id
			);
		}
	} else {
		// Split a comma seperated list
		$projList = split(',', $_GET['project']);
		foreach($projList as $item) {
			$projects[] = array(
				'id' => $item
			);
		}
	}
	
	/**
	 * ===== Search Filters =====
	 *
	 * You can specify a search filter.  These filters use
	 * the same functionality as the search feature in
	 * Pivotal Tracker.  By default the widget pulls
	 * everything using a filter of
	 * "state:feature,bug,chore,release".
	 *
	 * filter=mywork:jrd state:bug
	 */


	/**
	 * ===== Priority Labels =====
	 *
	 * You can opt to include a 'priority' column
	 * into the widget ouput.  This is a feature that
	 * doesn't currently exist in Pivotal Tracker but
	 * was added to pWidget.  When you turn it on, it will 
	 * mark each story with a priority of 'A', 'B', or 'C' 
	 * based on labels with those values in Pivotal Tracker. 
	 * If a story doesn't have a label of 'A' or 'B' then 
	 * that story will default to a 'C' priority.
	 *
	 * priority=y
	 */
	
	// Set a default filter; includes everything but unscheduled
	$filter = $_GET['filter'];
	if ($filter == '') {
		$filter = 'state:unstarted,started,finished,delivered,accepted,rejected';
	}

	// Create an SQLite DB and table in memory
	$sqlite = new SQLite3(':memory:');
	//$sqlite->open(':memory:');
	$create = 'CREATE TABLE stories '
			. '('
	        . ' id INTEGER,'
	        . ' current_state TEXT,'
	        . ' estimate INTEGER,'
	        . ' name TEXT,'
	        . ' created_at TEXT,'
	        . ' priority TEXT,'
	        . ' labels TEXT,'
	        . ' owned_by TEXT,'
	        . ' story_type TEXT'
	        . ')';
	$sqlite->exec($create);
	$sqlite->exec('BEGIN TRANSACTION');
	
	// Loop through the projects
	foreach ($projects as $p) {

		// Grab all the matching stories from this project
		$pull = $pivotal->getStories($p['id'], $filter);

		// Loop through the data building an array for our view
		foreach($pull->story as $p) {
			// Space seperate the labels
			$labels = str_replace(',', ', ', (string)$p->labels);
			// If priority was specified, set a priority for this item
			if (substr(strtolower($_GET['priority']), 0, 1) == 'y') {
				$labelList = split(',', (string)$p->labels);
				if (is_array($labelList)) {
					// See if a, b, or c exists in the list of labels
					if (in_array('a', $labelList)) {
						$priority = 'A';
					} elseif (in_array('b', $labelList)) {
						$priority = 'B';
					} else {
						$priority = 'C';
					}
				}
			}
			// Unset owned_by
			if (substr(strtolower($_GET['owner']), 0, 1) == 'n') {
				unset($p->owned_by);
			}
			// Unset labels
			if (substr(strtolower($_GET['labels']), 0, 1) == 'n') {
				unset($labels);
			}

			// Write the data to the sqlite table
			$insert = 'INSERT INTO stories VALUES '
					. '('
					. " '{$p->id}',"
					. " '{$p->current_state}',"
					. " '{$p->estimate}',"
					. " '{$p->name}',"
					. " '{$p->created_at}',"
					. " '$priority',"
					. " '$labels',"
					. " '{$p->owned_by}',"
					. " '{$p->story_type}'"
					. ')';
			$sqlite->exec($insert);

		}
	
	}
	
	// End the transaction and write the data
	$sqlite->exec('COMMIT TRANSACTION');
	
	/**
	 * ===== Sorting =====
	 *
	 * You can sort the resulting list of stories by one
	 * or more custom columns. By default the widget
	 * sorts by current_state.
	 *
	 * sort=current_state,priority
	 */
	
	// Setup the order by
	if ($_GET['sort']) {
		$order = 'ORDER BY ' . $_GET['sort'];
	} else {
		$order = 'ORDER BY current_state';
	}
	
	// Always order by ROWID also
	$order .= ',ROWID';
	
	// Query the data from sqlite
	$query = 'SELECT * FROM stories ' . $order;
	$result = $sqlite->query($query);
	while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
		$stories[] = $row;
	}
	
	// Set the title
	if ($_GET['title']) {
		$title = $_GET['title'];
	} else {
		$title = 'pWidget';
	}
	
	// Include the view
	require "index.phtml";

	/**
	 * ===== Required Fields =====
	 *
	 * A minimum widget call requires only access 
	 * credentials.  Either a token or a usermane and
	 * password combination.
	 *
	 * token=abcdefghijklmnoqrstuvwxyz
	 * username=johndoe&password=foobar
	 */

	// Validate each required field
	function validate() {
		if ($_GET['token'] == '') {
			if ($_GET['username'] == '') {
				$error[] = 'Error: No username specified.';
			}
			if ($_GET['password'] == '') {
				$error[] = 'Error: No password specified.';
			}
		}
		if ($error) {
			foreach($error as $msg) {
				echo $msg . '<br>';
			}
			echo "<br>\n";
			$readme = file_get_contents('readme.txt');
			$readme = str_replace("\n", "<br>\n", $readme);
			echo $readme;
			exit;
		}
	}
	
	// Save the options and redirect
	function save() {
		// Grab the get variables
		$data = $_GET;
		// Remove the save option
		unset($data['save']);
		// JSON encode the data
		$data = json_encode($data);
		// Generate a random filename code
		$file = md5(mt_rand() . time());
		// Save the data to a file
		file_put_contents("data/$file", $data);
		// Redirect the user
		header("Location: ?load=$file");
	}
	
	// Load a saved query string
	function load($file) {
		$string = $_GET;
		$file = json_decode(file_get_contents("data/$file"), true);
		$_GET = array_merge($file, $string);
	}

?>
