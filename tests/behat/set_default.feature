@mod @mod_surveypro
Feature: Verify good use of defaults
  In order to verify the default are applied only when needed
  As a student
  I make a submission and I view it in read only mode.

  @javascript
  Scenario: Verify defaults
    Given the following "courses" exist:
      | fullname      | shortname     | category | groupmode |
      | Test defaults | Test defaults | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Ttudent   | student  | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course        | role    |
      | student1 | Test defaults | student |
    And the following "activities" exist:
      | activity  | name                  | intro             | course        |
      | surveypro | Defaults in surveypro | To check defaults | Test defaults |
    And surveypro "Defaults in surveypro" has the following items:
      | type   | plugin      | settings                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               |
      | field  | boolean     | {"customnumber":"I",                                                                 "required":"1", "defaultoption":"1", "defaultvalue":"1"}                                                                                                                                                                                                                                                                                                                                                          |
      | field  | age         | {"customnumber":"II",   "indent":"2", "parentid":"@@itemid_01@@", "parentcontent":"1", "required":"1", "defaultoption":"1", "defaultvalueyear":"10", "defaultvaluemonth":"10",                                                                               "lowerboundyear":"8", "lowerboundmonth":"0",                                                                       "upperboundyear":"99", "upperboundmonth":"11"}                                                                         |
      | field  | character   | {"customnumber":"III",  "indent":"2", "parentid":"@@itemid_01@@", "parentcontent":"1", "required":"1",                      "defaultvalue":"Happy New Year", "pattern":"PATTERN_FREE"}                                                                                                                                                                                                                                                                                                                 |
      | field  | checkbox    | {"customnumber":"IV",   "indent":"2", "parentid":"@@itemid_01@@", "parentcontent":"1", "required":"1",                      "defaultvalue":"North\nSouth", "options":"North\nEast\nSouth\nWest"}                                                                                                                                                                                                                                                                                                       |
      | field  | date        | {"customnumber":"V",    "indent":"2", "parentid":"@@itemid_01@@", "parentcontent":"1", "required":"1", "defaultoption":"1", "defaultvalueday":"10", "defaultvaluemonth":"10", "defaultvalueyear":"1980",                                                     "lowerboundday":"1", "lowerboundmonth":"1", "lowerboundyear":"1970",                                               "upperboundday":"31", "upperboundmonth":"12", "upperboundyear":"2019"}                                                 |
      | field  | datetime    | {"customnumber":"VI",   "indent":"2", "parentid":"@@itemid_01@@", "parentcontent":"1", "required":"1", "defaultoption":"1", "defaultvalueday":"10", "defaultvaluemonth":"10", "defaultvalueyear":"1980", "defaultvaluehour":"10", "defaultvalueminute":"10", "lowerboundday":"1", "lowerboundmonth":"1", "lowerboundyear":"1970", "lowerboundhour":"0", "lowerboundminute":"0", "upperboundday":"31", "upperboundmonth":"12", "upperboundyear":"1999", "upperboundhour":"23", "upperboundminute":"59"} |
      | field  | integer     | {"customnumber":"VII",  "indent":"2", "parentid":"@@itemid_01@@", "parentcontent":"1", "required":"1", "defaultoption":"1", "defaultvalue":"10", "lowerbound":"6", "upperbound":"16"}                                                                                                                                                                                                                                                                                                                  |
      | field  | multiselect | {"customnumber":"VIII", "indent":"2", "parentid":"@@itemid_01@@", "parentcontent":"1", "required":"1",                      "defaultvalue":"North\nSouth", "options":"North\nEast\nSouth\nWest"}                                                                                                                                                                                                                                                                                                       |
      | field  | numeric     | {"customnumber":"IX",   "indent":"2", "parentid":"@@itemid_01@@", "parentcontent":"1", "required":"1",                      "defaultvalue":"10.0000000000"}                                                                                                                                                                                                                                                                                                                                            |
      | field  | radiobutton | {"customnumber":"X",    "indent":"2", "parentid":"@@itemid_01@@", "parentcontent":"1", "required":"1", "defaultoption":"1", "defaultvalue":"South", "options":"North\nEast\nSouth\nWest"}                                                                                                                                                                                                                                                                                                              |
      | field  | rate        | {"customnumber":"XI",   "indent":"2", "parentid":"@@itemid_01@@", "parentcontent":"1", "required":"1", "defaultoption":"1", "defaultvalue":"1\n2\n3\n1", "options":"IT\nEN\nFR\nES", "rates":"1\n2\n3"}                                                                                                                                                                                                                                                                                                |
      | field  | recurrence  | {"customnumber":"XII",  "indent":"2", "parentid":"@@itemid_01@@", "parentcontent":"1", "required":"1", "defaultoption":"1", "defaultvaluemonth":"10", "defaultvalueday":"10",                                                                                "lowerboundmonth":"1", "lowerboundday":"1",                                                                        "upperboundmonth":"12", "upperboundday":"31"}                                                                          |
      | field  | select      | {"customnumber":"XIII", "indent":"2", "parentid":"@@itemid_01@@", "parentcontent":"1", "required":"1", "defaultoption":"1", "defaultvalue":"South", "options":"North\nEast\nSouth\nWest"}                                                                                                                                                                                                                                                                                                              |
      | field  | shortdate   | {"customnumber":"XIV",  "indent":"2", "parentid":"@@itemid_01@@", "parentcontent":"1", "required":"1", "defaultoption":"1", "defaultvalueyear":"1980", "defaultvaluemonth":"10",                                                                             "lowerboundyear":"1970", "lowerboundmonth":"1",                                                                    "upperboundyear":"2019", "upperboundmonth":"12"}                                                                       |
      | field  | time        | {"customnumber":"XV",   "indent":"2", "parentid":"@@itemid_01@@", "parentcontent":"1", "required":"1", "defaultoption":"1", "defaultvaluehour":"10", "defaultvalueminute":"10",                                                                              "lowerboundhour":"0", "lowerboundminute":"0",                                                                      "upperboundhour":"23", "upperboundminute":"59"}                                                                        |
    And I am on the "Defaults in surveypro" "surveypro activity" page logged in as student1

    And I press "New response"
    # Boolean item
    Then the field "id_field_boolean_1" matches value "1"

    # Age item
    Then the field "id_field_age_2_year" matches value "10"
    Then the field "id_field_age_2_month" matches value "10"

    # Character item
    Then the field "id_field_character_3" matches value "Happy New Year"

    # Checkbox item
    Then the field "id_field_checkbox_4_0" matches value "1"
    Then the field "id_field_checkbox_4_1" matches value "0"
    Then the field "id_field_checkbox_4_2" matches value "1"
    Then the field "id_field_checkbox_4_3" matches value "0"

    # Date item
    Then the field "id_field_date_5_day" matches value "10"
    Then the field "id_field_date_5_month" matches value "10"
    Then the field "id_field_date_5_year" matches value "1980"

    # Datetime item
    Then the field "id_field_datetime_6_day" matches value "10"
    Then the field "id_field_datetime_6_month" matches value "10"
    Then the field "id_field_datetime_6_year" matches value "1980"
    Then the field "id_field_datetime_6_hour" matches value "10"
    Then the field "id_field_datetime_6_minute" matches value "10"

    # Integer item
    Then the field "id_field_integer_7" matches value "10"

    # Multiselect item
    Then the field "id_field_multiselect_8" matches value "North, South"

    # Numeric item
    Then the field "id_field_numeric_9" matches value "10"

    # Radiobutton item
    Then the field "id_field_radiobutton_10_0" matches value "0"
    Then the field "id_field_radiobutton_10_1" matches value "0"
    Then the field "id_field_radiobutton_10_2" matches value "1"
    Then the field "id_field_radiobutton_10_3" matches value "0"

    # Rate item
    Then the field "id_field_rate_11_0_0" matches value "1"
    Then the field "id_field_rate_11_1_1" matches value "1"
    Then the field "id_field_rate_11_2_2" matches value "1"
    Then the field "id_field_rate_11_3_0" matches value "1"

    # Recurrence item
    Then the field "id_field_recurrence_12_day" matches value "10"
    Then the field "id_field_recurrence_12_month" matches value "10"

    # Select item
    Then the field "id_field_select_13" matches value "South"

    # Shortdate item
    Then the field "id_field_shortdate_14_month" matches value "10"
    Then the field "id_field_shortdate_14_year" matches value "1980"

    # Time item
    Then the field "id_field_time_15_hour" matches value "10"
    Then the field "id_field_time_15_minute" matches value "10"

    # Now make the submission
    And I set the field "Is it true?" to "0"
    And I press "Submit"

    And I press "Continue to responses list"
    And I click action "Read only" on item 1

    # Boolean item
    Then I should see "No" in the "//span[@data-fieldtype='select']" "xpath_element"

    Then I should not see "10"
    Then I should not see "October"

    # Age item

    # Character item
    Then the field "id_field_character_3" matches value ""

    # Checkbox item
    Then the field "id_field_checkbox_4_0" matches value "0"
    Then the field "id_field_checkbox_4_1" matches value "0"
    Then the field "id_field_checkbox_4_2" matches value "0"
    Then the field "id_field_checkbox_4_3" matches value "0"

    # Date item

    # Datetime item

    # Integer item

    # Multiselect item

    # Numeric item
    Then the field "id_field_numeric_9" matches value ""

    # Radiobutton item
    Then the field "id_field_radiobutton_10_0" matches value "0"
    Then the field "id_field_radiobutton_10_1" matches value "0"
    Then the field "id_field_radiobutton_10_2" matches value "0"
    Then the field "id_field_radiobutton_10_3" matches value "0"

    # Rate item
    Then the field "id_field_rate_11_0_0" matches value "0"
    Then the field "id_field_rate_11_0_1" matches value "0"
    Then the field "id_field_rate_11_0_2" matches value "0"
    Then the field "id_field_rate_11_1_0" matches value "0"
    Then the field "id_field_rate_11_1_1" matches value "0"
    Then the field "id_field_rate_11_1_2" matches value "0"
    Then the field "id_field_rate_11_2_0" matches value "0"
    Then the field "id_field_rate_11_2_1" matches value "0"
    Then the field "id_field_rate_11_2_2" matches value "0"
    Then the field "id_field_rate_11_3_0" matches value "0"
    Then the field "id_field_rate_11_3_1" matches value "0"
    Then the field "id_field_rate_11_3_2" matches value "0"

    # Recurrence item

    # Select item

    # Shortdate item

    # Time item
