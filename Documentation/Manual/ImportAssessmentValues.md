# Import of external assessment values

In the context of external assessments (specialist centres, committees, etc.),
the assessment can be added to the applications by importing an Excel file.
The following requirements must be met:
1. the data format is an Excel file
2. the data must be in the first worksheet
3. the column with the signature - for clear assignment - is called "id"
4. the value to be imported is in the column with the heading "pred_value"
5. the action to be imported is in the column with the heading "pred_action"

The names of the columns are predefined. You are able to change the names in the Extension Configuration (TYPO3 Backend) in tab "backend".
The order must met the order descriped above.

Both column contents (value, action) are added to the proposal and can then be filtered
according to the action value. The output in the list then also includes the value.

The filtering is based on the real existing values, so that any content
can be used here - adapted to the internal process.

A new import of the data (or a partial list) overwrites the data without prompting.

A short summary is displayed as a flash message after the import.

```
The import result:
Values:
- new score values inserted: 40
- score values without change: 2
- score values overwritten: 0
Actions:
- new action values entered: 40
- action values without change:2
- action values overwritten :0

```
