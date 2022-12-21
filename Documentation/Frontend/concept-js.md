# JS for open-oap

## targets
JS programming should meet the following criteria (exceptions should be a. avoided if possible, b. temporary, c. documented):

- without dependencies
- vanilla
- modular
- with consideration of accessibility
- should be part of the plugin as an open-source component


## funktionen

- Assignment of the notes/error texts of the FE validation when calling up the form page
- FE validation of individual elements
  - Check field formatting (if not reasonably realisable by HTML5 form attribute, see "Error classes" below)
- FE validation of a page
- Input elements that cannot be created with standard elements
  - Datepicker (also for time period, i.e. from date 1 to date 2), with extensive configuration possibilities
  - Select (with configurable multiple function, search/filter function, display of groups)
  - File upload (configurable)

## Validation

### Error classes

- K1: A mandatory field that is not filled in is not marked as an error, the page can be saved but the application cannot be submitted.
- K2: A field that is filled in the wrong format or with too many characters will result in an error and the page cannot be saved.
-
### types of validation

- Mandatory field (K1)
- Maximum number of characters (K2)
- minimum and maximum value (K2)
- Format: e-mail, web address, telephone number, EUR (K2)
- Select: min. number, max. number (K2 ?)
- File upload: Format (must fit a given list of formats [pdf. docx. xlsx, jpg...], maximum file size).


### error messages

- Please fill in all mandatory fields (marked with %s)
- %s characters remaining

- This field is mandatory
- The value must not exceed %d.
- The value must be at least %d.
- At least %d option must be selected.
- A maximum of %d options may be chosen.

The following messages could be transferred into a form - with replacement:
- Please enter a valid email address.
- Please enter a valid web address.
- Please enter a valid phone number.

generalised
- Please enter a valid %s.
