===== pWidget =====

pWidget is a widget that lets you embed Pivotal Tracker search results into any web page, dashboard, Google Gadget, or anywhere else you can put an iframe.

The pWidget Home Page is available at https://www.sslcertified.com/pwidget.

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

===== Labels =====

You can supress the labels from being displayed in the widget by adding the labels option.

labels=n

===== Story Owner =====

By default each story shows the full owner name next to it.  The API returns the full owner name and not their initials, so that full name is displayed currently.  You can turn it off using the owner option.

owner=n

===== Sorting =====

You can sort the resulting list of stories by one or more custom columns. By default the widget sorts by current_state.  Sort columns include id, current_state, estimate, name, created_at, priority, labels, owned_by, and story_type.

sort=current_state,priority

===== Title =====

By default the widget has a title of 'pWidget' but you can change the title by adding the title attribute.  Setting the title to 'n' will hide the title completely.

title=n

===== Save =====

By default the widget has a save line at the bottom if you haven't loaded it from a saved URL.  Setting it to 'n' will force this to turn off.

save=n

===== Refresh =====

You can specify a refresh rate for the widget frame. By default the refresh is 0.  The refresh rate is in seconds with a minimum of 15 seconds.

refresh=30

===== Security =====

pWidget is hosted on a server that uses SSL.  Data, however, is passed to pWidget via the query string.  Web browsers encrypt that data in transit but any user who can see the page with pWidget embedded in it can see the full query string including your username and password combination or your API key.  Click the "Save and Secure" link to generate a saved result that does not include private details in the query string.  You can then append additional paramaters on your query string to overwrite the saved defaults.  It's useful, for example, to save a default widget with just your username/password combination.  Then load that and append additional options.

http://sslcertified.com/pwidget/app/?load=0b61f91e94fd41425820847ab0dcdc69&filter=mywork:jrd

===== Cross Domain iFrames =====

In order to target one iFrame from another the page that includes the iframes and the iframe with the link must exist on the same domain.  This isn't possible when using pWidget since it is hosted on sslceritifed.com and you'll be including it into a page that is not.  As a result all links will open in new windows.  One possible work around is to request the pWidget page using a programming lanuage and then include it's results into a DIV on the page.

===== MIT License =====

Copyright (c) 2011, 2012 Joel Dare

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

===== Support =====

For questions or comments, please email joel@joeldare.com.
