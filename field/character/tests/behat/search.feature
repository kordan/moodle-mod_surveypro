@mod @mod_surveypro @surveyprofield @surveyprofield_character
Feature: Search using one and two short text items
  In order to validate search form with one and two items
  As a student
  I search for submitted records.

  @javascript
  Scenario: Search using one and two character fields
    Given the following "courses" exist:
      | fullname                    | shortname   | category | numsections |
      | Test short text search form | Search form | 0        | 1           |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course      | role           |
      | teacher1 | Search form | editingteacher |
      | student1 | Search form | student        |
    And the following "activities" exist:
      | activity  | name                        | intro                  | course      |
      | surveypro | Short text search form test | For searching purposes | Search form |
    And I am on the "Short text search form test" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1

    # Create a two items long surveypro
    And I set the field "typeplugin" to "Text (short)"
    And I press "Add"
    And I expand all fieldsets

    Given I set the following fields to these values:
      | Content     | First Name |
      | Required    | 1          |
      | Search form | 1          |
    And I press "Add"

    And I set the field "typeplugin" to "Text (short)"
    And I press "Add"
    And I expand all fieldsets

    Given I set the following fields to these values:
      | Content  | Last Name |
      | Required | 1         |
    And I press "Add"

    And I am on the "Short text search form test" "mod_surveypro > Surveypro from secondary navigation" page logged in as student1

    # Add the first record
    And I press "New response"
    And I set the field "First Name" to "Johann"
    And I set the field "Last Name" to "Bernoulli"
    And I press "Submit"

    # Add the second record
    And I press "New response"
    And I set the field "First Name" to "Jakob"
    And I set the field "Last Name" to "Bernoulli"
    And I press "Submit"

    # 1st search for submitted records
    Given I am on the "Short text search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With only one field in the search form, "Ignore me" must not be in the form.
    And I should not see "*"

    And I set the field "First Name" to "Johann"
    And I press "Search"
    Then I should see "1" submissions

    # Add the second search field
    And I am on the "Short text search form test" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1
    And I click on "//a[contains(@class,'quickeditlink')]//img[contains(@id, 'addtosearch_item_2')]" "xpath_element"

    # 2nd search for submitted records
    Given I am on the "Short text search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With more than one field in the search form, "Ignore me" must be in the form.
    # I test it is in the form setting it to 0

    And I set the field "id_field_character_2_ignoreme" to "0"
    And I set the field "Last Name" to "Bernoulli"
    And I press "Search"
    Then I should see "2" submissions

    # 3rd search for submitted records
    Given I am on the "Short text search form test" "surveypro activity" page logged in as student1
    And I select "Search" from the "jump" singleselect

    # With more than one field in the search form, "Ignore me" must be in the form.
    # I test it is in the form setting it to 0

    And I set the field "id_field_character_1_ignoreme" to "0"
    And I set the field "First Name" to "Jakob"
    And I set the field "id_field_character_2_ignoreme" to "0"
    And I set the field "Last Name" to "Bernoulli"
    And I press "Search"
    Then I should see "1" submissions
