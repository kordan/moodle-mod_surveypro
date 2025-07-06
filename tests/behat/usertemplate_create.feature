@mod @mod_surveypro
Feature: Create a usertemplate
  In order to test partial item deletion
  As teacher1
  I overwite usertemplates with usertemplates

  @javascript @_file_upload
  Scenario: Load and save a usertemplate
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

    When I am on the "Create a usertemplate" "mod_surveypro > User templates from secondary navigation" page logged in as "teacher1"
    # now I am in the "Manage" page

    And I select "Import" from the "jump" singleselect
    And I upload "mod/surveypro/tests/fixtures/usertemplate/parent-child_2024032800.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/fixtures/usertemplate/MMM_2024032800.xml" file to "Choose files to import" filemanager

    And I set the field "Sharing level" to "This course"
    And I press "Import"

    And I am on the "Create a usertemplate" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Apply" from the "jump" singleselect
    And I set the following fields to these values:
      | User templates | (This course) MMM_2024032800.xml |
      | id_action_0    | 1                                |
    And I press "Apply"

    Then I should see "This is a demo survey to quickly see"

    And I am on the "Create a usertemplate" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Save" from the "jump" singleselect
    And I set the following fields to these values:
      | Template name | MMM user template |
      | Sharing level | This course       |
    And I press "Save"

    Then I should see "MMM_user_template.xml"

    And I log out
