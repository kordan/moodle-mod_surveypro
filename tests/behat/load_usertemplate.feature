@mod @mod_surveypro
Feature: Load and apply usertemplates in order to test, among others, partial item deletion
  In order to test partial item deletion
  As teacher1
  I overwite usertemplates with usertemplates

  @javascript
  Scenario: test partial item deletion
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
      | Survey name | Simple test                                                                               |
      | Description | This is a surveypro to test partial item deletion |
    And I follow "Simple test"

    And I follow "User templates"
    And I navigate to "Import" node in "Survey administration > User templates"
    And I upload "mod/surveypro/tests/usertemplate/parent-child_2014072801.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/usertemplate/MMM_2014072801.xml" file to "Choose files to import" filemanager

    And I set the field "Sharing level" to "Course: To apply usertemplate"
    And I press "Import template"
    And I follow "Apply"

    # now I am in the apply page
    And I set the following fields to these values:
      | User templates       | MMM_2014072801.xml |
      | id_action_1          | 1                  |
    And I press "Continue"

    # now I am in the Element > Manage page
    And I follow "show_367003"
    And I follow "show_367004"
    And I follow "show_367005"
    And I follow "show_367006"
    And I follow "show_367008"
    And I follow "show_367010"
    And I follow "show_367011"
    And I follow "show_367012"
    And I follow "show_367013"
    And I follow "show_367014"
    And I follow "show_367016"
    And I follow "show_367017"
    And I follow "show_367019"
    And I follow "show_367020"
    And I follow "show_367021"
    And I follow "show_367022"
    And I follow "show_367023"
    And I follow "show_367025"
    And I follow "show_367027"
    And I follow "show_367028"
    And I follow "show_367030"
    And I follow "show_367031"
    And I follow "show_367033"
    And I follow "show_367037"
    And I follow "show_367038"
    And I follow "show_367039"
    And I follow "show_367041"
    And I follow "show_367042"
    And I follow "show_367043"
    And I follow "show_367045"
    And I follow "show_367049"
    And I follow "show_367050"
    And I follow "show_367052"
    And I follow "show_367053"
    And I follow "show_367055"
    And I follow "show_367056"
    And I follow "show_367057"
    And I follow "show_367059"
    And I press "Yes, hide them all"

    And I navigate to "Apply" node in "Survey administration > User templates"
    And I set the following fields to these values:
      | User templates       | parent-child_2014072801.xml |
      | id_action_5          | 1                           |
    And I press "Continue"

    Then I should see "This is a demo survey I made to let you quickly"
    Then I should not see "How old is the person shown in the picture, in your personal opinion?"
    And I log out
