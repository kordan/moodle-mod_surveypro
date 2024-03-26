@mod @mod_surveypro
Feature: Load and apply usertemplates to test partial item deletion
  In order to test partial item deletion
  As teacher1
  I overwite usertemplates with usertemplates

  @javascript @_file_upload
  Scenario: Load and apply a usertemplate
    Given the following "courses" exist:
      | fullname              | shortname          | category | groupmode |
      | To apply usertemplate | Apply usertemplate | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course             | role           |
      | teacher1 | Apply usertemplate | editingteacher |
    And the following "activities" exist:
      | activity  | name                 | intro                             | course             |
      | surveypro | Apply a usertemplate | Surveypro to apply a usertemplate | Apply usertemplate |

    When I am on the "Apply a usertemplate" "mod_surveypro > User templates from secondary navigation" page logged in as "teacher1"
    # now I am in the "Manage" page

    And I select "Import" from the "jump" singleselect
    And I upload "mod/surveypro/tests/fixtures/usertemplate/parent-child_20240227.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/fixtures/usertemplate/MMM_20240227.xml" file to "Choose files to import" filemanager

    And I set the field "Sharing level" to "This course"
    And I press "Import"

    And I am on the "Apply a usertemplate" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Apply" from the "jump" singleselect
    And I set the following fields to these values:
      | User templates | (This course) MMM_20240227.xml |
      | id_action_0    | 1                                |
    And I press "Apply"

    # now I am in the Element > Manage page
    And I follow "hide_item_4"
    And I follow "hide_item_5"
    And I follow "hide_item_6"
    And I follow "hide_item_7"
    And I follow "hide_item_60"
    And I press "Continue"

    And I am on the "Apply a usertemplate" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Apply" from the "jump" singleselect
    And I set the following fields to these values:
      | User templates | (This course) parent-child_20240227.xml |
      | id_action_17   | 1                                         |
    And I press "Apply"

    Then I should see "This is a demo survey to quickly see"
    Then I should not see "How old is the person shown in the picture, in your personal opinion?"
    And I log out
