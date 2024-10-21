@mod @mod_surveypro
Feature: Load and apply usertemplates
  In order to test if usertemplates apply correctly
  As teacher1
  I apply all available usertemplates

  @javascript @_file_upload
  Scenario: Load and apply each usertemplate
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

    When I am on the "Apply all" "mod_surveypro > User templates from secondary navigation" page logged in as "teacher1"
    # now I am in the "Manage" page

    And I select "Import" from the "jump" singleselect
    And I upload "mod/surveypro/field/age/tests/fixtures/usertemplate/age_only_2024032800.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/field/fileupload/tests/fixtures/usertemplate/attachment_only_2024032800.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/field/autofill/tests/fixtures/usertemplate/autofill_only_2024032800.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/field/boolean/tests/fixtures/usertemplate/boolean_only_2024032800.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/field/checkbox/tests/fixtures/usertemplate/checkbox_only_2024032800.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/field/date/tests/fixtures/usertemplate/date_only_2024032800.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/field/shortdate/tests/fixtures/usertemplate/date_(short)_only_2024032800.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/field/datetime/tests/fixtures/usertemplate/date_and_time_only_2024032800.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/field/integer/tests/fixtures/usertemplate/integer_only_2024032800.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/field/multiselect/tests/fixtures/usertemplate/multiselect_only_2024032800.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/field/numeric/tests/fixtures/usertemplate/numeric_only_2024032800.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/field/radiobutton/tests/fixtures/usertemplate/radiobutton_only_2024032800.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/field/rate/tests/fixtures/usertemplate/rate_only_2024032800.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/field/recurrence/tests/fixtures/usertemplate/recurrence_only_2024032800.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/field/select/tests/fixtures/usertemplate/select_only_2024032800.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/field/textarea/tests/fixtures/usertemplate/textarea_only_2024032800.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/field/character/tests/fixtures/usertemplate/text_(short)_only_2024032800.xml" file to "Choose files to import" filemanager
    And I upload "mod/surveypro/field/time/tests/fixtures/usertemplate/time_only_2024032800.xml" file to "Choose files to import" filemanager

    And I set the field "Sharing level" to "This course"
    And I press "Import"

    And I am on the "Apply all" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Apply" from the "jump" singleselect
    And I set the following fields to these values:
      | User templates | (This course) age_only_2024032800.xml |
      | id_action_15   | 1                                     |
    And I press "Apply"

    And I am on the "Apply all" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Apply" from the "jump" singleselect
    And I set the following fields to these values:
      | User templates | (This course) attachment_only_2024032800.xml |
      | id_action_15   | 1                                            |
    And I press "Apply"

    And I am on the "Apply all" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Apply" from the "jump" singleselect
    And I set the following fields to these values:
      | User templates | (This course) autofill_only_2024032800.xml |
      | id_action_15   | 1                                          |
    And I press "Apply"

    And I am on the "Apply all" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Apply" from the "jump" singleselect
    And I set the following fields to these values:
      | User templates | (This course) boolean_only_2024032800.xml |
      | id_action_15   | 1                                         |
    And I press "Apply"

    And I am on the "Apply all" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Apply" from the "jump" singleselect
    And I set the following fields to these values:
      | User templates | (This course) checkbox_only_2024032800.xml |
      | id_action_15   | 1                                          |
    And I press "Apply"

    And I am on the "Apply all" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Apply" from the "jump" singleselect
    And I set the following fields to these values:
      | User templates | (This course) date_only_2024032800.xml |
      | id_action_15   | 1                                      |
    And I press "Apply"

    And I am on the "Apply all" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Apply" from the "jump" singleselect
    And I set the following fields to these values:
      | User templates | (This course) date_(short)_only_2024032800.xml |
      | id_action_15   | 1                                           |
    And I press "Apply"

    And I am on the "Apply all" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Apply" from the "jump" singleselect
    And I set the following fields to these values:
      | User templates | (This course) date_and_time_only_2024032800.xml |
      | id_action_15   | 1                                          |
    And I press "Apply"

    And I am on the "Apply all" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Apply" from the "jump" singleselect
    And I set the following fields to these values:
      | User templates | (This course) integer_only_2024032800.xml |
      | id_action_15   | 1                                         |
    And I press "Apply"

    And I am on the "Apply all" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Apply" from the "jump" singleselect
    And I set the following fields to these values:
      | User templates | (This course) multiselect_only_2024032800.xml |
      | id_action_15   | 1                                             |
    And I press "Apply"

    And I am on the "Apply all" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Apply" from the "jump" singleselect
    And I set the following fields to these values:
      | User templates | (This course) numeric_only_2024032800.xml |
      | id_action_15   | 1                                         |
    And I press "Apply"

    And I am on the "Apply all" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Apply" from the "jump" singleselect
    And I set the following fields to these values:
      | User templates | (This course) radiobutton_only_2024032800.xml |
      | id_action_15   | 1                                             |
    And I press "Apply"

    And I am on the "Apply all" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Apply" from the "jump" singleselect
    And I set the following fields to these values:
      | User templates | (This course) rate_only_2024032800.xml |
      | id_action_15   | 1                                      |
    And I press "Apply"

    And I am on the "Apply all" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Apply" from the "jump" singleselect
    And I set the following fields to these values:
      | User templates | (This course) recurrence_only_2024032800.xml |
      | id_action_15   | 1                                            |
    And I press "Apply"

    And I am on the "Apply all" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Apply" from the "jump" singleselect
    And I set the following fields to these values:
      | User templates | (This course) select_only_2024032800.xml |
      | id_action_15   | 1                                        |
    And I press "Apply"

    And I am on the "Apply all" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Apply" from the "jump" singleselect
    And I set the following fields to these values:
      | User templates | (This course) textarea_only_2024032800.xml |
      | id_action_15   | 1                                          |
    And I press "Apply"

    And I am on the "Apply all" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Apply" from the "jump" singleselect
    And I set the following fields to these values:
      | User templates | (This course) text_(short)_only_2024032800.xml |
      | id_action_15   | 1                                           |
    And I press "Apply"

    And I am on the "Apply all" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Apply" from the "jump" singleselect
    And I set the following fields to these values:
      | User templates | (This course) time_only_2024032800.xml |
      | id_action_15   | 1                                      |
    And I press "Apply"

    And I log out
