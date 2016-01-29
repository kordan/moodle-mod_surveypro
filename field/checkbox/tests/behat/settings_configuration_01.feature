@mod @mod_surveypro @surveyprofield @surveyprofield_checkbox
Feature: Validate creation and submit for "checkbox" elements using the principal combinations of settings (1 of 4)
  Setting I check in this test are:
      # required:                 0 - 1
      # Options (fixed):          milk\ncoffee\nbutter\nbread
      # Default:                  empty - coffee
      # Minimum required options: 0 - 2

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
    And I log in as "teacher1"

  @javascript
  Scenario: test checkbox element with the following settings: 0; milk\ncoffee\nbutter\nbread; empty; 0
      # required:                 0
      # Options (fixed):          milk\ncoffee\nbutter\nbread
      # Default:                  empty
      # Minimum required options: 0
    Given the following "activities" exist:
      | activity   | name           | intro              | course        | idnumber   |
      | surveypro  | Surveypro test | For testing backup | Checkbox item | surveypro1 |

    And I follow "Test submission for checkbox item"
    And I follow "Surveypro test"

    And I set the field "typeplugin" to "Checkbox"
    And I press "Add"

    And I set the following fields to these values:
      | Content                  | What do you usually get for breakfast? |
      | Required                 | 0                                      |
    And I fill the textarea "Options" with multiline content "milk\ncoffee\nbutter\nbread"
    And I set the following fields to these values:
      | Default                  |                                        |
      | Minimum required options | 0                                      |
    And I press "Add"

    And I log out
    When I log in as "student1"
    And I follow "Test submission for checkbox item"
    And I follow "Surveypro test"

    # Test number 1: Student flies over the answer
    And I press "New response"
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "1" submissions displayed
    # End of test number 1

    # Test number 2: Student submits a standard answer
    And I press "New response"
    And I set the following fields to these values:
      | id_surveypro_field_checkbox_1_0 | 1 |
      | id_surveypro_field_checkbox_1_3 | 1 |
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "2" submissions displayed
    # End of test number 2

    # Test number 3: Student chooses "No answer"
    And I press "New response"
    And I set the following fields to these values:
      | id_surveypro_field_checkbox_1_0        | 1 |
      | id_surveypro_field_checkbox_1_noanswer | 1 |
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "3" submissions displayed
    # End of test number 3

  @javascript
  Scenario: test checkbox element with the following settings: 0; milk\ncoffee\nbutter\nbread; empty; 2
      # required:                 0
      # Options (fixed):          milk\ncoffee\nbutter\nbread
      # Default:                  empty
      # Minimum required options: 2
    Given the following "activities" exist:
      | activity   | name           | intro              | course        | idnumber   |
      | surveypro  | Surveypro test | For testing backup | Checkbox item | surveypro1 |

    And I follow "Test submission for checkbox item"
    And I follow "Surveypro test"

    And I set the field "typeplugin" to "Checkbox"
    And I press "Add"

    And I set the following fields to these values:
      | Content                  | What do you usually get for breakfast? |
      | Required                 | 0                                      |
    And I fill the textarea "Options" with multiline content "milk\ncoffee\nbutter\nbread"
    And I set the following fields to these values:
      | Default                  |                                        |
      | Minimum required options | 2                                      |
    And I press "Add"

    And I log out
    When I log in as "student1"
    And I follow "Test submission for checkbox item"
    And I follow "Surveypro test"

    # Test number 4: Student flies over the answer
    And I press "New response"
    Then I should see "At least 2 checkboxes have to be selected"
    And I press "Submit"
    Then I should see "Please tick at least 2 options"
    And I set the following fields to these values:
      | id_surveypro_field_checkbox_1_0 | 1 |
    And I press "Submit"
    Then I should see "Please tick at least 2 options"
    And I set the following fields to these values:
      | id_surveypro_field_checkbox_1_3 | 1 |
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "1" submissions displayed
    # End of test number 4

    # Test number 5: Student submits a standard answer
    And I press "New response"
    Then I should see "At least 2 checkboxes have to be selected"
    And I set the following fields to these values:
      | id_surveypro_field_checkbox_1_0 | 1 |
      | id_surveypro_field_checkbox_1_3 | 1 |
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "2" submissions displayed
    # End of test number 5

    # Test number 6: Student chooses "No answer"
    And I press "New response"
    Then I should see "At least 2 checkboxes have to be selected"
    And I set the following fields to these values:
      | id_surveypro_field_checkbox_1_0        | 1 |
      | id_surveypro_field_checkbox_1_noanswer | 1 |
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "3" submissions displayed
    # End of test number 6
