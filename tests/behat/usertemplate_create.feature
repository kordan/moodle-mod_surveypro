@mod @mod_surveypro
Feature: Load, apply and save a usertemplate in order to test, among others, usertemplate creation
  In order to test partial item deletion
  As teacher1
  I overwite usertemplates with usertemplates

  @javascript
  Scenario: load and save a usertemplate
    Given the following "courses" exist:
      | fullname               | shortname           | category | groupmode |
      | To create usertemplate | Create usertemplate | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course              | role           |
      | teacher1 | Create usertemplate | editingteacher |

    And I log in as "teacher1"
    And I follow "To create usertemplate"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Name        | Create a usertemplate                        |
      | Description | This is a surveypro to cretae a usertemplate |
    And I follow "Create a usertemplate"

    And I navigate to "Import" node in "Surveypro administration > User templates"
    And I upload "mod/surveypro/tests/fixtures/usertemplate/parent-child_2015123000.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/fixtures/usertemplate/MMM_2015123000.xml" file to "Choose files to import" filemanager

    And I set the field "Sharing level" to "Course: To create usertemplate"
    And I press "Import"

    # now I am in the "Manage" page
    And I navigate to "Apply" node in "Surveypro administration > User templates"

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates       | (Course) MMM_2015123000.xml |
      | id_action_1          | 1                           |
    And I press "Continue"

    Then I should see "This is a demo survey to quickly see"

    And I navigate to "Save" node in "Surveypro administration > User templates"

    # now I am in the "Create" page
    And I set the following fields to these values:
      | Template name | MMM user template              |
      | Sharing level | Course: To create usertemplate |
    And I press "Continue"

    Then I should see "MMM_user_template.xml"
    And I log out
