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
    And the following "activities" exist:
      | activity  | name                 | intro                             | course             | idnumber   |
      | surveypro | Apply a usertemplate | Surveypro to apply a usertemplate | Apply usertemplate | surveypro1 |
    And I log in as "teacher1"
    And I follow "To apply usertemplat"
    And I follow "Apply a usertemplate"

    And I navigate to "Import" node in "Surveypro administration > User templates"
    And I upload "mod/surveypro/tests/fixtures/usertemplate/parent-child_2015123000.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/fixtures/usertemplate/MMM_2015123000.xml" file to "Choose files to import" filemanager

    And I set the field "Sharing level" to "Course: To apply usertemplate"
    And I press "Import"

    # now I am in the "Manage" page
    And I navigate to "Apply" node in "Surveypro administration > User templates"

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates       | (Course) MMM_2015123000.xml |
      | id_action_1          | 1                           |
    And I press "Continue"

    # now I am in the Element > Manage page
    And I follow "hide_item_4"
    And I follow "hide_item_5"
    And I follow "hide_item_6"
    And I follow "hide_item_7"
    And I follow "hide_item_9"
    And I follow "hide_item_11"
    And I follow "hide_item_12"
    And I follow "hide_item_13"
    And I follow "hide_item_14"
    And I follow "hide_item_15"
    And I follow "hide_item_17"
    And I follow "hide_item_18"
    And I follow "hide_item_20"
    And I follow "hide_item_21"
    And I follow "hide_item_22"
    And I follow "hide_item_23"
    And I follow "hide_item_24"
    And I follow "hide_item_26"
    And I follow "hide_item_28"
    And I follow "hide_item_29"
    And I follow "hide_item_31"
    And I follow "hide_item_32"
    And I follow "hide_item_34"
    And I follow "hide_item_38"
    And I follow "hide_item_39"
    And I follow "hide_item_40"
    And I follow "hide_item_42"
    And I follow "hide_item_43"
    And I follow "hide_item_44"
    And I follow "hide_item_46"
    And I follow "hide_item_50"
    And I follow "hide_item_51"
    And I follow "hide_item_53"
    And I follow "hide_item_54"
    And I follow "hide_item_56"
    And I follow "hide_item_57"
    And I follow "hide_item_58"
    And I follow "hide_item_60"
    And I press "Yes, hide them all"

    And I navigate to "Apply" node in "Surveypro administration > User templates"
    And I set the following fields to these values:
      | User templates       | (Course) parent-child_2015123000.xml |
      | id_action_5          | 1                                    |
    And I press "Continue"

    Then I should see "This is a demo survey to quickly see"
    Then I should not see "How old is the person shown in the picture, in your personal opinion?"
    And I log out
