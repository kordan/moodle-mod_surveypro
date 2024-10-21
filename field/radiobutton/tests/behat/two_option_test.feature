@mod @mod_surveypro
Feature: Load and apply mum_or_dad usertemplates to test preview do not rise up errors
  In order to test correct preview
  As teacher1
  I load mum_or_dad usertemplates and ask for preview

  @javascript @_file_upload
  Scenario: Test mum or dad displays correctly
    Given the following "courses" exist:
      | fullname           | shortname  | category | groupmode |
      | Preview mum or dad | Mum or dad | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course     | role           |
      | teacher1 | Mum or dad | editingteacher |
    And the following "activities" exist:
      | activity  | name                  | intro                             | course     |
      | surveypro | Test two options only | Surveypro to test two options only | Mum or dad |

    When I am on the "Test two options only" "mod_surveypro > User templates from secondary navigation" page logged in as "teacher1"
    # now I am in the "Manage" page

    And I select "Import" from the "jump" singleselect
    And I upload "mod/surveypro/field/radiobutton/tests/fixtures/usertemplate/mum_or_dad.xml" file to "Choose files to import" filemanager

    And I set the field "Sharing level" to "This course"
    And I press "Import"

    And I am on the "Test two options only" "mod_surveypro > User templates from secondary navigation" page
    # now I am in the "Manage" page

    And I select "Apply" from the "jump" singleselect
    And I set the following fields to these values:
      | User templates | (This course) mum_or_dad.xml |
      | id_action_0    | 1                                 |
    And I press "Apply"

    # now I am in the Element > Manage page
    And I select "Preview" from the "jump" singleselect
    And I should see "Who do you love the most?"
