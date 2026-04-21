@mod @mod_surveypro
Feature: Duplicate a response
  In order to test that response duplication works fine
  As student1
  I fill a surveypro and go to duplicate the submitted response

  @javascript
  Scenario: Duplicate a response
    Given the following "courses" exist:
      | fullname           | shortname          | category | groupmode |
      | Duplicate response | Duplicate response | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | student1  | user1    | student1@nowhere.net |
    And the following "permission overrides" exist:
      | capability                            | permission | role    | contextlevel | reference          |
      | mod/surveypro:editownsubmissions      | Allow      | student | Course       | Duplicate response |
      | mod/surveypro:duplicateownsubmissions | Allow      | student | Course       | Duplicate response |
    And the following "course enrolments" exist:
      | user     | course             | role           |
      | student1 | Duplicate response | student        |
    And the following "activities" exist:
      | activity  | name                  | intro                     | course             |
      | surveypro | Duplicate response sp | Test response duplication | Duplicate response |
    And surveypro "Duplicate response sp" has the following items:
      | type  | plugin  |
      | field | boolean |
    And I am on the "Duplicate response sp" "surveypro activity" page logged in as student1

    And I press "New response"
    And I set the field "Is it true?" to "Yes"
    And I press "Submit"
    And I press "Continue to responses list"

    # Duplicate my original response

    And I click action "Duplicate" on item 1
    Then I should see "Are you sure you want to duplicate the response created on"
    Then I should see "and never modified?"
    And I press "Continue"

    # Edit the duplicated response
    And I click action "Edit" on item 2
    And I set the field "Is it true?" to "No"
    And I press "Submit"

    And I press "Continue to responses list"
    # Duplicate my original edited response
    And I click action "Duplicate" on item 2
    Then I should see "Are you sure you want to duplicate the response created on"
    Then I should see "and modified on"
    And I press "No"

    And I log out

    And I am on the "Duplicate response sp" "surveypro activity" page logged in as admin
    And I select "Responses" from the "jump" singleselect
    # Duplicate other original response
    And I click action "Duplicate" on item 1
    Then I should see "Are you sure you want to duplicate the response owned by student1 user1, created on"
    Then I should see "and never modified?"
    And I press "No"

    # Duplicate other modified response
    And I click action "Duplicate" on item 2
    Then I should see "Are you sure you want to duplicate the response owned by student1 user1, created on"
    Then I should see "and modified on"
    And I press "No"

    And I log out
