@mod @mod_surveypro
Feature: Load and apply usertemplates in order to test, among others, partial item deletion
  In order to test partial item deletion
  As teacher1
  I overwite usertemplates with usertemplates

  @javascript
  Scenario: load and apply a usertemplate
    Given the following "courses" exist:
      | fullname              | shortname | category | groupmode |
      | To apply usertemplate | C1        | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | teacher  | teacher1@asd.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |

    And I log in as "teacher1"
    And I follow "To apply usertemplate"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Survey name | My user template                                                                               |
      | Description | This is a surveypro to test partial item deletion |
    And I follow "My user template"

    And I follow "User templates"
    And I navigate to "Import" node in "Surveypro administration > User templates"
    And I upload "mod/surveypro/tests/usertemplate/parent-child_2014090401.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/usertemplate/MMM_2014090401.xml" file to "Choose files to import" filemanager

    And I set the field "Sharing level" to "Course: To apply usertemplate"
    And I press "Import template"
    And I follow "Apply"

    # now I am in the apply page
    And I set the following fields to these values:
      | User templates       | MMM_2014090401.xml |
      | id_action_1          | 1                  |
    And I press "Continue"

    # now I am in the Element > Manage page
    And I follow "hide_368003"
    And I follow "hide_368004"
    And I follow "hide_368005"
    And I follow "hide_368006"
    And I follow "hide_368008"
    And I follow "hide_368010"
    And I follow "hide_368011"
    And I follow "hide_368012"
    And I follow "hide_368013"
    And I follow "hide_368014"
    And I follow "hide_368016"
    And I follow "hide_368017"
    And I follow "hide_368019"
    And I follow "hide_368020"
    And I follow "hide_368021"
    And I follow "hide_368022"
    And I follow "hide_368023"
    And I follow "hide_368025"
    And I follow "hide_368027"
    And I follow "hide_368028"
    And I follow "hide_368030"
    And I follow "hide_368031"
    And I follow "hide_368033"
    And I follow "hide_368037"
    And I follow "hide_368038"
    And I follow "hide_368039"
    And I follow "hide_368041"
    And I follow "hide_368042"
    And I follow "hide_368043"
    And I follow "hide_368045"
    And I follow "hide_368049"
    And I follow "hide_368050"
    And I follow "hide_368052"
    And I follow "hide_368053"
    And I follow "hide_368055"
    And I follow "hide_368056"
    And I follow "hide_368057"
    And I follow "hide_368059"
    And I press "Yes, hide them all"

    And I navigate to "Apply" node in "Surveypro administration > User templates"
    And I set the following fields to these values:
      | User templates       | parent-child_2014090401.xml |
      | id_action_5          | 1                           |
    And I press "Continue"

    Then I should see "This is a demo survey I made to let you quickly"
    Then I should not see "How old is the person shown in the picture, in your personal opinion?"
    And I log out
