@mod @mod_surveypro @surveyprofield @surveyprofield_fileupload
Feature: test the use of fileupload setup form
  In order to test fileupload setup form
  As a teacher
  I fill each its field and I return back to verify all is where I wrote it

  @javascript
  Scenario: test fileupload setup form
    Given the following "courses" exist:
      | fullname              | shortname             | category | groupmode |
      | Fileupload setup form | Fileupload setup form | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                | role           |
      | teacher1 | Fileupload setup form | editingteacher |
    And the following "activities" exist:
      | activity  | name                       | intro                      | course                |
      | surveypro | Test fileupload setup form | Test fileupload setup form | Fileupload setup form |
    And surveypro "Test fileupload setup form" contains the following items:
      | type  | plugin  |
      | field | boolean |
    And I am on the "Test fileupload setup form" "surveypro activity" page logged in as teacher1
    And I select "Layout" from secondary navigation

    # add an fileupload item
    And I set the field "typeplugin" to "Attachment"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                              | Please upload your profile image |
      | Required                             | 1                                |
      | Indent                               | 1                                |
      | Question position                    | left                             |
      | Element number                       | II.a                             |
      | Variable                             | F1                               |
      | Additional note                      | Additional note                  |
      | Hidden                               | 1                                |
      | Reserved                             | 1                                |
      | Parent element                       | Boolean [1]: Is it true?         |
      | Parent content                       | 1                                |
      | Maximum files                        | 2                                |
      | Maximum file size                    | 2097152                          |
      | Allowed file types (comma separated) | jpg, , png, *, GIF               |
    And I press "Add"

    Then I should see "File extensions must start with a dot"
    And I set the field "Allowed file types (comma separated)" to ".jpg, , ..png, *, .GIF"
    And I press "Add"

    Then I should see "File extensions can not be empty. Probabily you typed a comma twice."
    And I set the field "Allowed file types (comma separated)" to ".jpg, ..png, *, .GIF"
    And I press "Add"

    Then I should see "Only one dot is allowed per each file extension"
    And I set the field "Allowed file types (comma separated)" to ".jpg, .png, *, .GIF"
    And I press "Add"

    Then I should see "'*' is meaningful only if used individually"
    And I set the field "Allowed file types (comma separated)" to ".jpg, .png, .GIF"
    And I press "Add"

    Then I should see "Only lower case letters and numbers are allowed into file extensions"
    And I set the field "Allowed file types (comma separated)" to ".jpg, .png, .gif"
    And I press "Add"

    And I follow "edit_item_2"
    Then the field "Content" matches value "Please upload your profile image"
    Then the field "Required" matches value "1"
    Then the field "Indent" matches value "1"
    Then the field "Question position" matches value "left"
    Then the field "Element number" matches value "II.a"
    Then the field "Variable" matches value "F1"
    Then the field "Additional note" matches value "Additional note"
    Then the field "Hidden" matches value "1"
    Then the field "Reserved" matches value "1"
    Then the field "Parent element" matches value "Boolean [1]: Is it true?"
    Then the field "Parent content" matches value "1"
    Then the field "Maximum files" matches value "2"
    Then the field "Maximum file size" matches value "2097152"
    Then the field "Allowed file types (comma separated)" matches value ".jpg, .png, .gif"
    And I press "Cancel"

    And I follow "show_item_2"
    And I select "Preview" from the "jump" singleselect
    Then I should see "II.a Please upload your profile image"
    Then I should see "Additional note"
