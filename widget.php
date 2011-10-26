<?php

	/**
	 * ===== pWidget =====
	 *
	 * A widget that lets you embed Pivotal Tracker search 
	 * results into any web page, dashboard, Google Gadget, 
	 * or anywhere else you can put an iframe.
	 */

	// Include the file that defines the class
	require '../pivotal_class/pivotal.php';

	// Verify all of the fields
	validate();
	
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
	if ($_GET['projects'] == '') {
		// Get a list of all the users projects
		$projectObject = $pivotal->getProjects();
		foreach($projectObject->project as $pro) {
			$projects[] = array(
				'id' => $pro->id
			);
		}
	} else {
		// Split a comma seperated list
		$projList = split(',', $_GET['projects']);
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
	 * filter=mywork:jrd, state:bug
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
		$filter = 'state:feature,bug,chore,release';
	}

// TODO: Create an sqlite table
	
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
// TODO: Write the data to the sqlite table			
			// Add this story to an array
			$stories[] = array(
				'id' => (int)$p->id,
				'current_state' => (string)$p->current_state,
				'estimate' => (int)$p->estimate,
				'name' => (string)$p->name,
				'created_at' => (string)$p->created_at,
				'priority' => $priority,
				'labels' => $labels,
				'owned_by' => (string)$p->owned_by
			);
		}
	
	};
	
	/**
	 * ===== Sorting =====
	 *
	 * You can sort the resulting list of stories by one
	 * or more custom columns. By default the widget
	 * sorts by current_state then by created_at.
	 *
	 * filter=current_state,owned_by
	 */
	
	// Make a list of the columns to sort on
	if ($_GET['sort'] == '') {
		$_GET['sort'] = 'current_state,created_at';
	}
	
	// Split the sort list
	$sortList = split(',', $_GET['sort']);

// TODO: Setup the sqlite ORDER BY clause
// TODO: Query the data from sqlite

	// Loop through the stories creating lists of columns to sort on later
	foreach($stories as $s) {
		foreach ($sortList as $column) {
			$sortCol[] = $s[$column];
		}
	}
	
	// Sort the array on the specified columns
	/*
	foreach ($sortCol as $column) {
		$sortArgs[] = &$column;
		$sortArgs[] = 'SORT_ASC';
	}
	$sortArgs[] = &$stories;
	call_user_func_array('array_multisort', $sortArgs);
	*/
	
	// Include the view
	include "widget.phtml";

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

?>
