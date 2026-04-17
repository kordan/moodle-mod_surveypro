@mod @mod_surveypro @surveyprofield @surveyprofield_checkbox
Feature: Search using one and two checkbox items
  In order to validate search form with one and two items
  As a student
  I search for submitted records.

  @javascript
  Scenario: Search using one and two checkbox fields
    Given the following "courses" exist:
      | fullname                  | shortname   | category | numsections |
      | Test checkbox search form | Search form | 0        | 1           |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course      | role           |
      | teacher1 | Search form | editingteacher |
      | student1 | Search form | student        |
    And the following "activities" exist:
      | activity  | name                      | intro                  | course      |
      | surveypro | Checkbox search form test | For searching purposes | Search form |
    And surveypro "Checkbox search form test" has the following items:
      | type  | plugin   | settings                                                                                                                                                                                                 |
      | field | checkbox | {"content":"What do you usually get for breakfast?",                                "required":"1", "customnumber":"1", "adjustment":"1", "options":"milk\ncoffee\nbutter\nbread", "insearchform":"1"}   |
      | field | checkbox | {"content":"What do you usually wear when you go out cycling and it is very cold?", "required":"1", "customnumber":"2", "adjustment":"1", "options":"gloves\nhat\nwindbreaker\nwool undershirt\ntights"} |
    And I am on the "Checkbox search form test" "surveypro activity" page logged in as student1

    # Add the first record
    And I press "New response"
    And I set the following fields to these values:
      | id_field_checkbox_1_0 | 1 |
      | id_field_checkbox_1_1 | 1 |
      | id_field_checkbox_2_0 | 1 |
      | id_field_checkbox_2_1 | 1 |
      | id_field_checkbox_2_2 | 1 |
    And I press "Submit"

    # Add the second record
    And I press "New response"
    And I set the following fields to these values:
      | id_field_checkbox_1_0 | 1 |
      | id_field_checkbox_1_1 | 1 |
      | id_field_checkbox_2_1 | 1 |
      | id_field_checkbox_2_3 | 1 |
      | id_field_checkbox_2_4 | 1 |
    And I press "Submit"

    # 1st search for submitted records
    Given I am on the "Checkbox search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With only one field in the search form, "Ignore me" must not be in the form.
    And I should not see "*"

    And I set the following fields to these values:
      | id_field_checkbox_1_0 | 1 |
      | id_field_checkbox_1_1 | 1 |
    And I press "Search"
    Then I should see "2" submissions

    # Add the second search field
    And I am on the "Checkbox search form test" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1
    And I follow "addtosearch_item_2"

    # 2nd search for submitted records
    Given I am on the "Checkbox search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With more than one field in the search form, "Ignore me" must be in the form.
    # I test it is in the form setting it to 0

    And I set the field "id_field_checkbox_2_ignoreme" to "0"
    And I set the following fields to these values:
      | id_field_checkbox_2_1 | 1 |
      | id_field_checkbox_2_3 | 1 |
      | id_field_checkbox_2_4 | 1 |
    And I press "Search"
    Then I should see "1" submissions

    # 3rd search for submitted records
    Given I am on the "Checkbox search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With more than one field in the search form, "Ignore me" must be in the form.
    # I test it is in the form setting it to 0

    And I set the field "id_field_checkbox_1_ignoreme" to "0"
    And I set the following fields to these values:
      | id_field_checkbox_1_0 | 1 |
      | id_field_checkbox_1_1 | 1 |
    And I set the field "id_field_checkbox_2_ignoreme" to "0"
    And I set the following fields to these values:
      | id_field_checkbox_2_1 | 1 |
      | id_field_checkbox_2_3 | 1 |
      | id_field_checkbox_2_4 | 1 |
    And I press "Search"
    Then I should see "1" submissions
