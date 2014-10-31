@mod @mod_surveypro
Feature: Load and apply usertemplates in order to test, among others, partial item deletion
  In order to test partial item deletion
  As teacher1
  I overwite usertemplates with usertemplates

  @javascript
  Scenario: load and apply a usertemplate
    Given the following "courses" exist:
      | fullname              | shortname          | category | groupmode |
      | To apply usertemplate | Apply usertemplate | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course             | role           |
      | teacher1 | Apply usertemplate | editingteacher |

    And I log in as "teacher1"
    And I follow "To apply usertemplate"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Surveypro name | My user template                                  |
      | Description    | This is a surveypro to test partial item deletion |
    And I follow "My user template"

    And I navigate to "Import" node in "Surveypro administration > User templates"
    And I upload "mod/surveypro/tests/usertemplate/parent-child_2014090401.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/usertemplate/MMM_2014090401.xml" file to "Choose files to import" filemanager

    And I set the field "Sharing level" to "Course: To apply usertemplate"
    And I press "Import"

    # now I am in the "Manage" page
    And I navigate to "Apply" node in "Surveypro administration > User templates"

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates       | (Course) MMM_2014090401.xml |
      | id_action_1          | 1                           |
    And I press "Continue"

    # now I am in the Element > Manage page
    And I follow "hide_398003"
    And I follow "hide_398004"
    And I follow "hide_398005"
    And I follow "hide_398006"
    And I follow "hide_398008"
    And I follow "hide_398010"
    And I follow "hide_398011"
    And I follow "hide_398012"
    And I follow "hide_398013"
    And I follow "hide_398014"
    And I follow "hide_398016"
    And I follow "hide_398017"
    And I follow "hide_398019"
    And I follow "hide_398020"
    And I follow "hide_398021"
    And I follow "hide_398022"
    And I follow "hide_398023"
    And I follow "hide_398025"
    And I follow "hide_398027"
    And I follow "hide_398028"
    And I follow "hide_398030"
    And I follow "hide_398031"
    And I follow "hide_398033"
    And I follow "hide_398037"
    And I follow "hide_398038"
    And I follow "hide_398039"
    And I follow "hide_398041"
    And I follow "hide_398042"
    And I follow "hide_398043"
    And I follow "hide_398045"
    And I follow "hide_398049"
    And I follow "hide_398050"
    And I follow "hide_398052"
    And I follow "hide_398053"
    And I follow "hide_398055"
    And I follow "hide_398056"
    And I follow "hide_398057"
    And I follow "hide_398059"
    And I press "Yes, hide them all"

    And I navigate to "Apply" node in "Surveypro administration > User templates"
    And I set the following fields to these values:
      | User templates       | parent-child_2014090401.xml |
      | id_action_5          | 1                           |
    And I press "Continue"

    Then I should see "This is a demo survey I made to let you quickly"
    Then I should not see "How old is the person shown in the picture, in your personal opinion?"
    And I log out
