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
	 
	// Include the file that defines the class
	require '../pivotal_class/pivotal.php';
	
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
	 * priority=1
	 */
	
	// Set a default filter
	$filter = $_GET['filter'];
	if ($filter == '') {
		$filter = 'type:feature,bug,chore,release';
	}

	// Create an SQLite DB and table in memory
	$db = sqlite_open(':memory:', 0666, $error);
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
	$result = sqlite_exec($db, $create);
	$result = sqlite_exec($db, 'BEGIN TRANSACTION');
	
	// Loop through the projects
	foreach ($projects as $p) {

		// Grab all the matching stories from this project
		$pull = $pivotal->getStories($p['id'], $_GET['filter']);

		// Loop through the data building an array for our view
		foreach($pull->story as $p) {
			// Space seperate the labels
			$labels = str_replace(',', ', ', (string)$p->labels);
			// If priority was specified, set a priority for this item
			if ($_GET['priority']) {
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
			$result = sqlite_exec($db, $insert);

		}
	
	}
	
	// End the transaction and write the data
	$result = sqlite_exec($db, 'COMMIT TRANSACTION');
	
	/**
	 * ===== Sorting =====
	 *
	 * You can sort the resulting list of stories by one
	 * or more custom columns. By default the widget
	 * sorts by current_state then by created_at.
	 *
	 * sort=current_state,priority
	 */
	
	// Setup the order by
	if ($_GET['sort']) {
		$order = 'ORDER BY ' . $_GET['sort'];
	} else {
		$order = 'ORDER BY current_state,created_at';
	}
	
	// Query the data from sqlite
	$query = 'SELECT * FROM stories ' . $order;
	$stories = sqlite_array_query($db, $query, SQLITE_ASSOC);
	
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
		$_GET = json_decode(file_get_contents("data/$file"), true);
		$_GET['load'] = $file;
	}

?>
