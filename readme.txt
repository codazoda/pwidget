===== pWidget =====

pWidget is a widget that lets you embed Pivotal Tracker search results into any web page, dashboard, Google Gadget, or anywhere else you can put an iframe.

===== Required Fields =====

A minimum widget call requires access credentials as part of the query string.  Either an API token or a username and password combination.

token=abcdefghijklmnoqrstuvwxyz
username=johndoe&password=foobar

Here's an example of a minimal call to pWidget.

http://sslcertified.com/pwidget/app/?username=joel&password=secret

===== Link Target =====

You can specify a target for any links on the page. By default the target is 'pivotal'.  You can change this to the name of another iframe or to something like '_new' to open a new window.

target=pivotal

===== Project(s) =====

You can specify a single project or a comma seperated list of projects.  To specify all projects, leave the value off.  By default the widget will pull stories from all of a users projects.

project=123456,987654

===== Search Filters =====

You can specify a search filter.  These filters use the same functionality as the search feature in Pivotal Tracker.  By default the widget pulls everything using a filter of "state:feature,bug,chore,release".

filter=mywork:jrd state:bug

===== Priority Labels =====

You can opt to include a 'priority' column into the widget ouput.  This is a feature that doesn't currently exist in Pivotal Tracker but was added to pWidget.  When you turn it on, it will mark each story with a priority of 'A', 'B', or 'C' based on labels with those values in Pivotal Tracker. If a story doesn't have a label of 'A' or 'B' then that story will default to a 'C' priority.

priority=y

===== Sorting =====

You can sort the resulting list of stories by one or more custom columns. By default the widget sorts by current_state.

sort=current_state,priority

===== Security =====

pWidget is hosted on a server that uses SSL.  Data, however, is passed to pWidget via the query string.  Web browsers encrypt that data in transit but any user who can see the page with pWidget embedded in it can see the full query string including your username and password combination or your API key.  Click the "Save and Secure" link to generate a new URL with a random hash that does not include private details in the query string.

===== Support =====

For questions or comments, please email joel@joeldare.com.
