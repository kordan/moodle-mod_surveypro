@mod @mod_surveypro
Feature: load and apply usertemplates in order to test if they apply correctly
  In order to test if usertemplates apply correctly
  As teacher1
  I apply all available usertemplates

  @javascript @_file_upload
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
      | activity  | name      | intro                                | course                  |
      | surveypro | Apply all | Surveypro to apply all usertemplates | Apply each usertemplate |

    When I am on the "Apply all" "mod_surveypro > User templates import" page logged in as "teacher1"
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

    And I set the field "Sharing level" to "This course"
    And I press "Import"

    # now I am in the "Manage" page
    And I am on the "Apply all" "mod_surveypro > User templates apply" page

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (This course) age_only_2015123000.xml |
      | id_action_15   | 1                                     |
    And I press "Apply"

    # now I am in the "Manage" page
    And I am on the "Apply all" "mod_surveypro > User templates apply" page

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (This course) attachment_only_2015123000.xml |
      | id_action_15   | 1                                            |
    And I press "Apply"

    # now I am in the "Manage" page
    And I am on the "Apply all" "mod_surveypro > User templates apply" page

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (This course) autofill_only_2015123000.xml |
      | id_action_15   | 1                                          |
    And I press "Apply"

    # now I am in the "Manage" page
    And I am on the "Apply all" "mod_surveypro > User templates apply" page

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (This course) boolean_only_2015123000.xml |
      | id_action_15   | 1                                         |
    And I press "Apply"

    # now I am in the "Manage" page
    And I am on the "Apply all" "mod_surveypro > User templates apply" page

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (This course) checkbox_only_2015123000.xml |
      | id_action_15   | 1                                          |
    And I press "Apply"

    # now I am in the "Manage" page
    And I am on the "Apply all" "mod_surveypro > User templates apply" page

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (This course) date_only_2015123000.xml |
      | id_action_15   | 1                                      |
    And I press "Apply"

    # now I am in the "Manage" page
    And I am on the "Apply all" "mod_surveypro > User templates apply" page

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (This course) dateshort_only_2015123000.xml |
      | id_action_15   | 1                                           |
    And I press "Apply"

    # now I am in the "Manage" page
    And I am on the "Apply all" "mod_surveypro > User templates apply" page

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (This course) datetime_only_2015123000.xml |
      | id_action_15   | 1                                          |
    And I press "Apply"

    # now I am in the "Manage" page
    And I am on the "Apply all" "mod_surveypro > User templates apply" page

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (This course) integer_only_2015123000.xml |
      | id_action_15   | 1                                         |
    And I press "Apply"

    # now I am in the "Manage" page
    And I am on the "Apply all" "mod_surveypro > User templates apply" page

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (This course) multiselect_only_2015123000.xml |
      | id_action_15   | 1                                             |
    And I press "Apply"

    # now I am in the "Manage" page
    And I am on the "Apply all" "mod_surveypro > User templates apply" page

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (This course) numeric_only_2015123000.xml |
      | id_action_15   | 1                                         |
    And I press "Apply"

    # now I am in the "Manage" page
    And I am on the "Apply all" "mod_surveypro > User templates apply" page

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (This course) radiobutton_only_2015123000.xml |
      | id_action_15   | 1                                             |
    And I press "Apply"

    # now I am in the "Manage" page
    And I am on the "Apply all" "mod_surveypro > User templates apply" page

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (This course) rate_only_2015123000.xml |
      | id_action_15   | 1                                      |
    And I press "Apply"

    # now I am in the "Manage" page
    And I am on the "Apply all" "mod_surveypro > User templates apply" page

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (This course) recurrence_only_2015123000.xml |
      | id_action_15   | 1                                            |
    And I press "Apply"

    # now I am in the "Manage" page
    And I am on the "Apply all" "mod_surveypro > User templates apply" page

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (This course) select_only_2015123000.xml |
      | id_action_15   | 1                                        |
    And I press "Apply"

    # now I am in the "Manage" page
    And I am on the "Apply all" "mod_surveypro > User templates apply" page

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (This course) textarea_only_2015123000.xml |
      | id_action_15   | 1                                          |
    And I press "Apply"

    # now I am in the "Manage" page
    And I am on the "Apply all" "mod_surveypro > User templates apply" page

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (This course) textshort_only_2015123000.xml |
      | id_action_15   | 1                                           |
    And I press "Apply"

    # now I am in the "Manage" page
    And I am on the "Apply all" "mod_surveypro > User templates apply" page

    # now I am in the "Apply" page
    And I set the following fields to these values:
      | User templates | (This course) time_only_2015123000.xml |
      | id_action_15   | 1                                      |
    And I press "Apply"

    And I log out
