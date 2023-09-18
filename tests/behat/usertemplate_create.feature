@mod @mod_surveypro
Feature: load, apply and save a usertemplate in order to test, among others, usertemplate creation
  In order to test partial item deletion
  As teacher1
  I overwite usertemplates with usertemplates

  @javascript @_file_upload
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
    And the following "activities" exist:
      | activity  | name                  | intro                              | course              |
      | surveypro | Create a usertemplate | Surveypro to cretae a usertemplate | Create usertemplate |

    When I am on the "Create a usertemplate" "mod_surveypro > User templates Import" page logged in as "teacher1"
    And I upload "mod/surveypro/tests/fixtures/usertemplate/parent-child_2015123000.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/fixtures/usertemplate/MMM_2015123000.xml" file to "Choose files to import" filemanager

    And I set the field "Sharing level" to "This course"
    And I press "Import"

    # now I am in the "Manage" page
    And I navigate to "User templates > Apply" in current page administration

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates       | (This course) MMM_2015123000.xml |
      | id_action_0          | 1                                |
    And I press "Apply"

    Then I should see "This is a demo survey to quickly see"

    And I navigate to "User templates > Save" in current page administration

    # now I am in the "Create" page
    And I set the following fields to these values:
      | Template name | MMM user template |
      | Sharing level | This course       |
    And I press "Save"

    Then I should see "MMM_user_template.xml"

    And I log out
