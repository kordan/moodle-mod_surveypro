@mod @mod_surveypro @current
Feature: make a submission test for each available item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I fill a surveypro and go to see responses

  @javascript
  Scenario: test a submission works fine for each available item
    Given the following "courses" exist:
      | fullname                                | shortname       | category | groupmode |
      | Test submission for each available item | Submission test | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course          | role           |
      | teacher1 | Submission test | editingteacher |
      | student1 | Submission test | student        |

    And I log in as "teacher1"
    And I follow "Test submission for each available item"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Name        | Surveypro test                                                 |
      | Description | This is a surveypro to test submission for each available item |
    And I follow "Surveypro test"

    # #############
    # add an item using the 1st plugin
    And I set the field "typeplugin" to "Age [yy/mm]"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | How old were you at you first access to narcotics |
      | Required                 | 1                                                 |
      | Indent                   | 0                                                 |
      | Question position        | left                                              |
      | Element number           | 1                                                 |
      | Hide filling instruction | 1                                                 |
      | id_defaultoption_2       | Custom                                            |
      | id_defaultvalue_year     | 14                                                |
      | id_defaultvalue_month    | 4                                                 |
    And I press "Add"

    # #############
    # add an item using the 2nd plugin
    And I set the field "typeplugin" to "Attachment"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | Please upload your Curriculum Vitae |
      | Required                 | 1                                   |
      | Indent                   | 0                                   |
      | Question position        | left                                |
      | Element number           | 2                                   |
      | Hide filling instruction | 1                                   |
    And I press "Add"

    # #############
    # add an item using the 3rd plugin
    And I set the field "typeplugin" to "Autofill"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content             | Your user ID |
      | Indent              | 0            |
      | Question position   | left         |
      | Element number      | 3            |
      | id_element01_select | user ID      |
    And I press "Add"

    # #############
    # add two items using the 4th plugin
    And I set the field "typeplugin" to "Boolean"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content           | Is this true? |
      | Required          | 1             |
      | Indent            | 0             |
      | Question position | left          |
      | Element number    | 4a            |
    And I press "Add"

    And I set the field "typeplugin" to "Boolean"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content           | Is this true?            |
      | Required          | 1                        |
      | Indent            | 0                        |
      | Question position | left                     |
      | Element number    | 4b                       |
      | Boolean style     | horizontal radio buttons |
    And I press "Add"

    # #############
    # add an item using the 5th plugin
    And I set the field "typeplugin" to "Checkbox"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content           | What do you usually eat for breakfast? |
      | Indent            | 0                                      |
      | Question position | left                                   |
      | Element number    | 5                                      |
    And I fill the textarea "Options" with multiline content "milk\nsugar\njam\nchocolate"
    And I press "Add"

    # #############
    # add an item using the 6th plugin
    And I set the field "typeplugin" to "Date (short) [mm/yyyy]"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | When did you buy your current car? |
      | Required                 | 1                                  |
      | Indent                   | 0                                  |
      | Question position        | left                               |
      | Element number           | 6                                  |
      | Hide filling instruction | 1                                  |
    And I press "Add"

    # #############
    # add an item using the 7th plugin
    And I set the field "typeplugin" to "Date [dd/mm/yyyy]"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | When were you born? |
      | Required                 | 1                   |
      | Indent                   | 0                   |
      | Question position        | left                |
      | Element number           | 7                   |
      | Hide filling instruction | 1                   |
    And I press "Add"

    # #############
    # add an item using the 8th plugin
    And I set the field "typeplugin" to "Date and time [dd/mm/yyyy;hh:mm]"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | In which day and at what time do you remember it happened? |
      | Required                 | 1                                                          |
      | Indent                   | 0                                                          |
      | Question position        | left                                                       |
      | Element number           | 8                                                          |
      | Hide filling instruction | 1                                                          |
    And I press "Add"

    # #############
    # add an item using the 22th plugin
    And I set the field "typeplugin" to "Page break"
    And I press "Add"

    And I press "Add"

    # #############
    # add an item using the 9th plugin
    And I set the field "typeplugin" to "Integer (small)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | When did you have your last meal?           |
      | Required                 | 1                                           |
      | Indent                   | 0                                           |
      | Question position        | left                                        |
      | Element number           | 9                                           |
      | Additional note          | The value is supposed to be in hours        |
    And I press "Add"

    # #############
    # add an item using the 10th plugin
    And I set the field "typeplugin" to "Multiple selection"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                | What do you usually eat for breakfast? |
      | Indent                 | 0                                      |
      | Question position      | left                                   |
      | Element number         | 10                                     |
    And I fill the textarea "Options" with multiline content "milk\nsugar\njam\nchocolate"
    And I press "Add"

    # #############
    # add an item using the 11th plugin
    And I set the field "typeplugin" to "Numeric"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | Which temperature best suit your preferences? |
      | Required                 | 1                                             |
      | Indent                   | 0                                             |
      | Question position        | left                                          |
      | Element number           | 11                                            |
      | Hide filling instruction | 1                                             |
      | Decimal positions        | 1                                             |
      | Minimum value            | 10                                            |
      | Maximum value            | 40                                            |
    And I press "Add"

    # #############
    # add an item using the 12th plugin
    And I set the field "typeplugin" to "Radio buttons"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content           | Which summer holidays place do you prefer? |
      | Required          | 1                                          |
      | Indent            | 0                                          |
      | Question position | left                                       |
      | Element number    | 12                                         |
    And I fill the textarea "Options" with multiline content "sea\nmountain\nlake\nhills\ndesert"
    And I press "Add"

    # #############
    # add an item using the 19th plugin
    And I set the field "typeplugin" to "Fieldset"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content | Here you can find two different styled rate item |
    And I press "Add"

    # #############
    # add two items using the 13th plugin
    And I set the field "typeplugin" to "Rate"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content        | Please order these foreign languages according to your preferences |
      | Required       | 1                                                                  |
      | Indent         | 0                                                                  |
      | Element number | 13a                                                                |
    And I fill the textarea "Options" with multiline content "Italian\nSpanish\nEnglish\nFrench\nGerman"
    And I fill the textarea "Rates" with multiline content "Mother tongue\nQuite well\nNot sufficient\nCompletely unknown"
    And I press "Add"

    And I set the field "typeplugin" to "Rate"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content        | Please order these foreign languages according to your preferences |
      | Required       | 1                                                                  |
      | Indent         | 0                                                                  |
      | Element number | 13b                                                                |
      | Rate style     | dropdown menu                                                      |
    And I fill the textarea "Options" with multiline content "Italian\nSpanish\nEnglish\nFrench\nGerman"
    And I fill the textarea "Rates" with multiline content "Mother tongue\nQuite well\nNot sufficient\nCompletely unknown"
    And I press "Add"

    # #############
    # add an item using the 20th plugin
    And I set the field "typeplugin" to "Fieldset closure"
    And I press "Add"

    And I press "Add"

    # #############
    # add an item using the 14th plugin
    And I set the field "typeplugin" to "Recurrence [dd/mm]"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | When do you usually celebrate your name-day? |
      | Required                 | 1                                            |
      | Indent                   | 0                                            |
      | Question position        | left                                         |
      | Element number           | 14                                           |
      | Hide filling instruction | 1                                            |
    And I press "Add"

    # #############
    # add an item using the 15th plugin
    And I set the field "typeplugin" to "Select"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content           | Which summer holidays place do you prefer? |
      | Required          | 1                                          |
      | Indent            | 0                                          |
      | Question position | left                                       |
      | Element number    | 15                                         |
    And I fill the textarea "Options" with multiline content "sea\nmountain\nlake\nhills\ndesert"
    And I press "Add"

    # #############
    # add an item using the 16th plugin
    And I set the field "typeplugin" to "Text (long)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | Enter a short description of yourself |
      | Required                 | 1                                     |
      | Indent                   | 0                                     |
      | Question position        | left                                  |
      | Element number           | 16                                    |
      | Hide filling instruction | 1                                     |
    And I press "Add"

    # #############
    # add an item using the 17th plugin
    And I set the field "typeplugin" to "Text (short)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | Write down your email |
      | Required                 | 1                     |
      | Indent                   | 0                     |
      | Question position        | left                  |
      | Element number           | 17                    |
      | Hide filling instruction | 1                     |
      | id_pattern               | email address         |
    And I press "Add"

    # #############
    # add an item using the 18th plugin
    And I set the field "typeplugin" to "Time"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | At what time do you usually get breakfast? |
      | Required                 | 1                                          |
      | Indent                   | 0                                          |
      | Element number           | 18                                         |
      | Hide filling instruction | 1                                          |
    And I press "Add"

    And I log out

    # student1 logs in
    When I log in as "student1"
    And I follow "Test submission for each available item"
    And I follow "Surveypro test"
    And I press "New response"

    # student1 submits his first response
    And I set the following fields to these values:
      | id_surveypro_field_age_1_year        | 23      |
      | id_surveypro_field_age_1_month       | 8       |

    And I upload "mod/surveypro/tests/fixtures/dummyCV.pdf" file to "2: Please upload your Curriculum Vitae" filemanager

    And I set the following fields to these values:
      | 4a: Is this true?                    | Yes     |
      | id_surveypro_field_boolean_5_0       | 1       |
      | id_surveypro_field_checkbox_6_0      | 1       |
      | id_surveypro_field_checkbox_6_3      | 1       |
      | id_surveypro_field_shortdate_7_month | March   |
      | id_surveypro_field_shortdate_7_year  | 1975    |
      | id_surveypro_field_date_8_day        | 16      |
      | id_surveypro_field_date_8_month      | October |
      | id_surveypro_field_date_8_year       | 1988    |
      | id_surveypro_field_datetime_9_day    | 23      |
      | id_surveypro_field_datetime_9_month  | August  |
      | id_surveypro_field_datetime_9_year   | 2010    |
      | id_surveypro_field_datetime_9_hour   | 17      |
      | id_surveypro_field_datetime_9_minute | 35      |
    And I press "Next page >>"

    And I set the following fields to these values:
      | 9: When did you have your last meal?                | 7                  |
      | id_surveypro_field_multiselect_12                   | milk               |
      | 11: Which temperature best suit your preferences?   | 25.5               |
      | id_surveypro_field_radiobutton_14_3                 | 1                  |
      | id_surveypro_field_rate_16_0_0                      | 1                  |
      | id_surveypro_field_rate_16_1_1                      | 1                  |
      | id_surveypro_field_rate_16_2_2                      | 1                  |
      | id_surveypro_field_rate_16_3_3                      | 1                  |
      | id_surveypro_field_rate_16_4_2                      | 1                  |
      | id_surveypro_field_rate_17_0                        | Mother tongue      |
      | id_surveypro_field_rate_17_1                        | Quite well         |
      | id_surveypro_field_rate_17_2                        | Not sufficient     |
      | id_surveypro_field_rate_17_3                        | Completely unknown |
      | id_surveypro_field_rate_17_4                        | Not sufficient     |
      | id_surveypro_field_recurrence_19_day                | 7                  |
      | id_surveypro_field_recurrence_19_month              | June               |
      | 15: Which summer holidays place do you prefer?      | hills              |
      | 16: Enter a short description of yourself           | I am cool          |
      | 17: Write down your email                           | me@myserver.net    |
      | id_surveypro_field_time_23_hour                     | 7                  |
      | id_surveypro_field_time_23_minute                   | 15                 |
    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions displayed
