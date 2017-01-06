#THIS PROJECT IS DEPRECATED

PrevJumpNext is not maintained anymore. It maybe does not work in Evolution 1.1 anymore. Please fork it and bring it back to life, if you need it.

PrevJumpNext
================================================================================

Generates links to navigate between documents in a directory
for the MODX Evolution content management framework

Features:
--------------------------------------------------------------------------------
Highly configurable. It can display:
- 'Showing article x of x'
- 'Prev' and 'Next' links with or without document titles
- 'First' and 'Last' links.
- An 'Index' link to parent folder
- A dropdown select to jump quickly to a document

Installation:
--------------------------------------------------------------------------------
1. Upload the folder *assets/snippets/prevjumpnext* in the corresponding folder in your installation
2. Create a snippet called PrevJumpNext and fill the snippet code with the content of the file *install/assets/snippets/prevjumpnext.tpl*

Parameters:
--------------------------------------------------------------------------------

The following snippet parameter could be used

Name | Description | Default
---- | ----------- | -------
language | Language of the snippet output | english
startId | id of document folder to start from | parent document id
sortBy | Document variable to sort items by. 'menuindex' or 'id' or 'createdon', etc...  | createdon
sortDir | Sort direction ('ASC' or 'DESC') | ASC
displayTitle | Display document titles instead of &prevText and &nextText | 1
displayFixed | Show 'first' and 'last' links | 0
usePlaceHolder | Generate placeholders and don't output html | 0
useJump | Replace the index link by the select list when NOT using the placeholders | 0
indexDocumentId | ID of the document linked as 'index' | &startID
displayNoPrevNext | Display &noPrevNextText in place of 'previous' and 'next' when there is no previous or next item | 0
noPrevNextText | Text to display if there is no Prev or Next item and &displayNoPrevNext == 1 | language dependent
indexText | Title for 'index' item | language dependent
jumpText | Title for select list | language dependent
firstText | Title for 'first' link | language dependent
prevText | Title for 'previous' item when $displayTitle is set to false | language dependent
nextText | Title for 'next' item when $displayTitle is set to false | language dependent
lastText | Title for 'last' item | language dependent
exclude | Comma-separated list of document IDs to exclude from navigation | -
separator | Separator between two items | ` | `
displayIndex | Show 'index' item | 1
displayTotal | Show 'total' item ('Showing record X of Y') | 1
firstSymbol | Prefixed to 'first' item | -
prevSymbol | Prefixed to 'previous' item | -
nextSymbol | Suffixed to 'next' item | -
lastSymbol | Suffixed to 'last' item | -
firstClass | Class assigned to 'first' item | first
prevClass | Class assigned to 'previous' item | prev
nextClass | Class assigned to 'next' item | next
lastClass | Class assigned to 'last' item | last
indexClass | Class assigned to 'index' item | index
totalClass | Class assigned to 'total' item | total
currentNumberClass | Class assigned to 'currentNumber' in 'total' item | currentNumber
totalNumberClass | Class assigned to 'totalNumber' in 'total' item | totalNumber
recordTypeName | Name of records type for 'total' item (E.g. 'record': 'Showing record X of Y') | record
includeFolders | include folder documents in the item list | 1
maxTitleChars | Truncate document titles to this number of characters (0 = full title) | 0
circle | Link from last item to first item and vice versa | 0
useYams | Use YAMS for url generation | 0
langid | YAMS language id | -

The following placeholders are set in usePlaceholder mode:

Placeholder | Content
---- | -----------
PJN_first | the first document
PJN_prev | the previous document
PJN_jump | the droplist to jump to the selectionned document
PJN_index | the link to retern to the index page
PJN_next | the next document
PJN_last | the last document


Examples:
--------------------------------------------------------------------------------

```
[[PrevJumpNext?
&sortBy=`menuindex`
&sortDir=`ASC`
&displayTitle=`1`
]]
```

```
[[PrevJumpNext?
&sortBy=`pub_date`
&usePlaceHolder=`1`
&indexDocumentID=`84`
&firstText=`First`
&lastText=`Last`
&prevText=`Previous`
&nextText=`Next`
&indexText=`List of Articles`
&jumpText=`Quick Access`
&displayNoPrevNext=`1`
&noPrevNextText=`It's the end`
]]
```

will fill these placeholders in the document [+PJN_first+] [+PJN_prev+] [+PJN_jump+] [+PJN_index+] [+PJN_next+] [+PJN_last+]
