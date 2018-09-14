@mod @mod_surveypro @surveyprofield @surveyprofield_checkbox
Feature: Validate creation and submit for "checkbox" elements using the principal combinations of settings
  Setting I check in this test are:
      # Maximum required options: 0 - 3

  Background:
    Given the following "courses" exist:
      | fullname                          | shortname     | category | numsections |
      | Test submission for checkbox item | Checkbox item | 0        | 3           |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course        | role           |
      | teacher1 | Checkbox item | editingteacher |
      | student1 | Checkbox item | student        |
    And the following "activities" exist:
      | activity  | name           | intro              | course        | idnumber   |
      | surveypro | Surveypro test | For testing backup | Checkbox item | surveypro1 |
    And I log in as "teacher1"
    And I am on "Test submission for checkbox item" course homepage
    And I follow "Surveypro test"
    And I set the field "typeplugin" to "Checkbox"
    And I press "Add"
    And I expand all fieldsets

  @javascript
  Scenario: test checkbox element having maximumrequired = 0
    Given I set the following fields to these values:
      | Content                  | What do you usually get for breakfast? |
      | Maximum allowed options  | Unlimited                              |
    And I set the multiline field "Options" to "milk\ncoffee\nbutter\nbread"
    And I press "Add"

    And I log out
    When I log in as "student1"
    And I am on "Test submission for checkbox item" course homepage
    And I follow "Surveypro test"

    # Test number 1: Student submits a standard answer
    And I press "New response"
    Then I should not see "No more than"
    And I set the following fields to these values:
      | id_surveypro_field_checkbox_1_0        | 1 |
      | id_surveypro_field_checkbox_1_3        | 1 |
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "1" submissions
    # End of test number 2

  @javascript
  Scenario: test checkbox element having maximumrequired = 3
    Given I set the following fields to these values:
      | Content                  | What do you usually get for breakfast? |
      | Maximum allowed options  | 3                                      |
    And I set the multiline field "Options" to "milk\ncoffee\nbutter\nbread"
    And I press "Add"

    And I log out
    When I log in as "student1"
    And I am on "Test submission for checkbox item" course homepage
    And I follow "Surveypro test"

    # Test number 2: Student ticks too many checkboxes
    And I press "New response"
    Then I should see "No more than 3 checkboxes are allowed"
    And I set the field "id_surveypro_field_checkbox_1_0" to "1"
    And I set the field "id_surveypro_field_checkbox_1_1" to "1"
    And I set the field "id_surveypro_field_checkbox_1_2" to "1"
    And I set the field "id_surveypro_field_checkbox_1_3" to "1"
    And I press "Submit"
    Then I should see "Please tick no more than 3 options"
    And I set the field "id_surveypro_field_checkbox_1_1" to "0"
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "1" submissions
    # End of test number 2

    # Test number 3: Student submits a standard answer
    And I press "New response"
    Then I should see "No more than 3 checkboxes are allowed"
    And I set the field "id_surveypro_field_checkbox_1_0" to "1"
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "2" submissions
    # End of test number 3
