@mod @mod_surveypro
Feature: Load and apply usertemplates in order to test if they apply correctly
  In order to test if usertemplates apply correctly
  As teacher1
  I apply all available usertemplates

  @javascript
  Scenario: load and apply each usertemplate
    Given the following "courses" exist:
      | fullname                   | shortname               | category | groupmode |
      | To apply all usertemplates | Apply each usertemplate | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                  | role           |
      | teacher1 | Apply each usertemplate | editingteacher |
    And the following "activities" exist:
      | activity  | name      | intro                                | course                  | idnumber   |
      | surveypro | Apply all | Surveypro to apply all usertemplates | Apply each usertemplate | surveypro1 |
    And I log in as "teacher1"
    And I am on "To apply all usertemplates" course homepage
    And I follow "Apply all"

    And I navigate to "User templates > Import" in current page administration
    And I upload "mod/surveypro/tests/fixtures/usertemplate/age_only_2015123000.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/fixtures/usertemplate/attachment_only_2015123000.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/fixtures/usertemplate/autofill_only_2015123000.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/fixtures/usertemplate/boolean_only_2015123000.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/fixtures/usertemplate/checkbox_only_2015123000.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/fixtures/usertemplate/date_only_2015123000.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/fixtures/usertemplate/dateshort_only_2015123000.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/fixtures/usertemplate/datetime_only_2015123000.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/fixtures/usertemplate/integer_only_2015123000.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/fixtures/usertemplate/multiselect_only_2015123000.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/fixtures/usertemplate/numeric_only_2015123000.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/fixtures/usertemplate/radiobutton_only_2015123000.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/fixtures/usertemplate/rate_only_2015123000.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/fixtures/usertemplate/recurrence_only_2015123000.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/fixtures/usertemplate/select_only_2015123000.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/fixtures/usertemplate/textarea_only_2015123000.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/fixtures/usertemplate/textshort_only_2015123000.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/tests/fixtures/usertemplate/time_only_2015123000.xml" file to "Choose files to import" filemanager

    And I set the field "Sharing level" to "Course: To apply all usertemplates"
    And I press "Import"

    # now I am in the "Manage" page
    And I navigate to "User templates > Apply" in current page administration

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (Course) age_only_2015123000.xml |
      | id_action_15   | 1                                |
    And I press "Apply"

    # now I am in the "Manage" page
    And I navigate to "User templates > Apply" in current page administration

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (Course) attachment_only_2015123000.xml |
      | id_action_15   | 1                                       |
    And I press "Apply"

    # now I am in the "Manage" page
    And I navigate to "User templates > Apply" in current page administration

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (Course) autofill_only_2015123000.xml |
      | id_action_15   | 1                                     |
    And I press "Apply"

    # now I am in the "Manage" page
    And I navigate to "User templates > Apply" in current page administration

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (Course) boolean_only_2015123000.xml |
      | id_action_15   | 1                                    |
    And I press "Apply"

    # now I am in the "Manage" page
    And I navigate to "User templates > Apply" in current page administration

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (Course) checkbox_only_2015123000.xml |
      | id_action_15   | 1                                     |
    And I press "Apply"

    # now I am in the "Manage" page
    And I navigate to "User templates > Apply" in current page administration

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (Course) date_only_2015123000.xml |
      | id_action_15   | 1                                 |
    And I press "Apply"

    # now I am in the "Manage" page
    And I navigate to "User templates > Apply" in current page administration

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (Course) dateshort_only_2015123000.xml |
      | id_action_15   | 1                                      |
    And I press "Apply"

    # now I am in the "Manage" page
    And I navigate to "User templates > Apply" in current page administration

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (Course) datetime_only_2015123000.xml |
      | id_action_15   | 1                                     |
    And I press "Apply"

    # now I am in the "Manage" page
    And I navigate to "User templates > Apply" in current page administration

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (Course) integer_only_2015123000.xml |
      | id_action_15   | 1                                    |
    And I press "Apply"

    # now I am in the "Manage" page
    And I navigate to "User templates > Apply" in current page administration

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (Course) multiselect_only_2015123000.xml |
      | id_action_15   | 1                                        |
    And I press "Apply"

    # now I am in the "Manage" page
    And I navigate to "User templates > Apply" in current page administration

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (Course) numeric_only_2015123000.xml |
      | id_action_15   | 1                                    |
    And I press "Apply"

    # now I am in the "Manage" page
    And I navigate to "User templates > Apply" in current page administration

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (Course) radiobutton_only_2015123000.xml |
      | id_action_15   | 1                                        |
    And I press "Apply"

    # now I am in the "Manage" page
    And I navigate to "User templates > Apply" in current page administration

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (Course) rate_only_2015123000.xml |
      | id_action_15   | 1                                 |
    And I press "Apply"

    # now I am in the "Manage" page
    And I navigate to "User templates > Apply" in current page administration

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (Course) recurrence_only_2015123000.xml |
      | id_action_15   | 1                                       |
    And I press "Apply"

    # now I am in the "Manage" page
    And I navigate to "User templates > Apply" in current page administration

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (Course) select_only_2015123000.xml |
      | id_action_15   | 1                                   |
    And I press "Apply"

    # now I am in the "Manage" page
    And I navigate to "User templates > Apply" in current page administration

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (Course) textarea_only_2015123000.xml |
      | id_action_15   | 1                                     |
    And I press "Apply"

    # now I am in the "Manage" page
    And I navigate to "User templates > Apply" in current page administration

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (Course) textshort_only_2015123000.xml |
      | id_action_15   | 1                                      |
    And I press "Apply"

    # now I am in the "Manage" page
    And I navigate to "User templates > Apply" in current page administration

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (Course) time_only_2015123000.xml |
      | id_action_15   | 1                                 |
    And I press "Apply"

    And I log out
