@mod @mod_surveypro @surveyprofield @surveyprofield_multiselect
Feature: Validate creation and submit for "multiselect" elements using the principal combinations of settings
  Setting I check in this test are:
      # Maximum required options: 0 - 3

  Background:
    Given the following "courses" exist:
      | fullname                             | shortname        | category | numsections |
      | Test submission for multiselect item | Multiselect item | 0        | 3           |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course           | role           |
      | teacher1 | Multiselect item | editingteacher |
      | student1 | Multiselect item | student        |
    And the following "activities" exist:
      | activity  | name           | intro              | course           |
      | surveypro | Surveypro test | For testing backup | Multiselect item |
    And I am on the "Surveypro test" "surveypro activity" page logged in as "teacher1"

    And I set the field "typeplugin" to "Multiple selection"
    And I press "Add"
    And I expand all fieldsets

  @javascript
  Scenario: test multiselect element having maximumrequired = 0
    Given I set the following fields to these values:
      | Content                  | What do you usually get for breakfast? |
      | Maximum allowed options  | Unlimited                              |
    And I set the multiline field "Options" to "milk\ncoffee\nbutter\nbread"
    And I press "Add"

    And I log out
    When I log in as "student1"
    And I am on "Test submission for multiselect item" course homepage
    And I follow "Surveypro test"

    # Test number 1: Student submits a standard answer
    And I press "New response"
    Then I should not see "No more than"
    And I set the field "id_surveypro_field_multiselect_1" to "milk, coffee"
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "1" submissions
    # End of test number 1

  @javascript
  Scenario: test multiselect element having maximumrequired = 3
    Given I set the following fields to these values:
      | Content                  | What do you usually get for breakfast? |
      | Maximum allowed options  | 3                                      |
    And I set the multiline field "Options" to "milk\ncoffee\nbutter\nbread"
    And I press "Add"

    And I log out
    When I log in as "student1"
    And I am on "Test submission for multiselect item" course homepage
    And I follow "Surveypro test"

    # Test number 2: Student ticks too many items
    And I press "New response"
    Then I should see "No more than 3 items are allowed"
    And I set the field "id_surveypro_field_multiselect_1" to "milk, coffee, butter, bread"
    And I press "Submit"
    Then I should see "Please tick no more than 3 options"
    And I set the field "id_surveypro_field_multiselect_1" to "milk, coffee, bread"
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "1" submissions
    # End of test number 2

    # Test number 3: Student submits a standard answer
    And I press "New response"
    Then I should see "No more than 3 items are allowed"
    And I set the field "id_surveypro_field_multiselect_1" to "coffee"
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "2" submissions
    # End of test number 3
