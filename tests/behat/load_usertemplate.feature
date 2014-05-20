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
    And I upload "mod/surveypro/tests/usertemplate/parent-child_2014051601.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/usertemplate/MMM_2014051601.xml" file to "Choose files to import" filemanager

    And I set the field "Sharing level" to "Course: To apply usertemplate"
    And I press "Import template"
    And I follow "Apply"

    # now I am in the apply page
    And I set the following fields to these values:
      | User templates       | MMM_2014051601.xml |
      | id_action_1          | 1                  |
    And I press "Continue"

    # now I am in the Element > Manage page
    And I follow "show_4"
    And I follow "show_5"
    And I follow "show_6"
    And I follow "show_7"
    And I follow "show_9"
    And I follow "show_11"
    And I follow "show_12"
    And I follow "show_13"
    And I follow "show_14"
    And I follow "show_15"
    And I follow "show_17"
    And I follow "show_18"
    And I follow "show_20"
    And I follow "show_21"
    And I follow "show_22"
    And I follow "show_23"
    And I follow "show_24"
    And I follow "show_26"
    And I follow "show_28"
    And I follow "show_29"
    And I follow "show_31"
    And I follow "show_32"
    And I follow "show_34"
    And I follow "show_38"
    And I follow "show_39"
    And I follow "show_40"
    And I follow "show_42"
    And I follow "show_43"
    And I follow "show_44"
    And I follow "show_46"
    And I follow "show_50"
    And I follow "show_51"
    And I follow "show_53"
    And I follow "show_54"
    And I follow "show_56"
    And I follow "show_57"
    And I follow "show_58"
    And I follow "show_60"
    And I press "Yes, hide them all"

    And I navigate to "Apply" node in "Survey administration > User templates"
    And I set the following fields to these values:
      | User templates       | parent-child_2014051601.xml |
      | id_action_5          | 1                           |
    And I press "Continue"

    Then I should see "This is a demo survey I made to let you quickly"
    Then I should not see "How old is the person shown in the picture, in your personal opinion?"
    And I log out
