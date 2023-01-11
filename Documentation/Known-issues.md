# Known issues

## missing answers

It occasionally happened that in the list of answers in the application (comma-separated list of IDs of answers) answers of subgroups were missing.
<br>
This error leads to a TYPO3 because the link between answer and question cannot be broken.
<br>
So far it is not clear when and why this happens.
<br>
Ideas: possibly database interruption, double clicked....
<br>
In case of an occurrence, the missing number of answers can be recreated in the database. The data that may have already been entered is then not there, but the application can continue to be processed.

## missing files

It occasionally happened that the answer dataset (here upload items) contained references to FileSys entries (ID of the FileSys item) and that this entry was not (no longer) contained in the sysFile table and the file was also not (no longer) stored on the server.
<br>
When exporting, TYPO3 errors occurred because there is no entry in the database for the ID. Since v1.1.1, the error has been fixed, but only affects exports.
<br>
The reason could not be determined so far. All tests with upload and deletion of files were inconspicuous.
<br>
Ideas: When uploading, the file is registered in the system and referenced in the database. If the file is deleted from the frontend, the file is removed and removed from the SysFile table - but why not from the response in question.
In the answer dataset, FileIDs are noted separated by commas.

## freezing frontend

There were cases reported where the loading curl did not disappear when saving.
The users called this "frozen". However, since this state does not exist in a PHP application and the loading circle has no relation to the state of the server, we assume that this is a local problem.
<br>
No TYPO3 error could be found in the logs at the times mentioned.
<br>
Ideas:
- When saving, the HTTP connection to the server is terminated - for unknown and possibly uncontrollable reasons. The browser now waits for the page to be reloaded, but this does not happen.
- A previously unknown Javascript error prevents the data from being sent and thus the page from being reloaded.

Both variants have not yet been confirmed by our own tests.
