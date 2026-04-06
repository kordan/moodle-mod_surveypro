@mod @mod_surveypro
Feature: Test anonymous surveypro are really anonymous
  In order to test that an anonymous surveypro is really anonymous
  As teacher
  I go to look for reports and pages showwing user names

  @javascript
  Scenario: Anonymous surveypro and user with alwaysseeowner capability
    Given the following "courses" exist:
      | fullname         | shortname        | category | groupmode |
      | Anonymous course | Anonymous course | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course           | role           |
      | teacher1 | Anonymous course | editingteacher |
    And the following "activities" exist:
      | activity  | name                | intro               | course           |
      | surveypro | Anonymous surveypro | Anonymous surveypro | Anonymous course |
    And surveypro "Anonymous surveypro" has the following items:
      | type  | plugin  |
      | field | boolean |
    And I am on the "Anonymous surveypro" "Activity editing" page logged in as teacher1

    And I expand all fieldsets
    And I set the field "Anonymous responses" to "1"
    And I press "Save and display"

    And I am on the "Anonymous surveypro" "mod_surveypro > Reports from secondary navigation" page logged in as teacher1
    And I select "Users per count of responses" from the "jump" singleselect
    And I select "Responses per user" from the "jump" singleselect
    And I select "Late users" from the "jump" singleselect
    And I select "Frequency distribution" from the "jump" singleselect
    And I select "Attachments overview" from the "jump" singleselect
    And I log out

  @javascript
  Scenario: Not anonymous surveypro and user with alwaysseeowner capability
    Given the following "courses" exist:
      | fullname         | shortname        | category | groupmode |
      | Anonymous course | Anonymous course | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course           | role           |
      | teacher1 | Anonymous course | editingteacher |
    And the following "activities" exist:
      | activity  | name                | intro               | course           |
      | surveypro | Anonymous surveypro | Anonymous surveypro | Anonymous course |
    And surveypro "Anonymous surveypro" has the following items:
      | type  | plugin  |
      | field | boolean |
    And I am on the "Anonymous surveypro" "mod_surveypro > Reports from secondary navigation" page logged in as teacher1

    And I select "Users per count of responses" from the "jump" singleselect
    And I select "Responses per user" from the "jump" singleselect
    And I select "Late users" from the "jump" singleselect
    And I select "Frequency distribution" from the "jump" singleselect
    And I select "Attachments overview" from the "jump" singleselect
    And I log out

  @javascript
  Scenario: Anonymous surveypro and user without alwaysseeowner capability
    Given the following "courses" exist:
      | fullname         | shortname        | category | groupmode |
      | Anonymous course | Anonymous course | 0        | 0         |
    And the following "roles" exist:
      | shortname  | name       | archetype |
      | lowteacher | lowteacher | teacher   |
    And the following "permission overrides" exist:
      | capability                   | permission | role           | contextlevel | reference        |
      | mod/surveypro:alwaysseeowner | Prevent    | editingteacher | Course       | Anonymous course |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course           | role           |
      | teacher1 | Anonymous course | editingteacher |
    And the following "activities" exist:
      | activity  | name                | intro               | course           |
      | surveypro | Anonymous surveypro | Anonymous surveypro | Anonymous course |
    And surveypro "Anonymous surveypro" has the following items:
      | type  | plugin  |
      | field | boolean |
    And I am on the "Anonymous surveypro" "Activity editing" page logged in as teacher1

    And I expand all fieldsets
    And I set the field "Anonymous responses" to "1"
    And I press "Save and display"

    And I am on the "Anonymous surveypro" "mod_surveypro > Reports from secondary navigation" page logged in as teacher1
    And I select "Users per count of responses" from the "jump" singleselect
    And I select "Frequency distribution" from the "jump" singleselect
    And I log out

  @javascript
  Scenario: Not anonymous surveypro and user without alwaysseeowner capability
    Given the following "courses" exist:
      | fullname         | shortname        | category | groupmode |
      | Anonymous course | Anonymous course | 0        | 0         |
    And the following "roles" exist:
      | shortname  | name       | archetype |
      | lowteacher | lowteacher | teacher   |
    And the following "permission overrides" exist:
      | capability                   | permission | role           | contextlevel | reference        |
      | mod/surveypro:alwaysseeowner | Prevent    | editingteacher | Course       | Anonymous course |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course           | role           |
      | teacher1 | Anonymous course | editingteacher |
    And the following "activities" exist:
      | activity  | name                | intro               | course           |
      | surveypro | Anonymous surveypro | Anonymous surveypro | Anonymous course |
    And surveypro "Anonymous surveypro" has the following items:
      | type  | plugin  |
      | field | boolean |
    And I am on the "Anonymous surveypro" "mod_surveypro > Reports from secondary navigation" page logged in as teacher1

    And I select "Users per count of responses" from the "jump" singleselect
    And I select "Responses per user" from the "jump" singleselect
    And I select "Late users" from the "jump" singleselect
    And I select "Frequency distribution" from the "jump" singleselect
    And I select "Attachments overview" from the "jump" singleselect
    And I log out
