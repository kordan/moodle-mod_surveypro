@mod @mod_surveypro @surveyproformat @surveyprofield_fieldset
Feature: Create a fieldset item
  In order to verify fieldset items can be added to a survey
  As a teacher
  I add a fieldset item to a survey

  @javascript
  Scenario: Add fieldset item
    Given the following "courses" exist:
      | fullname          | shortname    | category | groupmode |
      | Add fieldset item | Add fieldset | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course       | role           |
      | teacher1 | Add fieldset | editingteacher |
    And the following "activities" exist:
      | activity  | name          | intro                             | course       |
      | surveypro | Fieldset test | To test addition of fieldset item | Add fieldset |
    And I am on the "Fieldset test" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1

    And I set the field "typeplugin" to "Fieldset"
    And I press "Add"

    And I expand all fieldsets
    And I set the field "Content" to "A bunch of items"
    And I press "Add"
